<?php

namespace App\Controllers\Api;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Session;
use App\Core\Registry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Helpers\SecurityHelper;

class OrderApiController extends BaseController
{
    private $orderModel;
    private $orderItemModel;
    private $orderController;
    public function __construct()
    {
        $this->orderModel = new Order(Registry::get('db'));
        $this->orderItemModel = new OrderItem(Registry::get('db'));
        $this->orderController = new \App\Controllers\OrderController();
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, PUT, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
    private function checkApiAuth(): bool
    {
        if (!Registry::get('session')->isAuthenticated()) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required.']);
            return false;
        }
        $userRole = Registry::get('session')->get('user_role');
        if ($userRole !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Permission denied. Requires administrator privileges.']);
            return false;
        }
        return true;
    }
    public function index()
    {
        if (!$this->checkApiAuth()) {
            return;
        }
        try {
            $orders = $this->orderModel->getAll();
            if ($orders === false) {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to retrieve orders.']);
                return;
            }
            http_response_code(200);
            echo json_encode($orders);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'An internal server error occurred while fetching orders.']);
        }
    }
    public function show(int $id)
    {
        if (!$this->checkApiAuth()) {
            return;
        }
        $orderId = filter_var($id, FILTER_VALIDATE_INT);
        if ($orderId === false || $orderId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid order ID provided.']);
            return;
        }
        try {
            $order = $this->orderModel->readOne($orderId);
            if (!$order) {
                http_response_code(404);
                echo json_encode(['error' => 'Order not found.']);
                return;
            }
            $order['items'] = $this->orderItemModel->readByOrder($orderId);
            http_response_code(200);
            echo json_encode($order);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'An internal server error occurred while fetching order details.']);
        }
    }
    public function update(int $id)
    {
        if (!$this->checkApiAuth()) {
            return;
        }
        $orderId = filter_var($id, FILTER_VALIDATE_INT);
        if ($orderId === false || $orderId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid order ID provided.']);
            return;
        }
        $requestData = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($requestData['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON payload. Expecting {"status": "new_status"}.']);
            return;
        }
        $newStatus = SecurityHelper::sanitizeInput($requestData['status']);
        $allowedStatuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
        if (!in_array($newStatus, $allowedStatuses)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid status value provided. Allowed values: ' . implode(', ', $allowedStatuses)]);
            return;
        }
        try {
            $order = $this->orderModel->readOne($orderId);
            if (!$order) {
                http_response_code(404);
                echo json_encode(['error' => 'Order not found.']);
                return;
            }
            $success = $this->orderModel->updateStatus($orderId, $newStatus);
            if ($success) {
                http_response_code(200);
                echo json_encode(['message' => 'Order status updated successfully.']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update order status.']);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'An internal server error occurred while updating order status.']);
        }
    }
    public function updateStatus(int $id)
    {
        if (!$this->checkApiAuth()) {
            return;
        }
        $orderId = filter_var($id, FILTER_VALIDATE_INT);
        if ($orderId === false || $orderId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid order ID provided.']);
            return;
        }
        $requestData = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($requestData['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON payload. Expecting {"status": "new_status"}.']);
            return;
        }
        $newStatus = SecurityHelper::sanitizeInput($requestData['status']);
        try {
            $order = $this->orderModel->readOne($orderId);
            if (!$order) {
                http_response_code(404);
                echo json_encode(['error' => 'Order not found.']);
                return;
            }
            $success = $this->orderController->updateOrderStatusAsManager($orderId, $newStatus);
            if ($success) {
                http_response_code(200);
                echo json_encode(['message' => 'Order status updated successfully.']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Failed to update order status. Status may be invalid or order cannot be updated.']);
            }
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'An internal server error occurred while updating order status.']);
        }
    }
}