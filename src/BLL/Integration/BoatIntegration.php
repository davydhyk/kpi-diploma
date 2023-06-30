<?php

namespace BLL\Integration;

use BLL\BAClient;
use BLL\MMKClient;
use DAL\Repository\BaseRepository;
use DAL\Repository\BoatRepository;
use DAL\Repository\CompanyRepository;
use DAL\Repository\CountryRepository;
use DAL\Repository\EquipmentRepository;
use DAL\Repository\ShipyardRepository;

class BoatIntegration {
  private MMKClient $mmk;
  private BAClient $ba;
  private CountryRepository $countryRepository;
  private BaseRepository $baseRepository;
  private CompanyRepository $companyRepository;
  private EquipmentRepository $equipmentRepository;
  private ShipyardRepository $shipyardRepository;
  private BoatRepository $boatRepository;

  public function __construct(MMKClient $mmk,
                              BAClient $ba,
                              CountryRepository $countryRepository,
                              BaseRepository $baseRepository,
                              CompanyRepository $companyRepository,
                              EquipmentRepository $equipmentRepository,
                              ShipyardRepository $shipyardRepository,
                              BoatRepository $boatRepository
  ) {
    $this->mmk = $mmk;
    $this->ba = $ba;
    $this->countryRepository = $countryRepository;
    $this->baseRepository = $baseRepository;
    $this->companyRepository = $companyRepository;
    $this->equipmentRepository = $equipmentRepository;
    $this->shipyardRepository = $shipyardRepository;
    $this->boatRepository = $boatRepository;
  }

  private function processMMK() {
    $companiesIdMap = $this->companyRepository->getMap('id_mmk', 'id');
    $shipyardsIdMap = $this->shipyardRepository->getMap('id_mmk', 'id');
    $equipmentIdMap = $this->equipmentRepository->getMap('id_mmk', 'id');
    $basesIdMap = $this->baseRepository->getMap('id_mmk', 'id');
    $boatsIdMap = $this->boatRepository->getMap('id_mmk', 'id');
    foreach ($companiesIdMap as $mmkCompanyId => $companyId) {
      if (!$mmkCompanyId) {
        continue;
      }
      $boats = $this->mmk->getBoats($mmkCompanyId);
      if (empty($boats)) {
        continue;
      }
      foreach ($boats as $boat) {
        $hash = md5(json_encode($boat));
        $id = $boat['id'];
        $boat['id_mmk'] = $id;
        $boat['hash_mmk'] = $hash;
        $boat['shipyardId'] = $shipyardsIdMap[$boat['shipyardId']] ?? null;
        $boat['companyId'] = $companyId;
        $boat['homeBaseId'] = $basesIdMap[$boat['homeBaseId']] ?? null;
        if (!empty($boat['equipmentIds'])) {
          $equipment = [];
          foreach ($boat['equipmentIds'] as $eq) {
            if (!isset($equipmentIdMap[$eq])) {
              continue;
            }
            $equipment[] = $equipmentIdMap[$eq];
          }
          $boat['equipmentIds'] = $equipment;
        }
        unset($boat['id']);
        $existBoat = !isset($boatsIdMap[$id])
          ? $this->boatRepository->getByNameAndCompany($boat['name'], $companyId)
          : $this->boatRepository->getById($boatsIdMap[$id]);
        if ($existBoat === false) {
          $this->boatRepository->insert($boat);
        } else if ($existBoat['hash_mmk'] !== $hash) {
          $existBoat = $this->mergeExistBoatWithNew($existBoat, $boat);
          $this->boatRepository->update($existBoat);
        }
      }
    }
  }

  private function processBA() {
    $companiesIdMap = $this->companyRepository->getMap('id_ba', 'id');
    $boatsIdMap = $this->boatRepository->getMap('id_ba', 'id');
    $countryIdMap = $this->countryRepository->getMap('shortName', 'id');
    $page = 1;
    $limit = 50;
    $boats = $this->ba->getBoats($page, $limit);
    $i = 0;
    while (!empty($boats)) {
      foreach ($boats as $boat) {
        $hash = md5(json_encode($boat));
        $id = $boat['_id'];
        $base = $this->baseRepository->getByName($boat['marina']);
        if ($base === false) {
          $countryId = $countryIdMap[strtoupper($boat['flag'])] ?? null;
          $base = $this->retrieveBase($boat, $countryId);
        }
        $companyId = $companiesIdMap[$boat['charter_id']];
        $shipyard = $this->shipyardRepository->getByName($boat['manufacturer']);
        $boat = $this->prepareNewBaBoat($boat);
        $boat['companyId'] = $companyId;
        $boat['shipyardId'] = $shipyard['id'];
        $boat['homeBaseId'] = $base['id'];
        $boat['hash_ba'] = $hash;
        $existBoat = !isset($boatsIdMap[$id])
          ? $this->boatRepository->getByNameAndCompany($boat['name'], $companyId)
          : $this->boatRepository->getById($boatsIdMap[$id]);
        if ($existBoat === false) {
          $this->boatRepository->insert($boat);
        } else if ($existBoat['hash_ba'] !== $hash) {
          $existBoat = $this->mergeExistBoatWithNew($existBoat, $boat);
          $this->boatRepository->update($existBoat);
        }
      }
      $boats = $this->ba->getBoats(++$page, $limit);
    }
  }

  private function mergeExistBoatWithNew($existBoat, $newBoat) {
    foreach ($existBoat as $key => $value) {
      if (empty($newBoat[$key]) || !is_scalar($newBoat[$key])) continue;
      $existBoat[$key] = $newBoat[$key];
    }
    return $existBoat;
  }

  private function prepareNewBaBoat($newBoat) {
    $boat = [];
    $boat['id_ba'] = $newBoat['_id'];
    $boat['slug'] = $newBoat['slug'];
    $boat['name'] = preg_replace('/^.*\| /', '', $newBoat['title']);
    $boat['model'] = $newBoat['manufacturer'] . ' ' . $newBoat['model'];
    $boat['kind'] = $this->boatKindMap[$newBoat['category_slug']] ?? $newBoat['category'];
    $boat['mainsailType'] = $newBoat['sail'];
    $boat['rating'] = $newBoat['reviewsScore'];
    $boat['images'] = [[
      'url' => 'https://imageresizer.yachtsbt.com/' . $newBoat['main_img'],
      'description' => 'Main image',
      'sortOrder' => 0
    ]];
    $boat['currency'] = $newBoat['currency'];
    $boat['price'] = $newBoat['priceFrom'];
    $boat['startPrice'] = $newBoat['priceFrom'];
    $boat['discountPercentage'] = $newBoat['discount'];

    $params = $newBoat['parameters'];
    $boat['berths'] = $params['max_sleeps'];
    $boat['cabins'] = $params['cabins'];
    $boat['wc'] = $params['toilets'];
    $boat['length'] = $params['length'];
    $boat['beam'] = $params['beam'];
    $boat['draught'] = $params['draft'];
    $boat['year'] = $params['year'];
    $boat['engine'] = (
      $params['number_engines'] > 1
        ? $params['number_engines'] . ' x '
        : ''
      ) . $params['engine_power'] . 'HP';
    $boat['fuelCapacity'] = $params['fuel'];
    $boat['waterCapacity'] = $params['water_tank'] ?? null;
    return $boat;
  }

  private array $boatKindMap = [
    'sailing-yacht' => 'Sail boat',
    'motor-boat' => 'Motor boat',
    'catamaran' => 'Catamaran',
    'gulet' => 'Gulet',
    'motor-yacht' => 'Motoryacht',
    'power-catamaran' => 'Power catamaran'
  ] ;

  private function retrieveBase($boat, $countryId) {
    $name = $boat['marina'];
    $coords = $boat['coordinates'];
    $baseByName = $this->baseRepository->getByName($name);
    if ($baseByName !== false) {
      return $baseByName;
    }
    $nearBases = $this->baseRepository->getNearByCoordinates($coords[0], $coords[1]);
    $similarity = 0; $similarBase = null;
    foreach ($nearBases as $nearBase) {
      if ($nearBase['diff'] > 0.01) continue;
      $p = $this->textSimilarity($name, $nearBase['name']);
      if ($p < $similarity) continue;
      $similarity = $p;
      $similarBase = $nearBase;
    }
    if ($similarBase === null || $similarity < 0.4) {
      $base = [
        'name_ba' => $name,
        'city' => $boat['city'],
        'latitude' => $coords[0],
        'longitude' => $coords[1],
        'countryId' => $countryId
      ];
      $this->baseRepository->insert($base);
      return $this->baseRepository->getByName($name);
    }
    $similarBase['name_ba'] = $name;
    $this->baseRepository->update($similarBase);
    return $similarBase;
  }

  private function textSimilarity($source, $name) {
    $sourceArr = explode(' ', mb_strtolower($source));
    $nameArr = explode(' ', mb_strtolower($name));
    $similarity = 0;
    foreach ($sourceArr as $s) {
      foreach ($nameArr as $n) {
        $p = 0;
        similar_text($s, $n, $p);
        $similarity += $p;
      }
    }
    return $similarity / (count($sourceArr) * count($nameArr));
  }

  public function process() {
    $this->processMMK();
    $this->processBA();
  }
}