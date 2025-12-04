<?php
require_once __DIR__ . '/auth.php';

ensureSession();
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

header('Content-Type: application/json');

function respond($data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $webauthn = webauthnInstance();
    $origin = expectedOrigin();

    switch ($action) {
        case 'session':
            respond([
                'authenticated' => (bool) currentUserId(),
                'username' => currentUsername(),
                'permissions' => currentPermissions(),
                'is_admin' => isAdmin(),
            ]);

        case 'logout':
            clearAuth();
            respond(['success' => true]);

        case 'start-registration':
            $username = trim($input['username'] ?? '');
            if ($username === '') {
                respond(['error' => 'Укажите логин для регистрации passkey'], 400);
            }

            $user = findUserByUsername($pdo, $username) ?? createUser($pdo, $username);
            $credentials = getCredentials($pdo, (int) $user['id']);
            $exclude = array_map(fn($c) => $c['credential_id'], $credentials);

            $challenge = $webauthn->generateChallenge();
            $_SESSION['webauthn_challenge'] = $challenge;
            $_SESSION['webauthn_action'] = 'register';
            $_SESSION['webauthn_username'] = $user['username'];

            $options = $webauthn->creationOptions($challenge, [
                'id' => base64url_encode((string) $user['id']),
                'name' => $user['username'],
                'displayName' => $user['username'],
            ], $exclude);

            respond([
                'publicKey' => [
                    'challenge' => base64url_encode($options['challenge']),
                    'rp' => $options['rp'],
                    'user' => [
                        'id' => base64url_encode($options['user']['id']),
                        'name' => $options['user']['name'],
                        'displayName' => $options['user']['displayName'],
                    ],
                    'pubKeyCredParams' => $options['pubKeyCredParams'],
                    'authenticatorSelection' => $options['authenticatorSelection'],
                    'timeout' => $options['timeout'],
                    'excludeCredentials' => array_map(fn($item) => [
                        'type' => $item['type'],
                        'id' => base64url_encode($item['id']),
                    ], $options['excludeCredentials']),
                ],
            ]);

        case 'finish-registration':
            if (($_SESSION['webauthn_action'] ?? '') !== 'register') {
                respond(['error' => 'Челлендж истек или не запрашивался'], 400);
            }
            $challenge = $_SESSION['webauthn_challenge'] ?? '';
            $username = $_SESSION['webauthn_username'] ?? '';
            $user = $username ? findUserByUsername($pdo, $username) : null;
            if (!$user) {
                respond(['error' => 'Пользователь не найден'], 400);
            }

            $credential = $input['credential'] ?? [];
            $result = $webauthn->verifyRegistration($credential, $challenge, $origin);
            if ($result['credentialId'] === '') {
                respond(['error' => 'Пустой идентификатор ключа'], 400);
            }

            storeCredential(
                $pdo,
                (int) $user['id'],
                $result['credentialId'],
                $result['publicKey'],
                (int) $result['signCount'],
                $result['algorithm'],
                $credential['transports'] ?? null
            );
            setAuthenticatedUser($user);
            unset($_SESSION['webauthn_challenge'], $_SESSION['webauthn_action'], $_SESSION['webauthn_username']);

            respond(['success' => true, 'username' => $user['username']]);

        case 'start-login':
            $username = trim($input['username'] ?? '');
            if ($username === '') {
                respond(['error' => 'Укажите логин для входа'], 400);
            }
            $user = findUserByUsername($pdo, $username);
            if (!$user) {
                respond(['error' => 'Пользователь не найден'], 404);
            }
            $credentials = getCredentials($pdo, (int) $user['id']);
            if (empty($credentials)) {
                respond(['error' => 'Для пользователя нет passkey, сначала зарегистрируйте его'], 404);
            }
            $allow = array_map(fn($c) => $c['credential_id'], $credentials);
            $challenge = $webauthn->generateChallenge();
            $_SESSION['webauthn_challenge'] = $challenge;
            $_SESSION['webauthn_action'] = 'login';
            $_SESSION['webauthn_username'] = $user['username'];

            $options = $webauthn->requestOptions($challenge, $allow);
            respond([
                'publicKey' => [
                    'challenge' => base64url_encode($options['challenge']),
                    'rpId' => $options['rpId'],
                    'timeout' => $options['timeout'],
                    'allowCredentials' => array_map(fn($item) => [
                        'type' => $item['type'],
                        'id' => base64url_encode($item['id']),
                    ], $options['allowCredentials']),
                    'userVerification' => $options['userVerification'],
                ],
            ]);

        case 'finish-login':
            if (($_SESSION['webauthn_action'] ?? '') !== 'login') {
                respond(['error' => 'Челлендж входа не найден'], 400);
            }
            $challenge = $_SESSION['webauthn_challenge'] ?? '';
            $username = $_SESSION['webauthn_username'] ?? '';
            $user = $username ? findUserByUsername($pdo, $username) : null;
            if (!$user) {
                respond(['error' => 'Пользователь не найден'], 404);
            }

            $credential = $input['credential'] ?? [];
            $credentialId = $credential['id'] ?? '';
            $stored = $credentialId ? findCredential($pdo, $credentialId) : null;
            if (!$stored || (int) $stored['user_id'] !== (int) $user['id']) {
                respond(['error' => 'Неизвестный ключ'], 404);
            }

            $result = $webauthn->verifyAuthentication($credential, $challenge, $origin, $stored);
            updateCounter($pdo, (int) $stored['id'], (int) $result['signCount']);
            setAuthenticatedUser($user);
            unset($_SESSION['webauthn_challenge'], $_SESSION['webauthn_action'], $_SESSION['webauthn_username']);

            respond(['success' => true, 'username' => $user['username']]);

        default:
            respond(['error' => 'Неизвестное действие'], 404);
    }
} catch (WebAuthnException $e) {
    respond(['error' => $e->getMessage()], 400);
} catch (\Throwable $e) {
    respond(['error' => 'Сбой аутентификации: ' . $e->getMessage()], 500);
}
