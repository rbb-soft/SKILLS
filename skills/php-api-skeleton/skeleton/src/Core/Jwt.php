<?php
declare(strict_types=1);
namespace App\Core;

final class Jwt
{
    public static function encode(array $payload, string $secret): string
    {
        $payload['iat'] = $payload['iat'] ?? time();
        $header = self::b64u(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
        $body   = self::b64u(json_encode($payload));
        $sig    = self::b64u(hash_hmac('sha256', "$header.$body", $secret, true));
        return "$header.$body.$sig";
    }

    public static function decode(string $token, string $secret): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $body, $sig] = $parts;

        $expected = self::b64u(hash_hmac('sha256', "$header.$body", $secret, true));
        if (!hash_equals($expected, $sig)) return null;

        $payload = json_decode(self::b64uDecode($body), true);
        if (!is_array($payload)) return null;

        if (isset($payload['exp']) && $payload['exp'] < time()) return null;

        return $payload;
    }

    private static function b64u(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function b64uDecode(string $data): string
    {
        $pad = strlen($data) % 4;
        if ($pad) $data .= str_repeat('=', 4 - $pad);
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
