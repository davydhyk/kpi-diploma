<?php

namespace BLL\Services;


use BLL\BAClient;
use BLL\MMKClient;
use DAL\Repository\BoatRepository;

class BoatService {

  private MMKClient $mmk;
  private BAClient $ba;
  private BoatRepository $boatRepository;

  public function __construct(MMKClient $mmk, BAClient $ba, BoatRepository $boatRepository) {
    $this->mmk = $mmk;
    $this->ba = $ba;
    $this->boatRepository = $boatRepository;
  }

  public function getById($id) {
    $boat = $this->boatRepository->getById($id);
    if ($boat !== false) {
      $boat['images'] = $this->boatRepository->getImagesByBoatId($id);
    }
    return $boat;
  }

  public function getAvailability($id, $checkIn, $checkOut) {
    $boat = $this->boatRepository->getById($id);
    if ($boat === false) {
      return false;
    }
    $years = array_unique([
      intval($checkIn->format('Y')),
      intval($checkOut->format('Y'))
    ]);
    $availability = !empty($boat['id_mmk'])
      ? $this->mmk->getBoatAvailability($boat['id_mmk'], $years)
      : $this->ba->getBoatAvailability($boat['slug'], $years);
    $isAvailable = $this->testAvailabilityString($availability, $checkIn, $checkOut);
    return ['available' => $isAvailable ];
  }

  private function testAvailabilityString($av, $checkIn, $checkOut) {
    $dayFrom = intval($checkIn->format('z'));
    $daysDiff = $checkOut->diff($checkIn)->days;
    $regExp = sprintf('/^[01]{%d}0{%d}/', $dayFrom, $daysDiff);
    return preg_match($regExp, $av) === 1;
  }

  public function getPrice($id, $checkIn, $checkOut) {
    $availability = $this->getAvailability($id, $checkIn, $checkOut);
    if ($availability === false) {
      return false;
    } elseif (!$availability['available']) {
      return ['prices' => []];
    }
    $boat = $this->boatRepository->getById($id);
    $prices = !empty($boat['id_mmk'])
      ? $this->mmk->getBoatPrice($boat['id_mmk'], $checkIn, $checkOut)
      : $this->ba->getBoatPrice($boat['slug'], $checkIn, $checkOut);
    return ['prices' => $prices];
  }

  public function getByFilter($filterSource) {
    $filterKeys = [
      'baseId', 'countryId', 'sailingAreaId', 'regionId', 'shipyardId', 'kind',
      'companyId', 'mainsailType', 'genoaType'
    ];
    $integerKeys = [
      'limit', 'page', 'yearFrom', 'yearTo', 'wcFrom', 'wcTo', 'berthsFrom', 'berthsTo', 'cabinsFrom', 'cabinsTo'
    ];
    $floatKeys = [
      'draughtFrom', 'draughtTo', 'beamFrom', 'beamTo', 'lengthFrom', 'lengthTo', 'waterCapacityFrom',
      'waterCapacityTo', 'fuelCapacityFrom', 'fuelCapacityTo', 'priceFrom', 'priceTo'
    ];
    $filter = [
      'limit' => 10,
      'offset' => 0
    ];
    foreach ($filterKeys as $filterKey) {
      if (!$filterSource[$filterKey]) continue;
      $filter[$filterKey] = $filterSource[$filterKey];
    }
    foreach ($integerKeys as $filterKey) {
      if (!is_numeric($filterSource[$filterKey])) continue;
      $filter[$filterKey] = intval($filterSource[$filterKey]);
    }
    foreach ($floatKeys as $filterKey) {
      if (!is_numeric($filterSource[$filterKey])) continue;
      $filter[$filterKey] = floatval($filterSource[$filterKey]);
    }
    if (isset($filter['page'])) {
      $filter['offset'] = $filter['limit'] * (max($filter['page'], 1) - 1);
    }
    return $this->boatRepository->getByFilter($filter);
  }
}