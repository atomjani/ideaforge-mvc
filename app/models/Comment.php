<?php

class Comment extends Model
{
    protected $table = 'comments';
    protected $primaryKey = 'id';
    
    public function getByTask($taskId)
    {
        $sql = "SELECT c.*, u.name as user_name, u.email as user_email 
                FROM {$this->table} c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE c.task_id = ? 
                ORDER BY c.created_at ASC";
        return $this->db->fetchAll($sql, [$taskId]);
    }
    
    public function createComment($taskId, $userId, $text)
    {
        $id = $this->generateUuid();
        
        $data = [
            'id' => $id,
            'task_id' => $taskId,
            'user_id' => $userId,
            'text' => $text,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($data);
    }
    
    public function deleteComment($id)
    {
        return $this->delete($id);
    }
}
