<?php

namespace App\Core;

use PDO; 
use PDOException; 
use App\Core\Registry; 


class Database
{
    
    private $host;
    
    private $db_name;
    
    private $username;
    
    private $password;
    
    private $charset = 'utf8';
    
    private $conn;
    
    private $error = '';

    
    public function __construct($host, $db_name, $username, $password, $charset = 'utf8')
    {
        $this->host = $host;
        $this->db_name = $db_name;
        $this->username = $username;
        $this->password = $password;
        $this->charset = $charset;
        $this->connect(); 
    }

    
    public function getConnection(): ?PDO
    {
        if ($this->conn) {
            return $this->conn;
        }
        
        return $this->connect();
    }

    
    private function connect(): ?PDO
    {
        
        if ($this->conn) {
            return $this->conn;
        }

        
        $this->conn = null;
        
        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;

        try {
            
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, 
                    PDO::ATTR_EMULATE_PREPARES => false, 
                ]
            );
            $this->error = ''; 
        } catch (PDOException $e) {
            
            $this->error = "Connection Error: " . $e->getMessage();
            
            if (Registry::has('logger')) {
                Registry::get('logger')->critical("Database connection error: " . $e->getMessage(), ['exception' => $e]);
            }
            $this->conn = null; 
        }

        return $this->conn;
    }

    
    public function getError(): string
    {
        return $this->error;
    }

    
    public function select($query, $params = [])
    {
        if (!$this->conn) {
            $this->error = "Database not connected.";
            if (Registry::has('logger')) {
                Registry::get('logger')->error("Attempted query on non-existent DB connection.");
            }
            return false;
        }

        try {
            $stmt = $this->conn->prepare($query); 
            $stmt->execute($params); 
            $this->error = ''; 
            return $stmt; 
        } catch (PDOException $e) {
            $this->error = "Query Error: " . $e->getMessage();
            if (Registry::has('logger')) {
                Registry::get('logger')->error("Database query error: " . $e->getMessage(), [
                    'query' => $query,
                    'params' => $params, 
                    'exception' => $e
                ]);
            }
            return false; 
        }
    }

    
    public function execute($query, $params = [])
    {
        if (!$this->conn) {
            $this->error = "Database not connected.";
            if (Registry::has('logger')) {
                Registry::get('logger')->error("Attempted execute on non-existent DB connection.");
            }
            return false;
        }

        try {
            $stmt = $this->conn->prepare($query); 
            $stmt->execute($params); 
            $this->error = ''; 
            return $stmt->rowCount(); 
        } catch (PDOException $e) {
            $this->error = "Query Error: " . $e->getMessage();
            if (Registry::has('logger')) {
                Registry::get('logger')->error("Database execute error: " . $e->getMessage(), [
                    'query' => $query,
                    'params' => $params, 
                    'exception' => $e
                ]);
            }
            return false; 
        }
    }

    
    public function beginTransaction(): bool
    {
        if (!$this->conn) {
            $this->error = "Database not connected.";
            if (Registry::has('logger')) {
                Registry::get('logger')->error("Attempted beginTransaction on non-existent DB connection.");
            }
            return false;
        }

        try {
            $this->error = '';
            return $this->conn->beginTransaction();
        } catch (PDOException $e) {
            $this->error = "Transaction Error: " . $e->getMessage();
            if (Registry::has('logger')) {
                Registry::get('logger')->error("Transaction begin error: " . $e->getMessage(), ['exception' => $e]);
            }
            return false;
        }
    }

    
    public function commit(): bool
    {
        if (!$this->conn || !$this->conn->inTransaction()) {
            $this->error = "Cannot commit: No active transaction or connection.";
            if (Registry::has('logger')) {
                Registry::get('logger')->error($this->error);
            }
            return false;
        }

        try {
            $this->error = '';
            return $this->conn->commit();
        } catch (PDOException $e) {
            $this->error = "Commit Error: " . $e->getMessage();
            if (Registry::has('logger')) {
                Registry::get('logger')->error("Transaction commit error: " . $e->getMessage(), ['exception' => $e]);
            }
            
            $this->rollback();
            return false;
        }
    }

    
    public function rollback(): bool
    {
        if (!$this->conn || !$this->conn->inTransaction()) {
            $this->error = "Cannot rollback: No active transaction or connection.";
            if (Registry::has('logger')) {
                Registry::get('logger')->error($this->error);
            }
            return false;
        }

        try {
            $this->error = '';
            return $this->conn->rollBack();
        } catch (PDOException $e) {
            $this->error = "Rollback Error: " . $e->getMessage();
            if (Registry::has('logger')) {
                Registry::get('logger')->error("Transaction rollback error: " . $e->getMessage(), ['exception' => $e]);
            }
            return false;
        }
    }

    
    public function lastInsertId($name = null)
    {
        if (!$this->conn) {
            $this->error = "Database not connected.";
            if (Registry::has('logger')) {
                Registry::get('logger')->error("Attempted lastInsertId on non-existent DB connection.");
            }
            return false;
        }

        try {
            $this->error = '';
            
            return $this->conn->lastInsertId($name);
        } catch (PDOException $e) {
            $this->error = "LastInsertId Error: " . $e->getMessage();
            if (Registry::has('logger')) {
                Registry::get('logger')->error("LastInsertId error: " . $e->getMessage(), ['exception' => $e]);
            }
            return false;
        }
    }

    
    public function closeConnection(): void
    {
        $this->conn = null;
    }

    
    public function __destruct()
    {
        $this->closeConnection();
    }
}
