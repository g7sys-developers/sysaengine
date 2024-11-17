<?php
  namespace sysaengine\router;

  use Laminas\Diactoros\ServerRequestFactory;
  use Laminas\Diactoros\Stream;

  class Router {
    private static $routes = [];

    public static function get(string $route, array $controller, array $middlewares=[]) : void
    {
      self::$routes[$route] = [
        'method' => 'get',
        'controller' => $controller,
        'middlewares' => $middlewares
      ];
    }

    public static function post(string $route, array $controller, array $middlewares=[]) : void
    {
      self::$routes[$route] = [
        'method' => 'post',
        'controller' => $controller,
        'middlewares' => $middlewares
      ];
    }

    public static function delete(string $route, array $controller, array $middlewares=[]) : void
    {
      self::$routes[$route] = [
        'method' => 'delete',
        'controller' => $controller,
        'middlewares' => $middlewares
      ];
    }

    public static function put(string $route, array $controller, array $middlewares=[]) : void
    {
      self::$routes[$route] = [
        'method' => 'put',
        'controller' => $controller,
        'middlewares' => $middlewares
      ];
    }

    public static function run() : void
    {
      $method = strtolower($_SERVER['REQUEST_METHOD']);
      $route = explode('?', $_SERVER['REQUEST_URI'])[0];
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

      $routeInfo = self::$routes[$route];
      $middlewares = $routeInfo['middlewares'];

      foreach ($middlewares as $middleware) {
        $response = $middleware($request);
        if ($response instanceof ResponseInterface) {
            http_response_code($response->getStatusCode());
            echo $response->getBody();
            return;
        }
      }

      $controller = new $routeInfo['controller'][0];
      $action = $routeInfo['controller'][1];
      $controller->$action($request);
    }
  }
?>