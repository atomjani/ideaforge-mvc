<?php

class Controller
{
    protected $db;
    protected $user;
    protected $isAdmin = false;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->checkSession();
    }
    
    protected function checkSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (isset($_SESSION['user_id'])) {
            $this->user = [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'] ?? '',
                'name' => $_SESSION['user_name'] ?? '',
                'role' => $_SESSION['user_role'] ?? 'user'
            ];
            $this->isAdmin = ($this->user['role'] === 'admin');
        }
    }
    
    protected function requireLogin()
    {
        if (!$this->user) {
            $this->jsonResponse(['error' => 'Nincs bejelentkezve'], 401);
        }
    }
    
    protected function requireAdmin()
    {
        $this->requireLogin();
        if (!$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
    }
    
    protected function getInput()
    {
        $input = file_get_contents('php://input');
        $json = json_decode($input, true) ?: [];
        
        return array_merge($_POST, $json);
    }
    
    protected function getPost($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }
    
    protected function getGet($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }
    
    protected function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    protected function jsonResponse($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }
    
    protected function render($view, $data = [])
    {
        extract($data);
        
        $viewFile = "../app/views/pages/{$view}.php";
        
        if (!file_exists($viewFile)) {
            throw new Exception("View not found: {$view}");
        }
        
        include $viewFile;
    }
}
