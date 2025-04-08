<?php
//echo "API coming soon";
// index.php - Main entry point for the API
// Set headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");
header("Access-Control-Allow-Origin: *"); // Adjust this for production
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header('Content-Type: application/json');

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
function debug($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    exit();
}
// Include required files
require_once 'src/Database.php';
require_once 'src/Router.php';
require_once 'src/controllers/CategoryController.php';
require_once 'src/controllers/CourseController.php';
require_once 'src/services/CategoryService.php';
require_once 'src/services/CourseService.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Run migrations if not already run
// check if database has table 'courses'
$query = "SHOW TABLES LIKE 'courses'";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    $database->runMigrations();
    // Migrations have not been run, so run them
}

// Initialize services with database connection
$categoryService = new CategoryService($db);
$courseService = new CourseService($db);

// Initialize controllers with services
$categoryController = new CategoryController($categoryService);
$courseController = new CourseController($courseService);

// Initialize router
$router = new Router();
// echo '<pre>';
// print_r($router);
// echo '</pre>';

// Define routes
$router->get('/categories', [$categoryController, 'getAll']);
$router->get('/categories/{id}', [$categoryController, 'getById']);
$router->get('/courses', [$courseController, 'getAll']);
$router->get('/courses/{id}', [$courseController, 'getById']);

// Handle request
$router->handleRequest();

