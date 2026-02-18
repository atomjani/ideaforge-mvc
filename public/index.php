<?php
error_reporting(0);
ini_set('display_errors', 0);

$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

if (strpos($path, '/api/') === 0 || strpos($path, '?action=') !== false) {
    require_once __DIR__ . '/api.php';
    exit;
}

if ($path === '/api' || strpos($path, '/api') === 0) {
    require_once __DIR__ . '/api.php';
    exit;
}

session_start();

require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Session.php';
require_once __DIR__ . '/app/core/Auth.php';
require_once __DIR__ . '/app/controllers/PageController.php';

$pageController = new PageController();

$routes = [
    '/' => 'home',
    '/login' => 'login',
    '/register' => 'register',
    '/logout' => 'logout',
    '/dashboard' => 'dashboard',
    '/ideas' => 'ideas',
    '/tasks' => 'tasks',
    '/profile' => 'profile',
    '/admin' => 'admin',
    '/guide' => 'guide',
    '/impresszum' => 'impresszum',
    '/privacy' => 'privacy',
    '/terms' => 'terms',
];

$route = $routes[$path] ?? null;

if ($route && method_exists($pageController, $route)) {
    $pageController->$route();
} else {
    http_response_code(404);
    echo "<h1>404 - Oldal nem található</h1>";
}
