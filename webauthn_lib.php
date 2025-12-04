<?php
namespace lbuchs\WebAuthn;

class WebAuthnException extends \Exception {}

class WebAuthn
{
    private string $rpName;
    private string $rpId;

    public function __construct(string $rpName, string $rpId)
    {
        $this->rpName = $rpName;
        $this->rpId = $rpId;
    }

    public function generateChallenge(int $length = 32): string
    {
        return $this->base64url(random_bytes($length));
    }

    public function creationOptions(string $challenge, array $user, array $exclude = []): array
    {
        $excludeDescriptors = array_map(fn($id) => [
            'type' => 'public-key',
            'id' => $this->base64url_decode($id),
        ], $exclude);

        return [
            'challenge' => $this->base64url_decode($challenge),
            'rp' => [
                'name' => $this->rpName,
                'id' => $this->rpId,
            ],
            'user' => [
                'id' => $this->base64url_decode($user['id']),
                'name' => $user['name'],
                'displayName' => $user['displayName'] ?? $user['name'],
            ],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7],
                ['type' => 'public-key', 'alg' => -257],
            ],
            'authenticatorSelection' => [
                'userVerification' => 'required',
            ],
            'timeout' => 60000,
            'excludeCredentials' => $excludeDescriptors,
        ];
    }

    public function requestOptions(string $challenge, array $allowedCredentials): array
    {
        $allow = array_map(fn($cred) => [
            'type' => 'public-key',
            'id' => $this->base64url_decode($cred),
        ], $allowedCredentials);

        return [
            'challenge' => $this->base64url_decode($challenge),
            'rpId' => $this->rpId,
            'timeout' => 60000,
            'allowCredentials' => $allow,
            'userVerification' => 'required',
        ];
    }

    public function verifyRegistration(array $credential, string $challenge, string $origin): array
    {
        $clientDataJSON = $this->base64url_decode($credential['response']['clientDataJSON'] ?? '');
        $clientData = json_decode($clientDataJSON, true);
        if (!$clientData || ($clientData['type'] ?? '') !== 'webauthn.create') {
            throw new WebAuthnException('Некорректный тип ответа при регистрации');
        }
        if (($clientData['challenge'] ?? '') !== $challenge) {
            throw new WebAuthnException('Челлендж не совпадает');
        }
        if (($clientData['origin'] ?? '') !== $origin) {
            throw new WebAuthnException('Неверное происхождение запроса');
        }

        $authenticatorDataB64 = $credential['response']['authenticatorData'] ?? '';
        $parsed = null;
        if ($authenticatorDataB64) {
            $authData = $this->base64url_decode($authenticatorDataB64);
            $parsed = $this->parseAuthData($authData);

            $expectedRpHash = hash('sha256', $this->rpId, true);
            if (!hash_equals($expectedRpHash, $parsed['rpIdHash'])) {
                throw new WebAuthnException('RP ID hash не совпадает');
            }
            if (!$parsed['userPresent'] || !$parsed['userVerified']) {
                throw new WebAuthnException('Требуется подтвержденное присутствие пользователя');
            }
        }

        $publicKey = $credential['publicKey'] ?? null;
        $algorithm = $credential['publicKeyAlgorithm'] ?? null;
        if (!$publicKey || $algorithm === null) {
            throw new WebAuthnException('Браузер не передал публичный ключ passkey');
        }
        $pem = $this->asPem($this->base64url_decode($publicKey));

        return [
            'credentialId' => $credential['id'] ?? '',
            'publicKey' => $pem,
            'signCount' => $parsed['signCount'] ?? 0,
            'algorithm' => $algorithm,
        ];
    }

    public function verifyAuthentication(array $credential, string $challenge, string $origin, array $stored): array
    {
        $clientDataJSON = $this->base64url_decode($credential['response']['clientDataJSON'] ?? '');
        $clientData = json_decode($clientDataJSON, true);
        if (!$clientData || ($clientData['type'] ?? '') !== 'webauthn.get') {
            throw new WebAuthnException('Некорректный тип ответа при входе');
        }
        if (($clientData['challenge'] ?? '') !== $challenge) {
            throw new WebAuthnException('Челлендж не совпадает');
        }
        if (($clientData['origin'] ?? '') !== $origin) {
            throw new WebAuthnException('Неверное происхождение запроса');
        }

        $authData = $this->base64url_decode($credential['response']['authenticatorData'] ?? '');
        $parsed = $this->parseAuthData($authData);

        $expectedRpHash = hash('sha256', $this->rpId, true);
        if (!hash_equals($expectedRpHash, $parsed['rpIdHash'])) {
            throw new WebAuthnException('RP ID hash не совпадает');
        }
        if (!$parsed['userPresent'] || !$parsed['userVerified']) {
            throw new WebAuthnException('Требуется подтвержденное присутствие пользователя');
        }

        $hash = hash('sha256', $clientDataJSON, true);
        $dataToVerify = $authData . $hash;

        $signature = $this->base64url_decode($credential['response']['signature'] ?? '');
        $publicKey = $stored['public_key'] ?? '';
        $algorithm = $stored['algorithm'] ?? -7;
        $algoName = $this->mapAlg((int) $algorithm);

        $ok = openssl_verify($dataToVerify, $signature, $publicKey, $algoName);
        if ($ok !== 1) {
            throw new WebAuthnException('Подпись passkey не прошла проверку');
        }

        if ($parsed['signCount'] < $stored['sign_count']) {
            throw new WebAuthnException('Счетчик подписей уменьшился');
        }

        return [
            'signCount' => $parsed['signCount'],
        ];
    }

    private function parseAuthData(string $authData): array
    {
        if (strlen($authData) < 37) {
            throw new WebAuthnException('Короткие данные authenticatorData');
        }
        $rpIdHash = substr($authData, 0, 32);
        $flags = ord($authData[32]);
        $signCount = unpack('N', substr($authData, 33, 4))[1];

        return [
            'rpIdHash' => $rpIdHash,
            'flags' => $flags,
            'signCount' => $signCount,
            'userPresent' => (bool) ($flags & 0x01),
            'userVerified' => (bool) ($flags & 0x04),
        ];
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64url_decode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private function asPem(string $key): string
    {
        $encoded = base64_encode($key);
        $pem = "-----BEGIN PUBLIC KEY-----\n";
        $pem .= trim(chunk_split($encoded, 64, "\n"));
        $pem .= "\n-----END PUBLIC KEY-----\n";
        return $pem;
    }

    private function mapAlg(int $alg): int
    {
        return match ($alg) {
            -257, -37 => OPENSSL_ALGO_SHA256,
            -258, -38 => OPENSSL_ALGO_SHA384,
            -259, -39 => OPENSSL_ALGO_SHA512,
            default => OPENSSL_ALGO_SHA256,
        };
    }
}
