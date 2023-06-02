<?php

namespace DAL\Repository;

use DAL\Interfaces\IDBContext;

class CountryRepository {
  private $db;

  public function __construct(IDBContext $db) {
    $this->db = $db;
  }

  public function getById(int $id) {
    $query = $this->db->prepare("
      SELECT * FROM countries
      WHERE id = ?
    ");
    $query->execute([ $id ]);
    return $query->fetch(\PDO::FETCH_ASSOC);
  }

  public function getAll() {
    $query = $this->db->prepare("
      SELECT * FROM countries
    ");
    $query->execute();
    return $query->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function getByName($name) {
    $query = $this->db->prepare("
      SELECT * FROM countries
      WHERE name = :name
    ");
    $query->execute(['name' => $name]);
    return $query->fetch(\PDO::FETCH_ASSOC);
  }

  public function getByWorldRegionId($regionId) {
    $query = $this->db->prepare("
      SELECT * FROM countries
      WHERE regionId = :regionId
    ");
    $query->execute(['regionId' => $regionId]);
    return $query->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function insert($country) {
    $query = $this->db->prepare("
      INSERT INTO countries (id_mmk, id_ba, name, shortName, longName, regionId)
      VALUES (:id_mmk, :id_ba, :name, :shortName, :longName, :worldRegion)
    ");
    $this->bindCountryToParams($query, $country);
    return $query->execute();
  }

  public function update($country) {
    $query = $this->db->prepare("
      UPDATE countries
      SET id_mmk = :id_mmk,
          id_ba = :id_ba,
          name = :name,
          shortName = :shortName,
          longName = :longName,
          regionId = :worldRegion
      WHERE 1=1
        AND id = :id
    ");
    $query->bindParam('id', $country['id']);
    $this->bindCountryToParams($query, $country);
    return $query->execute();
  }

  public function getMap($key, $value) {
    $results = $this->db->query("SELECT {$key}, {$value} FROM countries");
    return $results->fetchAll(\PDO::FETCH_KEY_PAIR);
  }

  private function bindCountryToParams(&$query, $country) {
    $params = ['id_mmk', 'id_ba', 'name', 'shortName', 'longName', 'worldRegion'];
    foreach ($params as $param) {
      $query->bindParam($param, $country[$param], !empty($country[$param]) ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
    }
  }
}