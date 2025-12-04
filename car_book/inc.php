<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';

function ensureCarBookSchema(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS car_book_vehicles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(150) NOT NULL,
        brand VARCHAR(100) DEFAULT NULL,
        license_plate VARCHAR(50) DEFAULT NULL,
        status VARCHAR(32) NOT NULL DEFAULT "ready",
        mileage INT DEFAULT 0,
        next_service_date DATE DEFAULT NULL,
        last_service_at DATE DEFAULT NULL,
        notes TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    $pdo->exec('CREATE TABLE IF NOT EXISTS car_book_events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vehicle_id INT NOT NULL,
        event_type VARCHAR(64) NOT NULL,
        status_after VARCHAR(32) DEFAULT NULL,
        mileage INT DEFAULT NULL,
        note TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (vehicle_id) REFERENCES car_book_vehicles(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    seedCarBookIfEmpty($pdo);
}

function seedCarBookIfEmpty(PDO $pdo): void
{
    $count = (int) $pdo->query('SELECT COUNT(*) AS c FROM car_book_vehicles')->fetchColumn();
    if ($count > 0) {
        return;
    }

    $vehicles = [
        ['title' => 'Ford Transit — смена А', 'brand' => 'Ford', 'license_plate' => 'A123AA', 'status' => 'ready', 'mileage' => 124500, 'next_service_date' => date('Y-m-d', strtotime('+3 months')), 'notes' => 'Готов к выезду'],
        ['title' => 'Газель — доставка', 'brand' => 'ГАЗ', 'license_plate' => 'B456BB', 'status' => 'maintenance', 'mileage' => 201340, 'next_service_date' => date('Y-m-d', strtotime('+1 month')), 'notes' => 'Плановое ТО'],
        ['title' => 'Lada Vesta — дежурная', 'brand' => 'Lada', 'license_plate' => 'C789CC', 'status' => 'reserved', 'mileage' => 85320, 'next_service_date' => date('Y-m-d', strtotime('+5 months')), 'notes' => 'Забронирована на ночную смену'],
    ];

    $stmt = $pdo->prepare('INSERT INTO car_book_vehicles (title, brand, license_plate, status, mileage, next_service_date, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
    foreach ($vehicles as $vehicle) {
        $stmt->execute([$vehicle['title'], $vehicle['brand'], $vehicle['license_plate'], $vehicle['status'], $vehicle['mileage'], $vehicle['next_service_date'], $vehicle['notes']]);
    }

    $serviceStmt = $pdo->prepare('INSERT INTO car_book_events (vehicle_id, event_type, status_after, mileage, note) VALUES (?, ?, ?, ?, ?)');
    $serviceStmt->execute([1, 'service', 'ready', 124500, 'После ремонта тормозов']);
    $serviceStmt->execute([2, 'diagnostic', 'maintenance', 201340, 'Ждёт замену масла']);
    $serviceStmt->execute([3, 'reservation', 'reserved', 85320, 'Закреплена за ответственным смены']);
}

function carBookStatusOptions(): array
{
    return ['ready', 'maintenance', 'reserved', 'offline'];
}

function carBookStatusLabel(string $status): string
{
    require_once __DIR__ . '/../i18n.php';
    $key = 'carbook.status.' . $status;
    return t($key, ucfirst($status));
}

function fetchCarBookVehicles(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM car_book_vehicles ORDER BY status, title');
    $vehicles = $stmt->fetchAll();
    foreach ($vehicles as &$vehicle) {
        $vehicle['next_service_date'] = $vehicle['next_service_date'] ?: null;
        $vehicle['last_event'] = fetchLastEventForVehicle($pdo, (int) $vehicle['id']);
    }
    return $vehicles;
}

function fetchLastEventForVehicle(PDO $pdo, int $vehicleId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM car_book_events WHERE vehicle_id = ? ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([$vehicleId]);
    $event = $stmt->fetch();
    return $event ?: null;
}

function fetchCarBookStats(array $vehicles): array
{
    $stats = [
        'total' => count($vehicles),
        'ready' => 0,
        'maintenance' => 0,
        'reserved' => 0,
    ];
    foreach ($vehicles as $vehicle) {
        $status = $vehicle['status'];
        if (isset($stats[$status])) {
            $stats[$status]++;
        }
    }
    return $stats;
}

function fetchCarBookEvents(PDO $pdo, int $limit = 15): array
{
    $stmt = $pdo->prepare('SELECT e.*, v.title, v.license_plate FROM car_book_events e JOIN car_book_vehicles v ON v.id = e.vehicle_id ORDER BY e.created_at DESC LIMIT ?');
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function saveCarBookVehicle(PDO $pdo, array $data): int
{
    $stmt = $pdo->prepare('INSERT INTO car_book_vehicles (title, brand, license_plate, status, mileage, next_service_date, notes) VALUES (:title, :brand, :license_plate, :status, :mileage, :next_service_date, :notes)');
    $stmt->execute([
        ':title' => $data['title'],
        ':brand' => $data['brand'] ?? null,
        ':license_plate' => $data['license_plate'] ?? null,
        ':status' => $data['status'],
        ':mileage' => $data['mileage'] ?? 0,
        ':next_service_date' => $data['next_service_date'] ?? null,
        ':notes' => $data['notes'] ?? null,
    ]);
    return (int) $pdo->lastInsertId();
}

function updateCarBookStatus(PDO $pdo, int $vehicleId, string $status): void
{
    $stmt = $pdo->prepare('UPDATE car_book_vehicles SET status = :status WHERE id = :id');
    $stmt->execute([':status' => $status, ':id' => $vehicleId]);
}

function logCarBookEvent(PDO $pdo, array $data): void
{
    $stmt = $pdo->prepare('INSERT INTO car_book_events (vehicle_id, event_type, status_after, mileage, note) VALUES (:vehicle_id, :event_type, :status_after, :mileage, :note)');
    $stmt->execute([
        ':vehicle_id' => $data['vehicle_id'],
        ':event_type' => $data['event_type'],
        ':status_after' => $data['status_after'] ?? null,
        ':mileage' => $data['mileage'] ?? null,
        ':note' => $data['note'] ?? null,
    ]);

    if (!empty($data['status_after'])) {
        updateCarBookStatus($pdo, (int) $data['vehicle_id'], $data['status_after']);
    }
    if (isset($data['mileage'])) {
        $mileage = (int) $data['mileage'];
        $pdo->prepare('UPDATE car_book_vehicles SET mileage = :mileage WHERE id = :id')->execute([
            ':mileage' => $mileage,
            ':id' => $data['vehicle_id'],
        ]);
    }
    if (($data['event_type'] ?? '') === 'service') {
        $pdo->prepare('UPDATE car_book_vehicles SET last_service_at = CURRENT_DATE WHERE id = :id')->execute([':id' => $data['vehicle_id']]);
    }
}
?>
