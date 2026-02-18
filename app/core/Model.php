<?php

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    public function getAll($orderBy = null, $limit = null)
    {
        $sql = "SELECT * FROM {$this->table}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->db->fetchAll($sql);
    }
    
    public function getById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->fetchOne($sql, ['id' => $id]);
    }
    
    public function getBy($field, $value)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = :value";
        return $this->db->fetchOne($sql, ['value' => $value]);
    }
    
    public function create($data)
    {
        if (!isset($data['id'])) {
            $data['id'] = $this->generateUuid();
        }
        
        return $this->db->insert($this->table, $data);
    }
    
    public function update($id, $data)
    {
        return $this->db->update(
            $this->table,
            $data,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }
    
    public function delete($id)
    {
        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }
    
    public function count($where = null, $params = [])
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] ?? 0;
    }
    
    protected function generateUuid()
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
