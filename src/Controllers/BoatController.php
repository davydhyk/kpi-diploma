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
    $base = $this->boatService->getById($id);
    if ($base === false) {
      return ['status' => 404];
    }
    return ['data' => $base];
  }

  public function getByFilter() {
    $filter = [];
    $filter['limit'] = intval($_GET['limit'] ?? 10);
    $filter['page'] = intval($_GET['page'] ?? 1);
    $filter['countryId'] = $_GET['countryId'] ?? null;
    $filter['baseId'] = $_GET['baseId'] ?? null;
    $filter['shipyardId'] = $_GET['shipyardId'] ?? null;
    $bases = $this->boatService->getByFilter($filter);
    return [
      'data' => $bases
    ];
  }
}