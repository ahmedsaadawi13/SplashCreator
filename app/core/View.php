<?php
// FILE: /app/core/View.php

class View {
    public function render($viewPath, $data = []) {
        extract($data);

        $viewFile = __DIR__ . '/../views/' . $viewPath . '.php';

        if (!file_exists($viewFile)) {
            die("View not found: {$viewPath}");
        }

        require_once $viewFile;
    }

    public static function escape($value) {
        if (is_array($value)) {
            return array_map([self::class, 'escape'], $value);
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function e($value) {
        return self::escape($value);
    }
}
