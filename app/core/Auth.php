<?php

class Auth
{
    private $db;
    private $session;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->session = Session::class;
    }
    
    public function login($email, $password)
    {
        $stmt = $this->db->getConnection()->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return ['error' => 'Hibás email vagy jelszó'];
        }
        
        Session::set('user_id', $user['id']);
        Session::set('user_email', $user['email']);
        Session::set('user_name', $user['name']);
        Session::set('user_role', $user['role']);
        
        $this->logActivity($user['id'], 'login');
        
        unset($user['password']);
        return ['user' => $user];
    }
    
    public function register($email, $name, $password)
    {
        $stmt = $this->db->getConnection()->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            return ['error' => 'Ez az email már foglalt'];
        }
        
        $id = $this->generateUuid();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO users (id, email, name, password, role) VALUES (?, ?, ?, ?, 'user')"
        );
        
        if ($stmt->execute([$id, $email, $name, $hash])) {
            Session::set('user_id', $id);
            Session::set('user_email', $email);
            Session::set('user_name', $name);
            Session::set('user_role', 'user');
            
            $this->logActivity($id, 'register');
            
            return ['user' => [
                'id' => $id,
                'email' => $email,
                'name' => $name,
                'role' => 'user'
            ]];
        }
        
        return ['error' => 'Regisztrációs hiba'];
    }
    
    public function logout()
    {
        $userId = Session::get('user_id');
        
        if ($userId) {
            $this->logActivity($userId, 'logout');
        }
        
        Session::destroy();
        return ['status' => 'ok'];
    }
    
    public function check()
    {
        if (!Session::has('user_id')) {
            return null;
        }
        
        return [
            'id' => Session::get('user_id'),
            'email' => Session::get('user_email'),
            'name' => Session::get('user_name'),
            'role' => Session::get('user_role')
        ];
    }
    
    public function isAdmin()
    {
        return Session::get('user_role') === 'admin';
    }
    
    public function isLoggedIn()
    {
        return Session::has('user_id');
    }
    
    public function changePassword($userId, $oldPassword, $newPassword)
    {
        $stmt = $this->db->getConnection()->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!password_verify($oldPassword, $user['password'])) {
            return ['error' => 'A jelenlegi jelszó hibás'];
        }
        
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $stmt = $this->db->getConnection()->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $userId]);
        
        $this->logActivity($userId, 'password_change');
        
        return ['status' => 'ok'];
    }
    
    public function updateProfile($userId, $name)
    {
        $stmt = $this->db->getConnection()->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->execute([$name, $userId]);
        
        Session::set('user_name', $name);
        
        return ['status' => 'ok'];
    }
    
    private function logActivity($userId, $action)
    {
        try {
            $stmt = $this->db->getConnection()->prepare(
                "INSERT INTO activity_log (id, user_id, action, ip_address) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([
                $this->generateUuid(),
                $userId,
                $action,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
        } catch (Exception $e) {
            // Activity logging failed - continue silently
        }
    }
    
    private function generateUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
