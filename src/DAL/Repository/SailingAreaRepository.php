<?php

namespace DAL\Repository;

use DAL\Interfaces\IDBContext;

class SailingAreaRepository {
  private IDBContext $db;

  public function __construct(IDBContext $db) {
    $this->db = $db;
  }

  public function getById(int $id) {
    $query = $this->db->prepare("
      SELECT id, name FROM sailing_areas
      WHERE id = ?
    ");
    $query->execute([ $id ]);
    return $query->fetch(\PDO::FETCH_ASSOC);
  }

  public function getAll() {
    $query = $this->db->prepare("
      SELECT id, name FROM sailing_areas
    ");
    $query->execute();
    return $query->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function insert($sailingArea) {
    $sailingArea['hash'] = md5(json_encode($sailingArea));
    $query = $this->db->prepare("
      INSERT INTO sailing_areas (id, name, hash)
      VALUES (:id, :name, :hash)
    ");
    return $query->execute($sailingArea);
  }

  public function update($sailingArea) {
    $sailingArea['hash'] = md5(json_encode($sailingArea));
    $query = $this->db->prepare("
      UPDATE sailing_areas
      SET name = :name, hash = :hash
      WHERE id = :id
    ");
    return $query->execute($sailingArea);
  }

  public function getIdHashMap() {
    $results = $this->db->query("SELECT id, hash FROM sailing_areas");
    return $results->fetchAll(\PDO::FETCH_KEY_PAIR);
  }
}