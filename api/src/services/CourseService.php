<?php

/**
 * Service for course-related operations
 */
class CourseService
{
    private $conn;

    /**
     * Constructor
     *
     * @param PDO $db
     */
    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    /**
     * Get all courses, optionally filtered by category
     *
     * @param string|null $categoryId
     * @return array
     */
    public function getAllCourses(?string $categoryId = null): array
    {
        $params = [];
        $whereClause = "";
        
        if ($categoryId) {
            // Get all subcategories if a category ID is provided
            $categoryService = new CategoryService($this->conn);
            $categoryIds = $this->getAllChildCategoryIds($categoryId);
            
            $placeholders = rtrim(str_repeat('?,', count($categoryIds)), ',');
            $whereClause = "WHERE c.category_id IN ($placeholders)";
            $params = $categoryIds;
        }
        // Get courses with main category name
        $query = "SELECT 
                    c.id, c.title as name, c.description, c.image_preview as preview , 
                    cat.name as main_category_name,
                    cat.id as category_id,
                    c.created_at, c.updated_at
                  FROM 
                    courses c
                  JOIN 
                    categories cat ON c.category_id = cat.id
                  $whereClause
                  ORDER BY c.title";
        
        //$query = "SELECT * FROM courses c ORDER BY c.title";
        $stmt = $this->conn->prepare($query);
        
        // Bind category IDs if filtering
        if ($categoryId) {
            foreach ($params as $index => $id) {
                $stmt->bindValue($index + 1, $id);
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get course by ID
     *
     * @param string $id
     * @return array|null
     */
    public function getCourseById(string $id): ?array
    {
        $query = "SELECT 
                    c.id, c.title as name, c.description, c.image_preview as preview , 
                    cat.name as main_category_name,
                    cat.id as category_id,
                    c.created_at, c.updated_at
                  FROM 
                    courses c
                  JOIN 
                    categories cat ON c.category_id = cat.id
                  WHERE 
                    c.id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $course = $stmt->fetch();
        return $course ?: null;
    }

    /**
     * Get all child category IDs for a given category
     *
     * @param string $categoryId
     * @return array
     */
    private function getAllChildCategoryIds(string $categoryId): array
    {
        $result = [$categoryId];
        
        $query = "SELECT id FROM categories WHERE parent_id = :parent_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':parent_id', $categoryId);
        $stmt->execute();
        
        while ($row = $stmt->fetch()) {
            $childIds = $this->getAllChildCategoryIds($row['id']);
            $result = array_merge($result, $childIds);
        }
        
        return $result;
    }
}