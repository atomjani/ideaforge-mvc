<?php

class Task extends Model
{
    protected $table = 'tasks';
    protected $primaryKey = 'id';
    
    const STATUS_BACKLOG = 'BACKLOG';
    const STATUS_REVIEW = 'REVIEW';
    const STATUS_READY_FOR_DEV = 'READY_FOR_DEV';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_LIVE_TESTING = 'LIVE_TESTING';
    const STATUS_VALIDATED = 'VALIDATED';
    
    const PRIORITY_MUST_HAVE = 'MUST_HAVE';
    const PRIORITY_IMPORTANT = 'IMPORTANT';
    const PRIORITY_NICE_TO_HAVE = 'NICE_TO_HAVE';
    const PRIORITY_NOT_NEEDED = 'NOT_NEEDED';
    
    const TYPE_FEATURE = 'FEATURE';
    const TYPE_BUG = 'BUG';
    
    public static function getStatuses()
    {
        return [
            self::STATUS_BACKLOG,
            self::STATUS_REVIEW,
            self::STATUS_READY_FOR_DEV,
            self::STATUS_IN_PROGRESS,
            self::STATUS_LIVE_TESTING,
            self::STATUS_VALIDATED
        ];
    }
    
    public static function getPriorities()
    {
        return [
            self::PRIORITY_MUST_HAVE,
            self::PRIORITY_IMPORTANT,
            self::PRIORITY_NICE_TO_HAVE,
            self::PRIORITY_NOT_NEEDED
        ];
    }
    
    public static function getTypes()
    {
        return [
            self::TYPE_FEATURE,
            self::TYPE_BUG
        ];
    }
    
    public function getByIdea($ideaId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE idea_id = ? ORDER BY priority ASC, created_at ASC";
        return $this->db->fetchAll($sql, [$ideaId]);
    }
    
    public function getByIdeaGroupedByStatus($ideaId)
    {
        $tasks = $this->getByIdea($ideaId);
        $grouped = [];
        
        foreach (self::getStatuses() as $status) {
            $grouped[$status] = [];
        }
        
        foreach ($tasks as $task) {
            $status = $task['status'] ?? self::STATUS_BACKLOG;
            if (!isset($grouped[$status])) {
                $grouped[$status] = [];
            }
            $grouped[$status][] = $task;
        }
        
        return $grouped;
    }
    
    public function getByStatus($status)
    {
        $sql = "SELECT t.*, i.name as idea_name 
                FROM {$this->table} t 
                LEFT JOIN ideas i ON t.idea_id = i.id 
                WHERE t.status = ? 
                ORDER BY t.created_at DESC";
        return $this->db->fetchAll($sql, [$status]);
    }
    
    public function createTask($ideaId, $description, $priority = self::PRIORITY_MUST_HAVE, $module = '', $type = self::TYPE_FEATURE, $iceImpact = 5, $iceConfidence = 5, $iceEase = 5, $name = '')
    {
        $id = $this->generateUuid();
        
        $data = [
            'id' => $id,
            'idea_id' => $ideaId,
            'name' => $name,
            'description' => $description,
            'priority' => $priority,
            'status' => self::STATUS_BACKLOG,
            'module' => $module,
            'type' => $type,
            'ice_impact' => $iceImpact,
            'ice_confidence' => $iceConfidence,
            'ice_ease' => $iceEase,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $this->create($data);
        return $id;
    }
    
    public function updateTask($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }
    
    public function updateStatus($id, $status)
    {
        if (!in_array($status, self::getStatuses())) {
            return false;
        }
        return $this->updateTask($id, ['status' => $status]);
    }
    
    public function deleteTask($id)
    {
        return $this->delete($id);
    }
    
    public function countByStatus()
    {
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
        return $this->db->fetchAll($sql);
    }
    
    public function countIdeaTasks($ideaId)
    {
        return $this->count("idea_id = ?", [$ideaId]);
    }
    
    public function countValidatedTasks($ideaId)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE idea_id = ? AND status = ?";
        $result = $this->db->fetchOne($sql, [$ideaId, self::STATUS_VALIDATED]);
        return $result ? $result['count'] : 0;
    }
    
    public function countTotalTasks($ideaId)
    {
        return $this->count("idea_id = ?", [$ideaId]);
    }
}
