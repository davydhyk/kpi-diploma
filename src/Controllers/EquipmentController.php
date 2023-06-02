<?php

namespace Controllers;

use BLL\Services\EquipmentService;

class EquipmentController {

  private EquipmentService $equipmentService;

  public function __construct(EquipmentService $equipmentService) {
    $this->equipmentService = $equipmentService;
  }

  public function getById($params) {
    $id = $params['id'];
    $equipment = $this->equipmentService->getById($id);
    if ($equipment === false) {
      return ['status' => 404];
    }
    return ['data' => $equipment];
  }

  public function getAll() {
    $equipment = $this->equipmentService->getAll();
    return [
      'data' => $equipment
    ];
  }

}