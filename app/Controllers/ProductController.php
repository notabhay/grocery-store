<?php
namespace App\Controllers;
use App\Core\BaseController;
use App\Core\Database;
use App\Core\Registry;
use App\Core\Request;
use App\Core\Session;
use App\Models\Category;
use App\Models\Product;
use Psr\Log\LoggerInterface;
use PDO; 
class ProductController extends BaseController
{
    private $db;
    private $session;
    private $request;
    private $logger;
    private $categoryModel;
    private $productModel;
    public function __construct()
    {
        $this->db = Registry::get('database');
        $this->session = Registry::get('session');
        $this->request = Registry::get('request');
        $this->logger = Registry::get('logger');
        $pdoConnection = $this->db->getConnection(); 
        if ($pdoConnection) {
            $this->categoryModel = new Category($pdoConnection);
            $this->productModel = new Product($pdoConnection);
        } else {
            $this->logger->critical("Database connection not available for ProductController.");
            throw new \RuntimeException("Database connection not available for ProductController.");
        }
    }
    public function showCategories(): void
    {
        $logged_in = $this->session->isAuthenticated(); 
        $categories = [];
        $initialProducts = [];
        $activeFilterForView = null; 
        try {
            $categories = $this->categoryModel->getAllTopLevel();
            $categoryFilter = $this->request->get('filter'); 
            if ($categoryFilter) {
                $this->logger->info("Category filter applied", ['filter' => $categoryFilter]);
                $activeFilterForView = $categoryFilter; 
                $categoryId = $this->getCategoryIdByName($categoryFilter);
                if ($categoryId) {
                    $initialProducts = $this->productModel->findByCategory($categoryId);
                } else {
                    $this->logger->warning("Category not found for filter, showing all products.", ['filter' => $categoryFilter]);
                    $initialProducts = $this->productModel->getAll();
                    $activeFilterForView = null; 
                }
            } else {
                $initialProducts = $this->productModel->getAll();
            }
        } catch (\Exception $e) {
            $this->logger->error("Error fetching categories or products for display.", ['exception' => $e]);
            $this->session->flash('error', 'Could not load product categories or products. Please try again later.');
            $categories = $categories ?: [];
            $initialProducts = $initialProducts ?: [];
        }
        $this->view('pages/categories', [
            'categories' => $categories,
            'products' => $initialProducts, 
            'activeFilter' => $activeFilterForView, 
            'page_title' => 'Browse Products',
            'meta_description' => 'Browse our wide selection of fresh groceries by category.',
            'meta_keywords' => 'products, categories, grocery, online shopping',
            'additional_css_files' => ['assets/css/categories.css'], 
            'logged_in' => $logged_in 
        ]);
    }
    private function getCategoryIdByName($categoryName): ?int
    {
        try {
            if (strpos($categoryName, '&') !== false) {
                $likeName = str_replace('&', '%', $categoryName); 
                $debugStmt = $this->db->getConnection()->prepare("SELECT category_name FROM categories WHERE category_name LIKE :likeName LIMIT 5");
                $debugStmt->bindParam(':likeName', $likeName, PDO::PARAM_STR);
                $debugStmt->execute();
                $potentialMatches = $debugStmt->fetchAll(PDO::FETCH_COLUMN);
                if ($potentialMatches) {
                    $this->logger->info("Potential category name matches in DB (debug)", ['search_term' => $categoryName, 'like_pattern' => $likeName, 'matches' => $potentialMatches]);
                } else {
                    $this->logger->info("No similar category names found in DB (debug)", ['search_term' => $categoryName, 'like_pattern' => $likeName]);
                }
            }
            $stmt = $this->db->getConnection()->prepare("SELECT category_id FROM categories WHERE category_name = :name");
            $stmt->bindParam(':name', $categoryName, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int) $result['category_id'] : null;
        } catch (\Exception $e) {
            $this->logger->error("Error finding category by name", ['name' => $categoryName, 'error' => $e->getMessage()]);
            return null; 
        }
    }
    public function getSubcategoriesAjax(): void
    {
        $categoryId = $this->request->get('categoryId');
        $this->logger->info('AJAX: getSubcategoriesAjax called (fetches products).', ['categoryId' => $categoryId]);
        if (!filter_var($categoryId, FILTER_VALIDATE_INT)) {
            $this->jsonResponse(['error' => 'Invalid Category ID provided.'], 400); 
            return;
        }
        $categoryId = (int) $categoryId; 
        try {
            $this->logger->info('AJAX: Calling productModel->findByCategory.', ['categoryId' => $categoryId]);
            $products = $this->productModel->findByCategory($categoryId);
            $this->logger->info('AJAX: Received products from model.', ['products_count' => count($products)]); 
            $this->jsonResponse(['products' => $products]);
        } catch (\Exception $e) {
            $this->logger->error("AJAX: Error fetching products for category.", ['categoryId' => $categoryId, 'exception' => $e]);
            $this->jsonResponse(['error' => 'Could not load products for this category.'], 500); 
        }
    }
    public function ajaxGetSubcategories(): void
    {
        $parentId = $this->request->get('parentId');
        $this->logger->info('AJAX: ajaxGetSubcategories called.', ['parentId' => $parentId]);
        if (!filter_var($parentId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            $this->logger->warning('AJAX: Invalid parentId received.', ['parentId' => $parentId]);
            $this->jsonResponse(['error' => 'Invalid Parent Category ID provided.'], 400); 
            return;
        }
        $parentId = (int) $parentId; 
        try {
            $this->logger->info('AJAX: Calling categoryModel->getSubcategoriesByParentId.', ['parentId' => $parentId]);
            $subcategories = $this->categoryModel->getSubcategoriesByParentId($parentId);
            $this->logger->info('AJAX: Received subcategories from model.', ['subcategories_count' => count($subcategories)]);
            $this->jsonResponse(['subcategories' => $subcategories]);
        } catch (\Exception $e) {
            $this->logger->error("AJAX: Error fetching subcategories.", ['parentId' => $parentId, 'exception' => $e]);
            $this->jsonResponse(['subcategories' => []]); 
        }
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
    public function index(): void
    {
        $this->showCategories();
    }
}
