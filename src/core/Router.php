<?php
    class Router{
        private $routes=[];

        public function addRoute($method,$path,$action){
            $this->routes[]=[
                'method'=>strtoupper($method),
                'path'=>$path,
                'action'=>$action
            ];
        }

        public function dispatch($currentMethod,$currentPath){
            foreach ($this->routes as $route){
                if($route['method']===strtoupper($currentMethod) && $route['path']===$currentPath){
                    [$controllerName,$methodName]=explode('@',$route['action']);
                    $controllerFile=__DIR__.'/../controller'.$controllerName.'php';

                    if(!file_exists($controllerFile)){
                        http_response_code(500);//server error
                        echo "Controller not found";
                        return;
                    }
                    require_once $controllerFile;

                    $controller=new $controllerName();

                    if(!method_exists($controller,$methodName)){
                        http_response_code(500);//server error
                        echo "Method not found";
                        return;
                    }
                    $controller->$methodName();
                    return;
                }
            }
            http_response_code(404);//method not found
            echo "404 Page not found";
        }
    }