<?php
namespace App\Helpers;
use PDO;
use PDOException;
use PDOStatement; 
function db_select(PDO $conn, string $query, array $params = []): array|false
{
    try {
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                if (is_int($param)) {
                    $stmt->bindValue($param + 1, $value, getParamType($value));
                } else {
                    $stmt->bindValue($param, $value, getParamType($value));
                }
            }
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error (SELECT): " . $e->getMessage() . " Query: " . $query);
        return false; 
    }
}
function db_insert(PDO $conn, string $table, array $data): string|false
{
    $table = sanitize_identifier($table);
    if (empty($data)) {
        error_log("Database error (INSERT): No data provided for table {$table}.");
        return false;
    }
    try {
        $columns = implode(', ', array_map('App\\Helpers\\sanitize_identifier', array_keys($data))); 
        $placeholders = ':' . implode(', :', array_keys($data)); 
        $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $conn->prepare($query);
        foreach ($data as $column => $value) {
            $stmt->bindValue(":{$column}", $value, getParamType($value));
        }
        $stmt->execute();
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        error_log("Database error (INSERT) into {$table}: " . $e->getMessage());
        return false;
    }
}
function db_update(PDO $conn, string $table, array $data, string $condition, array $params = []): int|false
{
    $table = sanitize_identifier($table);
    if (empty($data)) {
        error_log("Database error (UPDATE): No data provided for table {$table}.");
        return false;
    }
    try {
        $setClauses = [];
        foreach (array_keys($data) as $column) {
            $sanitizedColumn = sanitize_identifier($column); 
            $setClauses[] = "{$sanitizedColumn} = :set_{$sanitizedColumn}";
        }
        $set = implode(', ', $setClauses);
        $query = "UPDATE {$table} SET {$set} WHERE {$condition}";
        $stmt = $conn->prepare($query);
        foreach ($data as $column => $value) {
            $sanitizedColumn = sanitize_identifier($column);
            $stmt->bindValue(":set_{$sanitizedColumn}", $value, getParamType($value));
        }
        $paramIndex = 1; 
        foreach ($params as $param => $value) {
            if (is_int($param)) {
                $stmt->bindValue($paramIndex++, $value, getParamType($value)); 
            } else {
                $stmt->bindValue($param, $value, getParamType($value));
            }
        }
        $stmt->execute();
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Database error (UPDATE) on {$table}: " . $e->getMessage());
        return false;
    }
}
function db_delete(PDO $conn, string $table, string $condition, array $params = []): int|false
{
    $table = sanitize_identifier($table);
    try {
        $query = "DELETE FROM {$table} WHERE {$condition}";
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                if (is_int($param)) {
                    $stmt->bindValue($param + 1, $value, getParamType($value)); 
                } else {
                    $stmt->bindValue($param, $value, getParamType($value)); 
                }
            }
        }
        $stmt->execute();
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Database error (DELETE) from {$table}: " . $e->getMessage());
        return false;
    }
}
function db_query(PDO $conn, string $query, array $params = []): PDOStatement|false
{
    try {
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            foreach ($params as $param => $value) {
                if (is_int($param)) {
                    $stmt->bindValue($param + 1, $value, getParamType($value)); 
                } else {
                    $stmt->bindValue($param, $value, getParamType($value)); 
                }
            }
        }
        $stmt->execute();
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database error (QUERY): " . $e->getMessage() . " Query: " . $query);
        return false;
    }
}
function getParamType(mixed $value): int
{
    if (is_int($value)) {
        return PDO::PARAM_INT;
    } elseif (is_bool($value)) {
        return PDO::PARAM_BOOL;
    } elseif (is_null($value)) {
        return PDO::PARAM_NULL;
    } else {
        return PDO::PARAM_STR;
    }
}
function db_connect(string $host, string $db_name, string $username, string $password): PDO|false
{
    try {
        $dsn = "mysql:host={$host};dbname={$db_name};charset=utf8mb4"; 
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,         
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,    
            PDO::ATTR_EMULATE_PREPARES => false,                 
            PDO::MYSQL_ATTR_FOUND_ROWS => true                   
        ];
        $conn = new PDO($dsn, $username, $password, $options);
        return $conn;
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        return false;
    }
}
function sanitize_identifier(string $identifier): string
{
    return preg_replace('/[^a-zA-Z0-9_]/', '', $identifier);
}
function db_begin_transaction(PDO $conn): bool
{
    try {
        return $conn->beginTransaction();
    } catch (PDOException $e) {
        error_log("Transaction error (BEGIN): " . $e->getMessage());
        return false;
    }
}
function db_commit(PDO $conn): bool
{
    try {
        if ($conn->inTransaction()) {
            return $conn->commit();
        }
        error_log("Transaction warning (COMMIT): No active transaction to commit.");
        return false; 
    } catch (PDOException $e) {
        error_log("Transaction error (COMMIT): " . $e->getMessage());
        if ($conn->inTransaction()) {
            try {
                $conn->rollBack();
                error_log("Transaction error (COMMIT): Rollback attempted after commit failure.");
            } catch (PDOException $re) {
                error_log("Transaction error (COMMIT): Rollback failed after commit failure: " . $re->getMessage());
            }
        }
        return false;
    }
}
function db_rollback(PDO $conn): bool
{
    try {
        if ($conn->inTransaction()) {
            return $conn->rollBack();
        }
        error_log("Transaction warning (ROLLBACK): No active transaction to roll back.");
        return false; 
    } catch (PDOException $e) {
        error_log("Transaction error (ROLLBACK): " . $e->getMessage());
        return false;
    }
}
