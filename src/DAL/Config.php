<?php

namespace DAL;

use DAL\Interfaces\IConfig;

class Config implements IConfig {
  private $config = [];

  public function __construct($path) {
    $configSource = file_get_contents($path);
    $this->config = json_decode($configSource, true);
  }

  public function get($key) {
    return $this->config[$key];
  }

  public function set($key, $value) {
    $this->config[$key] = $value;
  }
}