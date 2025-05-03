<?php

namespace App\Models;

use PDO;
use App\Core\Database;


class Category
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

    
    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM categories ORDER BY category_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE category_id = :category_id");
        $stmt->bindParam(':category_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
    public function getAllTopLevel(): array
    {
        $stmt = $this->db->query("SELECT * FROM categories WHERE parent_id IS NULL ORDER BY category_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function getSubcategoriesByParentId(int $parentId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE parent_id = :parentId ORDER BY category_name ASC");
        $stmt->bindParam(':parentId', $parentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function getAllCategoriesPaginated(int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;

        
        $countStmt = $this->db->query("SELECT COUNT(*) FROM categories");
        $totalCount = (int) $countStmt->fetchColumn();

        
        $stmt = $this->db->prepare("
            SELECT c.*, p.category_name as parent_name
            FROM categories c
            LEFT JOIN categories p ON c.parent_id = p.category_id
            ORDER BY c.category_name ASC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        
        $totalPages = ceil($totalCount / $perPage);

        
        return [
            'categories' => $categories,
            'pagination' => [
                'total_items' => $totalCount,
                'total_pages' => $totalPages,
                'current_page' => $page,
                'per_page' => $perPage,
                'has_previous' => $page > 1,
                'has_next' => $page < $totalPages
            ]
        ];
    }

    
    public function createCategory(array $data): int|bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO categories (category_name, parent_id)
            VALUES (:name, :parent_id)
        ");
        $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);

        
        if (!empty($data['parent_id'])) {
            $stmt->bindParam(':parent_id', $data['parent_id'], PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':parent_id', null, PDO::PARAM_NULL);
        }

        if ($stmt->execute()) {
            return (int) $this->db->lastInsertId();
        }
        return false;
    }

    
    public function updateCategory(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE categories
            SET category_name = :name, parent_id = :parent_id
            WHERE category_id = :category_id
        ");
        $stmt->bindParam(':category_id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);

        
        if (!empty($data['parent_id']) && $data['parent_id'] != $id) {
            $stmt->bindParam(':parent_id', $data['parent_id'], PDO::PARAM_INT);
        } else {
            
            $stmt->bindValue(':parent_id', null, PDO::PARAM_NULL);
        }

        return $stmt->execute();
    }

    
    public function hasProducts(int $id): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM products WHERE category_id = :category_id
        ");
        $stmt->bindParam(':category_id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return (int) $stmt->fetchColumn() > 0;
    }

    
    public function deleteCategory(int $id): bool
    {
        
        if ($this->hasProducts($id)) {
            return false;
        }

        $stmt = $this->db->prepare("DELETE FROM categories WHERE category_id = :category_id");
        $stmt->bindParam(':category_id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    
    public function getTotalCategoryCount(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM categories");
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            
            if (class_exists('\\App\\Core\\Registry') && \App\Core\Registry::has('logger')) {
                \App\Core\Registry::get('logger')->error("Error counting total categories", ['exception' => $e]);
            }
            return 0; 
        }
    }
}
