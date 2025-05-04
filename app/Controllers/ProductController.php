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
use PDO; // Added for type hinting in getCategoryIdByName

/**
 * Class ProductController
 * Handles displaying product categories and products, including filtering by category.
 * Provides AJAX endpoints for dynamically loading subcategories and products based on category selection.
 *
 * @package App\Controllers
 */
class ProductController extends BaseController
{
    /**
     * @var Database Database connection wrapper instance.
     */
    private $db;

    /**
     * @var Session Session management instance.
     */
    private $session;

    /**
     * @var Request HTTP request handling instance.
     */
    private $request;

    /**
     * @var LoggerInterface Logger instance for recording events and errors.
     */
    private $logger;

    /**
     * @var Category Category model instance for database interactions.
     */
    private $categoryModel;

    /**
     * @var Product Product model instance for database interactions.
     */
    private $productModel;

    /**
     * ProductController constructor.
     * Initializes dependencies (Database, Session, Request, Logger) from the Registry.
     * Initializes Category and Product models.
     * Throws a RuntimeException if the database connection is unavailable.
     */
    public function __construct()
    {
        error_log("[DEBUG] ProductController::__construct - Entered constructor."); // ADDED DEBUG LOG
        try { // Wrap constructor
            $this->db = Registry::get('database');
            error_log("[DEBUG] ProductController::__construct - Got database wrapper."); // ADDED DEBUG LOG
            $this->session = Registry::get('session');
            error_log("[DEBUG] ProductController::__construct - Got session."); // ADDED DEBUG LOG
            $this->request = Registry::get('request');
            error_log("[DEBUG] ProductController::__construct - Got request."); // ADDED DEBUG LOG
            $this->logger = Registry::get('logger');
            error_log("[DEBUG] ProductController::__construct - Got logger."); // ADDED DEBUG LOG

            if (!$this->db || !$this->db instanceof \App\Core\Database) {
                error_log("[FATAL] ProductController::__construct - Invalid Database object from Registry.");
                throw new \RuntimeException("Invalid Database object retrieved from Registry.");
            }

            $pdoConnection = $this->db->getConnection(); // Get the actual PDO connection
            error_log("[DEBUG] ProductController::__construct - Got PDO connection: " . ($pdoConnection ? 'OK' : 'FAILED')); // ADDED DEBUG LOG

            // Ensure PDO connection is valid before instantiating models
            if ($pdoConnection) {
                $this->categoryModel = new Category($pdoConnection);
                error_log("[DEBUG] ProductController::__construct - Instantiated Category model."); // ADDED DEBUG LOG
                $this->productModel = new Product($pdoConnection);
                error_log("[DEBUG] ProductController::__construct - Instantiated Product model."); // ADDED DEBUG LOG
            } else {
                // Log critical error and stop if DB connection failed
                error_log("[FATAL] ProductController::__construct - PDO connection is invalid."); // Use error_log
                // $this->logger->critical("Database connection not available for ProductController."); // Logger might not be ready if DB failed early
                throw new \RuntimeException("Database connection not available for ProductController.");
            }
        } catch (\Throwable $e) {
            error_log("[FATAL] ProductController::__construct - Exception during construction: " . $e->getMessage() . "\n" . $e->getTraceAsString()); // ADDED ERROR LOG
            throw $e; // Re-throw
        }
        error_log("[DEBUG] ProductController::__construct - Exiting constructor successfully."); // ADDED DEBUG LOG
    }

    /**
     * Displays the main categories page.
     * Fetches top-level categories and initial products (either all or filtered by a category name from query param).
     * Handles potential errors during data fetching.
     *
     * @return void Renders the 'pages/categories' view.
     */
    public function showCategories(): void
    {
        error_log("[DEBUG] ProductController::showCategories - Entered method."); // ADDED DEBUG LOG
        $logged_in = $this->session->isAuthenticated(); // Check login status
        error_log("[DEBUG] ProductController::showCategories - Login status: " . ($logged_in ? 'true' : 'false')); // ADDED DEBUG LOG
        $categories = [];
        $initialProducts = [];
        $activeFilterForView = null; // To highlight the active filter in the view

        try {
            error_log("[DEBUG] ProductController::showCategories - Entering try block."); // ADDED DEBUG LOG
            // Fetch all top-level categories for display
            error_log("[DEBUG] ProductController::showCategories - Calling categoryModel->getAllTopLevel()."); // ADDED DEBUG LOG
            $categories = $this->categoryModel->getAllTopLevel();
            error_log("[DEBUG] ProductController::showCategories - Got categories: " . count($categories) . " items."); // ADDED DEBUG LOG

            // Check if a category filter is applied via query parameter (e.g., /categories?filter=Fruits+%26+Veggies)
            $categoryFilter = $this->request->get('filter'); // Get 'filter' query parameter
            error_log("[DEBUG] ProductController::showCategories - Category filter from request: " . ($categoryFilter ?: 'None')); // ADDED DEBUG LOG

            if ($categoryFilter) {
                // $this->logger->info("Category filter applied", ['filter' => $categoryFilter]); // Use error_log for now
                error_log("[DEBUG] ProductController::showCategories - Filter applied: " . $categoryFilter); // ADDED DEBUG LOG
                $activeFilterForView = $categoryFilter; // Pass the filter name to the view

                // Find the category ID corresponding to the filter name
                error_log("[DEBUG] ProductController::showCategories - Calling getCategoryIdByName for filter: " . $categoryFilter); // ADDED DEBUG LOG
                $categoryId = $this->getCategoryIdByName($categoryFilter);
                error_log("[DEBUG] ProductController::showCategories - Category ID found: " . ($categoryId ?: 'None')); // ADDED DEBUG LOG

                if ($categoryId) {
                    // Fetch products belonging to the specified category
                    error_log("[DEBUG] ProductController::showCategories - Calling productModel->findByCategory for ID: " . $categoryId); // ADDED DEBUG LOG
                    $initialProducts = $this->productModel->findByCategory($categoryId);
                    error_log("[DEBUG] ProductController::showCategories - Got filtered products: " . count($initialProducts) . " items."); // ADDED DEBUG LOG
                } else {
                    // If category name doesn't match, log a warning and show all products as fallback
                    // $this->logger->warning("Category not found for filter, showing all products.", ['filter' => $categoryFilter]); // Use error_log
                    error_log("[WARNING] ProductController::showCategories - Category not found for filter '" . $categoryFilter . "', showing all products."); // ADDED DEBUG LOG
                    error_log("[DEBUG] ProductController::showCategories - Calling productModel->getAll() as fallback."); // ADDED DEBUG LOG
                    $initialProducts = $this->productModel->getAll();
                    error_log("[DEBUG] ProductController::showCategories - Got all products: " . count($initialProducts) . " items."); // ADDED DEBUG LOG
                    $activeFilterForView = null; // Reset active filter as it was invalid
                }
            } else {
                // If no filter is applied, fetch all products initially
                error_log("[DEBUG] ProductController::showCategories - No filter, calling productModel->getAll()."); // ADDED DEBUG LOG
                $initialProducts = $this->productModel->getAll();
                error_log("[DEBUG] ProductController::showCategories - Got all products: " . count($initialProducts) . " items."); // ADDED DEBUG LOG
            }
        } catch (\Throwable $e) { // Catch Throwable for broader error catching
            // Log error if fetching categories or products fails
            // $this->logger->error("Error fetching categories or products for display.", ['exception' => $e]); // Use error_log
            error_log("[ERROR] ProductController::showCategories - Exception in try block: " . $e->getMessage() . "\n" . $e->getTraceAsString()); // ADDED ERROR LOG
            $this->session->flash('error', 'Could not load product categories or products. Please try again later.');
            // Ensure variables are arrays even on error
            $categories = $categories ?: [];
            $initialProducts = $initialProducts ?: [];
        }
        error_log("[DEBUG] ProductController::showCategories - Preparing data for view."); // ADDED DEBUG LOG

        // Prepare data for the view
        try {
            $this->view('pages/categories', [
                'categories' => $categories,
                'products' => $initialProducts, // Products to display initially
                'activeFilter' => $activeFilterForView, // Name of the active filter, if any
                'page_title' => 'Browse Products',
                'meta_description' => 'Browse our wide selection of fresh groceries by category.',
                'meta_keywords' => 'products, categories, grocery, online shopping',
                'additional_css_files' => ['assets/css/categories.css'], // Specific CSS
                'logged_in' => $logged_in // Pass login status
            ]);
            error_log("[DEBUG] ProductController::showCategories - View rendered successfully."); // ADDED DEBUG LOG
        } catch (\Throwable $e) {
             error_log("[ERROR] ProductController::showCategories - Exception during view rendering: " . $e->getMessage() . "\n" . $e->getTraceAsString()); // ADDED ERROR LOG
             // Optionally re-throw or handle differently
             throw $e;
        }
    }

    /**
     * Finds a category ID by its exact name.
     * Includes debug logging for names containing '&' to help diagnose potential encoding issues.
     *
     * @param string $categoryName The exact name of the category to find.
     * @return int|null The category ID if found, otherwise null.
     */
    private function getCategoryIdByName($categoryName): ?int
    {
        error_log("[DEBUG] ProductController::getCategoryIdByName - Entered. Searching for: " . $categoryName); // ADDED LOG
        try {
            // --- Debugging for names with ampersands ---
            // This helps check if the name received matches what's in the DB,
            // especially if URL encoding/decoding issues occur with '&'.
            if (strpos($categoryName, '&') !== false) {
                $likeName = str_replace('&', '%', $categoryName); // Create a LIKE pattern
                error_log("[DEBUG] ProductController::getCategoryIdByName - Ampersand detected. LIKE pattern: " . $likeName); // ADDED LOG
                $debugStmt = $this->db->getConnection()->prepare("SELECT category_name FROM categories WHERE category_name LIKE :likeName LIMIT 5");
                $debugStmt->bindParam(':likeName', $likeName, PDO::PARAM_STR);
                $debugStmt->execute();
                $potentialMatches = $debugStmt->fetchAll(PDO::FETCH_COLUMN);
                error_log("[DEBUG] ProductController::getCategoryIdByName - Potential matches from DB: " . implode(', ', $potentialMatches ?: ['None'])); // ADDED LOG
                // $this->logger->info("Potential category name matches in DB (debug)", ['search_term' => $categoryName, 'like_pattern' => $likeName, 'matches' => $potentialMatches]);
            }
            // --- End Debugging ---

            // Prepare and execute query to find category ID by exact name match
            error_log("[DEBUG] ProductController::getCategoryIdByName - Preparing exact match query."); // ADDED LOG
            $stmt = $this->db->getConnection()->prepare("SELECT category_id FROM categories WHERE category_name = :name");
            $stmt->bindParam(':name', $categoryName, PDO::PARAM_STR);
            error_log("[DEBUG] ProductController::getCategoryIdByName - Executing query with name: " . $categoryName); // ADDED LOG
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("[DEBUG] ProductController::getCategoryIdByName - Query executed. Result: " . ($result ? print_r($result, true) : 'false')); // ADDED LOG

            // Return the category ID as an integer if found, otherwise null
            $categoryId = $result ? (int) $result['category_id'] : null;
            error_log("[DEBUG] ProductController::getCategoryIdByName - Returning category ID: " . ($categoryId ?? 'null')); // ADDED LOG
            return $categoryId;
        } catch (\Exception $e) {
            // Log any errors during the database query
            error_log("[ERROR] ProductController::getCategoryIdByName - Exception: " . $e->getMessage()); // ADDED LOG
            // $this->logger->error("Error finding category by name", ['name' => $categoryName, 'error' => $e->getMessage()]);
            return null; // Return null on error
        }
    }

    /**
     * AJAX endpoint to fetch products belonging to a specific category ID.
     * Used by JavaScript to update the product list when a category is selected.
     * Expects 'categoryId' as a GET parameter.
     * Returns JSON response with products or an error.
     *
     * @deprecated This method seems redundant or possibly incorrectly named given ajaxGetSubcategories.
     *             It fetches *products* by category ID, not subcategories.
     *             Consider renaming or clarifying its purpose if kept.
     * @return void Outputs JSON response.
     */
    public function getSubcategoriesAjax(): void
    {
        error_log("[DEBUG] ProductController::getSubcategoriesAjax - Entered method."); // ADDED LOG
        // Get category ID from GET request
        $categoryId = $this->request->get('categoryId');
        error_log("[DEBUG] ProductController::getSubcategoriesAjax - Received categoryId: " . ($categoryId ?? 'null')); // ADDED LOG
        // $this->logger->info('AJAX: getSubcategoriesAjax called (fetches products).', ['categoryId' => $categoryId]);

        // Validate the category ID
        if (!filter_var($categoryId, FILTER_VALIDATE_INT)) {
            error_log("[WARNING] ProductController::getSubcategoriesAjax - Invalid categoryId: " . $categoryId); // ADDED LOG
            $this->jsonResponse(['error' => 'Invalid Category ID provided.'], 400); // Bad Request
            return;
        }
        $categoryId = (int) $categoryId; // Cast to integer
        error_log("[DEBUG] ProductController::getSubcategoriesAjax - Validated categoryId: " . $categoryId); // ADDED LOG

        try {
            // Fetch products using the Product model
            error_log("[DEBUG] ProductController::getSubcategoriesAjax - Calling productModel->findByCategory for ID: " . $categoryId); // ADDED LOG
            // $this->logger->info('AJAX: Calling productModel->findByCategory.', ['categoryId' => $categoryId]);
            $products = $this->productModel->findByCategory($categoryId);
            error_log("[DEBUG] ProductController::getSubcategoriesAjax - productModel->findByCategory returned " . count($products) . " products."); // ADDED LOG
            // $this->logger->info('AJAX: Received products from model.', ['products_count' => count($products)]); // Avoid logging full data unless debugging

            // Send successful JSON response with the products
            error_log("[DEBUG] ProductController::getSubcategoriesAjax - Sending JSON response."); // ADDED LOG
            $this->jsonResponse(['products' => $products]);
            error_log("[DEBUG] ProductController::getSubcategoriesAjax - JSON response sent."); // ADDED LOG
        } catch (\Exception $e) {
            // Log error and send error response
            error_log("[ERROR] ProductController::getSubcategoriesAjax - Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString()); // ADDED LOG
            // $this->logger->error("AJAX: Error fetching products for category.", ['categoryId' => $categoryId, 'exception' => $e]);
            $this->jsonResponse(['error' => 'Could not load products for this category.'], 500); // Internal Server Error
        }
    }

    /**
     * AJAX endpoint to fetch subcategories based on a parent category ID.
     * Used by JavaScript to dynamically populate subcategory lists.
     * Expects 'parentId' as a GET parameter.
     * Returns JSON response with subcategories or an error.
     *
     * @return void Outputs JSON response.
     */
    public function ajaxGetSubcategories(): void
    {
        error_log("[DEBUG] ProductController::ajaxGetSubcategories - Entered method."); // ADDED LOG
        // Get parent category ID from GET request
        $parentId = $this->request->get('parentId');
        error_log("[DEBUG] ProductController::ajaxGetSubcategories - Received parentId: " . ($parentId ?? 'null')); // ADDED LOG
        // $this->logger->info('AJAX: ajaxGetSubcategories called.', ['parentId' => $parentId]);

        // Validate the parent ID (must be a positive integer)
        if (!filter_var($parentId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            error_log("[WARNING] ProductController::ajaxGetSubcategories - Invalid parentId: " . $parentId); // ADDED LOG
            // $this->logger->warning('AJAX: Invalid parentId received.', ['parentId' => $parentId]);
            $this->jsonResponse(['error' => 'Invalid Parent Category ID provided.'], 400); // Bad Request
            return;
        }
        $parentId = (int) $parentId; // Cast to integer
        error_log("[DEBUG] ProductController::ajaxGetSubcategories - Validated parentId: " . $parentId); // ADDED LOG

        try {
            // Fetch subcategories using the Category model
            error_log("[DEBUG] ProductController::ajaxGetSubcategories - Calling categoryModel->getSubcategoriesByParentId for parent ID: " . $parentId); // ADDED LOG
            // $this->logger->info('AJAX: Calling categoryModel->getSubcategoriesByParentId.', ['parentId' => $parentId]);
            $subcategories = $this->categoryModel->getSubcategoriesByParentId($parentId);
            error_log("[DEBUG] ProductController::ajaxGetSubcategories - categoryModel->getSubcategoriesByParentId returned " . count($subcategories) . " subcategories."); // ADDED LOG
            // $this->logger->info('AJAX: Received subcategories from model.', ['subcategories_count' => count($subcategories)]);

            // Send successful JSON response with the subcategories
            error_log("[DEBUG] ProductController::ajaxGetSubcategories - Sending JSON response."); // ADDED LOG
            $this->jsonResponse(['subcategories' => $subcategories]);
            error_log("[DEBUG] ProductController::ajaxGetSubcategories - JSON response sent."); // ADDED LOG
        } catch (\Exception $e) {
            // Log error and send error response (return empty array as per original logic)
            error_log("[ERROR] ProductController::ajaxGetSubcategories - Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString()); // ADDED LOG
            // $this->logger->error("AJAX: Error fetching subcategories.", ['parentId' => $parentId, 'exception' => $e]);
            // Consider sending a 500 error instead of empty array for clearer error handling client-side
            $this->jsonResponse(['subcategories' => []]); // Original logic returned empty array on error
        }
    }

    /**
     * Helper method to send a JSON response.
     * Sets the Content-Type header, HTTP status code, encodes data to JSON, and echoes it.
     * Logs an error if headers have already been sent.
     *
     * @param mixed $data The data to encode as JSON.
     * @param int $statusCode The HTTP status code for the response (default: 200).
     * @return void
     */
    protected function jsonResponse($data, int $statusCode = 200): void
    {
        error_log("[DEBUG] ProductController::jsonResponse - Preparing JSON response. Status: " . $statusCode); // ADDED LOG
        // Check if headers have already been sent to prevent warnings/errors
        if (!headers_sent()) {
            header('Content-Type: application/json');
            http_response_code($statusCode); // Set the HTTP status code
            error_log("[DEBUG] ProductController::jsonResponse - Headers set."); // ADDED LOG
        } else {
            // Log an error if headers are already sent, as we can't set them again
            error_log("[ERROR] ProductController::jsonResponse - Headers already sent, cannot set JSON response headers."); // ADDED LOG
            // $this->logger->error("Headers already sent, cannot set JSON response headers.", ['status_code' => $statusCode]);
        }
        // Encode the data to JSON and output it
        $jsonData = json_encode($data);
        error_log("[DEBUG] ProductController::jsonResponse - JSON encoded. Outputting: " . substr($jsonData, 0, 200) . (strlen($jsonData) > 200 ? '...' : '')); // ADDED LOG (Truncated)
        echo $jsonData;
        // Note: exit() is not called here, allowing potential further script execution if needed,
        // though typically AJAX handlers terminate after sending the response.
    }

    /**
     * Default action for the ProductController (e.g., when accessing /products).
     * Simply calls the showCategories method to display the main product browsing page.
     *
     * @return void
     */
    public function index(): void
    {
        // The main entry point for this controller shows the categories page
        $this->showCategories();
    }
}