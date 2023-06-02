<?php

namespace DAL\Interfaces;

interface IConfig {
  public function get($key);

  public function set($key, $value);
}