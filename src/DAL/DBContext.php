<?php

namespace DAL;
use DAL\Interfaces\IConfig;
use DAL\Interfaces\IDBContext;

class DBContext implements IDBContext {

  private \PDO $pdo;

  public function __construct(IConfig $config) {
    $dsn = $config->get('dsn');
    $user = $config->get('user');
    $password = $config->get('password');
    $this->pdo = new \PDO($dsn, $user, $password, [ \PDO::ATTR_PERSISTENT => true, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING ]);
  }

  public function query(string $query) {
    return $this->pdo->query($query);
  }

  public function exec(string $query, $fetchMode) {
    return $this->pdo->exec($query, $fetchMode);
  }

  public function prepare(string $query, array $options = []) {
    return $this->pdo->prepare($query, $options);
  }

  public function lastInsertId() {
    return $this->pdo->lastInsertId();
  }

  public function quote(string $query) {
    return $this->pdo->quote($query);
  }

  public function quoteMaybeArray($values) {
    $quotedValues = [];
    if (is_array($values)) {
      foreach ($values as $v) {
        $quotedValues[] = $this->quote($v);
      }
    } else {
      $quotedValues[] = $this->quote($values);
    }
    return $quotedValues;
  }

  public function errorInfo() {
    return $this->pdo->errorInfo();
  }
}