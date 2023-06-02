<?php

namespace BLL\Integration;

use BLL\BAClient;
use BLL\MMKClient;
use DAL\Repository\ShipyardRepository;

class ShipyardIntegration {
  private MMKClient $mmk;
  private BAClient $ba;
  private ShipyardRepository $shipyardRepository;

  public function __construct(MMKClient $mmk, BAClient $ba, ShipyardRepository $shipyardRepository) {
    $this->mmk = $mmk;
    $this->ba = $ba;
    $this->shipyardRepository = $shipyardRepository;
  }

  private function processMMK() {
    $mmkIdMap = $this->shipyardRepository->getMap('id_mmk', 'id');
    $baIdMap = $this->shipyardRepository->getMap('id', 'id_ba');
    $shipyards = $this->mmk->getShipyards();
    foreach ($shipyards as $shipyard) {
      $mmkId = $shipyard['id'];
      unset($shipyard['id']);
      $shipyard['id_mmk'] = $mmkId;
      if (!isset($mmkIdMap[$mmkId])) {
        $this->shipyardRepository->insert($shipyard);
      } else {
        $id = $mmkIdMap[$mmkId];
        $shipyard['id'] = $id;
        if (isset($baIdMap[$id])) {
          $shipyard['id_ba'] = $baIdMap[$id];
        }
        $this->shipyardRepository->update($shipyard);
      }
    }
  }

  private function processBA() {
    $baToIdMap = $this->shipyardRepository->getMap('id_ba', 'id');
    $shipyards = $this->ba->getShipyards();
    foreach ($shipyards as $shipyard) {
      $baId = $shipyard['_id'];
      if (isset($baToIdMap[$baId])) continue;
      $existShipyard = $this->shipyardRepository->getByName($shipyard['name']);
      if ($existShipyard !== false) {
        $existShipyard['id_ba'] = $baId;
        $this->shipyardRepository->update($existShipyard);
      } else {
        $this->shipyardRepository->insert([
          'id_ba' => $baId,
          'name' => $shipyard['name']
        ]);
      }
    }
  }

  public function process() {
//    $this->processMMK();
    $this->processBA();
  }
}