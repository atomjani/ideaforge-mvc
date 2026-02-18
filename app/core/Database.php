<?php

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $pdo;
    
    private $host;
    private $dbname;
    private $username;
    private $password;
    private $charset;
    
    private function __construct()
    {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->dbname = getenv('DB_NAME') ?: 'kalandja_ideaforge';
        $this->username = getenv('DB_USER') ?: 'kalandja_Ideaforge';
        $this->password = getenv('DB_PASS') ?: 'Gamestar.1986';
        $this->charset = 'utf8mb4';
        
        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
        ];
        
        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection()
    {
        return $this->pdo;
    }
    
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    
    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function insert($table, $data)
    {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = implode(', ', array_map(fn($k) => ":$k", $keys));
        
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return $this->pdo->lastInsertId();
    }
    
    public function update($table, $data, $where, $whereParams = [])
    {
        $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($data)));
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        $params = array_merge($data, $whereParams);
        $this->query($sql, $params);
        
        return true;
    }
    
    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $this->query($sql, $params);
        
        return true;
    }
    
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }
    
    public function commit()
    {
        return $this->pdo->commit();
    }
    
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }
    
    private function __clone() {}
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
