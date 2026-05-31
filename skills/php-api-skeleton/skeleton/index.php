<?php
declare(strict_types=1);
define('BASE_PATH', __DIR__);

if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
} else {
    require_once BASE_PATH . '/autoload.php';
}

use App\Core\Router;

// ── CORS ─────────────────────────────────────────────────────────────────────
$allowedOrigins = ['http://localhost:4200'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
}

header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$router = Router::getInstance();

// ── Auth routes ───────────────────────────────────────────────────────────────
$router->post('/api/auth/register', 'App\Controllers\AuthController@register');
$router->post('/api/auth/login',    'App\Controllers\AuthController@login');
$router->get('/api/auth/me',        'App\Controllers\AuthController@me');

// ── Protected routes (require JWT) ────────────────────────────────────────────
// $router->get('/api/profile', 'App\Controllers\ProfileController@show');

// ── Admin routes (require JWT + role admin) ───────────────────────────────────
// $router->get('/api/admin/users',        'App\Controllers\AdminController@index');
// $router->patch('/api/admin/users/{id}', 'App\Controllers\AdminController@update');
// $router->delete('/api/admin/users/{id}','App\Controllers\AdminController@delete');

$router->dispatch();
