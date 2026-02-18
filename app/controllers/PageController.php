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
            
            $passwordErrors = Security::validatePasswordStrength($password, 8);
            if (!empty($passwordErrors)) {
                $this->render('register', ['error' => implode('. ', $passwordErrors) . '.']);
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
        
        $satisfiedUsers = $db->fetchOne("
            SELECT COUNT(*) as count FROM (
                SELECT user_id FROM feedbacks 
                WHERE rating_overall >= 4 
                GROUP BY user_id
            ) as satisfied
        ")['count'] ?? 0;
        
        $dissatisfiedUsers = $db->fetchOne("
            SELECT COUNT(*) as count FROM (
                SELECT user_id FROM feedbacks 
                WHERE rating_overall <= 2 AND rating_overall > 0
                GROUP BY user_id
            ) as dissatisfied
        ")['count'] ?? 0;
        
        $feedbackStats = $db->fetchOne("
            SELECT 
                COUNT(*) as total,
                AVG(rating_overall) as avg_overall,
                AVG(rating_ideas) as avg_ideas,
                AVG(rating_tasks) as avg_tasks,
                AVG(rating_ui) as avg_ui
            FROM (
                SELECT f1.*
                FROM feedbacks f1
                INNER JOIN (
                    SELECT user_id, MAX(created_at) as max_date
                    FROM feedbacks
                    WHERE rating_overall > 0
                    GROUP BY user_id
                ) f2 ON f1.user_id = f2.user_id AND f1.created_at = f2.max_date
                WHERE f1.rating_overall > 0
            ) as latest_ratings
        ") ?? [];
        
        $feedbacks = $db->fetchAll("
            SELECT f.*, u.name as user_name 
            FROM feedbacks f 
            LEFT JOIN users u ON f.user_id = u.id 
            ORDER BY f.created_at DESC 
            LIMIT 20
        ");
        
        $stats = [
            'totalIdeas' => $db->fetchOne("SELECT COUNT(*) as count FROM ideas WHERE user_id = ?", [$user['id']])['count'] ?? 0,
            'totalTasks' => $db->fetchOne("SELECT COUNT(*) as count FROM tasks t JOIN ideas i ON t.idea_id = i.id WHERE i.user_id = ?", [$user['id']])['count'] ?? 0,
            'completedTasks' => $db->fetchOne("SELECT COUNT(*) as count FROM tasks t JOIN ideas i ON t.idea_id = i.id WHERE i.user_id = ? AND t.status = 'VALIDATED'", [$user['id']])['count'] ?? 0,
            'totalUsers' => $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'] ?? 0,
            'ideasByPhase' => $db->fetchAll("SELECT phase, COUNT(*) as count FROM ideas WHERE user_id = ? GROUP BY phase", [$user['id']]),
            'tasksByStatus' => $db->fetchAll("SELECT t.status, COUNT(*) as count FROM tasks t JOIN ideas i ON t.idea_id = i.id WHERE i.user_id = ? GROUP BY t.status", [$user['id']]),
            'profitableCount' => $profitableCount,
            'popularCount' => $popularCount,
            'feedbackTotal' => $feedbackStats['total'] ?? 0,
            'avgOverall' => round($feedbackStats['avg_overall'] ?? 0, 1),
            'avgIdeas' => round($feedbackStats['avg_ideas'] ?? 0, 1),
            'avgTasks' => round($feedbackStats['avg_tasks'] ?? 0, 1),
            'avgUi' => round($feedbackStats['avg_ui'] ?? 0, 1),
            'satisfied' => $satisfiedUsers,
            'dissatisfied' => $dissatisfiedUsers,
            'feedbacks' => $feedbacks
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
            $ratingOverall = isset($_POST['rating_overall']) ? (int)$_POST['rating_overall'] : 0;
            $ratingIdeas = isset($_POST['rating_ideas']) ? (int)$_POST['rating_ideas'] : 0;
            $ratingTasks = isset($_POST['rating_tasks']) ? (int)$_POST['rating_tasks'] : 0;
            $ratingUi = isset($_POST['rating_ui']) ? (int)$_POST['rating_ui'] : 0;
            $saveRating = isset($_POST['save_rating']);
            
            if ($saveRating) {
                $existing = $db->fetchOne("SELECT id FROM feedbacks WHERE user_id = ? AND rating_overall > 0", [$user['id']]);
                
                if ($existing) {
                    $stmt = $db->getConnection()->prepare(
                        "UPDATE feedbacks SET rating_overall = ?, rating_ideas = ?, rating_tasks = ?, rating_ui = ? WHERE user_id = ?"
                    );
                    $stmt->execute([$ratingOverall, $ratingIdeas, $ratingTasks, $ratingUi, $user['id']]);
                } else {
                    $id = bin2hex(random_bytes(16));
                    $stmt = $db->getConnection()->prepare(
                        "INSERT INTO feedbacks (id, user_id, message, type, rating_overall, rating_ideas, rating_tasks, rating_ui, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
                    );
                    $stmt->execute([$id, $user['id'], '', 'rating', $ratingOverall, $ratingIdeas, $ratingTasks, $ratingUi]);
                }
                
                $successMessage = 'Értékelés elmentve!';
            } elseif ($message || $ratingOverall > 0 || $ratingIdeas > 0 || $ratingTasks > 0 || $ratingUi > 0) {
                $id = bin2hex(random_bytes(16));
                
                $stmt = $db->getConnection()->prepare(
                    "INSERT INTO feedbacks (id, user_id, message, type, rating_overall, rating_ideas, rating_tasks, rating_ui, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
                );
                $stmt->execute([$id, $user['id'], $message, $type, $ratingOverall, $ratingIdeas, $ratingTasks, $ratingUi]);
                
                $successMessage = 'Köszönjük a visszajelzést!';
            } elseif (!$saveRating) {
                $errorMessage = 'Kérlek írj üzenetet vagy adj értékelést!';
            }
        }
        
        if ($this->auth->isAdmin()) {
            $feedbacks = $db->fetchAll("SELECT f.*, u.name as user_name, u.email as user_email 
                FROM feedbacks f LEFT JOIN users u ON f.user_id = u.id 
                ORDER BY f.created_at DESC LIMIT 50");
            $userRating = null;
        } else {
            $feedbacks = $db->fetchAll("SELECT * FROM feedbacks WHERE user_id = ? ORDER BY created_at DESC", [$user['id']]);
            $userRating = $db->fetchOne("SELECT * FROM feedbacks WHERE user_id = ? AND rating_overall > 0 ORDER BY created_at DESC LIMIT 1", [$user['id']]);
        }
        
        $this->render('feedback', ['feedbacks' => $feedbacks, 'message' => $successMessage ?? null, 'error' => $errorMessage ?? null, 'userRating' => $userRating]);
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
    
    public function analytics()
    {
        if (!$this->auth->isAdmin()) {
            $this->redirect('/dashboard');
            return;
        }
        
        require_once __DIR__ . '/../models/Analytics.php';
        $analytics = new Analytics();
        
        $days = (int)($_GET['days'] ?? 30);
        if ($days < 1) $days = 1;
        if ($days > 90) $days = 90;
        
        $stats = [
            'uniqueVisitors' => $analytics->getUniqueVisitors($days),
            'pageViews' => $analytics->getPageViews($days),
            'topPages' => $analytics->getTopPages($days, 10),
            'trafficSources' => $analytics->getTrafficSources($days),
            'dailyStats' => $analytics->getDailyStats($days),
            'registrations' => $analytics->getUserRegistrations($days),
            'avgTimeOnPage' => $analytics->getAvgTimeOnPage($days),
            'bounceRate' => $analytics->getBounceRate($days),
            'days' => $days
        ];
        
        $this->render('analytics', ['stats' => $stats]);
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
        exit;
    }
}
