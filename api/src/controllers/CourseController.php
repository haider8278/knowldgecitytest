<?php

/**
 * Controller for course-related endpoints
 */
class CourseController
{
    private $courseService;

    /**
     * Constructor
     *
     * @param CourseService $courseService
     */
    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * Get all courses
     *
     * @param array $params
     * @return void
     */
    public function getAll(array $params = []): void
    {
        try {
            $categoryId = $params['category_id'] ?? null;
            $courses = $this->courseService->getAllCourses($categoryId);
            echo json_encode($courses);
        } catch (Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }

    /**
     * Get course by ID
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
                    "message" => "Course ID is required"
                ]);
                return;
            }
            
            $course = $this->courseService->getCourseById($id);
            
            if (!$course) {
                header("HTTP/1.1 404 Not Found");
                echo json_encode([
                    "status" => "error",
                    "message" => "Course not found"
                ]);
                return;
            }
            
            echo json_encode($course);
        } catch (Exception $e) {
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }
}