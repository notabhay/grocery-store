<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Registry;
use App\Models\Order;
use App\Controllers\OrderController;
use PDO;


class APIController extends BaseController
{
    
    private $db;

    
    private $order;

    
    private $response = [];

    
    private $response_code = 200;

    
    private $request_method;

    
    private $endpoint;

    
    private $params = [];

    
    private $is_authenticated = false;

    
    private $user_id = null;

    
    private $is_manager = false;

    
    public function __construct($db)
    {
        $this->db = $db;
        $this->order = new Order($db); 
        $this->request_method = $_SERVER['REQUEST_METHOD']; 
        $this->endpoint = $this->getEndpoint(); 
        $this->params = $this->getParams(); 
        $this->authenticate(); 
    }

    
    public function processRequest(): void
    {
        header('Content-Type: application/json'); 

        
        switch ($this->endpoint) {
            case 'orders':
                $this->handleOrdersEndpoint();
                break;
            default:
                
                $this->setResponse(404, ['error' => 'Endpoint not found']);
                break;
        }

        $this->sendResponse(); 
    }

    
    private function handleOrdersEndpoint(): void
    {
        
        if (!$this->is_authenticated) {
            $this->setResponse(401, ['error' => 'Authentication required']);
            return;
        }

        
        switch ($this->request_method) {
            case 'GET':
                
                if (isset($this->params['id'])) {
                    $this->getOrderById($this->params['id']);
                } else {
                    
                    if ($this->is_manager) {
                        $this->getAllOrders(); 
                    } else {
                        $this->getUserOrders(); 
                    }
                }
                break;
            case 'PUT':
                
                if ($this->is_manager && isset($this->params['id'])) {
                    $this->updateOrderStatus($this->params['id']);
                } else {
                    
                    $this->setResponse(403, ['error' => 'Forbidden']);
                }
                break;
            default:
                
                $this->setResponse(405, ['error' => 'Method not allowed']);
                break;
        }
    }

    
    private function getOrderById($order_id): void
    {
        $orderController = new OrderController(); 
        $order = $orderController->getOrderDetails($order_id, $this->user_id); 

        if ($order) {
            
            if ($this->is_manager || $order['user_id'] == $this->user_id) {
                $this->setResponse(200, $order); 
            } else {
                
                $this->setResponse(403, ['error' => 'You are not authorized to view this order']);
            }
        } else {
            
            $this->setResponse(404, ['error' => 'Order not found']);
        }
    }

    
    private function getAllOrders(): void
    {
        
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : Registry::get('config')['ITEMS_PER_PAGE'];
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $offset = ($page - 1) * $limit;

        
        $result = $this->order->getAll($status, $limit, $offset);

        if ($result) {
            $orders = [];
            
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $orders[] = $row;
            }
            
            $total = $this->order->countAll($status);
            
            $this->setResponse(200, [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit),
                'orders' => $orders
            ]);
        } else {
            
            $this->setResponse(500, ['error' => 'Failed to retrieve orders']);
        }
    }

    
    private function getUserOrders(): void
    {
        $orderController = new OrderController(); 
        $orders = $orderController->getUserOrders($this->user_id); 

        if ($orders !== false) { 
            $this->setResponse(200, ['orders' => $orders]); 
        } else {
            
            $this->setResponse(500, ['error' => 'Failed to retrieve orders']);
        }
    }

    
    private function updateOrderStatus($order_id): void
    {
        
        $data = json_decode(file_get_contents('php://input'), true);

        
        if (!isset($data['status'])) {
            $this->setResponse(400, ['error' => 'Status is required']);
            return;
        }

        $orderController = new OrderController(); 
        
        if ($orderController->updateOrderStatusAsManager($order_id, $data['status'])) {
            $this->setResponse(200, ['message' => 'Order status updated successfully']); 
        } else {
            
            $this->setResponse(500, ['error' => 'Failed to update order status. Check logs for details.']);
        }
    }

    
    private function authenticate(): void
    {
        $headers = getallheaders(); 

        
        if (isset($headers['Authorization'])) {
            $auth_header = $headers['Authorization'];
            
            if (strpos($auth_header, 'Bearer ') === 0) {
                
                $token = substr($auth_header, 7);

                
                if ($token == 'manager_token_123') { 
                    $this->is_authenticated = true;
                    $this->is_manager = true;
                    $this->user_id = 1; 
                } elseif ($token == 'user_token_456') { 
                    $this->is_authenticated = true;
                    $this->user_id = 2; 
                }
                
            }
        }
        
    }

    
    private function getEndpoint(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); 
        $path_parts = explode('/', trim($path, '/')); 

        
        $api_index = array_search('api', $path_parts);

        
        if ($api_index !== false && isset($path_parts[$api_index + 1])) {
            return $path_parts[$api_index + 1];
        }

        return ''; 
    }

    
    private function getParams(): array
    {
        $params = [];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); 
        $path_parts = explode('/', trim($path, '/')); 

        
        $endpoint_index = array_search($this->endpoint, $path_parts);

        
        if ($endpoint_index !== false && isset($path_parts[$endpoint_index + 1])) {
            $params['id'] = $path_parts[$endpoint_index + 1];
        }

        
        
        

        return $params;
    }

    
    private function setResponse($code, $data): void
    {
        $this->response_code = $code;
        $this->response = $data;
    }

    
    private function sendResponse(): void
    {
        http_response_code($this->response_code); 
        echo json_encode($this->response); 
        exit; 
    }
}
