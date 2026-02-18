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
    '/idea' => 'idea',
    '/tasks' => 'tasks',
    '/profile' => 'profile',
    '/admin' => 'admin',
    '/statistics' => 'statistics',
    '/newsletter' => 'newsletter',
    '/feedback' => 'feedback',
    '/guide' => 'guide',
    '/impresszum' => 'impresszum',
    '/privacy' => 'privacy',
    '/terms' => 'terms',
];

if (preg_match('#^/shared/([a-zA-Z0-9]+)$#', $path, $matches)) {
    require_once __DIR__ . '/app/core/Database.php';
    require_once __DIR__ . '/app/core/Session.php';
    require_once __DIR__ . '/app/core/Auth.php';
    
    $db = Database::getInstance();
    $token = $matches[1];
    $idea = $db->fetchOne("SELECT * FROM ideas WHERE share_token = ?", [$token]);
    
    if (!$idea || $idea['is_public'] == 0) {
        http_response_code(404);
        echo "<h1>404 - Az ötlet nem található vagy nem megosztott</h1>";
        exit;
    }
    
    $shareType = $idea['share_type'] ?? 'private';
    
    if ($shareType === 'registered') {
        $auth = new Auth();
        if (!$auth->isLoggedIn()) {
            header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    } elseif ($shareType === 'specific') {
        $auth = new Auth();
        if (!$auth->isLoggedIn()) {
            header('Location: /login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        $user = $auth->check();
        $specificEmails = $idea['specific_emails'] ?? '';
        $allowedEmails = array_map('trim', explode(',', $specificEmails));
        if (!in_array(strtolower($user['email'] ?? ''), array_map('strtolower', $allowedEmails))) {
            http_response_code(403);
            echo "<h1>403 - Nincs jogosultságod megtekinteni ezt az ötletet</h1>";
            exit;
        }
    }
    
    $statuses = ['BACKLOG', 'REVIEW', 'READY_FOR_DEV', 'IN_PROGRESS', 'LIVE_TESTING', 'VALIDATED'];
    $tasks = [];
    foreach ($statuses as $status) {
        $tasks[$status] = $db->fetchAll("SELECT * FROM tasks WHERE idea_id = ? AND status = ?", [$idea['id'], $status]);
    }
    include __DIR__ . '/app/views/pages/shared.php';
    exit;
}

$route = $routes[$path] ?? null;

if ($route && method_exists($pageController, $route)) {
    $pageController->$route();
} else {
    http_response_code(404);
    echo "<h1>404 - Oldal nem található</h1>";
}
