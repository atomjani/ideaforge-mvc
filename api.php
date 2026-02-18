<?php
error_reporting(0);
ini_set('display_errors', 0);

session_start();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    require_once __DIR__ . '/app/core/Database.php';
    require_once __DIR__ . '/app/core/Model.php';
    require_once __DIR__ . '/app/core/Controller.php';
    require_once __DIR__ . '/app/core/Session.php';
    require_once __DIR__ . '/app/core/Auth.php';
    require_once __DIR__ . '/app/core/SimpleMail.php';

    require_once __DIR__ . '/app/models/User.php';
    require_once __DIR__ . '/app/models/Idea.php';
    require_once __DIR__ . '/app/models/Task.php';
    require_once __DIR__ . '/app/models/Comment.php';
    require_once __DIR__ . '/app/models/Newsletter.php';

    require_once __DIR__ . '/app/controllers/UserController.php';
    require_once __DIR__ . '/app/controllers/IdeaController.php';
    require_once __DIR__ . '/app/controllers/TaskController.php';
    require_once __DIR__ . '/app/controllers/NewsletterController.php';
    require_once __DIR__ . '/app/controllers/AdminController.php';

    $db = Database::getInstance();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Adatbázis hiba: ' . $e->getMessage()]);
    exit;
}

$action = $_GET['action'] ?? '';

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true) ?: [];

if (empty($input)) {
    $input = $_POST;
} else {
    $input = array_merge($_POST, $input);
}

function response($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

switch ($action) {
    case 'login':
        $ctrl = new UserController();
        $ctrl->login();
        break;
    
    case 'register':
        $ctrl = new UserController();
        $ctrl->register();
        break;
    
    case 'logout':
        $ctrl = new UserController();
        $ctrl->logout();
        break;
    
    case 'check_session':
        $ctrl = new UserController();
        $ctrl->check_session();
        break;
    
    case 'update_profile':
        $ctrl = new UserController();
        $ctrl->update_profile();
        break;
    
    case 'change_password':
        $ctrl = new UserController();
        $ctrl->change_password();
        break;
    
    case 'delete_account':
        $ctrl = new UserController();
        $ctrl->delete_account();
        break;
    
    case 'get_ideas':
        $ctrl = new IdeaController();
        $ctrl->get_ideas();
        break;
    
    case 'get_idea':
        $ctrl = new IdeaController();
        $ctrl->get_idea();
        break;
    
    case 'create_idea':
        $ctrl = new IdeaController();
        $ctrl->create_idea();
        break;
    
    case 'update_idea':
        $ctrl = new IdeaController();
        $ctrl->update_idea();
        break;
    
    case 'delete_idea':
        $ctrl = new IdeaController();
        $ctrl->delete_idea();
        break;
    
    case 'search_ideas':
        $ctrl = new IdeaController();
        $ctrl->search_ideas();
        break;
    
    case 'update_tags':
        $ctrl = new IdeaController();
        $ctrl->update_tags();
        break;
    
    case 'share_idea':
        $ctrl = new IdeaController();
        $ctrl->share_idea();
        break;
    
    case 'transition_phase':
        $ctrl = new IdeaController();
        $ctrl->transition_phase();
        break;
    
    case 'can_transition':
        $ctrl = new IdeaController();
        $ctrl->can_transition();
        break;
    
    case 'get_tasks':
        $ctrl = new TaskController();
        $ctrl->get_tasks();
        break;
    
    case 'get_board':
        $ctrl = new TaskController();
        $ctrl->get_board();
        break;
    
    case 'create_task':
        $ctrl = new TaskController();
        $ctrl->create_task();
        break;
    
    case 'update_task':
        $ctrl = new TaskController();
        $ctrl->update_task();
        break;
    
    case 'update_status':
        $ctrl = new TaskController();
        $ctrl->update_status();
        break;
    
    case 'delete_task':
        $ctrl = new TaskController();
        $ctrl->delete_task();
        break;
    
    case 'get_comments':
        $ctrl = new TaskController();
        $ctrl->get_comments();
        break;
    
    case 'add_comment':
        $ctrl = new TaskController();
        $ctrl->add_comment();
        break;
    
    case 'delete_comment':
        $ctrl = new TaskController();
        $ctrl->delete_comment();
        break;
    
    case 'newsletter_subscribe':
        $ctrl = new NewsletterController();
        $ctrl->subscribe();
        break;
    
    case 'newsletter_unsubscribe':
        $ctrl = new NewsletterController();
        $ctrl->unsubscribe();
        break;
    
    case 'newsletter_check':
        $ctrl = new NewsletterController();
        $ctrl->check_subscription();
        break;
    
    case 'newsletter_send':
        $ctrl = new NewsletterController();
        $ctrl->send_newsletter();
        break;
    
    case 'newsletter_get_campaigns':
        $ctrl = new NewsletterController();
        $ctrl->get_campaigns();
        break;
    
    case 'get_stats':
        $ctrl = new AdminController();
        $ctrl->get_stats();
        break;
    
    case 'get_activity_log':
        $ctrl = new AdminController();
        $ctrl->get_activity_log();
        break;
    
    case 'get_settings':
        $ctrl = new AdminController();
        $ctrl->get_settings();
        break;
    
    case 'save_smtp_settings':
        $ctrl = new AdminController();
        $ctrl->save_smtp_settings();
        break;
    
    case 'delete_feedback':
        $ctrl = new AdminController();
        $ctrl->delete_feedback();
        break;
    
    case 'get_feedbacks':
        $ctrl = new AdminController();
        $ctrl->get_feedbacks();
        break;
    
    case 'create_feedback':
        $ctrl = new AdminController();
        $ctrl->create_feedback();
        break;
    
    case 'get_all_users':
        $ctrl = new UserController();
        $ctrl->get_all_users();
        break;
    
    case 'update_user':
        $ctrl = new UserController();
        $ctrl->update_user();
        break;
    
    default:
        response(['error' => 'Ismeretlen művelet: ' . $action], 404);
}
