<?php

namespace BLL\Services;


use DAL\Repository\BoatRepository;

class BoatService {

  private BoatRepository $boatRepository;

  public function __construct(BoatRepository $boatRepository) {
    $this->boatRepository = $boatRepository;
  }

  public function getById($id) {
    return $this->boatRepository->getById($id);
  }

  public function getByFilter($filterSource) {
    $filter = [
      'offset' => 0,
      'limit' => 10,
    ];
    if (!empty($filterSource['limit'])) {
      $filter['offset'] = $filterSource['limit'];
    }
    if (!empty($filterSource['page'])) {
      $filter['offset'] = $filter['limit'] * $filterSource['page'];
    }
    if (!empty($filterSource['shipyardId'])) {
      $filter['shipyardId'] = $filterSource['shipyardId'];
    }
    if (!empty($filterSource['baseId'])) {
      $filter['baseId'] = $filterSource['baseId'];
    } else if (!empty($filterSource['countryId'])) {
      $filter['countryId'] = $filterSource['countryId'];
    }
    return $this->boatRepository->getByFilter($filter);
  }
}