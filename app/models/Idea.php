<?php

class Idea extends Model
{
    protected $table = 'ideas';
    protected $primaryKey = 'id';
    
    const PHASE_MVP = 'MVP_CREATION';
    const PHASE_DEVELOPMENT = 'DEVELOPMENT';
    
    const TAG_PROFITABLE = 'nyereséges';
    const TAG_POPULAR = 'népszerű';
    
    public static function getAvailableTags()
    {
        return [
            self::TAG_PROFITABLE => 'Nyereséges',
            self::TAG_POPULAR => 'Népszerű'
        ];
    }
    
    public function getTags($ideaId)
    {
        $idea = $this->getById($ideaId);
        if (!$idea || empty($idea['tags'])) {
            return [];
        }
        return is_array($idea['tags']) ? $idea['tags'] : json_decode($idea['tags'], true) ?? [];
    }
    
    public function setTags($ideaId, $tags = [])
    {
        $data['tags'] = json_encode($tags);
        return $this->updateIdea($ideaId, $data);
    }
    
    public function getUserIdeas($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    public function getAllIdeas($limit = 100)
    {
        $sql = "SELECT i.*, u.name as user_name, u.email as user_email 
                FROM {$this->table} i 
                LEFT JOIN users u ON i.user_id = u.id 
                ORDER BY i.created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function createIdea($userId, $name, $description, $problem = '', $targetAudience = '', $phase = self::PHASE_MVP)
    {
        $id = $this->generateUuid();
        
        $data = [
            'id' => $id,
            'user_id' => $userId,
            'name' => $name,
            'description' => $description,
            'problem' => $problem,
            'target_audience' => $targetAudience,
            'phase' => $phase,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->create($data);
        return $id;
    }
    
    public function updateIdea($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }
    
    public function deleteIdea($id)
    {
        $this->db->query("DELETE FROM tasks WHERE idea_id = ?", [$id]);
        return $this->delete($id);
    }
    
    public function canTransitionToDevelopment($id)
    {
        $sql = "SELECT COUNT(*) as total, 
                SUM(CASE WHEN status = 'VALIDATED' THEN 1 ELSE 0 END) as validated
                FROM tasks WHERE idea_id = ?";
        $result = $this->db->fetchOne($sql, [$id]);
        
        return $result && $result['total'] > 0 && $result['total'] == $result['validated'];
    }
    
    public function transitionToDevelopment($id)
    {
        $idea = $this->getById($id);
        if (!$idea || $idea['phase'] !== self::PHASE_MVP) {
            return false;
        }
        
        if (!$this->canTransitionToDevelopment($id)) {
            return false;
        }
        
        return $this->updateIdea($id, ['phase' => self::PHASE_DEVELOPMENT]);
    }
    
    public function countByPhase()
    {
        $sql = "SELECT phase, COUNT(*) as count FROM {$this->table} GROUP BY phase";
        return $this->db->fetchAll($sql);
    }
    
    public function countUserIdeas($userId)
    {
        return $this->count("user_id = ?", [$userId]);
    }
    
    public function search($query)
    {
        $sql = "SELECT i.*, u.name as user_name 
                FROM {$this->table} i 
                LEFT JOIN users u ON i.user_id = u.id 
                WHERE i.name LIKE ? OR i.description LIKE ? 
                ORDER BY i.created_at DESC";
        
        $searchTerm = "%{$query}%";
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm]);
    }
}
