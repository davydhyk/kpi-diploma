<?php

function dbg(...$values) {
  foreach ($values as $value) {
    echo '<pre>';
    echo htmlentities(print_r($value, true));
    echo '</pre>';
  }
}

function cal_days_in_year($year) {
  return ($year % 4 === 0 && $year % 100 !== 0) || $year % 400 === 0
    ? 366
    : 365;
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
