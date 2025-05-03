<?php

namespace App\Models;

use PDO;
use App\Core\Database;
use App\Core\Registry;


class Product
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
        $stmt = $this->db->query("SELECT p.*, c.category_name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT p.*, c.category_name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    
    public function findByCategory(int $categoryId): array
    {
        $logger = Registry::get('logger');
        $logger->info('Product::findByCategory called.', ['categoryId' => $categoryId]);

        $childCategoryIds = [];
        try {
            
            $stmtChild = $this->db->prepare("SELECT category_id FROM categories WHERE parent_id = :parent_id");
            $stmtChild->bindParam(':parent_id', $categoryId, PDO::PARAM_INT);
            $stmtChild->execute();
            $childCategoryIds = $stmtChild->fetchAll(PDO::FETCH_COLUMN, 0); 
            $logger->info('Fetched child category IDs.', ['parent_id' => $categoryId, 'child_ids' => $childCategoryIds]);
        } catch (\PDOException $e) {
            $logger->error('Failed to fetch child categories.', ['error' => $e->getMessage(), 'categoryId' => $categoryId]);
            
            
        }

        
        $allCategoryIds = array_merge([$categoryId], $childCategoryIds);
        $allCategoryIds = array_unique(array_map('intval', $allCategoryIds)); 
        $logger->info('Combined category IDs for query.', ['allCategoryIds' => $allCategoryIds]);

        
        if (empty($allCategoryIds)) {
            $logger->warning('No valid category IDs found after combining parent and children.', ['original_categoryId' => $categoryId]);
            return [];
        }

        
        $placeholders = implode(',', array_fill(0, count($allCategoryIds), '?'));

        
        $sql = "SELECT p.*, c.category_name as category_name
                FROM products p
                JOIN categories c ON p.category_id = c.category_id
                WHERE p.category_id IN ($placeholders)
                ORDER BY p.name ASC";
        $logger->info('Preparing SQL query for findByCategory.', ['sql' => $sql]);

        $stmt = $this->db->prepare($sql);

        
        $paramIndex = 1; 
        foreach ($allCategoryIds as $id) {
            $stmt->bindValue($paramIndex++, $id, PDO::PARAM_INT);
        }
        $logger->info('Binding parameters for findByCategory.', ['bound_category_ids' => $allCategoryIds]);

        
        try {
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $logger->info('Fetched products by category.', ['results_count' => count($results), 'categoryIds' => $allCategoryIds]);
            return $results;
        } catch (\PDOException $e) {
            $logger->error('Database error during findByCategory execution.', ['error' => $e->getMessage(), 'sql' => $sql, 'params' => $allCategoryIds]);
            return []; 
        }
    }


    
    public function getFeaturedProducts(): array
    {
        
        
        $sql = "SELECT p.product_id, p.name, p.price, p.image_path, c.category_name as category_name FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id
                ORDER BY RAND()
                LIMIT 2"; 
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function getProductCategories(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT c.category_id, c.category_name FROM categories c JOIN products p ON c.category_id = p.category_id ORDER BY c.category_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function findMultipleByIds(array $ids): array
    {
        if (empty($ids)) {
            return []; 
        }

        
        $ids = array_map('intval', $ids);

        
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        
        $sql = "SELECT product_id as id, name, price, image_path as image, stock_quantity FROM products WHERE product_id IN ($placeholders)";
        $stmt = $this->db->prepare($sql);

        try {
            
            $stmt->execute($ids);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_UNIQUE);
        } catch (\PDOException $e) {
            
            Registry::get('logger')->error("Error fetching multiple products by ID", ['exception' => $e, 'ids' => $ids]);
            
            return []; 
        }
    }

    
    public function checkStock(int $id)
    {
        $stmt = $this->db->prepare("SELECT stock_quantity FROM products WHERE product_id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result !== false ? (int) $result['stock_quantity'] : false;
    }

    
    public function getAllProductsPaginated(int $page = 1, int $perPage = 15, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;

        
        $sql = "SELECT p.*, c.category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.category_id";
        $countSql = "SELECT COUNT(*) as total FROM products p"; 

        $whereConditions = [];
        $params = []; 
        $countParams = []; 

        
        if (!empty($filters['category_id'])) {
            $whereConditions[] = "p.category_id = ?"; 
            $params[] = $filters['category_id'];
            $countParams[] = $filters['category_id'];
        }
        if (isset($filters['is_active']) && ($filters['is_active'] === 0 || $filters['is_active'] === 1)) {
            $whereConditions[] = "p.is_active = ?";
            $params[] = $filters['is_active'];
            $countParams[] = $filters['is_active'];
        }

        
        if (!empty($whereConditions)) {
            $whereClause = " WHERE " . implode(" AND ", $whereConditions);
            $sql .= $whereClause;
            $countSql .= $whereClause;
        }

        
        $sql .= " ORDER BY p.name ASC LIMIT ? OFFSET ?";
        $params[] = $perPage; 
        $params[] = $offset;

        try {
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute($params);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($countParams); 
            $totalCount = (int) $countStmt->fetch(PDO::FETCH_ASSOC)['total']; 

            
            $totalPages = $totalCount > 0 ? ceil($totalCount / $perPage) : 0;

            $pagination = [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalCount,
                'total_pages' => $totalPages,
                'has_previous' => $page > 1,
                'has_next' => $page < $totalPages
            ];

            return [
                'products' => $products,
                'pagination' => $pagination
            ];
        } catch (\PDOException $e) {
            Registry::get('logger')->error("Error fetching paginated products", [
                'exception' => $e,
                'page' => $page,
                'perPage' => $perPage,
                'filters' => $filters
            ]);
            
            return [
                'products' => [],
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_items' => 0,
                    'total_pages' => 0,
                    'has_previous' => false,
                    'has_next' => false
                ]
            ];
        }
    }


    
    public function createProduct(array $data)
    {
        try {
            $sql = "INSERT INTO products (name, description, price, category_id, image_path, stock_quantity, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?)"; 
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                $data['name'] ?? null,
                $data['description'] ?? null,
                $data['price'] ?? 0.0,
                $data['category_id'] ?? null,
                $data['image_path'] ?? null,
                $data['stock_quantity'] ?? 0,
                $data['is_active'] ?? 1 
            ]);
            return (int) $this->db->lastInsertId(); 
        } catch (\PDOException $e) {
            Registry::get('logger')->error("Error creating product", ['exception' => $e, 'data' => $data]);
            
            return false;
        }
    }

    
    public function updateProduct(int $id, array $data): bool
    {
        try {
            $sql = "UPDATE products
                    SET name = ?, description = ?, price = ?, category_id = ?,
                        image_path = ?, stock_quantity = ?, is_active = ?
                    WHERE product_id = ?"; 
            $stmt = $this->db->prepare($sql);
            
            $result = $stmt->execute([
                $data['name'] ?? null,
                $data['description'] ?? null,
                $data['price'] ?? 0.0,
                $data['category_id'] ?? null,
                $data['image_path'] ?? null,
                $data['stock_quantity'] ?? 0,
                $data['is_active'] ?? 1,
                $id 
            ]);
            
            return $result && $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            Registry::get('logger')->error("Error updating product", ['exception' => $e, 'product_id' => $id, 'data' => $data]);
            
            return false;
        }
    }

    
    public function toggleProductActiveStatus(int $id): bool
    {
        try {
            
            $sql = "UPDATE products SET is_active = NOT is_active WHERE product_id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$id]);
            
            return $result && $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            Registry::get('logger')->error("Error toggling product status", ['exception' => $e, 'product_id' => $id]);
            
            return false;
        }
    }

    
    public function getLowStockProductCount(int $threshold = 5): int
    {
        try {
            
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE stock_quantity <= :threshold AND is_active = 1");
            $stmt->bindParam(':threshold', $threshold, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            Registry::get('logger')->error("Error counting low stock products", ['exception' => $e, 'threshold' => $threshold]);
            
            return 0; 
        }
    }

    
    public function getTotalProductCount(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM products");
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            Registry::get('logger')->error("Error counting total products", ['exception' => $e]);
            return 0; 
        }
    }
}
