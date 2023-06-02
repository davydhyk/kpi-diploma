<?php

class Route {

  static array $routes = [];

  public static function get($path, $controller) {
    self::addRoute($path, $controller, 'GET');
  }

  public static function post($path, $controller) {
    self::addRoute($path, $controller, 'POST');
  }

  private static function addRoute($path, $controller, $method) {
    self::$routes[$method][$path] = $controller;
  }
}