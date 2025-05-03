<?php

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Database;
use App\Core\Session;
use App\Core\Request;
use App\Core\Redirect;
use App\Models\Product;
use App\Models\Category;
use App\Models\User as UserModel;
use App\Helpers\SecurityHelper;

class AdminProductController extends BaseController
{
    private $db;
    private $session;
    private $request;
    private $productModel;
    private $categoryModel;
    public function __construct(Database $db, Session $session, Request $request)
    {
        $this->db = $db;
        $this->session = $session;
        $this->request = $request;
        $this->productModel = new Product($db->getConnection());
        $this->categoryModel = new Category($db->getConnection());
    }
    public function index(): void
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 15;
        $filters = [];
        if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
            $filters['category_id'] = (int) $_GET['category_id'];
        }
        if (isset($_GET['is_active']) && ($_GET['is_active'] === '0' || $_GET['is_active'] === '1')) {
            $filters['is_active'] = (int) $_GET['is_active'];
        }
        $result = $this->productModel->getAllProductsPaginated($page, $perPage, $filters);
        $categories = $this->categoryModel->getAll();
        $userId = $this->session->getUserId();
        $adminUserModel = new UserModel($this->db);
        $adminUser = $adminUserModel->findById($userId);
        $data = [
            'page_title' => 'Manage Products',
            'admin_user' => $adminUser,
            'products' => $result['products'] ?? [],
            'pagination' => $result['pagination'] ?? [],
            'filters' => $filters,
            'categories' => $categories,
            'csrf_token' => $this->session->generateCsrfToken()
        ];
        $this->viewWithAdminLayout('admin/products/index', $data);
    }
    public function create(): void
    {
        $categories = $this->categoryModel->getAll();
        $userId = $this->session->getUserId();
        $adminUserModel = new UserModel($this->db);
        $adminUser = $adminUserModel->findById($userId);
        $data = [
            'page_title' => 'Add New Product',
            'admin_user' => $adminUser,
            'categories' => $categories,
            'csrf_token' => $this->session->generateCsrfToken()
        ];
        $this->viewWithAdminLayout('admin/products/create', $data);
    }
    public function store(): void
    {
        if (!$this->session->validateCsrfToken($this->request->post('csrf_token'))) {
            $this->session->flash('error', 'Invalid form submission. Please try again.');
            Redirect::to('/admin/products/create');
            exit();
        }
        $name = trim($this->request->post('name'));
        $description = trim($this->request->post('description'));
        $price = $this->request->post('price');
        $categoryId = (int) $this->request->post('category_id');
        $stockQuantity = (int) $this->request->post('stock_quantity');
        $isActive = $this->request->post('is_active') ? 1 : 0;
        $errors = [];
        if (empty($name)) {
            $errors[] = 'Product name is required.';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Product name must be less than 100 characters.';
        }
        if (empty($description)) {
            $errors[] = 'Product description is required.';
        }
        if (empty($price) || !is_numeric($price) || $price <= 0) {
            $errors[] = 'Valid positive product price is required.';
        }
        if (empty($categoryId) || $categoryId <= 0 || !$this->categoryModel->findById($categoryId)) {
            $errors[] = 'Valid category is required.';
        }
        if ($stockQuantity < 0) {
            $errors[] = 'Stock quantity cannot be negative.';
        }
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleImageUpload($_FILES['image']);
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['path'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        } else {
            $uploadError = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
            if ($uploadError !== UPLOAD_ERR_NO_FILE) {
                $errors[] = 'Error uploading image. Code: ' . $uploadError;
            } else {
                $errors[] = 'Product image is required.';
            }
        }
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            Redirect::to('/admin/products/create');
            exit();
        }
        $productData = [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'category_id' => $categoryId,
            'image_path' => $imagePath,
            'stock_quantity' => $stockQuantity,
            'is_active' => $isActive
        ];
        $productId = $this->productModel->createProduct($productData);
        if ($productId) {
            $this->session->flash('success', 'Product created successfully.');
            Redirect::to('/admin/products');
        } else {
            $this->session->flash('error', 'Failed to create product. Please try again.');
            if (!empty($imagePath) && file_exists(__DIR__ . '/../../../public/' . $imagePath)) {
                unlink(__DIR__ . '/../../../public/' . $imagePath);
            }
            Redirect::to('/admin/products/create');
        }
    }
    public function edit(int $id): void
    {
        $product = $this->productModel->findById($id);
        if (!$product) {
            $this->session->flash('error', 'Product not found.');
            Redirect::to('/admin/products');
            exit();
        }
        $categories = $this->categoryModel->getAll();
        $userId = $this->session->getUserId();
        $adminUserModel = new UserModel($this->db);
        $adminUser = $adminUserModel->findById($userId);
        $data = [
            'page_title' => 'Edit Product',
            'admin_user' => $adminUser,
            'product' => $product,
            'categories' => $categories,
            'csrf_token' => $this->session->generateCsrfToken()
        ];
        $this->viewWithAdminLayout('admin/products/edit', $data);
    }
    public function update(int $id): void
    {
        if (!$this->session->validateCsrfToken($this->request->post('csrf_token'))) {
            $this->session->flash('error', 'Invalid form submission. Please try again.');
            Redirect::to('/admin/products/' . $id . '/edit');
            exit();
        }
        $product = $this->productModel->findById($id);
        if (!$product) {
            $this->session->flash('error', 'Product not found.');
            Redirect::to('/admin/products');
            exit();
        }
        $name = trim($this->request->post('name'));
        $description = trim($this->request->post('description'));
        $price = $this->request->post('price');
        $categoryId = (int) $this->request->post('category_id');
        $stockQuantity = (int) $this->request->post('stock_quantity');
        $isActive = $this->request->post('is_active') ? 1 : 0;
        $errors = [];
        if (empty($name)) $errors[] = 'Product name is required.';
        elseif (strlen($name) > 100) $errors[] = 'Product name must be less than 100 characters.';
        if (empty($description)) $errors[] = 'Product description is required.';
        if (empty($price) || !is_numeric($price) || $price <= 0) $errors[] = 'Valid positive product price is required.';
        if (empty($categoryId) || $categoryId <= 0 || !$this->categoryModel->findById($categoryId)) $errors[] = 'Valid category is required.';
        if ($stockQuantity < 0) $errors[] = 'Stock quantity cannot be negative.';
        $imagePath = $product['image_path'];
        $oldImagePath = $product['image_path'];
        $newImageUploaded = false;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleImageUpload($_FILES['image']);
            if ($uploadResult['success']) {
                $imagePath = $uploadResult['path'];
                $newImageUploaded = true;
            } else {
                $errors[] = $uploadResult['error'];
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errors[] = 'Error uploading new image. Code: ' . $_FILES['image']['error'];
        }
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            Redirect::to('/admin/products/' . $id . '/edit');
            exit();
        }
        $productData = [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'category_id' => $categoryId,
            'image_path' => $imagePath,
            'stock_quantity' => $stockQuantity,
            'is_active' => $isActive
        ];
        $success = $this->productModel->updateProduct($id, $productData);
        if ($success) {
            if ($newImageUploaded && !empty($oldImagePath) && $oldImagePath !== $imagePath) {
                $fullOldPath = __DIR__ . '/../../../public/' . $oldImagePath;
                if (file_exists($fullOldPath)) {
                    unlink($fullOldPath);
                }
            }
            $this->session->flash('success', 'Product updated successfully.');
            Redirect::to('/admin/products');
        } else {
            if ($newImageUploaded) {
                $fullNewPath = __DIR__ . '/../../../public/' . $imagePath;
                if (file_exists($fullNewPath)) {
                    unlink($fullNewPath);
                }
            }
            $this->session->flash('error', 'Failed to update product. Please try again.');
            Redirect::to('/admin/products/' . $id . '/edit');
        }
    }
    public function toggleActive(int $id): void
    {
        if (!$this->session->validateCsrfToken($this->request->post('csrf_token'))) {
            $this->session->flash('error', 'Invalid action or token missing. Please try again.');
            Redirect::to('/admin/products');
            exit();
        }
        $product = $this->productModel->findById($id);
        if (!$product) {
            $this->session->flash('error', 'Product not found.');
            Redirect::to('/admin/products');
            exit();
        }
        $success = $this->productModel->toggleProductActiveStatus($id);
        if ($success) {
            $newStatus = $product['is_active'] ? 'inactive' : 'active';
            $this->session->flash('success', "Product '{$product['name']}' is now {$newStatus}.");
        } else {
            $this->session->flash('error', 'Failed to update product status. Please try again.');
        }
        Redirect::to('/admin/products');
    }
    private function handleImageUpload(array $file): array
    {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024;
        if (!in_array($file['type'], $allowedTypes)) {
            return [
                'success' => false,
                'error' => 'Invalid file type. Allowed types: JPG, PNG, GIF, WEBP.'
            ];
        }
        if ($file['size'] > $maxSize) {
            return [
                'success' => false,
                'error' => 'File size exceeds the maximum allowed (5MB).'
            ];
        }
        $uploadDirRelative = 'assets/images/products/uploads/';
        $uploadDirAbsolute = __DIR__ . '/../../../public/' . $uploadDirRelative;
        if (!is_dir($uploadDirAbsolute)) {
            if (!mkdir($uploadDirAbsolute, 0755, true)) {
                error_log("Failed to create upload directory: " . $uploadDirAbsolute);
                return [
                    'success' => false,
                    'error' => 'Server error: Could not create image directory.'
                ];
            }
        }
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $uniqueFilename = SecurityHelper::generateRandomString(16) . '_' . time() . '.' . $fileExtension;
        $uploadPathAbsolute = $uploadDirAbsolute . $uniqueFilename;
        $uploadPathRelative = $uploadDirRelative . $uniqueFilename;
        if (move_uploaded_file($file['tmp_name'], $uploadPathAbsolute)) {
            return [
                'success' => true,
                'path' => $uploadPathRelative
            ];
        } else {
            error_log("Failed to move uploaded file to: " . $uploadPathAbsolute);
            return [
                'success' => false,
                'error' => 'Failed to save uploaded file. Check permissions or server logs.'
            ];
        }
    }
    protected function viewWithAdminLayout(string $view, array $data = []): void
    {
        $viewPath = __DIR__ . '/../../Views/' . str_replace('.', '/', $view) . '.php';
        $layoutPath = __DIR__ . '/../../Views/layouts/admin.php';
        if (!file_exists($viewPath)) {
            trigger_error("View file not found: {$viewPath}", E_USER_WARNING);
            echo "Error: View file '{$view}' not found.";
            exit;
        }
        if (!file_exists($layoutPath)) {
            trigger_error("Layout file not found: {$layoutPath}", E_USER_WARNING);
            echo "Error: Admin layout file not found.";
            exit;
        }
        try {
            $request = \App\Core\Registry::get('request');
            $uri = $request->uri();
            $currentPath = '/' . ($uri ?: '');
            $data['currentPath'] = $currentPath;
        } catch (\Exception $e) {
            $data['currentPath'] = '/';
        }
        extract($data);
        ob_start();
        try {
            include $viewPath;
        } catch (\Throwable $e) {
            ob_end_clean();
            error_log("Error rendering view '{$view}': " . $e->getMessage());
            echo "Error rendering view '{$view}'. Please check the logs.";
            exit;
        }
        $content = ob_get_clean();
        $data['content'] = $content;
        extract($data);
        include $layoutPath;
    }
}