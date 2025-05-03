<?php

namespace App\Models;

use PDO;
use App\Core\Database; 
use App\Core\Registry;


class OrderItem
{
    
    private $db;

    
    private $error_message = '';

    
    public $item_id;

    
    public $order_id;

    
    public $product_id;

    
    public $quantity;

    
    public $price;

    
    public $product_name;

    
    public $product_image;

    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    
    public function getErrorMessage(): string
    {
        return $this->error_message;
    }

    
    public function create(array $data): int|false
    {
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (:order_id, :product_id, :quantity, :price)";
        $stmt = $this->db->prepare($sql);

        
        $stmt->bindParam(':order_id', $data['order_id'], PDO::PARAM_INT);
        $stmt->bindParam(':product_id', $data['product_id'], PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $data['quantity'], PDO::PARAM_INT);
        $stmt->bindParam(':price', $data['price']); 

        try {
            if ($stmt->execute()) {
                return (int) $this->db->lastInsertId(); 
            } else {
                $this->error_message = "Failed to create order item: " . implode(", ", $stmt->errorInfo());
                return false;
            }
        } catch (\PDOException $e) {
            $this->error_message = "Database error creating order item: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e]);
            return false;
        }
    }

    
    public function createBulk(int $orderId, array $items): bool
    {
        if (empty($items)) {
            $this->error_message = "No items provided for bulk insert.";
            return false;
        }

        
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ";
        $valuePlaceholders = [];
        $params = [];
        $paramIndex = 0; 

        
        foreach ($items as $item) {
            
            if (!isset($item['product_id'], $item['quantity'], $item['price'])) {
                $this->error_message = "Invalid item data provided for bulk insert. Missing required keys.";
                Registry::get('logger')->warning($this->error_message, ['item_data' => $item, 'order_id' => $orderId]);
                return false; 
            }

            
            $orderIdPlaceholder = ":order_id_" . $paramIndex;
            $productIdPlaceholder = ":product_id_" . $paramIndex;
            $quantityPlaceholder = ":quantity_" . $paramIndex;
            $pricePlaceholder = ":price_" . $paramIndex;

            
            $valuePlaceholders[] = "($orderIdPlaceholder, $productIdPlaceholder, $quantityPlaceholder, $pricePlaceholder)";

            
            $params[$orderIdPlaceholder] = $orderId;
            $params[$productIdPlaceholder] = $item['product_id'];
            $params[$quantityPlaceholder] = $item['quantity'];
            $params[$pricePlaceholder] = $item['price'];

            $paramIndex++;
        }

        
        $sql .= implode(', ', $valuePlaceholders);

        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $placeholder => $value) {
            
            if (strpos($placeholder, ':order_id_') === 0 || strpos($placeholder, ':product_id_') === 0 || strpos($placeholder, ':quantity_') === 0) {
                $stmt->bindValue($placeholder, $value, PDO::PARAM_INT);
            } elseif (strpos($placeholder, ':price_') === 0) {
                $stmt->bindValue($placeholder, $value); 
            }
            
        }

        
        try {
            if ($stmt->execute()) {
                return true; 
            } else {
                $this->error_message = "Bulk order item insert failed: " . implode(", ", $stmt->errorInfo());
                return false;
            }
        } catch (\PDOException $e) {
            $this->error_message = "Database error during bulk order item creation: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'order_id' => $orderId]);
            return false;
        }
    }


    
    public function readByOrder(int $orderId)
    {
        $sql = "SELECT oi.item_id, oi.order_id, oi.product_id, oi.quantity, oi.price,
                       p.name as product_name, p.image_path as product_image
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                WHERE oi.order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return $stmt; 
            } else {
                $this->error_message = "Failed to execute query for order items: " . implode(", ", $stmt->errorInfo());
                return false;
            }
        } catch (\PDOException $e) {
            $this->error_message = "Database error fetching order items: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'order_id' => $orderId]);
            return false;
        }
    }

    
    public function findById(int $orderItemId)
    {
        
        $sql = "SELECT oi.*, p.name as product_name, p.description as product_description, p.image_path as product_image
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                WHERE oi.item_id = :order_item_id"; 
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':order_item_id', $orderItemId, PDO::PARAM_INT);

        try {
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); 
        } catch (\PDOException $e) {
            $this->error_message = "Database error fetching single order item: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'item_id' => $orderItemId]);
            return false;
        }
    }

    
    public function update(int $orderItemId, array $data): bool
    {
        $fields = [];
        $params = [':order_item_id' => $orderItemId]; 
        $allowedFields = ['quantity', 'price']; 

        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "`$key` = :$key";
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            $this->error_message = "No valid fields provided for item update.";
            return false; 
        }

        
        $sql = "UPDATE order_items SET " . implode(', ', $fields) . " WHERE item_id = :order_item_id"; 
        $stmt = $this->db->prepare($sql);

        
        foreach ($params as $key => &$value) { 
            if ($key === ':order_item_id' || $key === ':quantity') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } elseif ($key === ':price') {
                $stmt->bindValue($key, $value); 
            }
            
        }
        unset($value); 

        try {
            if ($stmt->execute()) {
                return $stmt->rowCount() > 0; 
            } else {
                $this->error_message = "Failed to execute item update: " . implode(", ", $stmt->errorInfo());
                return false;
            }
        } catch (\PDOException $e) {
            $this->error_message = "Database error updating order item: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'item_id' => $orderItemId]);
            return false;
        }
    }

    
    public function delete(int $orderItemId): bool
    {
        
        $sql = "DELETE FROM order_items WHERE item_id = :order_item_id"; 
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':order_item_id', $orderItemId, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return $stmt->rowCount() > 0; 
            } else {
                $this->error_message = "Failed to execute item delete: " . implode(", ", $stmt->errorInfo());
                return false;
            }
        } catch (\PDOException $e) {
            $this->error_message = "Database error deleting order item: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'item_id' => $orderItemId]);
            return false;
        }
    }

    
    public function deleteByOrderId(int $orderId): bool
    {
        $sql = "DELETE FROM order_items WHERE order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);

        try {
            
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            $this->error_message = "Database error deleting items by order ID: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'order_id' => $orderId]);
            return false;
        }
    }
}
