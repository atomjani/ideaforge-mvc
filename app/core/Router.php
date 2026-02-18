<?php

class Router
{
    private $routes = [];
    private $groupPrefix = '';
    private $groupMiddleware = [];
    
    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
    }
    
    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
    }
    
    public function put($path, $handler)
    {
        $this->addRoute('PUT', $path, $handler);
    }
    
    public function delete($path, $handler)
    {
        $this->addRoute('DELETE', $path, $handler);
    }
    
    public function any($path, $handler)
    {
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];
        foreach ($methods as $method) {
            $this->addRoute($method, $path, $handler);
        }
    }
    
    private function addRoute($method, $path, $handler)
    {
        $path = $this->groupPrefix . $path;
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $this->groupMiddleware
        ];
    }
    
    public function group($prefix, $callback, $middleware = [])
    {
        $previousPrefix = $this->groupPrefix;
        $previousMiddleware = $this->groupMiddleware;
        
        $this->groupPrefix = $prefix;
        $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        
        $callback($this);
        
        $this->groupPrefix = $previousPrefix;
        $this->groupMiddleware = $previousMiddleware;
    }
    
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $params = $this->matchPath($route['path'], $uri);
            
            if ($params !== false) {
                foreach ($route['middleware'] as $middleware) {
                    if (!$this->runMiddleware($middleware)) {
                        return;
                    }
                }
                
                $this->runHandler($route['handler'], $params);
                return;
            }
        }
        
        $this->notFound();
    }
    
    private function matchPath($routePath, $uri)
    {
        $routePath = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $routePath = '#^' . $routePath . '$#';
        
        if (preg_match($routePath, $uri, $matches)) {
            return array_filter($matches, fn($key) => !is_numeric($key), ARRAY_FILTER_USE_KEY);
        }
        
        return false;
    }
    
    private function runMiddleware($middleware)
    {
        if (is_callable($middleware)) {
            return $middleware();
        }
        
        if (is_string($middleware) && class_exists($middleware)) {
            $instance = new $middleware();
            if (method_exists($instance, 'handle')) {
                return $instance->handle();
            }
        }
        
        return true;
    }
    
    private function runHandler($handler, $params = [])
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
            return;
        }
        
        if (is_string($handler) && strpos($handler, '@') !== false) {
            list($controllerName, $action) = explode('@', $handler);
            
            $controllerFile = "../app/controllers/{$controllerName}.php";
            
            if (!file_exists($controllerFile)) {
                throw new Exception("Controller not found: {$controllerName}");
            }
            
            require_once $controllerFile;
            
            $controller = new $controllerName();
            
            if (!method_exists($controller, $action)) {
                throw new Exception("Action not found: {$action}");
            }
            
            call_user_func_array([$controller, $action], $params);
            return;
        }
        
        throw new Exception("Invalid handler");
    }
    
    private function notFound()
    {
        http_response_code(404);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['error' => '404 - Oldal nem található'], JSON_UNESCAPED_UNICODE);
    }
}
