<?php

class Newsletter extends Model
{
    protected $table = 'newsletter_subscriptions';
    protected $primaryKey = 'id';
    
    public function subscribe($email, $name, $type = 'public')
    {
        $id = $this->generateUuid();
        
        $stmt = $this->db->getConnection()->prepare(
            "INSERT INTO {$this->table} (id, email, name, type) 
             VALUES (?, ?, ?, ?) 
             ON DUPLICATE KEY UPDATE name = ?, type = ?, unsubscribed_at = NULL"
        );
        
        return $stmt->execute([$id, $email, $name, $type, $name, $type]);
    }
    
    public function unsubscribe($email)
    {
        $stmt = $this->db->getConnection()->prepare(
            "UPDATE {$this->table} SET unsubscribed_at = NOW() WHERE email = ?"
        );
        
        return $stmt->execute([$email]);
    }
    
    public function getActiveSubscribers()
    {
        $sql = "SELECT email, name FROM {$this->table} WHERE unsubscribed_at IS NULL";
        return $this->db->fetchAll($sql);
    }
    
    public function getAllEmails()
    {
        $emails = [];
        
        $subscribers = $this->getActiveSubscribers();
        foreach ($subscribers as $s) {
            $emails[] = $s['email'];
        }
        
        $users = $this->db->fetchAll("SELECT email FROM users");
        foreach ($users as $u) {
            if (!in_array($u['email'], $emails)) {
                $emails[] = $u['email'];
            }
        }
        
        return $emails;
    }
    
    public function getRegisteredUserEmails()
    {
        return $this->db->fetchAll("SELECT email, name FROM users");
    }
    
    public function isSubscribed($email)
    {
        $sql = "SELECT id FROM {$this->table} WHERE email = ? AND unsubscribed_at IS NULL";
        return (bool) $this->db->fetchOne($sql, [$email]);
    }
}
