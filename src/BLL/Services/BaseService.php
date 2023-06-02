<?php

namespace BLL\Services;

use DAL\Repository\BaseRepository;

class BaseService {

  private BaseRepository $baseRepository;

  public function __construct(BaseRepository $baseRepository) {
    $this->baseRepository = $baseRepository;
  }

  public function getById($id) {
    return $this->baseRepository->getById($id);
  }

  public function getByFilter($filter) {
    $bases = [];
    if ($filter['countryId'] !== false) {
      $bases = $this->baseRepository->getByCountryId($filter['countryId']);
    } else if ($filter['sailingAreaId'] !== false) {
      $bases = $this->baseRepository->getBySailingAreaId($filter['sailingAreaId']);
    } else if ($filter['worldRegionId'] !== false) {
      $bases = $this->baseRepository->getByWorldRegionId($filter['worldRegionId']);
    } else {
      $bases = $this->baseRepository->getAll();
    }
    return $bases;
  }
}