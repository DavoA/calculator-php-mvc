<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/src/helpers/url_helper.php';
require_once dirname(__DIR__) . '/src/helpers/view_helper.php';

use App\Router;

try {
    $router = Router::load(dirname(__DIR__) . '/src/app/routes.php');
    if ($router === null) {
        throw new Exception('Router failed to initialize.');
    }
    $router->setParams(getUri())->redirect();
} catch (Exception $e) {
    error_log("Router error: " . $e->getMessage());
    http_response_code(500);
    echo "<h1>Internal Server Error</h1><p>Unable to initialize router: {$e->getMessage()}</p>";
}