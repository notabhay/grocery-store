<?php
namespace App\Controllers\Admin;
use App\Core\BaseController;
use App\Core\Database;
use App\Core\Session;
use App\Core\Request;
use App\Core\Redirect;
use App\Models\Category;
use App\Models\User as UserModel; 
use App\Helpers\SecurityHelper;
class AdminCategoryController extends BaseController
{
    private $db;
    private $session;
    private $request;
    private $categoryModel;
    public function __construct(Database $db, Session $session, Request $request)
    {
        $this->db = $db;
        $this->session = $session;
        $this->request = $request;
        $this->categoryModel = new Category($db->getConnection());
    }
    public function index(): void
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 15; 
        $result = $this->categoryModel->getAllCategoriesPaginated($page, $perPage);
        $userId = $this->session->getUserId();
        $adminUserModel = new UserModel($this->db); 
        $adminUser = $adminUserModel->findById($userId);
        $data = [
            'page_title' => 'Manage Categories',
            'admin_user' => $adminUser,
            'categories' => $result['categories'] ?? [], 
            'pagination' => $result['pagination'] ?? [], 
            'csrf_token' => $this->session->generateCsrfToken() 
        ];
        $this->viewWithAdminLayout('admin/categories/index', $data);
    }
    public function create(): void
    {
        $categories = $this->categoryModel->getAll();
        $userId = $this->session->getUserId();
        $adminUserModel = new UserModel($this->db); 
        $adminUser = $adminUserModel->findById($userId);
        $data = [
            'page_title' => 'Add New Category',
            'admin_user' => $adminUser,
            'categories' => $categories, 
            'csrf_token' => $this->session->generateCsrfToken() 
        ];
        $this->viewWithAdminLayout('admin/categories/create', $data);
    }
    public function store(): void
    {
        if (!$this->session->validateCsrfToken($this->request->post('csrf_token'))) {
            $this->session->flash('error', 'Invalid form submission. Please try again.');
            Redirect::to('/admin/categories/create');
            exit();
        }
        $name = trim($this->request->post('name'));
        $parentId = (int) $this->request->post('parent_id');
        if ($parentId === 0) {
            $parentId = null;
        }
        $errors = [];
        if (empty($name)) {
            $errors[] = 'Category name is required.';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Category name must be less than 100 characters.';
        }
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            Redirect::to('/admin/categories/create');
            exit();
        }
        $categoryData = [
            'name' => $name,
            'parent_id' => $parentId
        ];
        $categoryId = $this->categoryModel->createCategory($categoryData);
        if ($categoryId) {
            $this->session->flash('success', 'Category created successfully.');
            Redirect::to('/admin/categories');
        } else {
            $this->session->flash('error', 'Failed to create category. Please try again.');
            Redirect::to('/admin/categories/create');
        }
    }
    public function edit(int $id): void
    {
        $category = $this->categoryModel->findById($id);
        if (!$category) {
            $this->session->flash('error', 'Category not found.');
            Redirect::to('/admin/categories');
            exit();
        }
        $categories = $this->categoryModel->getAll();
        $userId = $this->session->getUserId();
        $adminUserModel = new UserModel($this->db); 
        $adminUser = $adminUserModel->findById($userId);
        $hasProducts = $this->categoryModel->hasProducts($id);
        $data = [
            'page_title' => 'Edit Category',
            'admin_user' => $adminUser,
            'category' => $category, 
            'categories' => $categories, 
            'has_products' => $hasProducts, 
            'csrf_token' => $this->session->generateCsrfToken() 
        ];
        $this->viewWithAdminLayout('admin/categories/edit', $data);
    }
    public function update(int $id): void
    {
        if (!$this->session->validateCsrfToken($this->request->post('csrf_token'))) {
            $this->session->flash('error', 'Invalid form submission. Please try again.');
            Redirect::to('/admin/categories/' . $id . '/edit');
            exit();
        }
        $category = $this->categoryModel->findById($id);
        if (!$category) {
            $this->session->flash('error', 'Category not found.');
            Redirect::to('/admin/categories');
            exit();
        }
        $name = trim($this->request->post('name'));
        $parentId = (int) $this->request->post('parent_id');
        if ($parentId === 0 || $parentId === $id) {
            $parentId = null;
        }
        $errors = [];
        if (empty($name)) {
            $errors[] = 'Category name is required.';
        } elseif (strlen($name) > 100) {
            $errors[] = 'Category name must be less than 100 characters.';
        }
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            Redirect::to('/admin/categories/' . $id . '/edit');
            exit();
        }
        $categoryData = [
            'name' => $name,
            'parent_id' => $parentId
        ];
        $success = $this->categoryModel->updateCategory($id, $categoryData);
        if ($success) {
            $this->session->flash('success', 'Category updated successfully.');
            Redirect::to('/admin/categories');
        } else {
            $this->session->flash('error', 'Failed to update category. Please try again.');
            Redirect::to('/admin/categories/' . $id . '/edit');
        }
    }
    public function destroy(int $id): void
    {
        if (!$this->session->validateCsrfToken($this->request->post('csrf_token'))) {
            $this->session->flash('error', 'Invalid form submission or token missing. Please try again.');
            Redirect::to('/admin/categories');
            exit();
        }
        $category = $this->categoryModel->findById($id);
        if (!$category) {
            $this->session->flash('error', 'Category not found.');
            Redirect::to('/admin/categories');
            exit();
        }
        if ($this->categoryModel->hasProducts($id)) {
            $this->session->flash('error', 'Cannot delete category because it has associated products.');
            Redirect::to('/admin/categories');
            exit();
        }
        $success = $this->categoryModel->deleteCategory($id);
        if ($success) {
            $this->session->flash('success', 'Category deleted successfully.');
        } else {
            $this->session->flash('error', 'Failed to delete category. Please try again.');
        }
        Redirect::to('/admin/categories');
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
