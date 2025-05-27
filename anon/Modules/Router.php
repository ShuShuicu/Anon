<?php

/**
 * 路由处理
 */
if (!defined('ANON_ALLOWED_ACCESS')) exit;

class Anon_Router
{
    private static $routes = [];
    private static $errorHandlers = [];
    
    /**
     * 初始化路由系统
     */
    public static function init()
    {
        $routerConfig = Anon_Config::getRouterConfig();
        
        self::$routes = $routerConfig['routes'];
        self::$errorHandlers = $routerConfig['error_handlers'];
        
        try {
            $requestPath = self::getRequestPath();
            
            if (isset(self::$routes[$requestPath])) {
                self::dispatch($requestPath);
            } else {
                self::handleError(404);
            }
        } catch (Throwable $e) {
            error_log('Router error: ' . $e->getMessage());
            self::handleError(500);
        }
    }
    
    /**
     * 获取当前请求路径
     * @return string 清理后的路径
     */
    private static function getRequestPath(): string
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = trim(parse_url($requestUri, PHP_URL_PATH), '/');
        
        return strstr($path, '?', true) ?: $path;
    }
    
    /**
     * 执行路由处理函数
     * @param string $route 路由路径
     */
    private static function dispatch(string $route)
    {
        $handler = self::$routes[$route];
        
        if (is_callable($handler)) {
            try {
                $handler();
            } catch (Throwable $e) {
                error_log('Route handler error: ' . $e->getMessage());
                self::handleError(500);
            }
        } else {
            self::handleError(404);
        }
        
        exit;
    }
    
    /**
     * 处理错误
     * @param int $code HTTP状态码
     */
    private static function handleError(int $code)
    {
        http_response_code($code);
        
        if (isset(self::$errorHandlers[$code]) && is_callable(self::$errorHandlers[$code])) {
            self::$errorHandlers[$code]();
        } else {
            header("Content-Type: text/plain; charset=utf-8");
            echo "HTTP {$code}";
        }
        
        exit;
    }
}

// 初始化路由
Anon_Router::init();