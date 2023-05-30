<?php

namespace BLL\Integration;

use BLL\BAClient;
use BLL\MMKClient;
use DAL\Repository\BaseRepository;
use DAL\Repository\CountryRepository;

class BaseIntegration {
  private MMKClient $mmk;
  private BAClient $ba;
  private BaseRepository $baseRepository;
  private CountryRepository $countryRepository;

  public function __construct(MMKClient $mmk, BAClient $ba, BaseRepository $baseRepository, CountryRepository $countryRepository) {
    $this->mmk = $mmk;
    $this->ba = $ba;
    $this->countryRepository = $countryRepository;
    $this->baseRepository = $baseRepository;
  }

  private function processMMK() {
    $countryIdMap = $this->countryRepository->getMap('id_mmk', 'id');
    $mmkIdMap = $this->baseRepository->getMap('id_mmk', 'id');
    $baIdMap = $this->baseRepository->getMap('id', 'id_ba');
    $bases = $this->mmk->getBases();
    foreach ($bases as $base) {
      $mmkId = $base['id'];
      unset($base['id']);
      if ($mmkId === 0) continue;
      $base['id_mmk'] = $mmkId;
      $base['countryId'] = $countryIdMap[$base['countryId']];
      if (!isset($mmkIdMap[$mmkId])) {
        $this->baseRepository->insert($base);
      } else {
        $id = $mmkIdMap[$mmkId];
        $base['id'] = $id;
        if (isset($baIdMap[$id])) {
          $base['id_ba'] = $baIdMap[$id];
        }
        $this->baseRepository->update($base);
      }
    }
  }

  private function processBA() {
    $nameIdMap = $this->baseRepository->getMap('name', 'id');
    $baIdMap = $this->baseRepository->getMap('id_ba', 'id');
    $bases = $this->ba->getBases();
    dbg(count($bases));
    foreach ($bases as $base) {
      $nearBases = $this->baseRepository->getNearByCoordinates(44.054513, 15.296036);
      foreach ($nearBases as $nearBase) {
        $similarity =
        similar_text('D-Marin Marina Dalmacija', $nearBase['name'], );
        dbg($similarity, $nearBase);
      }
      break;
      if (!isset($nameIdMap[$base['name']])) {
        dbg($base);
      }
    }
  }

  public function process() {
    $this->processMMK();
//    $this->processBA();
  }
}