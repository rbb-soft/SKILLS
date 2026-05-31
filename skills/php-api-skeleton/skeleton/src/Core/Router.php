<?php
declare(strict_types=1);
namespace App\Core;

final class Router
{
    private static ?Router $instance = null;
    private array $routes = [];

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get(string $path, string $handler): void
    {
        $this->routes[] = ['GET', $path, $handler];
    }

    public function post(string $path, string $handler): void
    {
        $this->routes[] = ['POST', $path, $handler];
    }

    public function patch(string $path, string $handler): void
    {
        $this->routes[] = ['PATCH', $path, $handler];
    }

    public function delete(string $path, string $handler): void
    {
        $this->routes[] = ['DELETE', $path, $handler];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            if ($routeMethod !== $method) continue;

            $params  = [];
            $pattern = preg_replace('/\{([a-z_]+)\}/', '([^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#';
            preg_match_all('/\{([a-z_]+)\}/', $routePath, $paramNames);

            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $params = array_combine($paramNames[1], $matches);
                $this->call($handler, $params);
                return;
            }
        }

        self::json(['error' => 'Not found'], 404);
    }

    private function call(string $handler, array $params): void
    {
        [$class, $method] = explode('@', $handler, 2);

        if (!class_exists($class)) {
            self::json(['error' => 'Controller not found'], 500);
            return;
        }

        $controller = new $class();

        if (!method_exists($controller, $method)) {
            self::json(['error' => 'Method not found'], 500);
            return;
        }

        $controller->$method($params);
    }

    public static function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
