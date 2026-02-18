<?php

class AdminController extends Controller
{
    private $userModel;
    private $ideaModel;
    private $taskModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->ideaModel = new Idea();
        $this->taskModel = new Task();
    }
    
    public function get_stats()
    {
        $this->requireAdmin();
        
        $totalIdeas = $this->ideaModel->count();
        $totalTasks = $this->taskModel->count();
        
        $completedTasks = $this->taskModel->count("status = 'VALIDATED'");
        $inProgressTasks = $this->taskModel->count("status = 'IN_PROGRESS'");
        $totalUsers = $this->userModel->countUsers();
        
        $ideasByPhase = $this->ideaModel->countByPhase();
        
        $this->jsonResponse([
            'totalIdeas' => $totalIdeas,
            'totalTasks' => $totalTasks,
            'completedTasks' => $completedTasks,
            'inProgressTasks' => $inProgressTasks,
            'totalUsers' => $totalUsers,
            'ideasByPhase' => $ideasByPhase
        ]);
    }
    
    public function get_activity_log()
    {
        $this->requireAdmin();
        
        $limit = $this->getGet('limit') ?? 50;
        $limit = min($limit, 100);
        
        $logs = $this->db->fetchAll(
            "SELECT * FROM activity_log ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
        
        $this->jsonResponse($logs);
    }
    
    public function get_settings()
    {
        $this->requireAdmin();
        
        $settings = $this->db->fetchAll("SELECT * FROM settings");
        
        $result = [];
        foreach ($settings as $s) {
            $result[$s['key']] = $s['value'];
        }
        
        $this->jsonResponse($result);
    }
    
    public function save_smtp_settings()
    {
        $this->requireAdmin();
        
        $input = $this->getInput();
        
        $fields = [
            'smtp_host' => $input['smtp_host'] ?? '',
            'smtp_port' => $input['smtp_port'] ?? 587,
            'smtp_user' => $input['smtp_user'] ?? '',
            'smtp_pass' => $input['smtp_pass'] ?? '',
            'smtp_from_email' => $input['smtp_from_email'] ?? '',
            'smtp_from_name' => $input['smtp_from_name'] ?? ''
        ];
        
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO settings (id, key, value) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE value = VALUES(value)"
        );
        
        $id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        
        foreach ($fields as $key => $value) {
            $stmt->execute([$id, $key, $value]);
        }
        
        $this->jsonResponse(['status' => 'ok']);
    }
    
    public function delete_feedback()
    {
        $this->requireAdmin();
        
        $input = $this->getInput();
        $id = $input['id'] ?? '';
        
        if (!$id) {
            $this->jsonResponse(['error' => 'Hiányzó azonosító'], 400);
        }
        
        $this->db->delete('feedbacks', "id = :id", ['id' => $id]);
        
        $this->jsonResponse(['status' => 'ok']);
    }
    
    public function get_feedbacks()
    {
        $this->requireAdmin();
        
        $feedbacks = $this->db->fetchAll(
            "SELECT f.*, u.email as user_email, u.name as user_name 
             FROM feedbacks f 
             LEFT JOIN users u ON f.user_id = u.id 
             ORDER BY f.created_at DESC"
        );
        
        $this->jsonResponse($feedbacks);
    }
    
    public function create_feedback()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $message = $input['message'] ?? '';
        
        if (!$message) {
            $this->jsonResponse(['error' => 'Hiányzó üzenet'], 400);
        }
        
        $id = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO feedbacks (id, user_id, message, created_at) VALUES (?, ?, ?, NOW())"
        );
        
        $stmt->execute([$id, $this->user['id'], $message]);
        
        $this->jsonResponse(['status' => 'ok']);
    }
}
