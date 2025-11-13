<?php

namespace Core;

use Exception;

class View
{
    public static function render(string $view, array $data = [], ?string $layout = null): void
    {
        $content = self::renderFile($view, $data);

        if ($layout !== null) {
            $data['content'] = $content;
            echo self::renderFile('layouts/'.$layout, $data);
            return;
        }

        echo $content;
    }

    public static function renderPartial(string $view, array $data = []): string
    {
        return self::renderFile($view, $data);
    }

    private static function renderFile(string $view, array $data = []): string
    {
        $path = self::resolvePath($view);

        if (!file_exists($path)) {
            throw new Exception("View [{$view}] not found.");
        }

        ob_start();
        extract($data, EXTR_SKIP);
        include $path;
        return (string) ob_get_clean();
    }

    private static function resolvePath(string $view): string
    {
        $view = trim($view, '/');
        $view = str_replace(['.', '\\'], '/', $view);

        return 'app/views/' . $view . '.php';
    }
}

