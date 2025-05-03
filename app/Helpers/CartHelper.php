<?php
namespace App\Helpers;
use App\Core\Session;
use App\Models\Product;
use App\Core\Database;
class CartHelper
{
    private $session;
    private $db;
    private $productModel;
    public function __construct(Session $session, Database $db)
    {
        $this->session = $session;
        $this->db = $db;
        $this->productModel = new Product($db);
    }
    public function getCartData(): array
    {
        $cart = $this->session->get('cart', []);
        if (empty($cart)) {
            return [
                'cart_items' => [],
                'total_price' => 0.0, 
                'total_items' => 0,
                'is_empty' => true
            ];
        }
        $productIds = array_keys($cart);
        $products = $this->productModel->findMultipleByIds($productIds);
        $cartItems = [];
        $totalPrice = 0.0; 
        $totalItems = 0;
        foreach ($cart as $productId => $quantity) {
            if (isset($products[$productId])) {
                $product = $products[$productId];
                $itemPrice = (float)$product['price'] * (int)$quantity; 
                $totalPrice += $itemPrice;
                $totalItems += (int)$quantity; 
                $cartItems[] = [
                    'product_id' => $productId,
                    'name' => $product['name'],
                    'price' => (float)$product['price'], 
                    'image' => $product['image'] ?? 'default_image.png', 
                    'quantity' => (int)$quantity, 
                    'total_price' => $itemPrice
                ];
            } else {
                error_log("CartHelper: Product ID {$productId} found in cart session but not in database.");
            }
        }
        return [
            'cart_items' => $cartItems,
            'total_price' => $totalPrice,
            'total_items' => $totalItems,
            'is_empty' => ($totalItems === 0) 
        ];
    }
    public function updateCartItem(int $productId, int $quantity): array
    {
        $product = $this->productModel->findById($productId);
        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not found.'
            ];
        }
        $cart = $this->session->get('cart', []);
        $currentQuantity = isset($cart[$productId]) ? (int)$cart[$productId] : 0;
        $newQuantity = $currentQuantity + $quantity;
        if ($newQuantity <= 0) {
            if (isset($cart[$productId])) {
                unset($cart[$productId]);
            }
            $newQuantity = 0; 
        } else {
            $cart[$productId] = $newQuantity;
        }
        $this->session->set('cart', $cart);
        $cartData = $this->getCartData();
        $updatedProductData = null;
        if ($newQuantity > 0) {
            $updatedProductData = [
                'product_id' => $productId,
                'name' => $product['name'],
                'new_quantity' => $newQuantity,
                'price' => (float)$product['price'],
                'new_total' => (float)$product['price'] * $newQuantity
            ];
        } elseif ($currentQuantity > 0) { 
            $updatedProductData = [
                'product_id' => $productId,
                'name' => $product['name'],
                'new_quantity' => 0,
                'price' => (float)$product['price'],
                'new_total' => 0.0
            ];
        }
        return [
            'success' => true,
            'message' => 'Cart updated successfully.',
            'cart' => $cartData['cart_items'],
            'total_items' => $cartData['total_items'],
            'total_price' => $cartData['total_price'],
            'is_empty' => $cartData['is_empty'],
            'updated_product' => $updatedProductData
        ];
    }
    public function removeCartItem(int $productId): array
    {
        $cart = $this->session->get('cart', []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $this->session->set('cart', $cart);
        }
        $cartData = $this->getCartData();
        return [
            'success' => true,
            'message' => 'Item removed from cart.',
            'cart' => $cartData['cart_items'],
            'total_items' => $cartData['total_items'],
            'total_price' => $cartData['total_price'],
            'is_empty' => $cartData['is_empty']
        ];
    }
    public function clearCart(): array
    {
        $this->session->set('cart', []);
        return [
            'success' => true,
            'message' => 'Cart cleared.',
            'cart_items' => [],
            'total_price' => 0.0,
            'total_items' => 0,
            'is_empty' => true
        ];
    }
}
