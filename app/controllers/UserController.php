<?php

class UserController extends Controller
{
    private $auth;
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->auth = new Auth();
        $this->userModel = new User();
    }
    
    public function login()
    {
        $input = $this->getInput();
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        
        if (!$email || !$password) {
            $this->jsonResponse(['error' => 'Hiányzó adatok'], 400);
        }
        
        $result = $this->auth->login($email, $password);
        
        if (isset($result['error'])) {
            $this->jsonResponse($result, 401);
        }
        
        $this->jsonResponse($result);
    }
    
    public function register()
    {
        $input = $this->getInput();
        $email = $input['email'] ?? '';
        $name = $input['name'] ?? '';
        $password = $input['password'] ?? '';
        
        if (!$email || !$password || !$name) {
            $this->jsonResponse(['error' => 'Hiányzó adatok'], 400);
        }
        
        $result = $this->auth->register($email, $name, $password);
        
        if (isset($result['error'])) {
            $this->jsonResponse($result, 400);
        }
        
        $this->jsonResponse($result);
    }
    
    public function logout()
    {
        $result = $this->auth->logout();
        $this->jsonResponse($result);
    }
    
    public function check_session()
    {
        $user = $this->auth->check();
        
        if (!$user) {
            $this->jsonResponse(['error' => 'Nincs bejelentkezve'], 401);
        }
        
        $this->jsonResponse(['user' => $user]);
    }
    
    public function update_profile()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $name = $input['name'] ?? '';
        
        if (!$name) {
            $this->jsonResponse(['error' => 'Hiányzó név'], 400);
        }
        
        $result = $this->auth->updateProfile($this->user['id'], $name);
        $this->jsonResponse($result);
    }
    
    public function change_password()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $oldPassword = $input['old_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';
        
        if (!$oldPassword || !$newPassword) {
            $this->jsonResponse(['error' => 'Hiányzó adatok'], 400);
        }
        
        $result = $this->auth->changePassword($this->user['id'], $oldPassword, $newPassword);
        
        if (isset($result['error'])) {
            $this->jsonResponse($result, 400);
        }
        
        $this->jsonResponse($result);
    }
    
    public function get_all_users()
    {
        $this->requireAdmin();
        
        $users = $this->userModel->getAllUsers();
        $this->jsonResponse($users);
    }
    
    public function delete_account()
    {
        $this->requireLogin();
        
        $input = $this->getInput();
        $targetId = $input['user_id'] ?? $this->user['id'];
        
        if ($targetId !== $this->user['id'] && !$this->isAdmin) {
            $this->jsonResponse(['error' => 'Nincs jogosultság'], 403);
        }
        
        $this->userModel->deleteUser($targetId);
        
        if ($targetId === $this->user['id']) {
            $this->auth->logout();
        }
        
        $this->jsonResponse(['status' => 'ok']);
    }
    
    public function update_user()
    {
        $this->requireAdmin();
        
        $input = $this->getInput();
        $userId = $input['user_id'] ?? '';
        $name = $input['name'] ?? '';
        $role = $input['role'] ?? '';
        
        if (!$userId || !$name) {
            $this->jsonResponse(['error' => 'Hiányzó adatok'], 400);
        }
        
        $this->userModel->updateUser($userId, $name, $role);
        $this->jsonResponse(['status' => 'ok']);
    }
}
