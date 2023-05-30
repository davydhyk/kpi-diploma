<?php

namespace DAL\Repository;

use DAL\Interfaces\IDBContext;

class EquipmentRepository {
  private IDBContext $db;

  public function __construct(IDBContext $db) {
    $this->db = $db;
  }

  public function getById(int $id) {
    $query = $this->db->prepare("
      SELECT * FROM equipment
      WHERE id = ?
    ");
    $query->execute([ $id ]);
    return $query->fetch(\PDO::FETCH_ASSOC);
  }

  public function getByName($name) {
    $query = $this->db->prepare("
      SELECT * FROM equipment
      WHERE 0=1
        OR name LIKE :name
        OR name_ba LIKE :name
    ");
    $query->execute(['name' => $name]);
    return $query->fetch(\PDO::FETCH_ASSOC);
  }

  public function getAll() {
    $query = $this->db->prepare("
      SELECT * FROM equipment
    ");
    $query->execute();
    return $query->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function insert($equipment) {
    $equipment['hash'] = md5(json_encode($equipment));
    $query = $this->db->prepare("
      INSERT INTO equipment (id_mmk, name, name_ba)
      VALUES (:id_mmk, :name, :name_ba)
    ");
    $this->bindEquipmentToParams($query, $equipment);
    return $query->execute();
  }

  public function update($equipment) {
    $query = $this->db->prepare("
      UPDATE equipment
      SET id_mmk = :id_mmk,
          name = :name,
          name_ba = :name_ba
      WHERE id = :id
    ");
    $query->bindParam('id', $equipment['id']);
    $this->bindEquipmentToParams($query, $equipment);
    return $query->execute();
  }

  public function getMap($key, $value) {
    $results = $this->db->query("SELECT {$key}, {$value} FROM equipment");
    return $results->fetchAll(\PDO::FETCH_KEY_PAIR);
  }

  private function bindEquipmentToParams(&$query, $equipment) {
    $params = ['id_mmk', 'name', 'name_ba'];
    foreach ($params as $param) {
      $query->bindParam($param, $equipment[$param], !empty($equipment[$param]) ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
    }
  }
}