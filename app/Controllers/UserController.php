<?php
namespace App\Controllers;
use App\Core\BaseController;
use App\Core\Database;
use App\Core\Registry;
use App\Core\Request;
use App\Core\Session;
use App\Core\Redirect;
use App\Models\User;
use App\Helpers\SecurityHelper;
use App\Helpers\CaptchaHelper;
use Psr\Log\LoggerInterface;
class UserController extends BaseController
{
    private $db;
    private $session;
    private $request;
    private $userModel;
    private $logger;
    private $captchaHelper;
    public function __construct()
    {
        $this->db = Registry::get('database');
        $this->session = Registry::get('session');
        $this->request = Registry::get('request');
        $this->logger = Registry::get('logger');
        $this->captchaHelper = new CaptchaHelper($this->session);
        $pdoConnection = $this->db->getConnection(); 
        if ($pdoConnection) {
            $this->userModel = new User($pdoConnection);
        } else {
            $this->logger->critical("Database connection not available for UserController.");
            throw new \RuntimeException("Database connection not available for UserController.");
        }
    }
    public function showLogin(): void
    {
        if ($this->session->isAuthenticated()) {
            Redirect::to('/');
            return;
        }
        $captchaText = $this->captchaHelper->generateText();
        $this->captchaHelper->storeText($captchaText);
        $login_error = $this->session->getFlash('login_error');
        $captcha_error = $this->session->getFlash('captcha_error');
        $email = $this->session->getFlash('input_email'); 
        $success_message = $this->session->getFlash('success'); 
        $csrf_token = $this->session->getCsrfToken();
        $data = [
            'page_title' => 'Login - GhibliGroceries',
            'meta_description' => 'Login to GhibliGroceries - Access your account to place orders.',
            'meta_keywords' => 'login, grocery, online shopping, account access',
            'additional_css_files' => ['assets/css/login.css'], 
            'email' => $email ?? '', 
            'login_error' => $login_error ?? '', 
            'captcha_error' => $captcha_error ?? '', 
            'success_message' => $success_message ?? '', 
            'csrf_token' => $csrf_token 
        ];
        $this->view('pages/login', $data);
    }
    public function login(): void
    {
        if (!$this->request->isPost()) {
            Redirect::to('/login');
            return;
        }
        $csrf_input = $this->request->post('csrf_token', '');
        if (!$this->session->validateCsrfToken($csrf_input)) {
            $this->session->flash('login_error', 'Invalid security token. Please try again.');
            $this->logger->warning('CSRF token validation failed during login attempt.');
            $this->regenerateCaptchaAndRedirect('/login'); 
            return;
        }
        $captcha_input = $this->request->post('captcha', '');
        if (!$this->captchaHelper->validate($captcha_input)) {
            $this->session->flash('captcha_error', "The verification code is incorrect.");
            $this->session->flash('input_email', $this->request->post('email', '')); 
            $this->logger->warning('CAPTCHA validation failed during login attempt.');
            $this->regenerateCaptchaAndRedirect('/login');
            return;
        }
        $email = SecurityHelper::sanitizeInput($this->request->post('email', ''));
        $password = $this->request->post('password', ''); 
        if (!SecurityHelper::validateEmail($email) || empty($password)) {
            $this->session->flash('login_error', "Invalid email format or missing password.");
            $this->session->flash('input_email', $email); 
            $this->regenerateCaptchaAndRedirect('/login');
            return;
        }
        if ($this->userModel->verifyPassword($email, $password)) {
            $user = $this->userModel->findByEmail($email);
            if ($user) {
                if (isset($user['account_status']) && $user['account_status'] === 'locked') {
                    $this->session->flash('login_error', "Your account has been locked due to too many failed login attempts. Please contact support.");
                    $this->logger->warning('Login attempt on locked account.', ['email' => $email]);
                    $this->regenerateCaptchaAndRedirect('/login');
                    return;
                }
                $this->session->loginUser($user['user_id']); 
                $this->session->set('user_name', $user['name']); 
                $this->session->set('user_email', $user['email']); 
                $this->session->remove('captcha'); 
                $this->logger->info('User logged in successfully.', ['user_id' => $user['user_id'], 'email' => $email]);
                Redirect::to('/'); 
                return;
            } else {
                $this->logger->error('User data not found after successful password verification.', ['email' => $email]);
                $this->session->flash('login_error', "An unexpected error occurred during login.");
                $this->regenerateCaptchaAndRedirect('/login');
                return;
            }
        } else {
            $this->session->flash('login_error', "Invalid email or password.");
            $this->session->flash('input_email', $email); 
            $this->logger->warning('Invalid login attempt (wrong credentials).', ['email' => $email]);
            $this->regenerateCaptchaAndRedirect('/login');
            return;
        }
    }
    public function showRegister(): void
    {
        if ($this->session->isAuthenticated()) {
            Redirect::to('/');
            return;
        }
        $registration_error = $this->session->getFlash('registration_error');
        $registration_success = $this->session->getFlash('registration_success'); 
        $input_data = $this->session->getFlash('input_data', []); 
        $data = [
            'page_title' => 'Register - GhibliGroceries',
            'meta_description' => 'Create an account with GhibliGroceries to start ordering fresh groceries online.',
            'meta_keywords' => 'register, grocery, create account, sign up',
            'additional_css_files' => ['assets/css/register.css'], 
            'csrf_token' => $this->session->getCsrfToken(), 
            'registration_error' => $registration_error ?? '', 
            'registration_success' => $registration_success ?? false, 
            'input' => $input_data 
        ];
        $this->view('pages/register', $data);
    }
    public function register(): void
    {
        $isAjax = $this->request->isAjax(); 
        if ($this->session->isAuthenticated()) {
            if ($isAjax) {
                $this->jsonResponse(['success' => false, 'message' => 'Already logged in.'], 403); 
                return;
            } else {
                Redirect::to('/');
                return;
            }
        }
        if (!$this->request->isPost()) {
            if ($isAjax) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid request method.'], 405); 
                return;
            } else {
                Redirect::to('/register');
                return;
            }
        }
        $csrf_input = $this->request->input('csrf_token', ''); 
        if (!$this->session->validateCsrfToken($csrf_input)) {
            $this->logger->warning('CSRF token validation failed during registration attempt.', ['isAjax' => $isAjax]);
            if ($isAjax) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.'], 403);
                return;
            } else {
                $this->session->flash('registration_error', 'Invalid security token. Please try again.');
                Redirect::to('/register');
                return;
            }
        }
        $name = SecurityHelper::sanitizeInput($this->request->input('name', ''));
        $phone = SecurityHelper::sanitizeInput($this->request->input('phone', ''));
        $email = SecurityHelper::sanitizeInput($this->request->input('email', ''));
        $password = $this->request->input('password', ''); 
        $input_data = ['name' => $name, 'phone' => $phone, 'email' => $email]; 
        $errors = [];
        if (!SecurityHelper::validateName($name)) {
            $errors['name'] = 'Please enter a valid name (letters and spaces only).';
        }
        if (!SecurityHelper::validatePhone($phone)) {
            $errors['phone'] = 'Please enter a valid 10-digit phone number.';
        }
        if (!SecurityHelper::validateEmail($email)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if (!SecurityHelper::validatePassword($password)) {
            $errors['password'] = 'Password must be at least 8 characters long.';
        }
        if (empty($errors['email']) && $this->userModel->emailExists($email)) {
            $errors['email'] = 'This email address is already registered.';
        }
        if (!empty($errors)) {
            $this->logger->warning('Registration validation failed.', ['errors' => $errors, 'email' => $email, 'isAjax' => $isAjax]);
            if ($isAjax) {
                $this->jsonResponse(['success' => false, 'message' => 'Validation failed.', 'errors' => $errors], 422); 
                return;
            } else {
                $this->session->flash('registration_error', implode('<br>', $errors)); 
                $this->session->flash('input_data', $input_data); 
                Redirect::to('/register');
                return;
            }
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        if ($hashedPassword === false) {
            $this->logger->error("Password hashing failed during registration.", ['email' => $email, 'isAjax' => $isAjax]);
            if ($isAjax) {
                $this->jsonResponse(['success' => false, 'message' => 'Could not process registration due to a server error. Please try again.'], 500);
                return;
            } else {
                $this->session->flash('registration_error', 'Could not process registration due to a server error. Please try again.');
                Redirect::to('/register');
                return;
            }
        }
        try {
            $userId = $this->userModel->create($name, $phone, $email, $hashedPassword);
            if ($userId) {
                $this->logger->info('New user registered successfully.', ['user_id' => $userId, 'email' => $email, 'isAjax' => $isAjax]);
                if ($isAjax) {
                    $this->jsonResponse(['success' => true, 'message' => 'Registration successful!']);
                    return;
                } else {
                    $this->session->flash('success', 'Registration successful! You can now login.');
                    Redirect::to('/login');
                    return;
                }
            } else {
                $this->logger->error("User creation database operation failed.", ['email' => $email, 'isAjax' => $isAjax]);
                if ($isAjax) {
                    $this->jsonResponse(['success' => false, 'message' => 'Registration failed due to a database error. Please try again later.'], 500);
                    return;
                } else {
                    $this->session->flash('registration_error', 'Registration failed due to a database error. Please try again later.');
                    $this->session->flash('input_data', $input_data); 
                    Redirect::to('/register');
                    return;
                }
            }
        } catch (\Exception $e) {
            $this->logger->error("Exception during user creation.", ['email' => $email, 'error' => $e->getMessage(), 'isAjax' => $isAjax]);
            if ($isAjax) {
                $this->jsonResponse(['success' => false, 'message' => 'An unexpected error occurred during registration. Please try again.'], 500);
                return;
            } else {
                $this->session->flash('registration_error', 'An unexpected error occurred during registration. Please try again.');
                $this->session->flash('input_data', $input_data); 
                Redirect::to('/register');
                return;
            }
        }
    }
    public function checkEmail(): void
    {
        $email = $this->request->input('email');
        $exists = false;
        $error = null;
        if (empty($email)) {
            $error = 'Email parameter is missing.';
        } elseif (!SecurityHelper::validateEmail($email)) {
            $error = 'Invalid email format.';
        } else {
            try {
                $exists = $this->userModel->emailExists($email);
            } catch (\Exception $e) {
                $this->logger->error("Error checking email existence via AJAX.", ['email' => $email, 'error' => $e->getMessage()]);
                $error = 'Server error checking email.';
            }
        }
        if ($error) {
            $this->jsonResponse(['error' => $error], 400); 
        } else {
            $this->jsonResponse(['exists' => $exists]); 
        }
    }
    public function logout(): void
    {
        $userId = $this->session->get('user_id'); 
        $this->session->logoutUser(); 
        $this->session->flash('success', 'You have been logged out successfully.'); 
        $this->logger->info('User logged out.', ['user_id' => $userId ?? 'N/A']); 
        Redirect::to('/login'); 
    }
    protected function jsonResponse($data, int $statusCode = 200): void
    {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code($statusCode); 
        } else {
            $this->logger->error("Headers already sent, cannot set JSON response headers.", ['status_code' => $statusCode]);
        }
        echo json_encode($data);
    }
    private function regenerateCaptchaAndRedirect(string $redirectTo): void
    {
        $captchaText = $this->captchaHelper->generateText();
        $this->captchaHelper->storeText($captchaText);
        Redirect::to($redirectTo);
    }
}
