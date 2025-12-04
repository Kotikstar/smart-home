<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/webauthn_lib.php';

const PERMISSION_KEYS = [
    'dashboard',
    'fuel',
    'cards',
    'dispense',
    'logs',
    'diesel',
    'passes',
    'service',
];

use lbuchs\WebAuthn\WebAuthn;
use lbuchs\WebAuthn\WebAuthnException;

function ensureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function requireAuthPage(bool $allowAnonymous = false): void
{
    if ($allowAnonymous || currentUserId()) {
        return;
    }

    header('Location: all.php?login=1');
    exit;
}

function rpId(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return explode(':', $host)[0];
}

function expectedOrigin(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

function webauthnInstance(): WebAuthn
{
    return new WebAuthn('Smart Home Control', rpId());
}

function currentUserId(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function currentUsername(): ?string
{
    return $_SESSION['username'] ?? null;
}

function requireAuthJson(): void
{
    if (!currentUserId()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Требуется вход по passkey'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

function ensurePermissionsRow(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT * FROM user_permissions WHERE user_id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    if ($row) {
        return $row;
    }

    $insert = $pdo->prepare('INSERT INTO user_permissions (user_id) VALUES (?)');
    $insert->execute([$userId]);
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function permissionsRowToArray(array $row): array
{
    $perms = [];
    foreach (PERMISSION_KEYS as $key) {
        $column = 'can_' . $key;
        $perms[$key] = isset($row[$column]) ? (bool) $row[$column] : false;
    }
    return $perms;
}

function loadUserPermissions(PDO $pdo, int $userId): array
{
    $row = ensurePermissionsRow($pdo, $userId);
    $permissions = permissionsRowToArray($row);
    $_SESSION['permissions'] = $permissions;
    $_SESSION['is_admin'] = isset($row['is_admin']) ? (bool) $row['is_admin'] : false;
    return $permissions;
}

function currentPermissions(): array
{
    ensureSession();
    if (!currentUserId()) {
        return [];
    }
    if (isset($_SESSION['permissions']) && is_array($_SESSION['permissions'])) {
        return $_SESSION['permissions'];
    }
    global $pdo;
    return loadUserPermissions($pdo, currentUserId());
}

function isAdmin(): bool
{
    ensureSession();
    if (!currentUserId()) {
        return false;
    }
    if (isset($_SESSION['is_admin'])) {
        return (bool) $_SESSION['is_admin'];
    }
    global $pdo;
    loadUserPermissions($pdo, currentUserId());
    return (bool) ($_SESSION['is_admin'] ?? false);
}

function userHasPermission(string $permission): bool
{
    if (isAdmin()) {
        return true;
    }
    $permissions = currentPermissions();
    return $permissions[$permission] ?? false;
}

function requirePermissionPage(string $permission): void
{
    if (userHasPermission($permission)) {
        return;
    }

    header('Location: all.php?login=1&denied=1');
    exit;
}

function requirePermissionJson(string $permission): void
{
    if (userHasPermission($permission)) {
        return;
    }

    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Недостаточно прав для операции'], JSON_UNESCAPED_UNICODE);
    exit;
}

function base64url_encode(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string
{
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/'));
}

function findUserByUsername(PDO $pdo, string $username): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function createUser(PDO $pdo, string $username): array
{
    $stmt = $pdo->prepare('INSERT INTO users (username) VALUES (?)');
    $stmt->execute([$username]);
    $id = (int) $pdo->lastInsertId();
    ensurePermissionsRow($pdo, $id);
    return ['id' => $id, 'username' => $username];
}

function getCredentials(PDO $pdo, int $userId): array
{
    $stmt = $pdo->prepare('SELECT id, credential_id, public_key, sign_count, algorithm, transports FROM webauthn_credentials WHERE user_id = ?');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function findCredential(PDO $pdo, string $credentialId): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM webauthn_credentials WHERE credential_id = ? LIMIT 1');
    $stmt->execute([$credentialId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function storeCredential(PDO $pdo, int $userId, string $credentialId, string $publicKey, int $signCount, ?int $algorithm = null, ?string $transports = null): void
{
    $stmt = $pdo->prepare('INSERT INTO webauthn_credentials (user_id, credential_id, public_key, sign_count, algorithm, transports) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $credentialId, $publicKey, $signCount, $algorithm, $transports]);
}

function updateCounter(PDO $pdo, int $credentialId, int $counter): void
{
    $stmt = $pdo->prepare('UPDATE webauthn_credentials SET sign_count = ? WHERE id = ?');
    $stmt->execute([$counter, $credentialId]);
}

function setAuthenticatedUser(array $user): void
{
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'];
    global $pdo;
    loadUserPermissions($pdo, (int) $user['id']);
}

function clearAuth(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
