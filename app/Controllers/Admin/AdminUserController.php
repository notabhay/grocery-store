<?php

namespace App\Controllers\Admin;

use App\Core\BaseController;
use App\Core\Database;
use App\Core\Session;
use App\Core\Request;
use App\Core\Redirect;
use App\Models\User;



class AdminUserController extends BaseController
{
    
    private $db;

    
    private $session;

    
    private $userModel;

    
    private $request;

    
    public function __construct(Database $db, Session $session, Request $request)
    {
        $this->db = $db;
        $this->session = $session;
        $this->request = $request;
        $this->userModel = new User($db); 
    }

    
    public function index(): void
    {
        
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 15; 

        
        $result = $this->userModel->getAllUsersPaginated($page, $perPage);

        
        $userId = $this->session->getUserId();
        $adminUser = $this->userModel->findById($userId); 

        
        $data = [
            'page_title' => 'Manage Users',
            'admin_user' => $adminUser, 
            'users' => $result['users'] ?? [], 
            'pagination' => $result['pagination'] ?? [] 
        ];

        
        $this->viewWithAdminLayout('admin/users/index', $data);
    }

    
    public function show(int $id): void
    {
        
        $user = $this->userModel->findById($id);

        
        if (!$user) {
            $this->session->flash('error', 'User not found.');
            Redirect::to('/admin/users');
            exit();
        }

        
        $adminUserId = $this->session->getUserId();
        $adminUser = $this->userModel->findById($adminUserId);

        
        $data = [
            'page_title' => 'User Details',
            'admin_user' => $adminUser, 
            'user' => $user, 
            
            'csrf_token' => $this->session->generateCsrfToken()
        ];

        
        $this->viewWithAdminLayout('admin/users/show', $data);
    }

    
    public function edit(int $id): void
    {
        
        $user = $this->userModel->findById($id);

        
        if (!$user) {
            $this->session->flash('error', 'User not found.');
            Redirect::to('/admin/users');
            exit();
        }

        
        $adminUserId = $this->session->getUserId();
        $adminUser = $this->userModel->findById($adminUserId);

        
        $data = [
            'page_title' => 'Edit User',
            'admin_user' => $adminUser, 
            'user' => $user, 
            'csrf_token' => $this->session->generateCsrfToken() 
        ];

        
        $this->viewWithAdminLayout('admin/users/edit', $data);
    }

    
    public function update(int $id): void
    {
        
        if (!$this->session->validateCsrfToken($this->request->post('csrf_token'))) {
            $this->session->flash('error', 'Invalid form submission. Please try again.');
            Redirect::to('/admin/users/' . $id . '/edit');
            exit();
        }

        
        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->session->flash('error', 'User not found.');
            Redirect::to('/admin/users');
            exit();
        }

        
        $name = trim($this->request->post('name'));
        $phone = trim($this->request->post('phone'));
        $role = $this->request->post('role');
        $accountStatus = $this->request->post('account_status');

        
        $errors = [];
        if (empty($name)) {
            $errors[] = 'Name is required.';
        } elseif (strlen($name) > 100) { 
            $errors[] = 'Name must be less than 100 characters.';
        }
        if (empty($phone)) {
            $errors[] = 'Phone is required.';
        } elseif (!preg_match('/^[0-9+\-\s()]{5,20}$/', $phone)) { 
            $errors[] = 'Phone number format is invalid (allow numbers, +, -, spaces, parentheses, 5-20 chars).';
        }
        
        if (!in_array($role, ['customer', 'admin'])) {
            $errors[] = 'Invalid role selected. Must be "customer" or "admin".';
        }
        
        if (!in_array($accountStatus, ['active', 'inactive'])) {
            $errors[] = 'Invalid account status selected. Must be "active" or "inactive".';
        }

        
        $adminUserId = $this->session->getUserId();
        if ($id == $adminUserId && $role != 'admin') {
            $errors[] = 'You cannot remove your own admin role.';
        }
        
        if ($id == $adminUserId && $accountStatus != 'active') {
            $errors[] = 'You cannot deactivate your own account.';
        }


        
        if (!empty($errors)) {
            $this->session->flash('errors', $errors);
            Redirect::to('/admin/users/' . $id . '/edit');
            exit();
        }

        
        $data = [
            'name' => $name,
            'phone' => $phone,
            'role' => $role,
            'account_status' => $accountStatus
            
        ];

        
        $success = $this->userModel->updateUser($id, $data);

        
        if ($success) {
            $this->session->flash('success', 'User updated successfully.');
            
            Redirect::to('/admin/users/' . $id);
        } else {
            $this->session->flash('error', 'Failed to update user. Please try again.');
            Redirect::to('/admin/users/' . $id . '/edit');
        }
    }

    
    public function triggerPasswordReset(int $id): void
    {
        
        if (!$this->session->validateCsrfToken($this->request->post('csrf_token'))) {
            $this->session->flash('error', 'Invalid action or token missing. Please try again.');
            Redirect::to('/admin/users/' . $id); 
            exit();
        }

        
        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->session->flash('error', 'User not found.');
            Redirect::to('/admin/users'); 
            exit();
        }

        
        $token = $this->userModel->generatePasswordResetToken($id);
        if (!$token) {
            
            $this->session->flash('error', 'Failed to generate password reset token. Please try again.');
            Redirect::to('/admin/users/' . $id);
            exit();
        }

        
        
        $scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $resetLink = $scheme . '://' . $host . '/reset-password?token=' . urlencode($token);

        
        $emailSubject = 'GhibliGroceries Password Reset Request';
        $emailBody = "Hello {$user['name']},\n\n";
        $emailBody .= "A password reset was requested for your account by an administrator.\n";
        $emailBody .= "Please click the link below to set a new password:\n\n";
        $emailBody .= $resetLink . "\n\n";
        $emailBody .= "This link will expire in 1 hour.\n\n";
        $emailBody .= "If you did not expect this, please contact support or ignore this email.\n\n";
        $emailBody .= "Regards,\nThe GhibliGroceries Team";

        
        
        

        
        error_log("Password reset triggered for user ID {$id} ({$user['email']}). Reset Link: " . $resetLink);

        
        $this->session->flash(
            'success',
            "Password reset process initiated for {$user['email']}. " .
                "An email simulation has been logged. <br><br>" .
                "<strong>Reset Link (for testing):</strong> <a href='{$resetLink}' target='_blank'>{$resetLink}</a>"
        );

        
        Redirect::to('/admin/users/' . $id);
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
