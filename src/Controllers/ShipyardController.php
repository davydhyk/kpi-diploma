<?php

namespace Controllers;

use BLL\Services\ShipyardService;

class ShipyardController {

  private ShipyardService $shipyardService;

  public function __construct(ShipyardService $shipyardService) {
    $this->shipyardService = $shipyardService;
  }

  public function getById($params) {
    $id = $params['id'];
    $shipyard = $this->shipyardService->getById($id);
    if ($shipyard === false) {
      return ['status' => 404];
    }
    return ['data' => $shipyard];
  }

  public function getAll() {
    $shipyards = $this->shipyardService->getAll();
    return ['data' => $shipyards];
  }

}