<?php

namespace Controllers;

use BLL\Services\WorldRegionService;

class WorldRegionController {

  private WorldRegionService $worldRegionsService;
  public function __construct(WorldRegionService $worldRegionService) {
    $this->worldRegionsService = $worldRegionService;
  }

  public function getById($params) {
    $id = $params['id'];
    $worldRegion = $this->worldRegionsService->getById($id);
    if ($worldRegion === false) {
      return ['status' => 404];
    }
    return [
      'data' => $worldRegion
    ];
  }

  public function getAll() {
    $worldRegions = $this->worldRegionsService->getAll();
    return [
      'data' => $worldRegions
    ];
  }
}