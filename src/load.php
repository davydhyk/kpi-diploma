<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function dbg(...$values) {
  foreach ($values as $value) {
    echo '<pre>';
    echo htmlentities(print_r($value, true));
    echo '</pre>';
  }
}

spl_autoload_register(function ($class_name) {
  include __DIR__ . '/' . $class_name . '.php';
});

$configPath = __DIR__ . '/../config.json';
$config = new \DAL\Config($configPath);

global $container;
$container = new Container();
$container->singleton(\DAL\Interfaces\IConfig::class, $config);
$container->set(\DAL\Interfaces\IDBContext::class, \DAL\DBContext::class);
