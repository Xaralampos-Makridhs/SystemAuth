<?php
    require_once __DIR__.'/../Config/bootstrap.php';
    require_once __DIR__.'/../core/Router.php';

    $router=new Router();

    $router->addRoute('GET','/','HomeController@index');
    $router->addRoute('GET','/about','HomeController@about');

    $router->dispatch(
        $_SERVER['REQUEST_METHOD'],
        parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)
    );