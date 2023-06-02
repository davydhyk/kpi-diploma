<?php

namespace Controllers;

use BLL\Services\CountryService;

class CountryController {

  private CountryService $countryService;

  public function __construct(CountryService $countryService) {
    $this->countryService = $countryService;
  }

  public function getById($params) {
    $id = $params['id'];
    $country = $this->countryService->getById($id);
    if ($country === false) {
      return ['status' => 404];
    } else {
      return [
        'data' => $country
      ];
    }
  }

  public function getByFilter() {
    $worldRegionId = !empty($_GET['worldRegionId']) ? $_GET['worldRegionId'] : false;
    $countries = $this->countryService->getByFilter([
      'worldRegionId' => $worldRegionId
    ]);
    return [
      'data' => $countries
    ];
  }
}