<?php
declare(strict_types=1);

namespace Crm\Core;

/**
 * Minimal router. Every route requires a signed-in user unless registered with
 * ['public' => true]; ['role' => 'admin'] restricts it to administrators.
 */
final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler, array $options = []): void
    {
        $this->add('GET', $path, $handler, $options);
    }

    public function post(string $path, array $handler, array $options = []): void
    {
        $this->add('POST', $path, $handler, $options);
    }

    private function add(string $method, string $path, array $handler, array $options): void
    {
        $pattern = preg_replace('#\{([a-z_]+)\}#', '(?P<$1>[^/]+)', $path);
        $this->routes[] = [
            'method' => $method,
            'pattern' => '#^' . $pattern . '$#',
            'handler' => $handler,
            'options' => $options,
        ];
    }

    /** @return array{handler: array, params: array, options: array}|null */
    public function match(string $method, string $uri): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['pattern'], $uri, $m)) {
                $params = array_filter($m, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
                return ['handler' => $route['handler'], 'params' => $params, 'options' => $route['options']];
            }
        }
        return null;
    }

    /** True if the path exists for some other method (→ 405 instead of 404). */
    public function pathExists(string $uri): bool
    {
        foreach ($this->routes as $route) {
            if (preg_match($route['pattern'], $uri)) {
                return true;
            }
        }
        return false;
    }
}
