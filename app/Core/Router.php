<?php

namespace App\Core;

use Exception;
use ReflectionClass;
use ReflectionParameter;
use App\Helpers\CaptchaHelper;
use App\Core\Session;
use App\Core\Redirect;

class Router
{
    protected $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
    ];
    protected $params = [];
    protected $matchedController = null;
    protected $matchedPattern = null;
    protected static $routePrefix = '';
    public static function load(string $file): self
    {
        $router = new static;
        require $file;
        return $router;
    }
    public function get(string $uri, array $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }
    public function post(string $uri, array $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }
    public function put(string $uri, array $action): void
    {
        $this->addRoute('PUT', $uri, $action);
    }
    public static function group(string $prefix, callable $callback): void
    {
        $previousPrefix = self::$routePrefix ?? '';
        self::$routePrefix = $previousPrefix . '/' . trim($prefix, '/');
        $callback();
        self::$routePrefix = $previousPrefix;
    }
    protected function addRoute(string $method, string $uri, array $action): void
    {
        $fullUri = trim(self::$routePrefix . '/' . trim($uri, '/'), '/');
        if ($fullUri === '')
            $fullUri = '/';
        if (count($action) !== 2 || !is_string($action[0]) || !is_string($action[1])) {
            throw new Exception("Invalid action format for route [{$method}] {$fullUri}. Expected [Controller::class, 'method'].");
        }
        $controllerClass = $action[0];
        $methodName = $action[1];
        if ($fullUri === '/') {
            $pattern = '#^/$#i';
        } else {
            $pattern = '#^' . preg_replace('/\{([a-z_]+)\}/', '(?P<\1>[^/]+)', $fullUri) . '$#i';
        }
        $this->routes[$method][$pattern] = ['controller' => $controllerClass, 'action' => $methodName];
    }
    protected function match(string $uri, string $requestType): bool
    {
        if (!isset($this->routes[$requestType])) {
            return false;
        }
        $uri = trim($uri, '/');
        if ($uri === '')
            $uri = '/';
        foreach ($this->routes[$requestType] as $pattern => $routeInfo) {
            if (preg_match($pattern, $uri, $matches)) {
                $this->params = [];
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $this->params[$key] = is_numeric($match) ? (int) $match : $match;
                    }
                }
                $this->matchedController = $routeInfo;
                $this->matchedPattern = $pattern;
                return true;
            }
        }
        return false;
    }
    public function direct(string $uri, string $requestType)
    {
        $requestUriPath = parse_url($uri, PHP_URL_PATH) ?: '/';
        $baseUrlPath = defined('BASE_URL') ? parse_url(BASE_URL, PHP_URL_PATH) : '';
        $requestPathClean = trim($requestUriPath, '/');
        $basePathClean = trim($baseUrlPath, '/');
        $appPath = '/';
        if (!empty($basePathClean) && strpos($requestPathClean, $basePathClean) === 0) {
            $appPathSegment = substr($requestPathClean, strlen($basePathClean));
            $appPath = '/' . ltrim($appPathSegment, '/');
        } elseif (empty($basePathClean) && !empty($requestPathClean)) {
            $appPath = '/' . $requestPathClean;
        }
        if (empty(trim($appPath, '/'))) {
            $appPath = '/';
        }
        $uriPathClean = trim($appPath, '/');
        if ($uriPathClean === '') {
            $uriPathClean = '/';
        }
        $pathForMatching = ltrim($appPath, '/');
        if ($this->match($pathForMatching, $requestType) && $this->matchedController) {
            $protectedRoutePatterns = [
                '#^orders$#i',
                '#^order/process$#i',
                '#^order/details/(?P<id>[^/]+)$#i',
                '#^order/confirmation/(?P<id>[^/]+)$#i',
                '#^order/cancel/(?P<id>[^/]+)$#i',
                '#^api/orders$#i',
                '#^api/orders/(?P<id>[^/]+)$#i',
            ];
            $isProtectedRoute = false;
            if ($this->matchedPattern && in_array($this->matchedPattern, $protectedRoutePatterns, true)) {
                $isProtectedRoute = true;
            }
            if (!$isProtectedRoute) {
                foreach ($protectedRoutePatterns as $pattern) {
                    if (preg_match($pattern, $uriPathClean)) {
                        $isProtectedRoute = true;
                        break;
                    }
                }
            }
            if ($isProtectedRoute && !Registry::get('session')->isAuthenticated()) {
                if (strpos($uriPathClean, 'api/') === 0) {
                    header('Content-Type: application/json');
                    http_response_code(401);
                    echo json_encode(['error' => 'Authentication required.']);
                    exit();
                } else {
                    Registry::get('session')->flash('error', 'Please log in to access that page.');
                    Redirect::to('/login');
                    exit();
                }
            }
            $controllerClass = $this->matchedController['controller'];
            $action = $this->matchedController['action'];
            return $this->callAction($controllerClass, $action, $this->params);
        }
        $logger = Registry::has('logger') ? Registry::get('logger') : null;
        $definedRoutes = isset($this->routes[$requestType]) ? array_keys($this->routes[$requestType]) : [];
        $readableRoutes = [];
        if (isset($this->routes[$requestType])) {
            foreach ($this->routes[$requestType] as $pattern => $routeInfo) {
                $readableRoutes[] = [
                    'pattern' => $pattern,
                    'controller' => $routeInfo['controller'],
                    'action' => $routeInfo['action']
                ];
            }
        }
        $routesInfo = [
            'requested_uri' => $requestUriPath,
            'application_path' => $appPath,
            'request_method' => $requestType,
            'defined_routes' => $definedRoutes,
            'readable_routes' => $readableRoutes,
            'base_url' => defined('BASE_URL') ? BASE_URL : '/',
            'base_path' => $basePathClean,
            'uri_path_clean' => $uriPathClean
        ];
        if ($logger) {
            $logger->error("404 Error: No route defined for URI", $routesInfo);
        } else {
            error_log("404 Error: No route defined for URI: " . $requestUriPath . " [" . $requestType . "]" .
                " | App Path: " . $appPath .
                " | Defined routes: " . json_encode($definedRoutes));
        }
        throw new Exception("No route defined for this URI: {$requestUriPath} [{$requestType}]", 404);
    }
    protected function callAction(string $controllerClass, string $action, array $params = [])
    {
        if (!class_exists($controllerClass)) {
            throw new Exception("Controller class {$controllerClass} not found.");
        }
        $reflection = new ReflectionClass($controllerClass);
        $constructor = $reflection->getConstructor();
        $dependencies = [];
        if ($constructor) {
            $constructorParams = $constructor->getParameters();
            foreach ($constructorParams as $param) {
                $paramType = $param->getType();
                $resolved = false;
                if ($paramType && !$paramType->isBuiltin() && $paramType instanceof \ReflectionNamedType) {
                    $dependencyClassName = $paramType->getName();
                    if ($dependencyClassName === Database::class && Registry::has('database')) {
                        $dependencies[] = Registry::get('database');
                        $resolved = true;
                    } elseif ($dependencyClassName === Session::class && Registry::has('session')) {
                        $dependencies[] = Registry::get('session');
                        $resolved = true;
                    } elseif ($dependencyClassName === CaptchaHelper::class && Registry::has('captchaHelper')) {
                        $dependencies[] = Registry::get('captchaHelper');
                        $resolved = true;
                    }
                    if (!$resolved && Registry::has($dependencyClassName)) {
                        $dependencies[] = Registry::get($dependencyClassName);
                        $resolved = true;
                    } elseif (!$resolved && Registry::has(strtolower($param->getName()))) {
                        $dependencies[] = Registry::get(strtolower($param->getName()));
                        $resolved = true;
                    } elseif (!$resolved) {
                        if ($param->isOptional()) {
                            $dependencies[] = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
                            $resolved = true;
                        } else {
                            throw new Exception("Cannot resolve dependency '{$dependencyClassName}' for {$controllerClass} constructor.");
                        }
                    }
                } else {
                    if ($param->isDefaultValueAvailable()) {
                        $dependencies[] = $param->getDefaultValue();
                    } elseif ($param->isOptional()) {
                        $dependencies[] = null;
                    } else {
                        $dependencies[] = null;
                    }
                }
            }
            $controllerInstance = $reflection->newInstanceArgs($dependencies);
        } else {
            $controllerInstance = new $controllerClass;
        }
        if (!method_exists($controllerInstance, $action)) {
            throw new Exception(
                "{$controllerClass} does not respond to the {$action} action."
            );
        }
        return $controllerInstance->$action($params);
    }
}