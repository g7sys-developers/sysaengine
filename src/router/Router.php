<?php
  namespace sysaengine\router;

  use Laminas\Diactoros\ServerRequestFactory;
  use Laminas\Diactoros\Stream;

  class Router {
    private static $routes = [];

    public static function get(string $route, string $controller) : void
    {
      self::$routes[$route] = [
        'method' => 'get',
        'controller' => $controller
      ];
    }

    public static function post(string $route, string $controller) : void
    {
      self::$routes[$route] = [
        'method' => 'post',
        'controller' => $controller
      ];
    }

    public static function delete(string $route, string $controller) : void
    {
      self::$routes[$route] = [
        'method' => 'delete',
        'controller' => $controller
      ];
    }

    public static function put(string $route, string $controller) : void
    {
      self::$routes[$route] = [
        'method' => 'put',
        'controller' => $controller
      ];
    }

    public static function run(string $uri) : void
    {
      $method = strtolower($_SERVER['REQUEST_METHOD']);
      $route = explode('?', $uri)[0];
      if(!array_key_exists($route, self::$routes)) {
        http_response_code(404);
        echo '404 - Not found'; die;
      }

      if($method !== self::$routes[$route]['method']) {
        http_response_code(405);
        echo '405 - Method not allowed'; die;
      }

      $request = ServerRequestFactory::fromGlobals(
        $_SERVER,
        $_GET,
        $_POST,
        $_COOKIE,
        $_FILES
      );

      $controller = self::$routes[$route]['controller'][0];
      $action = self::$routes[$route]['controller'][1];

      $controller = new $controller;
      $controller->$action($request);
    }
  }
?>