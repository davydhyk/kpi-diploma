<?php

namespace BLL\Services;

use DAL\Repository\SailingAreaRepository;

class SailingAreaService {

  private SailingAreaRepository $sailingAreaRepository;

  public function __construct(SailingAreaRepository $sailingAreaRepository) {
    $this->sailingAreaRepository = $sailingAreaRepository;
  }

  public function getById($id) {
    return $this->sailingAreaRepository->getById($id);
  }

  public function getAll() {
    return $this->sailingAreaRepository->getAll();
  }
}