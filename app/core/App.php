<?php

class App
{
    // --- Configuration (Backward Compatible) ---
    private $CONTROLLERS_DIR;
    private $_404_FILE;
    private $controller = "Home";
    private $method = "index";
    private $params = []; 
    
    // --- New Routing Features ---
    // Static property to hold all defined routes (GET, POST, etc.)
    private static $routes = [];

    public function __construct()
    {
        // Path calculation remains the same, relying on the correctly defined SERVER_ROOT
        // (Assuming SERVER_ROOT is defined in your config.php)
        $this->CONTROLLERS_DIR = SERVER_ROOT . 'app' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR;
        $this->_404_FILE = $this->CONTROLLERS_DIR . '_404.php';
    }

    // --- New: Static methods for defining routes ---

    /**
     * Defines a GET route.
     * @param string $uri The URI pattern (e.g., 'users/{id}')
     * @param string $controllerMethod The Controller@method string (e.g., 'UserController@show')
     */
    public static function get($uri, $controllerMethod)
    {
        self::$routes['GET'][$uri] = $controllerMethod;
    }

    /**
     * Defines a POST route.
     * @param string $uri The URI pattern
     * @param string $controllerMethod The Controller@method string
     */
    public static function post($uri, $controllerMethod)
    {
        self::$routes['POST'][$uri] = $controllerMethod;
    }

    // You can add App::put() and App::delete() similarly for a complete set.


    // --- Core Logic (Modified for Hybrid Routing) ---

    private function split_url()
    {
        $URL = $_GET["url"] ?? "home";
        $URL = explode("?", $URL)[0]; 
        $URL = trim($URL, "/"); 
        
        return $URL; // Returns the raw URI string here for route matching
    }
    
    public function load_controller()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $this->split_url();
        
        // 1. --- TRY ADVANCED ROUTING FIRST (New Feature) ---
        
        if (isset(self::$routes[$requestMethod])) {
            foreach (self::$routes[$requestMethod] as $routePattern => $controllerMethod) {
                
                // Convert route pattern (e.g., 'users/{id}') into a regex
                $regex = preg_replace('/\{([a-zA-Z0-9_-]+)\}/', '([a-zA-Z0-9_-]+)', $routePattern);
                
                // Add delimiters for regex matching
                if (preg_match("#^$regex$#", $uri, $matches)) {
                    
                    // Match found! Extract Controller and Method
                    list($controllerName, $methodName) = explode('@', $controllerMethod);
                    
                    // Set class and method
                    $this->controller = $controllerName;
                    $this->method = $methodName;
                    
                    // The parameters are the captured groups (excluding the full match at index 0)
                    $this->params = array_slice($matches, 1);
                    
                    // Proceed to loading and executing the controller
                    return $this->execute_controller();
                }
            }
        }

        // 2. --- FALLBACK TO BACKWARD COMPATIBLE LOGIC (Original Feature) ---
        
        // Split the URI into segments for the old logic
        $URL = explode("/", $uri);
        
        $controller_name = array_shift($URL);
        
        // Construct the ABSOLUTE FILE PATH
        $filename = $this->CONTROLLERS_DIR . ucfirst($controller_name) . ".php";
        
        if (file_exists($filename)) {
            require $filename; 
            $this->controller = ucfirst($controller_name);
        } else {
            // Load 404 (file system path)
            require $this->_404_FILE; 
            $this->controller = "_404";
            $URL = []; // Clear segments if we load the 404
        }

        // Determine Method and Parameters (Original Logic)
        if (!empty($URL[0])) {
            $this->method = $URL[0];
            array_shift($URL); 
        }
        
        if ($this->controller !== '_404') {
             $controller = new $this->controller;
             if (!method_exists($controller, $this->method)) {
                 $this->method = "index"; 
             }
        }
        
        $this->params = $URL;
        
        // Proceed to execute the controller
        return $this->execute_controller();
    }
    
    // --- Helper Method to Reduce Code Duplication ---
    
    private function execute_controller()
    {
        // 1. Load the controller class if not already loaded by the fallback logic
        $filename = $this->CONTROLLERS_DIR . ucfirst($this->controller) . ".php";
        if (!class_exists($this->controller) && file_exists($filename)) {
            require $filename;
        }

        // 2. Handle 404 if the controller or method still doesn't exist
        if (!class_exists($this->controller) || !method_exists($this->controller, $this->method)) {
            require $this->_404_FILE;
            $this->controller = "_404";
            $this->method = "index";
            // Re-instantiate if necessary (e.g., if we defaulted to 404)
        }
        
        $controller = new $this->controller;
        
        // 3. Call the method with collected parameters
        call_user_func_array([$controller, $this->method], $this->params);
    }
}