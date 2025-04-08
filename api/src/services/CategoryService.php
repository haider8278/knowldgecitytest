<?php

/**
 * Service for category-related operations
 */
class CategoryService
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
     * Get all categories with course counts
     *
     * @return array
     */
    public function getAllCategories(): array
    {
        // Get all categories
        $categoriesQuery = "SELECT id, name, description, parent_id, created_at, updated_at 
                           FROM categories 
                           ORDER BY name";
        
        $stmt = $this->conn->prepare($categoriesQuery);
        $stmt->execute();
        $categories = $stmt->fetchAll();
        
        // Calculate course counts for each category (including subcategories)
        foreach ($categories as &$category) {
            $category['count_of_courses'] = $this->getCoursesCountForCategory($category['id']);
        }
        
        return $categories;
    }

    /**
     * Get category by ID with course count
     *
     * @param string $id
     * @return array|null
     */
    public function getCategoryById(string $id): ?array
    {
        $query = "SELECT id, name, description, parent_id, created_at, updated_at 
                 FROM categories 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $category = $stmt->fetch();
        
        if ($category) {
            $category['count_of_courses'] = $this->getCoursesCountForCategory($id);
        }
        
        return $category ?: null;
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

    /**
     * Get total count of courses for a category including all subcategories
     *
     * @param string $categoryId
     * @return int
     */
    private function getCoursesCountForCategory(string $categoryId): int
    {
        $categoryIds = $this->getAllChildCategoryIds($categoryId);
        
        // Convert array to comma-separated string for SQL IN clause
        $placeholders = rtrim(str_repeat('?,', count($categoryIds)), ',');
        
        $query = "SELECT COUNT(*) as count FROM courses WHERE category_id IN ($placeholders)";
        $stmt = $this->conn->prepare($query);
        
        // Bind values for each placeholder
        foreach ($categoryIds as $index => $id) {
            $stmt->bindValue($index + 1, $id);
        }
        
        $stmt->execute();
        $result = $stmt->fetch();
        
        return (int)$result['count'];
    }
}