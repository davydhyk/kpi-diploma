<?php

namespace Controllers;

use BLL\Services\SailingAreaService;

class SailingAreaController {

  private $sailingAreaService;

  public function __construct(SailingAreaService $sailingAreaService) {
    $this->sailingAreaService = $sailingAreaService;
  }

  public function getById($params) {
    $id = $params['id'];
    $sailingArea = $this->sailingAreaService->getById($id);
    if ($sailingArea === false) {
      return ['status' => 404];
    }
    return [
      'data' => $sailingArea
    ];
  }

  public function getAll() {
    $sailingAreas = $this->sailingAreaService->getAll();
    return [
      'data' => $sailingAreas
    ];
  }
}