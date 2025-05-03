<?php

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Database;
use App\Core\Session;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;


class AdminDashboardController extends BaseController
{
    
    private $db;

    
    private $session;

    
    private $userModel;

    
    private $orderModel;

    
    private $productModel;

    
    private $categoryModel;

    
    public function __construct(Database $db, Session $session)
    {
        $this->db = $db;
        $this->session = $session;
        
        $this->userModel = new User($db);
        $this->orderModel = new Order($db->getConnection());
        $this->productModel = new Product($db); 
        $this->categoryModel = new Category($db->getConnection());
    }

    
    public function index(): void
    {
        
        $userId = $this->session->getUserId();
        $adminUser = $this->userModel->findById($userId);

        
        $totalUsers = $this->userModel->getTotalUserCount();
        $totalOrders = $this->orderModel->getTotalOrderCount();
        $pendingOrders = $this->orderModel->getOrderCountByStatus('pending');
        $processingOrders = $this->orderModel->getOrderCountByStatus('processing');
        $completedOrders = $this->orderModel->getOrderCountByStatus('completed');
        $cancelledOrders = $this->orderModel->getOrderCountByStatus('cancelled');
        $recentOrders = $this->orderModel->getRecentOrders(5); 
        $lowStockProducts = $this->productModel->getLowStockProductCount(); 
        $totalProducts = $this->productModel->getTotalProductCount(); 
        $totalCategories = $this->categoryModel->getTotalCategoryCount(); 

        
        $data = [
            'page_title' => 'Admin Dashboard',
            'admin_user' => $adminUser, 
            'stats' => [ 
                'total_users' => $totalUsers,
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'processing_orders' => $processingOrders,
                'completed_orders' => $completedOrders,
                'cancelled_orders' => $cancelledOrders,
                'total_products' => $totalProducts,
                'total_categories' => $totalCategories,
                'low_stock_products' => $lowStockProducts
            ],
            'recent_orders' => $recentOrders 
        ];

        
        $this->viewWithAdminLayout('admin/dashboard', $data);
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
