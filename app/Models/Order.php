<?php

namespace App\Models;

use PDO;
use App\Core\Database; 
use App\Core\Registry;


class Order
{
    
    private $db;

    
    private $error_message = '';

    
    public $order_id;

    
    public $user_id;

    
    public $total_amount;

    
    public $status;

    
    public $notes;

    
    public $shipping_address;

    
    public $order_date;

    
    public $user_name;

    
    public $user_email;

    
    public $user_phone;

    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    
    public function getErrorMessage(): string
    {
        return $this->error_message;
    }

    
    public function beginTransaction(): bool
    {
        try {
            return $this->db->beginTransaction();
        } catch (\PDOException $e) {
            $this->error_message = "Failed to begin transaction: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e]);
            return false;
        }
    }

    
    public function commit(): bool
    {
        try {
            return $this->db->commit();
        } catch (\PDOException $e) {
            $this->error_message = "Failed to commit transaction: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e]);
            return false;
        }
    }

    
    public function rollback(): bool
    {
        try {
            return $this->db->rollBack();
        } catch (\PDOException $e) {
            $this->error_message = "Failed to rollback transaction: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e]);
            return false;
        }
    }

    
    public function create(array $data): int|false
    {
        $sql = "INSERT INTO orders (user_id, total_amount, status, notes, shipping_address, order_date)
                VALUES (:user_id, :total_amount, :status, :notes, :shipping_address, NOW())";
        $stmt = $this->db->prepare($sql);

        
        $data['status'] = $data['status'] ?? 'pending';
        $data['notes'] = $data['notes'] ?? null;
        $data['shipping_address'] = $data['shipping_address'] ?? null;

        
        $stmt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(':total_amount', $data['total_amount']); 
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);
        $stmt->bindParam(':notes', $data['notes'], PDO::PARAM_STR);
        $stmt->bindParam(':shipping_address', $data['shipping_address'], PDO::PARAM_STR);

        try {
            if ($stmt->execute()) {
                $this->order_id = (int) $this->db->lastInsertId(); 
                return $this->order_id;
            } else {
                $this->error_message = "Order execution failed: " . implode(", ", $stmt->errorInfo());
                return false;
            }
        } catch (\PDOException $e) {
            $this->error_message = "Database error during order creation: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e]);
            return false;
        }
    }

    
    public function readOneByIdAndUser(int $orderId, int $userId): array|false
    {
        $sql = "SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                FROM orders o
                JOIN users u ON o.user_id = u.user_id
                WHERE o.order_id = :order_id AND o.user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        try {
            $stmt->execute();
            $orderData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($orderData) {
                
                $this->order_id = (int) $orderData['order_id'];
                $this->user_id = (int) $orderData['user_id'];
                $this->total_amount = (float) $orderData['total_amount'];
                $this->status = $orderData['status'];
                $this->notes = $orderData['notes'];
                $this->shipping_address = $orderData['shipping_address'];
                $this->order_date = $orderData['order_date'];
                $this->user_name = $orderData['user_name'];
                $this->user_email = $orderData['user_email'];
                $this->user_phone = $orderData['user_phone'];
                return $orderData;
            } else {
                $this->error_message = "Order not found or access denied.";
                return false;
            }
        } catch (\PDOException $e) {
            $this->error_message = "Database error fetching order: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'order_id' => $orderId, 'user_id' => $userId]);
            return false;
        }
    }

    
    public function readByUser(int $userId): \PDOStatement|false
    {
        $sql = "SELECT o.order_id, o.order_date, o.total_amount, o.status
                FROM orders o
                WHERE o.user_id = :user_id
                ORDER BY o.order_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return $stmt;
            } else {
                $this->error_message = "Failed to execute query for user orders: " . implode(", ", $stmt->errorInfo());
                return false;
            }
        } catch (\PDOException $e) {
            $this->error_message = "Database error fetching user orders: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'user_id' => $userId]);
            return false;
        }
    }

    
    public function updateStatus(int $orderId, string $status): bool
    {
        $sql = "UPDATE orders SET status = :status WHERE order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return $stmt->rowCount() > 0;
            } else {
                $this->error_message = "Failed to execute status update: " . implode(", ", $stmt->errorInfo());
                return false;
            }
        } catch (\PDOException $e) {
            $this->error_message = "Database error updating order status: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'order_id' => $orderId, 'status' => $status]);
            return false;
        }
    }

    
    public function readOne(int $orderId): array|false
    {
        $sql = "SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                FROM orders o
                JOIN users u ON o.user_id = u.user_id
                WHERE o.order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);

        try {
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC); 
        } catch (\PDOException $e) {
            $this->error_message = "Database error fetching order: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'order_id' => $orderId]);
            return false;
        }
    }

    
    public function update(int $orderId, array $data): bool
    {
        $fields = [];
        $params = [':order_id' => $orderId];
        $allowedFields = ['total_amount', 'status', 'notes', 'shipping_address']; 

        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "`$key` = :$key"; 
                $params[":$key"] = $value;
            }
        }

        if (empty($fields)) {
            $this->error_message = "No valid fields provided for update.";
            return false; 
        }

        $sql = "UPDATE orders SET " . implode(', ', $fields) . " WHERE order_id = :order_id";
        $stmt = $this->db->prepare($sql);

        
        foreach ($params as $key => &$value) { 
            if ($key === ':order_id') { 
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } elseif ($key === ':total_amount') {
                $stmt->bindValue($key, $value); 
            } else { 
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
        unset($value); 

        try {
            if ($stmt->execute()) {
                return $stmt->rowCount() > 0;
            } else {
                $this->error_message = "Failed to execute generic update: " . implode(", ", $stmt->errorInfo());
                return false;
            }
        } catch (\PDOException $e) {
            $this->error_message = "Database error during generic order update: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'order_id' => $orderId]);
            return false;
        }
    }

    
    public function delete(int $orderId): bool
    {
        
        $sql = "DELETE FROM orders WHERE order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);

        try {
            if ($stmt->execute()) {
                return $stmt->rowCount() > 0;
            } else {
                $this->error_message = "Failed to execute delete: " . implode(", ", $stmt->errorInfo());
                return false;
            }
        } catch (\PDOException $e) {
            $this->error_message = "Database error deleting order: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'order_id' => $orderId]);
            return false;
        }
    }

    
    public function getAll(?string $status = null, ?int $limit = null, ?int $offset = null): \PDOStatement|false
    {
        $sql = "SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                 FROM orders o
                 JOIN users u ON o.user_id = u.user_id";
        $params = [];

        
        if ($status !== null) {
            $sql .= " WHERE o.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY o.order_date DESC";

        
        
        $limitParam = ':limit';
        $offsetParam = ':offset';

        if ($limit !== null) {
            $sql .= " LIMIT $limitParam"; 
            $params[$limitParam] = $limit;
        }
        if ($offset !== null) {
            
            if ($limit === null) {
                
                $sql .= " LIMIT :default_limit";
                $params[':default_limit'] = 1000000; 
            }
            $sql .= " OFFSET $offsetParam"; 
            $params[$offsetParam] = $offset;
        }


        try {
            $stmt = $this->db->prepare($sql);

            
            foreach ($params as $key => &$value) {
                if ($key === $limitParam || $key === $offsetParam || $key === ':default_limit') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else { 
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }
            unset($value); 

            if ($stmt->execute()) {
                return $stmt;
            } else {
                $this->error_message = "Failed to execute query for all orders: " . implode(", ", $stmt->errorInfo());
                return false;
            }
        } catch (\PDOException $e) {
            $this->error_message = "Database error fetching all orders: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'status' => $status, 'limit' => $limit, 'offset' => $offset]);
            return false;
        }
    }

    
    public function getAllOrdersPaginated(int $page = 1, int $perPage = 15, array $filters = [])
    {
        $offset = ($page - 1) * $perPage;

        
        $sql = "SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                FROM orders o
                JOIN users u ON o.user_id = u.user_id
                WHERE 1=1"; 

        
        $countSql = "SELECT COUNT(*) FROM orders o WHERE 1=1";

        $params = []; 

        
        if (!empty($filters['status'])) {
            $sql .= " AND o.status = :status";
            $countSql .= " AND o.status = :status";
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND o.order_date >= :start_date";
            $countSql .= " AND o.order_date >= :start_date";
            
            $params[':start_date'] = $filters['start_date'] . ' 00:00:00';
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND o.order_date <= :end_date";
            $countSql .= " AND o.order_date <= :end_date";
            
            $params[':end_date'] = $filters['end_date'] . ' 23:59:59';
        }

        
        $sql .= " ORDER BY o.order_date DESC";
        $sql .= " LIMIT :limit OFFSET :offset";

        
        $queryParams = $params; 
        $queryParams[':limit'] = $perPage;
        $queryParams[':offset'] = $offset;

        
        $countParams = $params;

        try {
            
            $countStmt = $this->db->prepare($countSql);
            
            foreach ($countParams as $key => &$value) {
                $countStmt->bindValue($key, $value); 
            }
            unset($value);
            $countStmt->execute();
            $totalOrders = (int) $countStmt->fetchColumn();
            $totalPages = ceil($totalOrders / $perPage);

            
            $stmt = $this->db->prepare($sql);
            
            foreach ($queryParams as $key => &$value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value); 
                }
            }
            unset($value);

            if (!$stmt->execute()) {
                $this->error_message = "Failed to execute paginated orders query: " . implode(", ", $stmt->errorInfo());
                return false;
            }

            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            
            return [
                'orders' => $orders,
                'pagination' => [
                    'total' => $totalOrders,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'has_more_pages' => ($page < $totalPages)
                ]
            ];
        } catch (\PDOException $e) {
            $this->error_message = "Database error fetching paginated orders: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, [
                'exception' => $e,
                'page' => $page,
                'per_page' => $perPage,
                'filters' => $filters
            ]);
            return false;
        }
    }


    
    public function countAll(?string $status = null): int
    {
        $sql = "SELECT COUNT(*) FROM orders";
        $params = [];
        if ($status !== null) {
            $sql .= " WHERE status = :status";
            $params[':status'] = $status;
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params); 
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->error_message = "Database error counting orders: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'status' => $status]);
            return 0; 
        }
    }

    
    public function findOrderWithDetails(int $orderId): array|false
    {
        try {
            
            $order = $this->readOne($orderId);
            if (!$order) {
                
                if (empty($this->error_message)) {
                    $this->error_message = "Order not found."; 
                }
                return false;
            }

            
            $orderItemModel = new OrderItem($this->db); 
            $itemsStmt = $orderItemModel->readByOrder($orderId);

            if (!$itemsStmt) {
                
                $this->error_message = "Failed to fetch order items: " . $orderItemModel->getErrorMessage();
                return false;
            }

            $items = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

            
            $calculatedTotalAmount = 0.0;
            foreach ($items as $item) {
                
                if (isset($item['price']) && is_numeric($item['price']) && isset($item['quantity']) && is_numeric($item['quantity'])) {
                    $calculatedTotalAmount += (float) $item['price'] * (int) $item['quantity'];
                } else {
                    Registry::get('logger')->warning("Invalid price or quantity for item in order.", [
                        'order_id' => $orderId,
                        'item_id' => $item['item_id'] ?? 'N/A',
                        'item_price' => $item['price'] ?? 'N/A',
                        'item_quantity' => $item['quantity'] ?? 'N/A'
                    ]);
                }
            }

            
            $order['items'] = $items;

            
            if (isset($order['total_amount']) && abs((float)$order['total_amount'] - $calculatedTotalAmount) > 0.01) { 
                Registry::get('logger')->warning("Order total discrepancy detected.", [
                    'order_id' => $orderId,
                    'stored_total' => $order['total_amount'],
                    'calculated_total' => $calculatedTotalAmount
                ]);
                
            }

            return $order;
        } catch (\PDOException $e) {
            
            $this->error_message = "Database error fetching order with details: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'order_id' => $orderId]);
            return false;
        } catch (\Exception $e) {
            
            $this->error_message = "Application error fetching order details: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'order_id' => $orderId]);
            return false;
        }
    }

    
    public function updateOrderStatus(int $orderId, string $status): bool
    {
        $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            $this->error_message = "Invalid order status provided: {$status}";
            return false;
        }
        
        return $this->updateStatus($orderId, $status);
    }

    
    public function getTotalOrderCount(): int
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) FROM orders");
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->error_message = "Database error counting orders: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e]);
            return 0; 
        }
    }

    
    public function getOrderCountByStatus(string $status): int
    {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE status = :status");
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->error_message = "Database error counting orders by status: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'status' => $status]);
            return 0; 
        }
    }

    
    public function getRecentOrders(int $limit = 5): array|false
    {
        try {
            $sql = "SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                    FROM orders o
                    JOIN users u ON o.user_id = u.user_id
                    ORDER BY o.order_date DESC
                    LIMIT :limit"; 
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT); 
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->error_message = "Database error fetching recent orders: " . $e->getMessage();
            Registry::get('logger')->error($this->error_message, ['exception' => $e, 'limit' => $limit]);
            return false;
        }
    }
}
