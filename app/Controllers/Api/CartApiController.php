<?php

namespace App\Controllers\Api;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Session;
use App\Models\Product;
use App\Core\Registry;
use App\Helpers\CartHelper;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class CartApiController extends BaseController
{
    private $cartHelper;
    private $session;
    private $db;
    private $logger;
    public function __construct()
    {
        $this->session = Registry::get('session');
        $this->db = Registry::get('database');
        $this->cartHelper = new CartHelper($this->session, Registry::get('database'));
        $this->logger = new Logger('cart_api');
        $logFilePath = BASE_PATH . '/logs/app.log';
        $logDir = dirname($logFilePath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $this->logger->pushHandler(new StreamHandler($logFilePath, Logger::DEBUG));
    }
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method. Only POST is allowed.'], 405);
            return;
        }
        if (!$this->session->isAuthenticated()) {
            $this->jsonResponse(['error' => 'Authentication required.'], 401);
            return;
        }
        $requestData = json_decode(file_get_contents('php://input'), true);
        if (!isset($requestData['product_id']) || !isset($requestData['quantity']) || !is_numeric($requestData['product_id']) || !is_numeric($requestData['quantity']) || $requestData['quantity'] <= 0) {
            $this->jsonResponse(['error' => 'Invalid input. Please provide a valid product_id and a positive quantity.'], 400);
            return;
        }
        $productId = (int) $requestData['product_id'];
        $quantity = (int) $requestData['quantity'];
        $result = $this->cartHelper->updateCartItem($productId, $quantity);
        if (!$result['success']) {
            $statusCode = ($result['message'] === 'Product not found or insufficient stock.') ? 404 : 400;
            $this->jsonResponse(['error' => $result['message']], $statusCode);
            return;
        }
        $this->jsonResponse([
            'success' => true,
            'message' => 'Product added to cart successfully.',
            'total_items' => $result['total_items'],
            'added_product_id' => $productId,
            'added_quantity' => $quantity,
            'product_name' => $result['updated_product']['name'] ?? 'N/A'
        ], 200);
    }
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method. Only POST is allowed.'], 405);
            return;
        }
        if (!$this->session->isAuthenticated()) {
            $this->jsonResponse(['error' => 'Authentication required.'], 401);
            return;
        }
        $requestData = json_decode(file_get_contents('php://input'), true);
        if (!isset($requestData['product_id']) || !isset($requestData['quantity']) || !is_numeric($requestData['product_id']) || !is_numeric($requestData['quantity'])) {
            $this->jsonResponse(['error' => 'Invalid input. Please provide a valid product_id and quantity.'], 400);
            return;
        }
        $productId = (int) $requestData['product_id'];
        $newQuantity = (int) $requestData['quantity'];
        $cart = $this->session->get('cart', []);
        $currentQuantity = isset($cart[$productId]) ? $cart[$productId] : 0;
        $quantityChange = $newQuantity - $currentQuantity;
        if ($newQuantity <= 0) {
            $result = $this->cartHelper->removeCartItem($productId);
        } else {
            $result = $this->cartHelper->updateCartItem($productId, $quantityChange);
        }
        if (!$result['success']) {
            $statusCode = ($result['message'] === 'Item not found in cart.' || $result['message'] === 'Product not found or insufficient stock.') ? 404 : 400;
            $this->jsonResponse(['error' => $result['message']], $statusCode);
            return;
        }
        $this->jsonResponse([
            'success' => true,
            'message' => $result['message'] ?? 'Cart updated successfully.',
            'cart' => $result['cart'],
            'total_items' => $result['total_items'],
            'total_price' => $result['total_price'],
            'is_empty' => $result['is_empty'],
            'updated_product' => $result['updated_product'] ?? null
        ], 200);
    }
    public function getCart()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['error' => 'Invalid request method. Only GET is allowed.'], 405);
            return;
        }
        if (!$this->session->isAuthenticated()) {
            $this->jsonResponse(['error' => 'Authentication required.'], 401);
            return;
        }
        $cartData = $this->cartHelper->getCartData();
        $this->jsonResponse([
            'success' => true,
            'cart' => $cartData['cart_items'],
            'total_items' => $cartData['total_items'],
            'total_price' => $cartData['total_price'],
            'is_empty' => $cartData['is_empty']
        ], 200);
    }
    public function remove()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method. Only POST is allowed.'], 405);
            return;
        }
        if (!$this->session->isAuthenticated()) {
            $this->jsonResponse(['error' => 'Authentication required.'], 401);
            return;
        }
        $requestData = json_decode(file_get_contents('php://input'), true);
        if (!isset($requestData['product_id']) || !is_numeric($requestData['product_id'])) {
            $this->jsonResponse(['error' => 'Invalid input. Please provide a valid product_id.'], 400);
            return;
        }
        $productId = (int) $requestData['product_id'];
        $result = $this->cartHelper->removeCartItem($productId);
        $statusCode = $result['success'] ? 200 : ($result['message'] === 'Item not found in cart.' ? 404 : 400);
        $this->jsonResponse($result, $statusCode);
    }
    public function removeItem($params)
    {
        $this->logger->info('Received request to delete item from cart via URL parameter.', ['params_received' => $params]);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->logger->warning('Invalid request method for removeItem.', ['method' => $_SERVER['REQUEST_METHOD']]);
            $this->jsonResponse(['error' => 'Invalid request method. Only POST is allowed for this action.'], 405);
            return;
        }
        if (!$this->session->isAuthenticated()) {
            $this->logger->warning('Authentication required for removeItem.', ['params_received' => $params]);
            $this->jsonResponse(['error' => 'Authentication required.'], 401);
            return;
        }
        if (!isset($params['product_id']) || !is_numeric($params['product_id']) || (int)$params['product_id'] <= 0) {
            $this->logger->warning('Invalid or missing product ID received for removeItem.', ['params_received' => $params]);
            $this->jsonResponse(['success' => false, 'error' => 'Invalid product ID.'], 400);
            return;
        }
        $actualProductId = (int) $params['product_id'];
        $this->logger->info('Validated product ID.', ['productId' => $actualProductId]);
        $this->logger->info('Attempting to remove item from session/cart helper.', ['productId' => $actualProductId]);
        $result = $this->cartHelper->removeCartItem($actualProductId);
        if ($result['success']) {
            $this->logger->info('Successfully removed item from cart.', ['productId' => $actualProductId, 'result' => $result]);
            $responseData = [
                'success' => true,
                'message' => $result['message'] ?? 'Item removed from cart.',
                'cart' => $result['cart'],
                'total_items' => $result['total_items'],
                'total_price' => $result['total_price'],
                'is_empty' => $result['is_empty']
            ];
            $statusCode = 200;
        } else {
            $this->logger->warning('Failed to remove item from cart (item might not exist?).', ['productId' => $actualProductId, 'result' => $result]);
            $statusCode = ($result['message'] === 'Item not found in cart.') ? 404 : 400;
            $responseData = [
                'success' => false,
                'error' => $result['message'] ?? 'Could not remove item from cart.'
            ];
        }
        $this->logger->info('Sending JSON response for delete request.', ['response' => $responseData, 'statusCode' => $statusCode]);
        $this->jsonResponse($responseData, $statusCode);
    }
    public function clearCart()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Invalid request method. Only POST is allowed.'], 405);
            return;
        }
        if (!$this->session->isAuthenticated()) {
            $this->jsonResponse(['error' => 'Authentication required.'], 401);
            return;
        }
        $result = $this->cartHelper->clearCart();
        $this->jsonResponse([
            'success' => true,
            'message' => 'Cart cleared.',
            'total_items' => $result['total_items'],
            'total_price' => $result['total_price'],
            'is_empty' => $result['is_empty']
        ], 200);
    }
    public function getCartCount()
    {
        $this->logger->info(date('[Y-m-d H:i:s] ') . "CartApiController::getCartCount - Entered method.", [
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'base_url' => BASE_URL ?? 'unknown'
        ]);
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->jsonResponse(['error' => 'Invalid request method. Only GET is allowed.'], 405);
            return;
        }
        $cartData = $this->cartHelper->getCartData();
        $count = $cartData['total_items'] ?? 0;
        $this->logger->info(date('[Y-m-d H:i:s] ') . "CartApiController::getCartCount - Returning count: " . $count);
        $this->jsonResponse([
            'count' => $count
        ], 200);
    }
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
}