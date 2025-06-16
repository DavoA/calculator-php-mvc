<?php
function view(string $view, array $data = []): void {
    extract($data);
    $file = APPROOT . '/src/views/' . $view . '.php';
    if (is_readable($file)) {
        require_once $file;
    } else {
        die('<h1>404 Page not found</h1>');
    }
}