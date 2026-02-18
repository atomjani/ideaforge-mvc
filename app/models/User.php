<?php

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    public function getAllUsers($limit = 100)
    {
        $sql = "SELECT id, email, name, role, created_at FROM users ORDER BY created_at DESC LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    public function getByEmail($email)
    {
        return $this->getBy('email', $email);
    }
    
    public function createUser($email, $name, $password, $role = 'user')
    {
        $id = $this->generateUuid();
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $data = [
            'id' => $id,
            'email' => $email,
            'name' => $name,
            'password' => $hash,
            'role' => $role,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($data);
    }
    
    public function updateUser($id, $name, $role = null)
    {
        $data = ['name' => $name];
        
        if ($role) {
            $data['role'] = $role;
        }
        
        return $this->update($id, $data);
    }
    
    public function updatePassword($id, $newPassword)
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($id, ['password' => $hash]);
    }
    
    public function deleteUser($id)
    {
        $this->db->query("DELETE FROM tasks WHERE idea_id IN (SELECT id FROM ideas WHERE user_id = ?)", [$id]);
        $this->db->query("DELETE FROM ideas WHERE user_id = ?", [$id]);
        $this->db->query("DELETE FROM feedbacks WHERE user_id = ?", [$id]);
        
        return $this->delete($id);
    }
    
    public function countUsers()
    {
        return $this->count();
    }
}
