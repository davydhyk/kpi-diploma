<?php

namespace BLL\Integration;

use BLL\MMKClient;
use DAL\Repository\WorldRegionRepository;

class WorldRegionIntegration {
  private MMKClient $mmk;
  private WorldRegionRepository $worldRegionRepository;

  public function __construct(MMKClient $mmk, WorldRegionRepository $worldRegionRepository) {
    $this->mmk = $mmk;
    $this->worldRegionRepository = $worldRegionRepository;
  }

  public function process() {
    $hashMap = $this->worldRegionRepository->getIdHashMap();
    $worldRegions = $this->mmk->getWorldRegions();
    foreach ($worldRegions as $worldRegion) {
      $id = $worldRegion['id'];
      /** broken entity with id 17 */
      if ($id === 17) continue;
      $hash = md5(json_encode($worldRegion));
      if (!isset($hashMap[$id])) {
        $this->worldRegionRepository->insert($worldRegion);
      } else if ($hashMap[$id] !== $hash) {
        $this->worldRegionRepository->update($worldRegion);
      }
    }
  }
}