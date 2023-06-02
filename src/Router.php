<?php

class Router {
  private array $routes;

  public function __construct($routes) {
    $this->routes = $routes;
  }

  public function process($method, $uri) {
    $request = parse_url($uri);
    foreach ($this->routes[$method] as $route => $executor) {
      $names = [];
      preg_match_all('/\/:([^\/]+)/', $route, $names);
      $search = ['/\//', '/\/:[^\/\\\]+/'];
      $replacement = ['\/', '/([^\/]+)'];
      $regExp = '/^' . preg_replace($search, $replacement, $route) . '$/';
      if (preg_match_all($regExp, $request['path'], $matches) !== 1) {
        continue;
      }
      $params = [];
      if (count($names) > 1) {
        for ($i = 0; $i < count($names[1]); $i++) {
          $params[$names[1][$i]] = $matches[$i + 1][0];
        }
      }
      try {
        global $container;
        $controller = $container->get($executor[0]);
        $controllerMethod = $executor[1];
        $result = $controller->$controllerMethod($params);
        $this->end($result);
        return;
      } catch (Exception $exception) {
        $this->end([
          'status' => 500,
        ]);
        return;
      }
    }
    $this->end([
      'status' => 404,
    ]);
  }

  private function end($result) {
    $status = !empty($result['status']) ? $result['status'] : 200;
    http_response_code($status);
    $this->sendHeaders();
    if (!empty($result['data'])) {
      $this->sendData($result['data']);
    }
  }

  private function sendHeaders() {

  }

  private function sendData($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
//    dbg($data);
  }
}