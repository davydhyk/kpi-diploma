<?php

namespace BLL\Services;

use DAL\Repository\WorldRegionRepository;

class WorldRegionService {

  private WorldRegionRepository $worldRegionRepository;
  public function __construct(WorldRegionRepository $worldRegionRepository) {
    $this->worldRegionRepository = $worldRegionRepository;
  }

  public function getById($id) {
    return $this->worldRegionRepository->getById($id);
  }

  public function getAll() {
    return $this->worldRegionRepository->getAll();
  }
}