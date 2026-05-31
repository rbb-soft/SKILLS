<?php
declare(strict_types=1);
namespace App\Core;

final class Auth
{
    /**
     * Valida JWT. Retorna payload o responde 401 y termina.
     */
    public static function requireUser(): array
    {
        $cfg = require BASE_PATH . '/config.php';

        $header = $_SERVER['HTTP_AUTHORIZATION']
               ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
               ?? '';

        if (!str_starts_with($header, 'Bearer ')) {
            Router::json(['error' => 'No autorizado'], 401);
            exit;
        }

        $token   = substr($header, 7);
        $payload = Jwt::decode($token, $cfg['jwt_secret']);

        if ($payload === null) {
            Router::json(['error' => 'Token inválido o expirado'], 401);
            exit;
        }

        return $payload;
    }

    /**
     * requireUser() + verifica role === 'admin'.
     */
    public static function requireAdmin(): array
    {
        $payload = self::requireUser();

        if (($payload['role'] ?? '') !== 'admin') {
            Router::json(['error' => 'Acceso denegado'], 403);
            exit;
        }

        return $payload;
    }

    /**
     * Verifica X-Internal-Key para llamadas internas (bots Node.js, cron jobs).
     */
    public static function requireInternal(): void
    {
        $cfg = require BASE_PATH . '/config.php';

        $key = $_SERVER['HTTP_X_INTERNAL_KEY']
            ?? $_SERVER['REDIRECT_HTTP_X_INTERNAL_KEY']
            ?? '';

        if (!hash_equals((string) $cfg['internal_key'], $key)) {
            Router::json(['error' => 'Acceso denegado'], 403);
            exit;
        }
    }
}
