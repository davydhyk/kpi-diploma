<?php

namespace DAL\Repository;

use DAL\Interfaces\IDBContext;

class BoatRepository{
  private IDBContext $db;

  public function __construct(IDBContext $db) {
    $this->db = $db;
  }

  public function getById($id) {
    $query = $this->db->prepare("
      SELECT * FROM boats
      WHERE id = ?
    ");
    $query->execute([ $id ]);
    return $query->fetch(\PDO::FETCH_ASSOC);
  }

  public function getByFilter($filter) {
    $params = [];
    $where = '';
    $joins = [];
    if (isset($filter['shipyardId'])) {
      $where .= ' AND b.shipyardId = :shipyardId';
      $params[] = ['shipyardId', $filter['shipyardId'], \PDO::PARAM_INT];
    }
    if (isset($filter['baseId'])) {
      $where .= ' AND b.homeBaseId = :homeBaseId';
      $params[] = ['homeBaseId', $filter['baseId'], \PDO::PARAM_INT];
    }
    if (isset($filter['countryId'])) {
      $where .= ' AND bs.countryId = :countryId';
      $joins[] = ' LEFT JOIN bases bs ON bs.id = b.homeBaseId';
      $params[] = ['countryId', $filter['countryId'], \PDO::PARAM_INT];
    }
    $joins = implode(' ', $joins);
    $query = $this->db->prepare("
      SELECT b.* FROM boats b
      {$joins} WHERE 1=1 {$where} 
      LIMIT :limit OFFSET :offset
    ");
    foreach ($params as $values) {
      $query->bindParam(...$values);
    }
    $query->bindParam('limit', $filter['limit'], \PDO::PARAM_INT);
    $query->bindParam('offset', $filter['offset'], \PDO::PARAM_INT);
    $query->execute();
    return $query->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function getByNameAndCompany($name, $companyId) {
    $query = $this->db->prepare("
      SELECT * FROM boats
      WHERE 1=1
        AND name LIKE :name
        AND companyId = :companyId
    ");
    $query->execute([
      'name' => $name,
      'companyId' => $companyId
    ]);
    return $query->fetch(\PDO::FETCH_ASSOC);
  }

  public function insert($boat): bool {
    $query = $this->db->prepare("
      INSERT INTO boats (
        id_ba, id_mmk, name, slug, model, shipyardId, year, kind, homeBaseId, companyId, draught, beam, length,
        waterCapacity, fuelCapacity, engine, price, startPrice, discountPercentage, deposit, currency,
        wc, berths, cabins, mainsailArea, genoaArea, mainsailType, genoaType, hash_mmk, hash_ba
      )
      VALUES (
        :id_ba, :id_mmk, :name, :slug, :model, :shipyardId, :year, :kind, :homeBaseId, :companyId, :draught, :beam,
        :length, :waterCapacity, :fuelCapacity, :engine, :price, :startPrice, :discountPercentage, :deposit,
        :currency, :wc, :berths, :cabins, :mainsailArea, :genoaArea, :mainsailType, :genoaType, :hash_mmk, :hash_ba
      )
    ");
    $this->bindBoatToParams($query, $boat);
    $result = $query->execute();
    $boat['id'] = $this->db->lastInsertId();
    if (!empty($boat['images'])) {
      $result = $result && $this->insertImagesToBoats($boat['id'], $boat['images']);
    }
    if (!empty($boat['equipment'])) {
      $result = $result && $this->insertEquipmentToBoats($boat['id'], $boat['equipmentIds']);
    }
    return $result;
  }

  public function update($boat) {
    $query = $this->db->prepare("
      UPDATE boats
      SET id_mmk = :id_mmk,
          id_ba = :id_ba,
          name = :name,
          slug = :slug,
          model = :model,
          shipyardId = :shipyardId,
          year = :year,
          kind = :kind,
          homeBaseId = :homeBaseId,
          companyId = :companyId,
          draught = :draught,
          beam = :beam,
          length = :length,
          waterCapacity = :waterCapacity,
          fuelCapacity = :fuelCapacity,
          engine = :engine,
          price = :price,
          startPrice = :startPrice,
          discountPercentage = :discountPercentage,
          deposit = :deposit,
          currency = :currency,
          wc = :wc,
          berths = :berths,
          cabins = :cabins,
          mainsailArea = :mainsailArea,
          genoaArea = :genoaArea,
          mainsailType = :mainsailType,
          genoaType = :genoaType,
          hash_mmk = :hash_mmk,
          hash_ba = :hash_ba
      WHERE id = :id
    ");
    $query->bindParam('id', $boat['id']);
    $this->bindBoatToParams($query, $boat);
    $result = $query->execute();

    if (isset($boat['images'])) {
      $deleteImages = $this->db->prepare("DELETE FROM images WHERE boatId = ?");
      $deleteImages->execute([$boat['id']]);
      if (!empty($boat['images'])) {
        $result = $result && $this->insertImagesToBoats($boat['id'], $boat['images']);
      }
    }

    if (isset($boat['equipmentIds'])) {
      $deleteEquipment = $this->db->prepare("DELETE FROM boats_to_equipment WHERE boatId = ?");
      $deleteEquipment->execute([$boat['id']]);
      if (!empty($boat['equipmentIds'])) {
        $result = $result && $this->insertEquipmentToBoats($boat['id'], $boat['equipmentIds']);
      }
    }
    return $result;
  }

  public function getMap($key, $value) {
    $results = $this->db->query("SELECT {$key}, {$value} FROM boats");
    return $results->fetchAll(\PDO::FETCH_KEY_PAIR);
  }

  private function bindBoatToParams(&$query, $boat) {
    $params = [
      'id_mmk', 'id_ba', 'name', 'slug', 'model', 'shipyardId', 'year', 'kind', 'homeBaseId', 'companyId', 'draught',
      'beam', 'length', 'waterCapacity', 'fuelCapacity', 'engine', 'price', 'startPrice', 'discountPercentage',
      'deposit', 'currency', 'wc', 'berths', 'cabins', 'mainsailArea', 'genoaArea', 'mainsailType', 'genoaType',
      'hash_mmk', 'hash_ba'
    ];
    foreach ($params as $param) {
      $query->bindParam($param, $boat[$param], !empty($boat[$param]) ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
    }
  }

  private function insertImagesToBoats($boatId, $images): bool {
    $result = true;
    $query = $this->db->prepare("
      INSERT INTO images (url, description, sortOrder, boatId)
      VALUES (:url, :description, :sortOrder, :boatId)
    ");
    foreach ($images as $image) {
      if (empty($image['url'])) continue;
      $result = $result && $query->execute([
        'url' => $image['url'],
        'description' => $image['description'],
        'sortOrder' => $image['sortOrder'],
        'boatId' => $boatId
      ]);
    }
    return $result;
  }

  private function insertEquipmentToBoats($boatId, $equipments): bool {
    $result = true;
    $query = $this->db->prepare("
      INSERT INTO boats_to_equipment (boatId, equipmentId)
      VALUES (:boatId, :equipmentId)
    ");
    foreach ($equipments as $equipmentId) {
      if (in_array($equipmentId, [38, 28, 39, 15, 20])) continue;
      $result = $result && $query->execute([
        'boatId' => $boatId,
        'equipmentId' => $equipmentId
      ]);
    }
    return $result;
  }

  private function insertProductsToBoats($boatId, $products): bool {
    $result = true;
    $query = $this->db->prepare("
      INSERT INTO products (name, extras, boatId)
      VALUES (:name, :extras, :boatId)
    ");
    foreach ($products as $product) {
      $result = $result && $query->execute([
        'name' => $product['name'],
        'extras' => json_encode($product['extras']),
        'boatId' => $boatId
      ]);
    }
    return $result;
  }
}