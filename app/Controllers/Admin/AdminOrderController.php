<?php
namespace App\Controllers\Admin;
use App\Core\BaseController;
use App\Core\Database;
use App\Core\Session;
use App\Core\Request;
use App\Core\Redirect;
use App\Models\Order;
use App\Models\User;
use App\Models\OrderItem; 
class AdminOrderController extends BaseController
{
    private $db;
    private $session;
    private $request;
    private $orderModel;
    private $userModel;
    public function __construct(Database $db, Session $session, Request $request)
    {
        $this->db = $db;
        $this->session = $session;
        $this->request = $request;
        $this->orderModel = new Order($db->getConnection());
        $this->userModel = new User($db->getConnection()); 
    }
    public function index(): void
    {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 15; 
        $filters = [];
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
            $filters['start_date'] = $_GET['start_date'];
        }
        if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
            $filters['end_date'] = $_GET['end_date'];
        }
        $result = $this->orderModel->getAllOrdersPaginated($page, $perPage, $filters);
        $userId = $this->session->getUserId();
        $adminUser = $this->userModel->findById($userId);
        $data = [
            'page_title' => 'Manage Orders',
            'admin_user' => $adminUser,
            'orders' => $result['orders'] ?? [], 
            'pagination' => $result['pagination'] ?? [], 
            'filters' => $filters, 
            'csrf_token' => $this->session->generateCsrfToken() 
        ];
        $this->viewWithAdminLayout('admin/orders/index', $data);
    }
    public function show(int $id): void
    {
        $order = $this->orderModel->findOrderWithDetails($id);
        if (!$order) {
            $this->session->flash('error', 'Order not found.');
            Redirect::to('/admin/orders');
            exit();
        }
        $adminUserId = $this->session->getUserId();
        $adminUser = $this->userModel->findById($adminUserId);
        $data = [
            'page_title' => 'Order Details',
            'admin_user' => $adminUser,
            'order' => $order, 
            'csrf_token' => $this->session->generateCsrfToken() 
        ];
        $this->viewWithAdminLayout('admin/orders/show', $data);
    }
    public function updateStatus(int $id): void
    {
        if (!$this->session->validateCsrfToken($this->request->post('csrf_token'))) {
            $this->session->flash('error', 'Invalid form submission. Please try again.');
            Redirect::to('/admin/orders/' . $id); 
            exit();
        }
        $order = $this->orderModel->readOne($id); 
        if (!$order) {
            $this->session->flash('error', 'Order not found.');
            Redirect::to('/admin/orders'); 
            exit();
        }
        $newStatus = $this->request->post('status');
        $validStatuses = ['pending', 'processing', 'completed', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            $this->session->flash('error', 'Invalid order status provided.');
            Redirect::to('/admin/orders/' . $id); 
            exit();
        }
        $success = $this->orderModel->updateOrderStatus($id, $newStatus);
        if ($success) {
            $this->session->flash('success', "Order #{$id} status updated to '{$newStatus}'.");
        } else {
            $errorMessage = $this->orderModel->getErrorMessage() ?: 'Failed to update order status.';
            $this->session->flash('error', $errorMessage . ' Please try again.');
        }
        Redirect::to('/admin/orders/' . $id);
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
