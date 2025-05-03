<?php
namespace App\Controllers;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Core\Database;
use App\Core\BaseController;
use App\Core\Registry;
use App\Core\Session;
use App\Core\Request;
use App\Core\Redirect;
use App\Helpers\SecurityHelper;
use Psr\Log\LoggerInterface;
use PDO;
use Exception;
class OrderController extends BaseController
{
    private Session $session;
    private Request $request;
    private LoggerInterface $logger;
    private Order $orderModel;
    private OrderItem $orderItemModel;
    private Product $productModel;
    public function __construct()
    {
        $this->session = Registry::get('session');
        $this->request = Registry::get('request');
        $this->logger = Registry::get('logger');
        $db = Registry::get('database'); 
        $pdoConnection = $db->getConnection(); 
        if (!$pdoConnection) {
            $this->logger->critical("Database connection not available for OrderController.");
            throw new \RuntimeException("Database connection not available for OrderController.");
        }
        $this->orderModel = new Order($pdoConnection);
        $this->orderItemModel = new OrderItem($pdoConnection);
        $this->productModel = new Product($pdoConnection);
    }
    public function showSingleProductOrderForm($params): void
    {
        $this->session->requireLogin('/login'); 
        $productId = filter_var($params['productId'] ?? null, FILTER_VALIDATE_INT);
        if (!$productId) {
            $this->logger->warning("Invalid or missing product ID for single product order form.", ['params' => $params]);
            $this->session->flash('error', 'Invalid product specified.');
            Redirect::to('/'); 
            return;
        }
        try {
            $product = $this->productModel->findById($productId);
            if (!$product) {
                $this->logger->warning("Product not found for single product order form.", ['product_id' => $productId]);
                $this->session->flash('error', 'Product not found.');
                Redirect::to('/'); 
                return;
            }
            $productData = [
                'product_id' => $product['product_id'] ?? $productId,
                'name' => $product['name'] ?? '',
                'description' => $product['description'] ?? '',
                'price' => $product['price'] ?? 0,
                'stock_quantity' => $product['stock_quantity'] ?? 0,
                'image_path' => $product['image_path'] ?? $product['image'] ?? 'default.png', 
            ];
        } catch (Exception $e) {
            $this->logger->error("Error fetching product details for single order form.", ['product_id' => $productId, 'exception' => $e]);
            $this->session->flash('error', 'Could not load product details. Please try again.');
            Redirect::to('/');
            return;
        }
        $csrfToken = $this->session->getCsrfToken();
        $data = [
            'product' => $productData,
            'csrfToken' => $csrfToken,
            'page_title' => 'Place Order - ' . htmlspecialchars($productData['name']),
            'meta_description' => 'Place your order for ' . htmlspecialchars($productData['name']) . ' at GhibliGroceries',
            'meta_keywords' => 'order, grocery, purchase, ' . htmlspecialchars($productData['name']),
            'additional_css_files' => ['assets/css/order.css'], 
        ];
        $this->view('pages/order', $data);
    }
    public function processSingleProductOrder($params): void
    {
        $this->session->requireLogin(); 
        if (!$this->request->isPost()) {
            $this->session->flash('error', 'Invalid request method.');
            Redirect::back(); 
            return;
        }
        $productId = filter_var($params['productId'] ?? null, FILTER_VALIDATE_INT);
        if (!$productId) {
            $this->logger->warning("Invalid or missing product ID for single product order processing.", ['params' => $params]);
            $this->session->flash('error', 'Invalid product specified.');
            Redirect::to('/'); 
            return;
        }
        $submittedToken = $this->request->post('csrf_token');
        if (!$this->session->validateCsrfToken($submittedToken)) {
            $this->logger->warning("CSRF token mismatch for single product order.", ['product_id' => $productId]);
            $this->session->flash('error', 'Invalid security token. Please try submitting the form again.');
            Redirect::to('/order/product/' . $productId); 
            return;
        }
        $quantity = filter_var($this->request->post('quantity'), FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity <= 0) {
            $this->session->flash('error', 'Please enter a valid quantity.');
            Redirect::to('/order/product/' . $productId);
            return;
        }
        $notes = SecurityHelper::sanitizeInput($this->request->post('notes', ''));
        try {
            $product = $this->productModel->findById($productId);
            if (!$product || $product['stock_quantity'] < $quantity) {
                $this->logger->warning("Product not found or insufficient stock during single order processing.", [
                    'product_id' => $productId,
                    'requested_qty' => $quantity,
                    'available_qty' => $product['stock_quantity'] ?? 'N/A' 
                ]);
                $this->session->flash('error', 'Product not found or insufficient stock (' . ($product['stock_quantity'] ?? 0) . ' available).');
                Redirect::to('/order/product/' . $productId);
                return;
            }
            $currentPrice = $product['price'];
            $productName = $product['name']; 
        } catch (Exception $e) {
            $this->logger->error("Error re-fetching product details for single order processing.", ['product_id' => $productId, 'exception' => $e]);
            $this->session->flash('error', 'Could not verify product details. Please try again.');
            Redirect::to('/order/product/' . $productId);
            return;
        }
        $items = [
            [
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $currentPrice 
            ]
        ];
        $totalAmount = $currentPrice * $quantity;
        $userId = $this->session->get('user_id');
        if (!$userId) {
            $this->logger->error("User ID not found in session during single order processing.", ['product_id' => $productId]);
            $this->session->flash('error', 'User session not found. Please log in again.');
            Redirect::to('/login');
            return;
        }
        $orderId = $this->createOrder($userId, $items, $totalAmount, $notes);
        if ($orderId) {
            $this->logger->info("Single product order placed successfully.", ['order_id' => $orderId, 'user_id' => $userId, 'product_id' => $productId, 'quantity' => $quantity]);
            $this->session->flash('success', 'Your order for ' . htmlspecialchars($productName) . ' has been placed successfully!');
            Redirect::to('/order/confirmation/' . $orderId); 
            return;
        } else {
            $this->session->flash('error', 'Failed to place your order. Please try again.');
            Redirect::to('/order/product/' . $productId); 
            return;
        }
    }
    public function showOrderForm(): void
    {
        $this->session->requireLogin(); 
        $cart = $this->session->get('cart', []);
        $totalAmount = 0;
        $cartItemsDetails = []; 
        if (!empty($cart)) {
            $productIds = array_keys($cart); 
            if (!empty($productIds)) {
                try {
                    $productsById = $this->productModel->findMultipleByIds($productIds);
                    foreach ($cart as $productId => $quantity) {
                        if (isset($productsById[$productId])) {
                            $product = $productsById[$productId];
                            $subtotal = $product['price'] * $quantity;
                            $totalAmount += $subtotal;
                            $cartItemsDetails[] = [
                                'product_id' => $productId,
                                'name' => $product['name'],
                                'price' => $product['price'],
                                'image_url' => BASE_URL . 'assets/images/products/' . basename($product['image'] ?? 'default.png'),
                                'quantity' => $quantity,
                                'subtotal' => $subtotal
                            ];
                        } else {
                            $this->logger->warning("Product ID {$productId} from cart not found in database during order form display.");
                            $currentCart = $this->session->get('cart', []);
                            unset($currentCart[$productId]);
                            $this->session->set('cart', $currentCart);
                            $this->session->flash('warning', "Some items were removed from your cart as they are no longer available.");
                            Redirect::to('/order'); 
                            return;
                        }
                    }
                } catch (Exception $e) {
                    $this->logger->error("Error fetching product details for order form.", ['product_ids' => $productIds, 'exception' => $e]);
                    $this->session->flash('error', 'Could not load product details. Please try again.');
                    Redirect::to('/cart'); 
                    return;
                }
            }
        }
        if (empty($cartItemsDetails)) {
            $this->session->flash('info', 'Your cart is empty. Add some products before placing an order.');
            Redirect::to('/categories'); 
            return;
        }
        $csrfToken = $this->session->getCsrfToken();
        $data = [
            'cartItems' => $cartItemsDetails,
            'totalAmount' => $totalAmount,
            'csrfToken' => $csrfToken,
            'page_title' => 'Place Your Order',
            'meta_description' => 'Review your cart and place your order.',
            'meta_keywords' => 'order, checkout, cart, grocery',
            'additional_css_files' => ['assets/css/order.css'],
        ];
        $this->view('pages/order_form', $data);
    }
    public function processOrder(): void
    {
        if (!$this->request->isPost()) {
            $this->session->flash('error', 'Invalid request method.');
            Redirect::to('/order');
            return;
        }
        $this->session->requireLogin(); 
        if (!$this->session->validateCsrfToken($this->request->post('csrf_token'))) {
            $this->session->flash('error', 'Invalid security token. Please try again.');
            Redirect::to('/order');
            return;
        }
        $userId = $this->session->get('user_id');
        if (!$userId) {
            $this->logger->error("User ID not found in session during order processing.");
            $this->session->flash('error', 'User session not found. Please log in again.');
            Redirect::to('/login');
            return;
        }
        $cart = $this->session->get('cart', []);
        if (empty($cart)) {
            $this->session->flash('error', 'Your cart is empty.');
            Redirect::to('/categories'); 
            return;
        }
        $notes = SecurityHelper::sanitizeInput($this->request->post('order_notes', ''));
        $orderItemsData = [];
        $totalAmount = 0;
        $productIds = array_keys($cart);
        if (!empty($productIds)) {
            try {
                $productsById = $this->productModel->findMultipleByIds($productIds);
                foreach ($cart as $productId => $quantity) {
                    $this->logger->debug("Product data structure check:", [
                        'product_id' => $productId,
                        'product_data' => $productsById[$productId] ?? 'Not found',
                        'has_stock_quantity' => isset($productsById[$productId]['stock_quantity']),
                        'stock_quantity_value' => $productsById[$productId]['stock_quantity'] ?? 'N/A',
                        'requested_quantity' => $quantity
                    ]);
                    if (isset($productsById[$productId]) && isset($productsById[$productId]['stock_quantity']) && $productsById[$productId]['stock_quantity'] >= $quantity) {
                        $price = $productsById[$productId]['price']; 
                        $orderItemsData[] = [
                            'product_id' => $productId,
                            'quantity' => $quantity,
                            'price' => $price
                        ];
                        $totalAmount += $price * $quantity;
                    } else {
                        $reason = isset($productsById[$productId]) ?
                            (isset($productsById[$productId]['stock_quantity']) ? 'insufficient stock' : 'stock quantity not available') :
                            'no longer available';
                        $this->logger->warning("Product ID {$productId} from cart invalid during order processing ({$reason}).", [
                            'requested_qty' => $quantity,
                            'available_qty' => $productsById[$productId]['stock_quantity'] ?? 'N/A',
                            'product_data_keys' => isset($productsById[$productId]) ? array_keys($productsById[$productId]) : []
                        ]);
                        $this->session->flash('error', 'Some items in your cart (' . ($productsById[$productId]['name'] ?? 'ID:' . $productId) . ') are no longer available in the requested quantity. Please review your order.');
                        Redirect::to('/order'); 
                        return;
                    }
                }
            } catch (Exception $e) {
                $this->logger->error("Error fetching product details for order processing.", ['product_ids' => $productIds, 'exception' => $e]);
                $this->session->flash('error', 'Could not verify product details. Please try again.');
                Redirect::to('/order');
                return;
            }
        } else {
            $this->logger->error("Cart product IDs array was empty during order processing.");
            $this->session->flash('error', 'Error processing cart items.');
            Redirect::to('/order');
            return;
        }
        $orderId = $this->createOrder($userId, $orderItemsData, $totalAmount, $notes);
        if ($orderId) {
            $this->session->set('cart', []); 
            $this->session->flash('success', 'Your order has been placed successfully!');
            Redirect::to('/order/confirmation/' . $orderId); 
            return;
        } else {
            $this->session->flash('error', 'Failed to place order. Please check the details and try again.');
            Redirect::to('/order'); 
            return;
        }
    }
    public function myOrders(): void
    {
        $this->session->requireLogin(); 
        $userId = $this->session->get('user_id');
        $orders = $this->getUserOrders($userId);
        if ($orders === false) {
            $this->session->flash('error', 'Could not retrieve your orders at this time.');
            $orders = []; 
        }
        $formattedOrders = [];
        foreach ($orders as $order) {
            $formattedOrders[] = $this->formatOrderForDisplay($order);
        }
        $data = [
            'orders' => $formattedOrders,
            'page_title' => 'My Orders',
            'meta_description' => 'View your past orders with GhibliGroceries.',
            'meta_keywords' => 'orders, history, purchase history, grocery',
            'additional_css_files' => ['assets/css/order.css'],
        ];
        $this->view('pages/my_orders', $data);
    }
    public function orderConfirmation($params): void
    {
        $this->session->requireLogin(); 
        $userId = $this->session->get('user_id');
        $orderId = filter_var($params['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$orderId) {
            $this->logger->warning("Order ID missing or invalid in confirmation request.", ['params' => $params]);
            $this->session->flash('error', 'Order ID not provided or invalid.');
            Redirect::to('/orders'); 
            return;
        }
        $orderDetails = $this->getOrderDetails($orderId, $userId);
        if (!$orderDetails) {
            $this->session->flash('error', 'Could not retrieve order confirmation details or order not found.');
            Redirect::to('/orders');
            return;
        }
        $data = [
            'order' => $this->formatOrderForDisplay($orderDetails), 
            'page_title' => 'Order Confirmation #' . $orderId,
            'meta_description' => 'Your GhibliGroceries order #' . $orderId . ' has been placed successfully.',
            'meta_keywords' => 'order confirmation, grocery, purchase',
            'additional_css_files' => ['assets/css/order.css'],
        ];
        $this->view('pages/order_confirmation', $data);
    }
    public function orderDetails($params): void
    {
        $this->session->requireLogin(); 
        $userId = $this->session->get('user_id');
        $orderId = filter_var($params['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$orderId) {
            $this->logger->warning("Order ID missing or invalid in details request.", ['params' => $params]);
            $this->session->flash('error', 'Order ID not provided or invalid.');
            Redirect::to('/orders'); 
            return;
        }
        $orderDetails = $this->getOrderDetails($orderId, $userId);
        if (!$orderDetails) {
            $this->session->flash('error', 'Could not retrieve order details or order not found.');
            Redirect::to('/orders');
            return;
        }
        $data = [
            'order' => $this->formatOrderForDisplay($orderDetails), 
            'page_title' => 'Order Details #' . $orderId,
            'meta_description' => 'Details for your GhibliGroceries order #' . $orderId . '.',
            'meta_keywords' => 'order details, grocery, purchase',
            'additional_css_files' => ['assets/css/order.css'],
            'csrfToken' => $this->session->getCsrfToken() 
        ];
        $this->view('pages/order_details', $data);
    }
    public function cancelOrder($params): void
    {
        if (!$this->request->isPost()) {
            $this->session->flash('error', 'Invalid request method.');
            Redirect::to('/orders'); 
            return;
        }
        $this->session->requireLogin(); 
        $userId = $this->session->get('user_id');
        $orderId = filter_var($params['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$orderId) {
            $this->logger->warning("Order ID missing or invalid in cancel request.", ['params' => $params]);
            $this->session->flash('error', 'Order ID not provided for cancellation.');
            Redirect::to('/orders');
            return;
        }
        if (!$this->session->validateCsrfToken($this->request->post('csrf_token'))) {
            $this->session->flash('error', 'Invalid security token. Please try again.');
            Redirect::to('/order/details/' . $orderId); 
            return;
        }
        if ($this->updateOrderStatus($orderId, 'cancelled', $userId)) {
            $this->session->flash('success', 'Order #' . $orderId . ' has been cancelled.');
            Redirect::to('/orders'); 
        } else {
            $this->session->flash('error', 'Could not cancel order. It might have already been processed or cancelled.');
            Redirect::to('/order/details/' . $orderId); 
        }
    }
    private function createOrder(int $user_id, array $items, float $total_amount, ?string $notes = null): int|false
    {
        try {
            $this->orderModel->beginTransaction();
            $orderData = [
                'user_id' => $user_id,
                'total_amount' => $total_amount,
                'status' => 'pending', 
                'notes' => $notes
            ];
            $orderId = $this->orderModel->create($orderData);
            if (!$orderId) {
                throw new Exception("Failed to create order record in database.");
            }
            foreach ($items as $item) {
                $orderItemData = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'] 
                ];
                if (!$this->orderItemModel->create($orderItemData)) {
                    throw new Exception("Failed to create order item record for product ID " . $item['product_id']);
                }
                $stockLevel = $this->productModel->checkStock($item['product_id']);
                if ($stockLevel !== false && $stockLevel >= $item['quantity']) {
                    $db = Registry::get('database')->getConnection();
                    $newStockLevel = $stockLevel - $item['quantity'];
                    $updateStmt = $db->prepare("UPDATE products SET stock_quantity = :new_qty WHERE product_id = :product_id");
                    $updateStmt->bindParam(':new_qty', $newStockLevel, PDO::PARAM_INT);
                    $updateStmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
                    if (!$updateStmt->execute()) {
                        throw new Exception("Failed to update product stock for product ID " . $item['product_id']);
                    }
                } else {
                    throw new Exception("Insufficient stock detected during transaction for product ID " . $item['product_id']);
                }
            }
            $this->orderModel->commit();
            return $orderId; 
        } catch (Exception $e) {
            $this->orderModel->rollback();
            $this->logger->error("Error creating order: " . $e->getMessage(), [
                'user_id' => $user_id,
                'items_count' => count($items), 
                'total_amount' => $total_amount,
                'exception' => $e 
            ]);
            return false; 
        }
    }
    public function getOrderDetails(int $order_id, int $user_id): array|false
    {
        try {
            $orderData = $this->orderModel->readOneByIdAndUser($order_id, $user_id);
            if (!$orderData) {
                $errorMessage = $this->orderModel->getErrorMessage() ?: "Order not found or access denied.";
                if (strpos($errorMessage, 'denied') === false) {
                    $this->logger->warning($errorMessage, ['order_id' => $order_id, 'user_id' => $user_id]);
                }
                return false; 
            }
            $items_stmt = $this->orderItemModel->readByOrder($order_id);
            if (!$items_stmt) {
                $errorMessage = "Failed to get order items: " . $this->orderItemModel->getErrorMessage();
                $this->logger->error($errorMessage, ['order_id' => $order_id]);
                return false;
            }
            $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
            $orderData['items'] = $items;
            return $orderData; 
        } catch (Exception $e) {
            $this->logger->error("Error getting order details: " . $e->getMessage(), ['exception' => $e, 'order_id' => $order_id, 'user_id' => $user_id]);
            return false; 
        }
    }
    public function getUserOrders(int $user_id): array|false
    {
        try {
            $orders_stmt = $this->orderModel->readByUser($user_id);
            if (!$orders_stmt) {
                $errorMessage = "Failed to get user orders statement: " . $this->orderModel->getErrorMessage();
                $this->logger->error($errorMessage, ['user_id' => $user_id]);
                return false; 
            }
            $orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);
            return $orders; 
        } catch (Exception $e) {
            $this->logger->error("Error getting user orders: " . $e->getMessage(), ['exception' => $e, 'user_id' => $user_id]);
            return false; 
        }
    }
    private function updateOrderStatus(int $order_id, string $status, int $user_id): bool
    {
        $valid_statuses = ['cancelled']; 
        if (!in_array($status, $valid_statuses)) {
            $this->logger->warning("Invalid status value provided for user update.", ['order_id' => $order_id, 'user_id' => $user_id, 'status' => $status]);
            return false;
        }
        $db = Registry::get('database');
        $pdoConnection = $db->getConnection();
        if (!$pdoConnection) {
            $this->logger->critical("Failed to get PDO connection for order status update transaction.");
            return false;
        }
        try {
            $pdoConnection->beginTransaction();
            $currentOrderData = $this->orderModel->readOneByIdAndUser($order_id, $user_id);
            if (!$currentOrderData) {
                $errorMessage = $this->orderModel->getErrorMessage() ?: "Order not found or access denied for status update.";
                if (strpos($errorMessage, 'denied') === false) { 
                    $this->logger->warning($errorMessage, ['order_id' => $order_id, 'user_id' => $user_id]);
                }
                $pdoConnection->rollBack(); 
                return false;
            }
            $currentStatus = $currentOrderData['status'];
            if ($currentStatus === 'completed' || $currentStatus === 'cancelled') {
                $this->logger->info("User attempted to change status of an already {$currentStatus} order.", ['order_id' => $order_id, 'user_id' => $user_id, 'new_status' => $status]);
                $pdoConnection->rollBack();
                return false;
            }
            if ($status === 'cancelled' && $currentStatus !== 'pending') {
                $this->logger->info("User attempted to cancel a non-pending order.", ['order_id' => $order_id, 'user_id' => $user_id, 'current_status' => $currentStatus]);
                $pdoConnection->rollBack();
                return false;
            }
            if (!$this->orderModel->updateStatus($order_id, $status)) {
                $errorMessage = "Failed to update order status in database: " . $this->orderModel->getErrorMessage();
                $pdoConnection->rollBack(); 
                $this->logger->error($errorMessage, ['order_id' => $order_id, 'status' => $status]);
                return false;
            }
            $pdoConnection->commit();
            $this->logger->info("Order status updated by user.", ['order_id' => $order_id, 'user_id' => $user_id, 'new_status' => $status]);
            return true; 
        } catch (Exception $e) {
            if ($pdoConnection->inTransaction()) {
                $pdoConnection->rollBack();
            }
            $this->logger->error("Error updating order status: " . $e->getMessage(), ['exception' => $e, 'order_id' => $order_id, 'user_id' => $user_id]);
            return false; 
        }
    }
    public function updateOrderStatusAsManager(int $order_id, string $status): bool
    {
        $valid_statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            $this->logger->warning("Invalid status value provided for manager update.", ['order_id' => $order_id, 'status' => $status]);
            return false;
        }
        try {
            $orderExists = $this->orderModel->readOne($order_id);
            if (!$orderExists) {
                $this->logger->warning("Order not found for manager status update.", ['order_id' => $order_id]);
                return false;
            }
            $currentStatus = $orderExists['status'];
            if ($currentStatus === 'completed' || $currentStatus === 'cancelled') {
                $this->logger->info("Manager attempted to change status of an already {$currentStatus} order.", ['order_id' => $order_id, 'new_status' => $status]);
                return false; 
            }
            if (!$this->orderModel->updateStatus($order_id, $status)) {
                $errorMessage = "Failed to update order status via manager action: " . $this->orderModel->getErrorMessage();
                $this->logger->error($errorMessage, ['order_id' => $order_id, 'status' => $status]);
                return false;
            }
            $this->logger->info("Order status updated by manager.", ['order_id' => $order_id, 'new_status' => $status]);
            return true; 
        } catch (Exception $e) {
            $this->logger->error("Error during manager order status update: " . $e->getMessage(), ['exception' => $e, 'order_id' => $order_id, 'status' => $status]);
            return false; 
        }
    }
    private function formatOrderForDisplay(array $order_details): array
    {
        if (empty($order_details)) {
            return [];
        }
        $formatted = $order_details; 
        if (!empty($formatted['order_date'])) {
            try {
                $date = new \DateTime($formatted['order_date']);
                $formatted['order_date_formatted'] = $date->format('D, d M Y H:i');
            } catch (Exception $e) {
                $formatted['order_date_formatted'] = 'Invalid Date';
                $this->logger->warning("Invalid date format encountered for order ID {$formatted['order_id']}", ['date' => $formatted['order_date']]);
            }
        } else {
            $formatted['order_date_formatted'] = 'N/A'; 
        }
        $status_map = [
            'pending' => ['text' => 'Pending Confirmation', 'class' => 'status-pending'],
            'processing' => ['text' => 'Processing', 'class' => 'status-processing'],
            'shipped' => ['text' => 'Shipped', 'class' => 'status-shipped'],
            'completed' => ['text' => 'Completed', 'class' => 'status-completed'],
            'cancelled' => ['text' => 'Cancelled', 'class' => 'status-cancelled']
        ];
        $status_info = $status_map[$formatted['status']] ?? ['text' => ucfirst($formatted['status'] ?? 'Unknown'), 'class' => 'status-unknown'];
        $formatted['status_text'] = $status_info['text'];
        $formatted['status_class'] = $status_info['class'];
        $formatted['total_amount_formatted'] = '$' . number_format($formatted['total_amount'] ?? 0, 2);
        if (isset($formatted['items']) && is_array($formatted['items'])) {
            foreach ($formatted['items'] as &$item) { 
                if (is_array($item)) {
                    $item['price_formatted'] = '$' . number_format($item['price'] ?? 0, 2);
                    if (!isset($item['subtotal'])) {
                        $item['subtotal'] = ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
                    }
                    $item['subtotal_formatted'] = '$' . number_format($item['subtotal'] ?? 0, 2);
                    $image_filename = $item['product_image'] ?? ($item['image'] ?? 'default.png'); 
                    $item['image_url'] = BASE_URL . 'assets/images/products/' . basename($image_filename);
                    $item['product_name'] = htmlspecialchars($item['product_name'] ?? 'N/A');
                }
            }
            unset($item); 
        } elseif (!isset($formatted['items'])) {
            $formatted['items'] = [];
        }
        $formatted['notes'] = isset($formatted['notes']) ? htmlspecialchars($formatted['notes']) : '';
        $formatted['user_name'] = isset($formatted['user_name']) ? htmlspecialchars($formatted['user_name']) : 'N/A'; 
        $formatted['user_email'] = isset($formatted['user_email']) ? htmlspecialchars($formatted['user_email']) : 'N/A'; 
        $formatted['order_id'] = isset($formatted['order_id']) ? htmlspecialchars($formatted['order_id']) : 'N/A'; 
        return $formatted; 
    }
}
