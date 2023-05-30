<?php

namespace DAL\Repository;

use DAL\Interfaces\IDBContext;

class WorldRegionRepository {
  private $db;

  public function __construct(IDBContext $db) {
    $this->db = $db;
  }

  public function getById(int $id) {
    $query = $this->db->prepare("
      SELECT id, name FROM world_regions
      WHERE id = ?
    ");
    $query->execute([ $id ]);
    return $query->fetch(\PDO::FETCH_ASSOC);
  }

  public function getAll() {
    $query = $this->db->prepare("
      SELECT id, name FROM world_regions
    ");
    $query->execute();
    return $query->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function insert($worldRegion) {
    $worldRegion['hash'] = md5(json_encode($worldRegion));
    $query = $this->db->prepare("
      INSERT INTO world_regions (id, name, hash)
      VALUES (:id, :name, :hash)
    ");
    return $query->execute($worldRegion);
  }

  public function update($worldRegion) {
    $worldRegion['hash'] = md5(json_encode($worldRegion));
    $query = $this->db->prepare("
      UPDATE world_regions
      SET name = :name, hash = :hash
      WHERE id = :id
    ");
    return $query->execute($worldRegion);
  }

  public function getIdHashMap() {
    $results = $this->db->query("SELECT id, hash FROM world_regions");
    return $results->fetchAll(\PDO::FETCH_KEY_PAIR);
  }
}