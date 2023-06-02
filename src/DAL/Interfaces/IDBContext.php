<?php

namespace DAL\Interfaces;

interface IDBContext {
  public function exec(string $query, $fetchMode);
  public function prepare(string $query, array $options = []);
  public function query(string $query);
  public function lastInsertId();
}