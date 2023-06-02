<?php

namespace BLL\Services;

use DAL\Repository\ShipyardRepository;

class ShipyardService {

  private ShipyardRepository $shipyardRepository;

  public function __construct(ShipyardRepository $shipyardRepository) {
    $this->shipyardRepository = $shipyardRepository;
  }

  public function getById($id) {
    return $this->shipyardRepository->getById($id);
  }

  public function getAll() {
    return $this->shipyardRepository->getAll();
  }
}