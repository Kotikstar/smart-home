<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/inc.php';
ensureSession();
requireAuthJson();
requirePermissionJson('carbook');
ensureCarBookSchema($pdo);

header('Content-Type: application/json');

$action = $_POST['action'] ?? null;

try {
    switch ($action) {
        case 'create_vehicle':
            $status = $_POST['status'] ?? 'ready';
            if (!in_array($status, carBookStatusOptions(), true)) {
                throw new RuntimeException('Unknown status');
            }
            $data = [
                'title' => trim((string) ($_POST['title'] ?? '')),
                'brand' => trim((string) ($_POST['brand'] ?? '')) ?: null,
                'license_plate' => trim((string) ($_POST['license_plate'] ?? '')) ?: null,
                'status' => $status,
                'mileage' => (int) ($_POST['mileage'] ?? 0),
                'next_service_date' => $_POST['next_service_date'] ?? null,
                'notes' => trim((string) ($_POST['notes'] ?? '')) ?: null,
            ];
            if ($data['title'] === '') {
                throw new RuntimeException('Название обязательно');
            }
            $id = saveCarBookVehicle($pdo, $data);
            echo json_encode(['ok' => true, 'id' => $id]);
            break;
        case 'log_event':
            $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
            $eventType = trim((string) ($_POST['event_type'] ?? ''));
            if ($vehicleId <= 0 || $eventType === '') {
                throw new RuntimeException('Неполные данные события');
            }
            $statusAfter = $_POST['status_after'] ?? null;
            if ($statusAfter && !in_array($statusAfter, carBookStatusOptions(), true)) {
                throw new RuntimeException('Неверный статус');
            }
            logCarBookEvent($pdo, [
                'vehicle_id' => $vehicleId,
                'event_type' => $eventType,
                'status_after' => $statusAfter,
                'mileage' => isset($_POST['mileage']) ? (int) $_POST['mileage'] : null,
                'note' => trim((string) ($_POST['note'] ?? '')) ?: null,
            ]);
            echo json_encode(['ok' => true]);
            break;
        case 'update_status':
            $vehicleId = (int) ($_POST['vehicle_id'] ?? 0);
            $status = $_POST['status'] ?? '';
            if ($vehicleId <= 0 || !in_array($status, carBookStatusOptions(), true)) {
                throw new RuntimeException('Неверные данные');
            }
            updateCarBookStatus($pdo, $vehicleId, $status);
            logCarBookEvent($pdo, [
                'vehicle_id' => $vehicleId,
                'event_type' => 'status',
                'status_after' => $status,
                'note' => null,
            ]);
            echo json_encode(['ok' => true]);
            break;
        default:
            throw new RuntimeException('Unknown action');
    }
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

