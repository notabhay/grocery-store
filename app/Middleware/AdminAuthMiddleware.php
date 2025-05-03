<?php

namespace App\Middleware;

use App\Core\Session;
use App\Core\Redirect;
use App\Core\Registry;
use App\Models\User;

class AdminAuthMiddleware
{
    private $session;
    private $db;
    public function __construct()
    {
        $this->session = Registry::get('session');
        $this->db = Registry::get('database');
    }
    public function handle(callable $next): mixed
    {
        if (!$this->session->isAuthenticated()) {
            $this->session->flash('error', 'Please log in to access the admin area.');
            Redirect::to('/login');
            exit();
        }
        $userId = $this->session->getUserId();
        if (!$userId) {
            $this->session->flash('error', 'Authentication error. Please log in again.');
            $this->session->destroy();
            Redirect::to('/login');
            exit();
        }
        $userModel = new User($this->db);
        $user = $userModel->findById($userId);
        if (!$user || !isset($user['role']) || $user['role'] !== 'admin') {
            $this->session->flash('error', 'You do not have permission to access the admin area.');
            Redirect::to('/');
            exit();
        }
        return $next();
    }
}