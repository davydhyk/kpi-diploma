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
      SELECT b.*,
             c.name as country,
             COALESCE(b2.name, b2.name_ba) as base,
             (SELECT i.url FROM images i WHERE i.description = 'Main image' AND i.boatId = b.id LIMIT 1) as image,
             s.name as shipyard,
             c2.name as company,
             c2.telephone as companyMobile,
             c2.web as companyWeb
      FROM boats b
      INNER JOIN bases b2 on b.homeBaseId = b2.id
      INNER JOIN countries c on b2.countryId = c.id
      LEFT JOIN shipyards s on b.shipyardId = s.id
      LEFT JOIN companies c2 on b.companyId = c2.id
      WHERE b.id = ?
    ");
    $query->execute([ $id ]);
    return $query->fetch(\PDO::FETCH_ASSOC);
  }

  public function getImagesByBoatId($id) {
    $query = $this->db->prepare("
      SELECT *
      FROM images
      WHERE boatId = ?
    ");
    $query->execute([ $id ]);
    return $query->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function getByFilter($filter) {
    $arrayKeys = [
      'shipyardId', 'kind', 'companyId', 'mainsailType', 'genoaType'
    ];
    $geoKeys = [
      'baseId', 'countryId', 'sailingAreaId', 'regionId'
    ];
    $rangeKeys = [
      'yearFrom', 'yearTo', 'wcFrom', 'wcTo', 'berthsFrom', 'berthsTo', 'cabinsFrom', 'cabinsTo',
      'draughtFrom', 'draughtTo', 'beamFrom', 'beamTo', 'lengthFrom', 'lengthTo', 'waterCapacityFrom',
      'waterCapacityTo', 'fuelCapacityFrom', 'fuelCapacityTo', 'priceFrom', 'priceTo'
    ];
    $params = [];
    $where = '';
    foreach ($rangeKeys as $fKey) {
      if (!isset($filter[$fKey])) {
        continue;
      }
      $compareKey = strpos($fKey, 'From') !== false ? '>=' : '<=';
      $key = str_replace(['From', 'To'], '', $fKey);
      $where .= " AND b.$key $compareKey :$fKey";
      $params[] = [$fKey, $filter[$fKey]];
    }
    foreach ($arrayKeys as $fKey) {
      if (!isset($filter[$fKey])) {
        continue;
      }
      $values = $this->db->quoteMaybeArray($filter[$fKey]);
      $values = implode(', ', $values);
      $where .= " AND b.$fKey IN ($values)";
    }
    $whereGeoOr = [];
    foreach ($geoKeys as $gKey) {
      if (!isset($filter[$gKey])) {
        continue;
      }
      $values = $this->db->quoteMaybeArray($filter[$gKey]);
      $values = implode(', ', $values);
      if ($gKey === 'regionId') {
        $subQuery = "
          SELECT b.id FROM boats b
          INNER JOIN bases bs ON bs.id = b.homeBaseId
          INNER JOIN countries c ON c.id = bs.countryId
          INNER JOIN world_regions wr on wr.id = c.regionId
          WHERE wr.id IN ($values)
        ";
        $whereGeoOr[] = "b.id IN ($subQuery)";
      } elseif ($gKey === 'sailingAreaId') {
        $subQuery = "
          SELECT b.id FROM boats b
          INNER JOIN bases bs ON bs.id = b.homeBaseId
          INNER JOIN bases_to_sailing_areas btsa on bs.id_mmk = btsa.baseId
          INNER JOIN sailing_areas sa on btsa.sailingAreaId = sa.id
          WHERE sa.id IN ($values)
        ";
        $whereGeoOr[] = "b.id IN ($subQuery)";
      } elseif ($gKey === 'countryId') {
        $subQuery = "
          SELECT b.id FROM boats b
          INNER JOIN bases bs ON bs.id = b.homeBaseId
          INNER JOIN countries c ON c.id = bs.countryId
          WHERE c.id IN ($values)
        ";
        $whereGeoOr[] = "b.id IN ($subQuery)";
      } elseif ($gKey === 'baseId') {
        $subQuery = "
          SELECT b.id FROM boats b
          INNER JOIN bases bs ON bs.id = b.homeBaseId
          WHERE bs.id IN ($values)
        ";
        $whereGeoOr[] = "b.id IN ($subQuery)";
      }
    }
    if (!empty($whereGeoOr)) {
      $whereGeoOr = implode(' OR ', $whereGeoOr);
      $where .= " AND $whereGeoOr";
    }
    $query = $this->db->prepare("
      SELECT b.*,
             c.name as country,
             COALESCE(b2.name, b2.name_ba) as base,
             (SELECT i.url FROM images i WHERE i.description = 'Main image' AND i.boatId = b.id LIMIT 1) as image
      FROM boats b
      INNER JOIN bases b2 on b.homeBaseId = b2.id
      INNER JOIN countries c on b2.countryId = c.id
      WHERE 1 {$where}
      ORDER BY IF(b.price IS NULL, 0, 1) DESC
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