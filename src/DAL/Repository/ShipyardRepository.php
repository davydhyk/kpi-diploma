<?php

namespace DAL\Repository;

use DAL\Interfaces\IDBContext;

class ShipyardRepository {
  private IDBContext $db;

  public function __construct(IDBContext $db) {
    $this->db = $db;
  }

  public function getById(int $id) {
    $query = $this->db->prepare("
      SELECT * FROM shipyards
      WHERE id = ?
    ");
    $query->execute([ $id ]);
    return $query->fetch(\PDO::FETCH_ASSOC);
  }

  public function getByName($name) {
    $query = $this->db->prepare("
      SELECT * FROM shipyards
      WHERE 1=0
        OR name LIKE :name
        OR shortName LIKE :name
    ");
    $query->execute(['name' => $name]);
    return $query->fetch(\PDO::FETCH_ASSOC);
  }

  public function getAll() {
    $query = $this->db->prepare("
      SELECT * FROM shipyards
    ");
    $query->execute();
    return $query->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function insert($shipyard) {
    $query = $this->db->prepare("
      INSERT INTO shipyards (id_mmk, id_ba, name, shortName)
      VALUES (:id_mmk, :id_ba, :name, :shortName)
    ");
    $this->bindShipyardToParams($query, $shipyard);
    return $query->execute();
  }

  public function update($shipyard) {
    $shipyard['hash'] = md5(json_encode($shipyard));
    $query = $this->db->prepare("
      UPDATE shipyards
      SET id_mmk = :id_mmk,
          id_ba = :id_ba,
          name = :name,
          shortName = :shortName
      WHERE id = :id
    ");
    $query->bindParam('id', $shipyard['id']);
    $this->bindShipyardToParams($query, $shipyard);
    return $query->execute();
  }

  public function getMap($key, $value) {
    $results = $this->db->query("SELECT {$key}, {$value} FROM shipyards");
    return $results->fetchAll(\PDO::FETCH_KEY_PAIR);
  }

  private function bindShipyardToParams(&$query, $shipyard) {
    $params = ['id_mmk', 'id_ba', 'name', 'shortName'];
    foreach ($params as $param) {
      $query->bindParam($param, $shipyard[$param], !empty($shipyard[$param]) ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
    }
  }
}