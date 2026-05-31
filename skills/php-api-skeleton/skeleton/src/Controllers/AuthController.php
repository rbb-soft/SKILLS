<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Router;
use App\Core\Auth;
use App\Core\Jwt;
use App\Models\User;

final class AuthController
{
    private User $user;

    public function __construct()
    {
        $this->user = new User();
    }

    public function login(array $params): void
    {
        $cfg  = require BASE_PATH . '/config.php';
        $body = (string) file_get_contents('php://input');
        $data = json_decode($body, true) ?? [];

        $email    = $data['email']    ?? '';
        $password = $data['password'] ?? '';

        if ($email === '' || $password === '') {
            Router::json(['error' => 'Email y password requeridos'], 400);
            return;
        }

        $user = $this->user->findByEmail($email);

        if ($user === null || !password_verify($password, $user['password'])) {
            Router::json(['error' => 'Credenciales inválidas'], 401);
            return;
        }

        if (!($user['active'] ?? true)) {
            Router::json(['error' => 'Usuario inactivo'], 403);
            return;
        }

        $token = Jwt::encode([
            'sub'   => (int) $user['id'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'exp'   => time() + 86400 * 60,
        ], $cfg['jwt_secret']);

        Router::json([
            'token' => $token,
            'user'  => [
                'id'    => $user['id'],
                'email' => $user['email'],
                'name'  => $user['name'],
                'role'  => $user['role'],
            ],
        ]);
    }

    public function register(array $params): void
    {
        $cfg  = require BASE_PATH . '/config.php';
        $body = (string) file_get_contents('php://input');
        $data = json_decode($body, true) ?? [];

        $email    = $data['email']    ?? '';
        $password = $data['password'] ?? '';
        $name     = $data['name']     ?? null;

        if ($email === '' || $password === '') {
            Router::json(['error' => 'Email y password requeridos'], 400);
            return;
        }

        if ($this->user->findByEmail($email) !== null) {
            Router::json(['error' => 'El email ya está registrado'], 409);
            return;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $id   = $this->user->create($email, $hash, $name, 'user');

        $token = Jwt::encode([
            'sub'   => $id,
            'email' => $email,
            'role'  => 'user',
            'exp'   => time() + 86400 * 60,
        ], $cfg['jwt_secret']);

        Router::json([
            'token' => $token,
            'user'  => ['id' => $id, 'email' => $email, 'role' => 'user'],
        ], 201);
    }

    public function me(array $params): void
    {
        $payload = Auth::requireUser();
        $user    = $this->user->findById((int) $payload['sub']);

        if ($user === null) {
            Router::json(['error' => 'Usuario no encontrado'], 404);
            return;
        }

        Router::json(['user' => $user]);
    }
}
