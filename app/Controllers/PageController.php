<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Core\BaseController;
use App\Core\Request;
use App\Core\Redirect;
use App\Helpers\SecurityHelper;
use App\Helpers\CartHelper;
use App\Core\Registry;
use Psr\Log\LoggerInterface;
use App\Models\Product;

class PageController extends BaseController
{
    private $db;
    private $session;
    private $request;
    private $securityHelper;
    private $logger;
    private $cartHelper;
    public function __construct()
    {
        $this->db = Registry::get('database');
        $this->session = Registry::get('session');
        $this->request = Registry::get('request');
        $this->logger = Registry::get('logger');
        $this->securityHelper = new SecurityHelper();
        $this->cartHelper = new CartHelper($this->session, $this->db);
        $pdoConnection = $this->db->getConnection();
        if (!$pdoConnection) {
            $this->logger->critical("Database connection not available for PageController.");
            throw new \RuntimeException("Database connection not available for PageController.");
        }
    }
    public function index(): void
    {
        $random_products = [];
        try {
            $productModel = new Product($this->db->getConnection());
            $random_products = $productModel->getFeaturedProducts();
            foreach ($random_products as &$product) {
                $image_filename = isset($product['image']) ? basename($product['image']) : 'default.png';
                $product['image_url'] = BASE_URL . 'assets/images/products/' . $image_filename;
            }
            unset($product);
        } catch (\Exception $e) {
            $this->logger->error("Error fetching products for homepage.", ['exception' => $e]);
        }
        $data = [
            'page_title' => 'GhibliGroceries - Fresh Food Delivered',
            'meta_description' => 'Fresh groceries delivered to your door. Shop vegetables, meat, and more at our online grocery store.',
            'meta_keywords' => 'grocery, online shopping, vegetables, meat, fresh produce',
            'random_products' => $random_products,
            'logged_in' => $this->session->isAuthenticated(),
        ];
        $this->view('pages/index', $data);
    }
    public function about(): void
    {
        $data = [
            'page_title' => 'About Us - GhibliGroceries',
            'meta_description' => 'Learn more about GhibliGroceries, your source for fresh, quality groceries delivered fast.',
            'meta_keywords' => 'about us, grocery store, online groceries, GhibliGroceries',
            'logged_in' => $this->session->isAuthenticated(),
            'additional_css_files' => ['assets/css/about.css'],
        ];
        $this->view('pages/about', $data);
    }
    public function contact(): void
    {
        $data = [
            'page_title' => 'Contact Us - GhibliGroceries',
            'meta_description' => 'Get in touch with GhibliGroceries. Contact us for support, inquiries, or feedback.',
            'meta_keywords' => 'contact, support, help, grocery store, GhibliGroceries',
            'csrf_token' => $this->session->getCsrfToken(),
            'flash_message' => $this->session->getFlash('contact_message'),
            'form_errors' => $this->session->getFlash('form_errors', []),
            'old_input' => $this->session->getFlash('old_input', []),
            'logged_in' => $this->session->isAuthenticated(),
            'additional_css_files' => ['assets/css/contact.css'],
        ];
        $this->view('pages/contact', $data);
    }
    public function submitContact(): void
    {
        if (!$this->request->isPost()) {
            Redirect::to('/contact');
            return;
        }
        $name = $this->request->post('name', '');
        $email = $this->request->post('email', '');
        $message = $this->request->post('message', '');
        $submitted_token = $this->request->post('csrf_token', '');
        if (!$this->session->validateCsrfToken($submitted_token)) {
            $this->session->flash('contact_message', ['type' => 'error', 'text' => 'Invalid security token. Please try again.']);
            Redirect::to('/contact');
            return;
        }
        $sanitized_name = $this->securityHelper->sanitizeInput($name);
        $sanitized_email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $sanitized_message = $this->securityHelper->sanitizeInput($message);
        $errors = [];
        if (empty($sanitized_name)) {
            $errors['name'] = 'Name is required.';
        }
        if (!filter_var($sanitized_email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email address is required.';
        }
        if (empty($sanitized_message)) {
            $errors['message'] = 'Message is required.';
        }
        if (strlen($sanitized_message) < 10 || strlen($sanitized_message) > 1000) {
            $errors['message'] = 'Message must be between 10 and 1000 characters.';
        }
        if (!empty($errors)) {
            $this->session->flash('contact_message', ['type' => 'error', 'text' => 'Please correct the errors below.']);
            $this->session->flash('form_errors', $errors);
            $this->session->flash('old_input', ['name' => $sanitized_name, 'email' => $sanitized_email, 'message' => $sanitized_message]);
            Redirect::to('/contact');
            return;
        }
        $this->logger->info("Contact form submitted successfully (simulation).", ['name' => $sanitized_name, 'email' => $sanitized_email]);
        $submission_successful = true;
        if ($submission_successful) {
            $this->session->flash('contact_message', ['type' => 'success', 'text' => 'Thank you for your message! We will get back to you soon.']);
        } else {
            $this->session->flash('contact_message', ['type' => 'error', 'text' => 'Sorry, there was an error sending your message. Please try again later.']);
            $this->session->flash('old_input', ['name' => $sanitized_name, 'email' => $sanitized_email, 'message' => $sanitized_message]);
        }
        Redirect::to('/contact');
    }
    public function cart(): void
    {
        if (!$this->session->isAuthenticated()) {
            Redirect::to('/login');
            return;
        }
        $cartData = $this->cartHelper->getCartData();
        $data = array_merge($cartData, [
            'page_title' => 'Your Shopping Cart - GhibliGroceries',
            'meta_description' => 'View and manage your shopping cart at GhibliGroceries.',
            'meta_keywords' => 'shopping cart, checkout, grocery store, GhibliGroceries',
            'logged_in' => $this->session->isAuthenticated(),
            'additional_css_files' => ['assets/css/cart.css'],
        ]);
        $this->view('pages/cart', $data);
    }
}