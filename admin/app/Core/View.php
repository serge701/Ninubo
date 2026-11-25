<?php
declare(strict_types=1);

namespace Crm\Core;

final class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        $content = self::capture($view, $data);
        if ($layout === null) {
            echo $content;
            return;
        }
        echo self::capture($layout, array_merge($data, ['content' => $content]));
    }

    /** Named HTML blocks a view hands to its layout (e.g. 'actions' = header buttons). */
    private static array $sections = [];
    private static array $open = [];

    public static function start(string $name): void
    {
        self::$open[] = $name;
        ob_start();
    }

    public static function end(): void
    {
        $name = array_pop(self::$open);
        self::$sections[$name] = (string)ob_get_clean();
    }

    public static function section(string $name): string
    {
        return self::$sections[$name] ?? '';
    }

    public static function partial(string $view, array $data = []): string
    {
        return self::capture($view, $data);
    }

    /** Internal names are prefixed with "__" so view variables such as $data or $view are never shadowed. */
    private static function capture(string $__view, array $__vars): string
    {
        $__path = CRM_ROOT . '/app/Views/' . $__view . '.php';
        if (!is_file($__path)) {
            throw new \RuntimeException("View not found: {$__view}");
        }
        unset($__vars['__view'], $__vars['__vars'], $__vars['__path']);
        extract($__vars);
        unset($__vars);
        ob_start();
        try {
            include $__path;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string)ob_get_clean();
    }
}
