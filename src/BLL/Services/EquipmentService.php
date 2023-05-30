<?php

namespace BLL\Services;

use DAL\Repository\EquipmentRepository;

class EquipmentService {

  private EquipmentRepository $equipmentRepository;

  public function __construct(EquipmentRepository $equipmentRepository) {
    $this->equipmentRepository = $equipmentRepository;
  }

  public function getById($id) {
    return $this->equipmentRepository->getById($id);
  }

  public function getAll() {
    return $this->equipmentRepository->getAll();
  }

}