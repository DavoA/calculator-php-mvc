<?php
namespace App;

class Router {
    private $routes = [];
    private $params = [];

    public static function load(string $file): Router {
        $router = new self();
        if (!file_exists($file)) {
            throw new \Exception("Routes file not found: {$file}");
        }
        require_once $file;
        return $router;
    }

    public function addRoute(string $route, array $params = []): void {
        $route = preg_replace('/\//', '\\/', $route);
        $route = preg_replace('/\{([a-z0-9]+)\}/', '(?P<\1>[a-z0-9-]+)', $route);
        $route = preg_replace('/\{([a-z0-9]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        $route = '/^' . $route . '$/i';
        $this->routes[$route] = $params;
    }

    public function setParams(string $uri): Router {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $uri, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        if ($key === 'controller') {
                            $match = ucwords($match);
                        } elseif ($key === 'num1' || $key === 'num2') {
                            $match = (float) $match;
                        }
                        $params[$key] = $match;
                    }
                }
                $this->params = $params;
                break;
            }
        }
        return $this;
    }

    public function redirect(): void {
        if (!isset($this->params['controller']) || !isset($this->params['action'])) {
            throw new \Exception('Invalid route configuration: controller or action missing.');
        }

        $controller = $this->getNamespace() . $this->params['controller'] . 'Controller';
        $action = $this->params['action'];

        if (class_exists($controller)) {
            $controller = new $controller;
            unset($this->params['controller']);

            if (is_callable([$controller, $action])) {
                unset($this->params['action']);
                unset($this->params['namespace']);
            } else {
                throw new \Exception('Page not found: action does not exist.');
            }
        } else {
            header('Location: ' . URLROOT);
            exit;
        }

        call_user_func_array([$controller, $action], [$this->params]);
    }

    private function getNamespace(): string {
        $namespace = '\\Controllers\\';
        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }
        return $namespace;
    }

    private function capitalizeAction(string $action): string {
        $action = explode('-', $action);
        for ($i = 1; $i < count($action); $i++) {
            $action[$i] = ucwords($action[$i]);
        }
        return implode('', $action);
    }
}