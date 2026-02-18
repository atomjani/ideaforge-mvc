<?php

require_once __DIR__ . '/../core/Auth.php';

class PageController
{
    private $auth;
    
    public function __construct()
    {
        $this->auth = new Auth();
    }
    
    public function render($view, $data = [])
    {
        extract($data);
        
        $viewFile = __DIR__ . '/../views/pages/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            $this->notFound();
            return;
        }
        
        include $viewFile;
    }
    
    public function home()
    {
        if ($this->auth->isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->render('home');
    }
    
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $result = $this->auth->login($email, $password);
            
            if (isset($result['error'])) {
                $this->render('login', ['error' => $result['error']]);
            } else {
                $this->redirect('/dashboard');
            }
            return;
        }
        
        if ($this->auth->isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->render('login');
    }
    
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $name = $_POST['name'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (strlen($password) < 6) {
                $this->render('register', ['error' => 'A jelszónak minimum 6 karakternek kell lennie']);
                return;
            }
            
            $result = $this->auth->register($email, $name, $password);
            
            if (isset($result['error'])) {
                $this->render('register', ['error' => $result['error']]);
            } else {
                $this->redirect('/dashboard');
            }
            return;
        }
        
        if ($this->auth->isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->render('register');
    }
    
    public function logout()
    {
        $this->auth->logout();
        $this->redirect('/login');
    }
    
    public function dashboard()
    {
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        $user = $this->auth->check();
        
        $db = Database::getInstance();
        
        $totalIdeas = $db->fetchOne("SELECT COUNT(*) as count FROM ideas WHERE user_id = ?", [$user['id']])['count'] ?? 0;
        $totalTasks = $db->fetchOne("SELECT COUNT(*) as count FROM tasks t 
            JOIN ideas i ON t.idea_id = i.id WHERE i.user_id = ?", [$user['id']])['count'] ?? 0;
        $completedTasks = $db->fetchOne("SELECT COUNT(*) as count FROM tasks t 
            JOIN ideas i ON t.idea_id = i.id WHERE i.user_id = ? AND t.status = 'VALIDATED'", [$user['id']])['count'] ?? 0;
        
        $recentIdeas = $db->fetchAll("SELECT * FROM ideas WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$user['id']]);
        
        $stats = [
            'totalIdeas' => $totalIdeas,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'user' => $user
        ];
        
        $this->render('dashboard', $stats);
    }
    
    public function ideas()
    {
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        $user = $this->auth->check();
        $db = Database::getInstance();
        
        $ideas = $db->fetchAll("SELECT * FROM ideas WHERE user_id = ? ORDER BY created_at DESC", [$user['id']]);
        
        foreach ($ideas as &$idea) {
            $taskCount = $db->fetchOne("SELECT COUNT(*) as count FROM tasks WHERE idea_id = ?", [$idea['id']])['count'] ?? 0;
            $validatedCount = $db->fetchOne("SELECT COUNT(*) as count FROM tasks WHERE idea_id = ? AND status = 'VALIDATED'", [$idea['id']])['count'] ?? 0;
            
            $idea['task_count'] = $taskCount;
            $idea['validated_count'] = $validatedCount;
            $idea['can_transition'] = $taskCount > 0 && $taskCount === $validatedCount;
            $idea['is_public'] = !empty($idea['is_public']);
        }
        
        $this->render('ideas', ['ideas' => $ideas, 'user' => $user]);
    }
    
    public function idea()
    {
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        $user = $this->auth->check();
        $db = Database::getInstance();
        
        $ideaId = $_GET['id'] ?? '';
        
        if (!$ideaId) {
            $this->redirect('/ideas');
            return;
        }
        
        $idea = $db->fetchOne("SELECT * FROM ideas WHERE id = ?", [$ideaId]);
        
        if (!$idea || ($idea['user_id'] !== $user['id'] && $user['role'] !== 'admin')) {
            $this->redirect('/ideas');
            return;
        }
        
        $statuses = ['BACKLOG', 'REVIEW', 'READY_FOR_DEV', 'IN_PROGRESS', 'LIVE_TESTING', 'VALIDATED'];
        $board = [];
        
        foreach ($statuses as $status) {
            $board[$status] = $db->fetchAll(
                "SELECT * FROM tasks WHERE idea_id = ? AND status = ? ORDER BY priority ASC, created_at ASC",
                [$ideaId, $status]
            );
        }
        
        $taskCount = $db->fetchOne("SELECT COUNT(*) as count FROM tasks WHERE idea_id = ?", [$ideaId])['count'] ?? 0;
        $validatedCount = $db->fetchOne("SELECT COUNT(*) as count FROM tasks WHERE idea_id = ? AND status = 'VALIDATED'", [$ideaId])['count'] ?? 0;
        
        $canTransition = $taskCount > 0 && $taskCount === $validatedCount;
        
        $this->render('idea', [
            'idea' => $idea,
            'board' => $board,
            'taskCount' => $taskCount,
            'validatedCount' => $validatedCount,
            'canTransition' => $canTransition,
            'user' => $user
        ]);
    }
    
    public function tasks()
    {
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        $user = $this->auth->check();
        $db = Database::getInstance();
        
        $ideas = $db->fetchAll("SELECT id, name FROM ideas WHERE user_id = ? ORDER BY name", [$user['id']]);
        
        $backlog = $db->fetchAll("SELECT t.*, i.name as idea_title FROM tasks t 
            JOIN ideas i ON t.idea_id = i.id WHERE i.user_id = ? AND t.status = 'BACKLOG' 
            ORDER BY t.created_at", [$user['id']]);
        
        $inProgress = $db->fetchAll("SELECT t.*, i.name as idea_title FROM tasks t 
            JOIN ideas i ON t.idea_id = i.id WHERE i.user_id = ? AND t.status = 'IN_PROGRESS' 
            ORDER BY t.created_at", [$user['id']]);
        
        $validated = $db->fetchAll("SELECT t.*, i.name as idea_title FROM tasks t 
            JOIN ideas i ON t.idea_id = i.id WHERE i.user_id = ? AND t.status = 'VALIDATED' 
            ORDER BY t.created_at", [$user['id']]);
        
        $this->render('tasks', [
            'ideas' => $ideas,
            'backlog' => $backlog,
            'inProgress' => $inProgress,
            'validated' => $validated,
            'user' => $user
        ]);
    }
    
    public function profile()
    {
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        $user = $this->auth->check();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['name'])) {
                $this->auth->updateProfile($user['id'], $_POST['name']);
                $user['name'] = $_POST['name'];
                $message = 'Név sikeresen frissítve';
            } elseif (isset($_POST['old_password']) && isset($_POST['new_password'])) {
                $result = $this->auth->changePassword($user['id'], $_POST['old_password'], $_POST['new_password']);
                $message = isset($result['error']) ? $result['error'] : 'Jelszó sikeresen megváltoztatva';
            }
        }
        
        $this->render('profile', ['user' => $user, 'message' => $message ?? null, 'error' => $error ?? null]);
    }
    
    public function admin()
    {
        if (!$this->auth->isAdmin()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $db = Database::getInstance();
        
        $stats = [
            'totalIdeas' => $db->fetchOne("SELECT COUNT(*) as count FROM ideas")['count'] ?? 0,
            'totalTasks' => $db->fetchOne("SELECT COUNT(*) as count FROM tasks")['count'] ?? 0,
            'completedTasks' => $db->fetchOne("SELECT COUNT(*) as count FROM tasks WHERE status = 'VALIDATED'")['count'] ?? 0,
            'totalUsers' => $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
        ];
        
        $users = $db->fetchAll("SELECT id, email, name, role, created_at FROM users ORDER BY created_at DESC");
        
        $this->render('admin', ['stats' => $stats, 'users' => $users]);
    }
    
    public function statistics()
    {
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        $user = $this->auth->check();
        $db = Database::getInstance();
        
        $allIdeas = $db->fetchAll("SELECT id, tags FROM ideas WHERE user_id = ?", [$user['id']]);
        $profitableCount = 0;
        $popularCount = 0;
        foreach ($allIdeas as $idea) {
            if (!empty($idea['tags'])) {
                $tags = is_array($idea['tags']) ? $idea['tags'] : json_decode($idea['tags'], true);
                if (is_array($tags)) {
                    if (in_array('nyereséges', $tags)) $profitableCount++;
                    if (in_array('népszerű', $tags)) $popularCount++;
                }
            }
        }
        
        $stats = [
            'totalIdeas' => $db->fetchOne("SELECT COUNT(*) as count FROM ideas WHERE user_id = ?", [$user['id']])['count'] ?? 0,
            'totalTasks' => $db->fetchOne("SELECT COUNT(*) as count FROM tasks t JOIN ideas i ON t.idea_id = i.id WHERE i.user_id = ?", [$user['id']])['count'] ?? 0,
            'completedTasks' => $db->fetchOne("SELECT COUNT(*) as count FROM tasks t JOIN ideas i ON t.idea_id = i.id WHERE i.user_id = ? AND t.status = 'VALIDATED'", [$user['id']])['count'] ?? 0,
            'totalUsers' => $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
            'ideasByPhase' => $db->fetchAll("SELECT phase, COUNT(*) as count FROM ideas WHERE user_id = ? GROUP BY phase", [$user['id']]),
            'tasksByStatus' => $db->fetchAll("SELECT t.status, COUNT(*) as count FROM tasks t JOIN ideas i ON t.idea_id = i.id WHERE i.user_id = ? GROUP BY t.status", [$user['id']]),
            'profitableCount' => $profitableCount,
            'popularCount' => $popularCount
        ];
        
        $this->render('statistics', ['stats' => $stats, 'user' => $user]);
    }
    
    public function newsletter()
    {
        if (!$this->auth->isAdmin()) {
            $this->redirect('/dashboard');
            return;
        }
        
        $db = Database::getInstance();
        
        $campaigns = $db->fetchAll("SELECT * FROM newsletter_campaigns ORDER BY created_at DESC LIMIT 20");
        $settings = $db->fetchAll("SELECT * FROM settings");
        
        $smtp = [];
        foreach ($settings as $s) {
            $smtp[$s['key']] = $s['value'];
        }
        
        $this->render('newsletter', ['campaigns' => $campaigns, 'smtp' => $smtp]);
    }
    
    public function feedback()
    {
        if (!$this->auth->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        $user = $this->auth->check();
        $db = Database::getInstance();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $message = $_POST['message'] ?? '';
            $type = $_POST['type'] ?? 'opinion';
            
            if ($message) {
                $id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                    mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000,
                    mt_rand(0, 0x3fff) | 0x8000,
                    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                );
                
                $stmt = $db->getConnection()->prepare(
                    "INSERT INTO feedbacks (id, user_id, message, type, created_at) VALUES (?, ?, ?, ?, NOW())"
                );
                $stmt->execute([$id, $user['id'], $message, $type]);
                
                $message = 'Köszönjük a visszajelzést!';
            }
        }
        
        if ($this->auth->isAdmin()) {
            $feedbacks = $db->fetchAll("SELECT f.*, u.name as user_name, u.email as user_email 
                FROM feedbacks f LEFT JOIN users u ON f.user_id = u.id 
                ORDER BY f.created_at DESC LIMIT 50");
        } else {
            $feedbacks = $db->fetchAll("SELECT * FROM feedbacks WHERE user_id = ? ORDER BY created_at DESC", [$user['id']]);
        }
        
        $this->render('feedback', ['feedbacks' => $feedbacks, 'message' => $message ?? null]);
    }
    
    public function guide()
    {
        $this->render('guide');
    }
    
    public function impresszum()
    {
        $this->render('impresszum');
    }
    
    public function privacy()
    {
        $this->render('privacy');
    }
    
    public function terms()
    {
        $this->render('terms');
    }
    
    private function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }
    
    private function notFound()
    {
        http_response_code(404);
        echo "<h1>404 - Oldal nem található</h1>";
    }
}
