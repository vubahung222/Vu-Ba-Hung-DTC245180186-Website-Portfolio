<?php
/**
 * Router - Simple HTTP router
 */

declare(strict_types=1);

namespace Core;

class Router
{
    private array $routes = [];
    private $notFound = null;

    public function get(string $path, array|callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array|callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function setNotFound(callable $handler): void
    {
        $this->notFound = $handler;
    }

    private function addRoute(string $method, string $path, array|callable $handler): void
    {
        // Convert {param} to named regex groups
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#u';
        $this->routes[] = compact('method', 'path', 'pattern', 'handler');
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = '/' . trim(rawurldecode($uri), '/');
        if ($uri === '/') {
            $uri = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
                $this->call($route['handler'], $params);
                return;
            }
        }

        // Not found
        if ($this->notFound) {
            ($this->notFound)();
        } else {
            http_response_code(404);
            echo '404 Not Found';
        }
    }

    private function call(array|callable $handler, array $params): void
    {
        if (is_callable($handler)) {
            $handler($params);
            return;
        }

        [$class, $method] = $handler;
        // Strip leading backslash if present
        $class = ltrim($class, '\\');
        $controller = new $class();
        $controller->$method($params);
    }
}
