<?php

/**
 * Controller for category-related endpoints
 */
class CategoryController
{
    private $categoryService;

    /**
     * Constructor
     *
     * @param CategoryService $categoryService
     */
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Get all categories
     *
     * @param array $params
     * @return void
     */
    public function getAll(array $params = []): void
    {
        try {
            $categories = $this->categoryService->getAllCategories();
            echo json_encode($categories);
        } catch (Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    /**
     * Get category by ID
     *
     * @param array $params
     * @return void
     */
    public function getById(array $params): void
    {
        try {
            $id = $params['id'] ?? null;
            
            if (!$id) {
                header("HTTP/1.1 400 Bad Request");
                echo json_encode([
                    "status" => "error",
                    "message" => "Category ID is required"
                ]);
                return;
            }
            
            $category = $this->categoryService->getCategoryById($id);
            
            if (!$category) {
                header("HTTP/1.1 404 Not Found");
                echo json_encode([
                    "status" => "error",
                    "message" => "Category not found"
                ]);
                return;
            }
            
            echo json_encode($category);
        } catch (Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }
}