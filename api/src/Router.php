<?php

/**
 * Router class for handling API routes
 */
class Router
{
    private $routes = [];

    /**
     * Register GET route
     *
     * @param string $path
     * @param callable $handler
     * @return void
     */
    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * Handle the current request
     *
     * @return void
     */
    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove trailing slash if present
        $path = rtrim($path, '/');
        
        // Set default path if empty
        if (empty($path)) {
            $path = '/';
        }
        $path = str_replace('/index.php', '', $path); // Remove '/api' prefix if present
        // Check if route exists
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $handler) {
                // Convert route parameters to regex pattern
                $pattern = $this->convertRouteToRegex($route);
                // Check if the current path matches the route pattern
                 
                    
                if (preg_match($pattern, $path, $matches)) {
                    array_shift($matches); // Remove the full match
                    
                    // Extract parameters from URL
                    $params = $this->extractParams($route, $matches);
                    
                    // Add query parameters if any
                    if ($_GET) {
                        $params = array_merge($params, $_GET);
                    }
                    
                    // Call the handler with parameters
                    call_user_func_array($handler, [$params]);
                    return;
                }
            }
        }
        
        // Route not found
        header("HTTP/1.1 404 Not Found");
        echo json_encode([
            "status" => "error",
            "message" => "Endpoint not found"
        ]);
    }

    /**
     * Convert route with parameters to regex pattern
     *
     * @param string $route
     * @return string
     */
    private function convertRouteToRegex(string $route): string
    {
        return '#^' . preg_replace('/{([a-zA-Z0-9_]+)}/', '([^/]+)', $route) . '$#';
    }

    /**
     * Extract parameters from route
     *
     * @param string $route
     * @param array $matches
     * @return array
     */
    private function extractParams(string $route, array $matches): array
    {
        $params = [];
        $paramNames = [];
        
        // Extract parameter names from route
        preg_match_all('/{([a-zA-Z0-9_]+)}/', $route, $paramNames);
        
        // Combine parameter names with values
        foreach ($paramNames[1] as $index => $name) {
            $params[$name] = $matches[$index];
        }
        
        return $params;
    }
}