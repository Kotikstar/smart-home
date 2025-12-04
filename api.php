<?php
require 'db.php'; // $pdo для базы пропусков
require 'config.php'; // $conn для базы топливной системы (mysqli)
require 'functions.php';
require 'auth.php';

ensureSession();

function jsonResponse($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function ensureFuelRow(mysqli $conn): void {
    $check = $conn->query('SELECT id FROM fuel LIMIT 1');
    if ($check && $check->num_rows === 0) {
        $conn->query("INSERT INTO fuel (amount) VALUES (0)");
    }
}

function ensureServiceRows(mysqli $conn): void {
    $types = ['coarse', 'fine'];
    foreach ($types as $type) {
        $stmt = $conn->prepare('SELECT id FROM service WHERE type = ? LIMIT 1');
        $stmt->bind_param('s', $type);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $interval = 1000;
            $insert = $conn->prepare('INSERT INTO service (type, status, last_serviced, liters_at_service, interval_liters) VALUES (?, "ok", NOW(), 0, ?)');
            $insert->bind_param('sd', $type, $interval);
            $insert->execute();
        }
    }
}

function getTotalDispensed(mysqli $conn): float {
    $res = $conn->query("SELECT COALESCE(SUM(amount),0) AS total FROM logs WHERE type='dispense'");
    return $res ? (float)$res->fetch_assoc()['total'] : 0.0;
}

// Проверка пропуска по номеру (обратная совместимость)
if (isset($_GET['plate']) && !isset($_GET['resource'])) {
    if (!currentUserId()) {
        http_response_code(401);
        header('Content-Type: text/plain');
        echo 'auth';
        exit;
    }
    header('Content-Type: text/plain');
    $plate = preg_replace('/\s+/', '', $_GET['plate']);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM passes WHERE license_plate = ? AND (pass_type = 'permanent' OR (pass_type = 'temporary' AND end_time > NOW()))");
    $stmt->execute([$plate]);
    $exists = $stmt->fetchColumn();
    echo $exists ? '1' : '0';
    exit;
}

$resource = $_GET['resource'] ?? null;
$action = $_GET['action'] ?? null;

if (!$resource) {
    jsonResponse(['error' => 'Укажите параметр resource']);
}

if (!currentUserId()) {
    jsonResponse(['error' => 'Требуется вход по passkey'], 401);
}

switch ($resource) {
    case 'fuel':
        ensureFuelRow($conn);
        if ($action === 'update') {
            $delta = (float)($_GET['delta'] ?? 0);
            $stmt = $conn->prepare('UPDATE fuel SET amount = GREATEST(0, amount + ?) LIMIT 1');
            $stmt->bind_param('d', $delta);
            $stmt->execute();
            // Лог пополнения/списания топлива без привязки к карте
            $type = $delta >= 0 ? 'refill' : 'dispense';
            $amount = abs($delta);
            if ($amount > 0) {
                $stmt = $conn->prepare('INSERT INTO logs (card_id, amount, type) VALUES (NULL, ?, ?)');
                $stmt->bind_param('ds', $amount, $type);
                $stmt->execute();
            }
        }
        $res = $conn->query('SELECT amount FROM fuel LIMIT 1');
        $data = $res ? $res->fetch_assoc() : ['amount' => 0];
        jsonResponse($data);
        break;

    case 'cards':
        if ($action === 'add') {
            $name = $_GET['name'] ?? '';
            $identifier = $_GET['identifier'] ?? '';
            $limit = (float)($_GET['limit'] ?? 0);
            $stmt = $conn->prepare('INSERT INTO cards (name, identifier, fuel_limit, used) VALUES (?, ?, ?, 0)');
            $stmt->bind_param('ssd', $name, $identifier, $limit);
            $stmt->execute();
            jsonResponse(['success' => true]);
        } elseif ($action === 'refill') {
            $id = (int)($_GET['id'] ?? 0);
            $amount = (float)($_GET['amount'] ?? 0);
            $stmt = $conn->prepare('UPDATE cards SET fuel_limit = fuel_limit + ? WHERE id = ?');
            $stmt->bind_param('di', $amount, $id);
            $stmt->execute();
            // лог пополнения лимита карты
            $log = $conn->prepare('INSERT INTO logs (card_id, amount, type) VALUES (?, ?, "refill")');
            $log->bind_param('id', $id, $amount);
            $log->execute();
            jsonResponse(['success' => true]);
        } elseif ($action === 'delete') {
            $id = (int)($_GET['id'] ?? 0);
            $deleteLogs = $conn->prepare('DELETE FROM logs WHERE card_id = ?');
            $deleteLogs->bind_param('i', $id);
            $deleteLogs->execute();

            $deleteCard = $conn->prepare('DELETE FROM cards WHERE id = ?');
            $deleteCard->bind_param('i', $id);
            $deleteCard->execute();
            jsonResponse(['success' => true]);
        } else {
            $res = $conn->query('SELECT id, name, identifier, fuel_limit, used FROM cards ORDER BY id DESC');
            $cards = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            jsonResponse($cards);
        }
        break;

    case 'dispense':
        if ($action !== 'issue') {
            jsonResponse(['error' => 'Неизвестное действие'], 400);
        }
        $identifier = $_GET['identifier'] ?? '';
        $amount = (float)($_GET['amount'] ?? 0);
        $cardStmt = $conn->prepare('SELECT id, name, fuel_limit, used FROM cards WHERE identifier = ? LIMIT 1');
        $cardStmt->bind_param('s', $identifier);
        $cardStmt->execute();
        $cardRes = $cardStmt->get_result();
        if ($cardRes->num_rows === 0) {
            jsonResponse(['success' => false, 'message' => 'Карта не найдена'], 404);
        }
        $card = $cardRes->fetch_assoc();
        $remaining = (float)$card['fuel_limit'] - (float)$card['used'];
        if ($amount <= 0 || $amount > $remaining) {
            jsonResponse(['success' => false, 'message' => 'Недостаточно лимита'], 400);
        }
        ensureFuelRow($conn);
        $fuelRes = $conn->query('SELECT amount FROM fuel LIMIT 1');
        $fuel = $fuelRes ? (float)$fuelRes->fetch_assoc()['amount'] : 0;
        if ($fuel < $amount) {
            jsonResponse(['success' => false, 'message' => 'Недостаточно топлива на складе'], 400);
        }
        $updateCard = $conn->prepare('UPDATE cards SET used = used + ? WHERE id = ?');
        $updateCard->bind_param('di', $amount, $card['id']);
        $updateCard->execute();
        $fuelUpdate = $conn->prepare('UPDATE fuel SET amount = GREATEST(0, amount - ?) LIMIT 1');
        $fuelUpdate->bind_param('d', $amount);
        $fuelUpdate->execute();
        $log = $conn->prepare('INSERT INTO logs (card_id, amount, type) VALUES (?, ?, "dispense")');
        $log->bind_param('id', $card['id'], $amount);
        $log->execute();
        jsonResponse(['success' => true]);
        break;

    case 'logs':
        $identifier = $_GET['identifier'] ?? null;
        $from = $_GET['from'] ?? null;
        $to = $_GET['to'] ?? null;

        $sql = "SELECT logs.*, cards.name, cards.identifier FROM logs LEFT JOIN cards ON logs.card_id = cards.id WHERE 1=1";
        $params = [];
        $types = '';
        if ($identifier) {
            $sql .= ' AND cards.identifier = ?';
            $params[] = $identifier;
            $types .= 's';
        }
        if ($from) {
            $sql .= ' AND DATE(logs.created_at) >= ?';
            $params[] = $from;
            $types .= 's';
        }
        if ($to) {
            $sql .= ' AND DATE(logs.created_at) <= ?';
            $params[] = $to;
            $types .= 's';
        }
        $sql .= ' ORDER BY logs.created_at DESC LIMIT 500';
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        jsonResponse($logs);
        break;

    case 'stats':
        $dispensed = $conn->query("SELECT COALESCE(SUM(amount),0) AS total FROM logs WHERE type='dispense'")->fetch_assoc()['total'];
        $refilled = $conn->query("SELECT COALESCE(SUM(amount),0) AS total FROM logs WHERE type='refill'")->fetch_assoc()['total'];
        $topRes = $conn->query("SELECT cards.name, cards.identifier, SUM(logs.amount) AS total FROM logs JOIN cards ON logs.card_id = cards.id WHERE logs.type='dispense' GROUP BY logs.card_id ORDER BY total DESC LIMIT 1");
        $top = $topRes && $topRes->num_rows > 0 ? $topRes->fetch_assoc() : null;
        jsonResponse([
            'dispensed' => (float)$dispensed,
            'refilled' => (float)$refilled,
            'top_card' => $top ? ($top['name'] . ' (' . $top['identifier'] . ')') : null,
        ]);
        break;

    case 'chart_data':
        $startDate = date('Y-m-d', strtotime('-6 days'));
        $stmt = $conn->prepare("SELECT DATE(created_at) AS d, type, SUM(amount) AS total FROM logs WHERE DATE(created_at) >= ? GROUP BY d, type");
        $stmt->bind_param('s', $startDate);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $dispenseMap = [];
        $refillMap = [];
        foreach ($rows as $row) {
            if ($row['type'] === 'dispense') {
                $dispenseMap[$row['d']] = (float)$row['total'];
            } else {
                $refillMap[$row['d']] = (float)$row['total'];
            }
        }
        $labels = [];
        $dispense = [];
        $refill = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = $day;
            $dispense[] = $dispenseMap[$day] ?? 0;
            $refill[] = $refillMap[$day] ?? 0;
        }
        jsonResponse(['labels' => $labels, 'dispense' => $dispense, 'refill' => $refill]);
        break;

    case 'service':
        ensureServiceRows($conn);
        $types = ['coarse', 'fine'];
        $totalDispensed = getTotalDispensed($conn);

        if ($action === 'start' || $action === 'end' || $action === 'set_interval') {
            $type = $_GET['type'] ?? '';
            if (!in_array($type, $types, true)) {
                jsonResponse(['error' => 'Неверный тип фильтра'], 400);
            }
            if ($action === 'start') {
                $stmt = $conn->prepare("UPDATE service SET status='in_service' WHERE type=?");
                $stmt->bind_param('s', $type);
                $stmt->execute();
            } elseif ($action === 'end') {
                $stmt = $conn->prepare("UPDATE service SET status='ok', last_serviced = NOW(), liters_at_service = ? WHERE type=?");
                $stmt->bind_param('ds', $totalDispensed, $type);
                $stmt->execute();
            } elseif ($action === 'set_interval') {
                $liters = (float)($_GET['liters'] ?? 0);
                $stmt = $conn->prepare('UPDATE service SET interval_liters = ? WHERE type=?');
                $stmt->bind_param('ds', $liters, $type);
                $stmt->execute();
            }
        }

        $statuses = [];
        foreach ($types as $t) {
            $stmt = $conn->prepare('SELECT status, last_serviced, liters_at_service, interval_liters FROM service WHERE type = ? LIMIT 1');
            $stmt->bind_param('s', $t);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $elapsed = max(0, $totalDispensed - (float)$row['liters_at_service']);
            $statuses[$t] = [
                'status' => $row['status'],
                'last_service' => $row['last_serviced'],
                'elapsed' => $elapsed,
                'interval' => (float)$row['interval_liters'],
            ];
        }
        jsonResponse($statuses);
        break;

    case 'diesel_prices':
        $res = $conn->query('SELECT date, price FROM diesel_prices ORDER BY date ASC');
        $prices = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        jsonResponse($prices);
        break;

    default:
        jsonResponse(['error' => 'Неизвестный ресурс'], 404);
}
