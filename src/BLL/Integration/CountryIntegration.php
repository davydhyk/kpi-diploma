<?php

namespace BLL\Integration;

use BLL\BAClient;
use BLL\MMKClient;
use DAL\Repository\CountryRepository;

class CountryIntegration {
  private MMKClient $mmk;
  private BAClient $ba;
  private CountryRepository $countryRepository;

  public function __construct(MMKClient $mmk, BAClient $ba, CountryRepository $countryRepository) {
    $this->mmk = $mmk;
    $this->ba = $ba;
    $this->countryRepository = $countryRepository;
  }

  private function processMMK() {
    $mmkIdMap = $this->countryRepository->getMap('id_mmk', 'id');
    $baIdMap = $this->countryRepository->getMap('id', 'id_ba');
    $countries = $this->mmk->getCountries();
    foreach ($countries as $country) {
      $mmkId = $country['id'];
      unset($country['id']);
      $country['id_mmk'] = $mmkId;
      if (!isset($mmkIdMap[$mmkId])) {
        $this->countryRepository->insert($country);
      } else {
        $id = $mmkIdMap[$mmkId];
        $country['id'] = $id;
        if (isset($baIdMap[$id])) {
          $country['id_ba'] = $baIdMap[$id];
        }
        $this->countryRepository->update($country);
      }
    }
  }

  private function processBA() {
    $baToIdMap = $this->countryRepository->getMap('id_ba', 'id');
    $countries = $this->ba->getCountries();
    foreach ($countries as $country) {
      $baId = $country['_id'];
      if (isset($baToIdMap[$baId])) continue;
      $existCountry = $this->countryRepository->getByName($country['name']);
      if ($existCountry !== false) {
        $existCountry['id_ba'] = $baId;
        $this->countryRepository->update($existCountry);
      }
    }
  }

  public function process() {
    $this->processMMK();
    $this->processBA();
  }
}