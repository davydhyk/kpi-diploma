<?php

namespace DAL\Repository;

use DAL\Interfaces\IDBContext;

class BaseRepository {
  private IDBContext $db;

  public function __construct(IDBContext $db) {
    $this->db = $db;
  }

  public function getById(int $id) {
    $query = $this->db->prepare("
      SELECT * FROM bases
      WHERE id = ?
    ");
    $query->execute([ $id ]);
    return $query->fetch(\PDO::FETCH_ASSOC);
  }

  public function getBySailingAreaId($sailingAreaId) {
    $query = $this->db->prepare("
      SELECT b.*
      FROM bases b
      LEFT JOIN bases_to_sailing_areas btsa on b.id_mmk = btsa.baseId
      WHERE btsa.sailingAreaId = ?
    ");
    $query->execute([ $sailingAreaId ]);
    return $query->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function getByCountryId($countryId) {
    $query = $this->db->prepare("
      SELECT b.*
      FROM bases b
      LEFT JOIN countries c on c.id = b.countryId
      WHERE c.id = ?
    ");
    $query->execute([ $countryId ]);
    return $query->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function getByWorldRegionId($worldRegionId) {
    $query = $this->db->prepare("
      SELECT b.*
      FROM bases b
      LEFT JOIN countries c on c.id = b.countryId
      LEFT JOIN world_regions wr on wr.id = c.regionId
      WHERE wr.id = ?
    ");
    $query->execute([ $worldRegionId ]);
    return $query->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function getByName($name) {
    $query = $this->db->prepare("
      SELECT * FROM bases
      WHERE 1=0
        OR bases.name LIKE :name
        OR bases.name_ba LIKE :name
    ");
    $query->execute(['name' => $name]);
    return $query->fetch(\PDO::FETCH_ASSOC);
  }

  public function getNearByCoordinates($lat, $lng) {
    $query = $this->db->prepare("
      SELECT * FROM (
        SELECT b.*,
          SQRT(
            POWER(CAST(:lat as DOUBLE ) - b.latitude, 2) +
            POWER(CAST(:lng as DOUBLE ) - b.longitude, 2)
          ) as diff
        FROM bases b
        WHERE 1=1
          AND b.latitude IS NOT NULL
          AND b.longitude IS NOT NULL
        ORDER BY diff ASC
        LIMIT 10
      ) d WHERE diff < 0.101
    ");
    $query->execute([
      'lat' => $lat,
      'lng' => $lng
    ]);
    return $query->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function getAll() {
    $query = $this->db->prepare("
      SELECT * FROM bases
    ");
    $query->execute();
    return $query->fetchAll(\PDO::FETCH_ASSOC);
  }

  public function insert($base) {
    $query = $this->db->prepare("
      INSERT INTO bases (id_mmk, id_ba, name, name_ba, city, address, latitude, longitude, countryId)
      VALUES (:id_mmk, :id_ba, :name, :name_ba, :city, :address, :latitude, :longitude, :countryId)
    ");
    $this->bindBaseToParams($query, $base);
    $result = $query->execute();
    if (!empty($base['sailingAreas'])) {
      $result = $result && $this->insertBaseToSailingAreas($base['id_mmk'], $base['sailingAreas']);
    }
    return $result;
  }

  public function update($base) {
    $base['hash'] = md5(json_encode($base));
    $query = $this->db->prepare("
      UPDATE bases
      SET id_mmk = :id_mmk,
          id_ba = :id_ba,
          name = :name,
          name_ba = :name_ba,
          city = :city,
          address = :address,
          latitude = :latitude,
          longitude = :longitude,
          countryId = :countryId
      WHERE id = :id
    ");
    $query->bindParam('id', $base['id']);
    $this->bindBaseToParams($query, $base);
    $result = $query->execute();

    if (isset($base['sailingAreas'])) {
      $delete = $this->db->prepare("DELETE FROM bases_to_sailing_areas WHERE baseId = ?");
      $delete->execute([$base['id_mmk']]);
    }
    if (!empty($base['sailingAreas'])) {
      $result = $result && $this->insertBaseToSailingAreas($base['id_mmk'], $base['sailingAreas']);
    }
    return $result;
  }

  public function getMap($key, $value) {
    $results = $this->db->query("SELECT {$key}, {$value} FROM bases");
    return $results->fetchAll(\PDO::FETCH_KEY_PAIR);
  }

  private function bindBaseToParams(&$query, $base) {
    $params = ['id_mmk', 'id_ba', 'name', 'name_ba', 'city', 'address', 'latitude', 'longitude', 'countryId'];
    foreach ($params as $param) {
      $query->bindParam($param, $base[$param], !empty($base[$param]) ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
    }
  }

  private function insertBaseToSailingAreas($baseId, $sailingAreas): bool {
    $result = true;
    $query = $this->db->prepare("
      INSERT INTO bases_to_sailing_areas (baseId, sailingAreaId)
      VALUES (:baseId, :sailingAreaId)
    ");
    foreach ($sailingAreas as $sailingAreaId) {
      $result = $result && $query->execute([
        'baseId' => $baseId,
        'sailingAreaId' => $sailingAreaId
      ]);
      if (!$result) dbg($baseId, $sailingAreas);
    }
    return $result;
  }
}