<?php

namespace App\Models;

use PDO;
use App\Core\Database;


class User
{
    
    private $db;

    
    public function __construct($db)
    {
        if ($db instanceof Database) {
            $this->db = $db->getConnection();
        } elseif ($db instanceof PDO) {
            $this->db = $db;
        } else {
            throw new \InvalidArgumentException("Invalid database connection provided.");
        }
    }

    
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT user_id, name, phone, email, password, role, registration_date FROM users WHERE user_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result; 
    }

    
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT user_id, name, phone, email, password, role, registration_date, account_status, failed_login_attempts FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result === false ? null : $result; 
    }

    
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchColumn() > 0;
    }

    
    public function create(string $name, string $phone, string $email, string $password)
    {
        
        $stmt = $this->db->prepare("INSERT INTO users (name, phone, email, password) VALUES (:name, :phone, :email, :password)");
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':phone', $phone, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR); 

        if ($stmt->execute()) {
            return (int) $this->db->lastInsertId(); 
        }
        
        return false;
    }

    
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [':id' => $id];
        $allowedFields = ['name', 'phone', 'email', 'password', 'role']; 

        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "`$key` = :$key"; 
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false; 
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = :id";
        $stmt = $this->db->prepare($sql);

        
        foreach ($params as $key => &$value) { 
            if ($key === ':id') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        unset($value); 

        
        
        return $stmt->execute();
    }

    
    public function delete(int $id): bool
    {
        
        $stmt = $this->db->prepare("DELETE FROM users WHERE user_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute(); 
    }

    
    public function verifyPassword(string $email, string $password): bool
    {
        $user = $this->findByEmail($email);
        
        if ($user && isset($user['password'])) {
            
            if (\App\Core\Registry::has('logger')) {
                $logger = \App\Core\Registry::get('logger');
                $logger->debug("Password verification attempt", [
                    'email' => $email,
                    'password_length' => strlen($password),
                    'stored_hash' => $user['password'],
                    'hash_length' => strlen($user['password']),
                    'hash_starts_with' => substr($user['password'], 0, 7)
                ]);

                
                $result = password_verify($password, $user['password']);

                $logger->debug("Password verification result", [
                    'email' => $email,
                    'result' => $result ? 'success' : 'failure'
                ]);

                return $result;
            }

            
            return password_verify($password, $user['password']);
        }
        return false; 
    }

    
    public function getAll(): array
    {
        
        $stmt = $this->db->query("SELECT user_id, name, phone, email, role, account_status, registration_date FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function getAllUsersPaginated(int $page = 1, int $perPage = 15): array
    {
        $page = max(1, $page); 
        $offset = ($page - 1) * $perPage;

        
        $countStmt = $this->db->query("SELECT COUNT(*) FROM users");
        $totalUsers = (int) $countStmt->fetchColumn();
        $totalPages = $totalUsers > 0 ? ceil($totalUsers / $perPage) : 0;

        
        $stmt = $this->db->prepare("
            SELECT user_id, name, phone, email, role, account_status, registration_date
            FROM users
            ORDER BY user_id DESC 
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        
        return [
            'users' => $users,
            'pagination' => [
                'total' => $totalUsers,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => $totalPages,
                'has_more_pages' => ($page < $totalPages)
            ]
        ];
    }

    
    public function updateUser(int $id, array $data): bool
    {
        
        $allowedFields = ['name', 'phone', 'role', 'account_status'];
        $fields = [];
        $params = [':id' => $id];

        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "`$key` = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            return false; 
        }

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = :id";
        $stmt = $this->db->prepare($sql);

        
        foreach ($params as $key => &$value) {
            if ($key === ':id') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        unset($value); 

        return $stmt->execute(); 
    }

    
    public function generatePasswordResetToken(int $userId)
    {
        try {
            $token = bin2hex(random_bytes(32)); 
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); 

            $stmt = $this->db->prepare("
                UPDATE users
                SET reset_token = :token, reset_token_expires = :expires_at
                WHERE user_id = :user_id
            ");
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':expires_at', $expiresAt, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

            if ($stmt->execute()) {
                
                if ($stmt->rowCount() > 0) {
                    return $token;
                } else {
                    
                    return false;
                }
            }
            return false; 
        } catch (\Exception $e) {
            
            \App\Core\Registry::get('logger')->error("Error generating password reset token", ['exception' => $e, 'user_id' => $userId]);
            
            return false;
        }
    }

    
    public function hasRole(int $userId, string $role): bool
    {
        $user = $this->findById($userId);
        
        return $user && isset($user['role']) && $user['role'] === $role;
    }

    
    public function isAdmin(int $userId): bool
    {
        return $this->hasRole($userId, 'admin');
    }

    
    public function getTotalUserCount(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM users");
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            \App\Core\Registry::get('logger')->error("Error counting users", ['exception' => $e]);
            
            return 0; 
        }
    }
}
