<?php

namespace BLL\Services;

use DAL\Repository\CountryRepository;

class CountryService {

  private CountryRepository $countryRepository;
  public function __construct(CountryRepository $countryRepository) {
    $this->countryRepository = $countryRepository;
  }

  public function getById($id) {
    return $this->countryRepository->getById($id);
  }

  public function getByFilter($filter) {
    $countries = [];
    if ($filter['worldRegionId'] !== false) {
      $countries = $this->countryRepository->getByWorldRegionId($filter['worldRegionId']);
    } else {
      $countries = $this->countryRepository->getAll();
    }
    return $countries;
  }

}