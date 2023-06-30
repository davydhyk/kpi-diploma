<?php

namespace Controllers;

use BLL\Services\BoatService;

class BoatController {

  private BoatService $boatService;

  public function __construct(BoatService $boatService) {
    $this->boatService = $boatService;
  }

  public function getById($params) {
    $id = $params['id'];
    $boat = $this->boatService->getById($id);
    if ($boat === false) {
      return ['status' => 404];
    }
    return ['data' => $boat];
  }

  public function getAvailability($params) {
    $id = $params['id'];
    $dates = $this->processDatesParams();
    if ($dates === false) {
      return ['status' => 400];
    }
    [$checkIn, $checkOut] = $dates;
    $availability = $this->boatService->getAvailability($id, $checkIn, $checkOut);
    if ($availability === false) {
      return ['status' => 404];
    }
    return [
      'data' => $availability
    ];
  }

  public function getPrice($params) {
    $id = $params['id'];
    $dates = $this->processDatesParams();
    if ($dates === false) {
      return ['status' => 400];
    }
    [$checkIn, $checkOut] = $dates;
    $availability = $this->boatService->getPrice($id, $checkIn, $checkOut);
    if ($availability === false) {
      return ['status' => 404];
    }
    return [
      'data' => $availability
    ];
  }

  private function processDatesParams() {
    if (empty($_GET['checkIn']) || empty($_GET['checkOut'])) {
      return false;
    }
    $dateTimeFormat = 'Y-m-d';
    $checkIn = \DateTime::createFromFormat($dateTimeFormat, $_GET['checkIn']);
    $checkOut = \DateTime::createFromFormat($dateTimeFormat, $_GET['checkOut']);
    if ($checkIn === false || $checkOut === false || $checkOut <= $checkIn) {
      return false;
    }
    return [$checkIn, $checkOut];
  }

  public function getByFilter() {
    $filterKeys = [
      'limit', 'page', 'baseId', 'countryId', 'sailingAreaId', 'regionId', 'shipyardId',
      'yearFrom', 'yearTo', 'kind', 'companyId', 'draughtFrom', 'draughtTo', 'beamFrom', 'beamTo',
      'lengthFrom', 'lengthTo', 'waterCapacityFrom', 'waterCapacityTo', 'fuelCapacityFrom', 'fuelCapacityTo',
      'priceFrom', 'priceTo', 'wcFrom', 'wcTo', 'berthsFrom', 'berthsTo', 'cabinsFrom', 'cabinsTo',
      'mainsailType', 'genoaType'
    ];
    $filter = [];
    foreach ($filterKeys as $filterKey) {
      $filter[$filterKey] = $_GET[$filterKey] ?? null;
    }
    $boats = $this->boatService->getByFilter($filter);
    return [
      'data' => $boats
    ];
  }
}