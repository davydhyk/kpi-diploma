<?php

namespace BLL\Integration;

use BLL\MMKClient;
use DAL\Repository\SailingAreaRepository;

class SailingAreaIntegration {
  private $mmk;
  private $sailingAreaRepository;

  public function __construct(MMKClient $mmk, SailingAreaRepository $sailingAreaRepository) {
    $this->mmk = $mmk;
    $this->sailingAreaRepository = $sailingAreaRepository;
  }

  public function process() {
    $hashMap = $this->sailingAreaRepository->getIdHashMap();
    $sailingAreas = $this->mmk->getSailingAreas();
    foreach ($sailingAreas as $sailingArea) {
      $id = $sailingArea['id'];
      $hash = md5(json_encode($sailingArea));
      if (!isset($hashMap[$id])) {
        $this->sailingAreaRepository->insert($sailingArea);
      } else if ($hashMap[$id] !== $hash) {
        $this->sailingAreaRepository->update($sailingArea);
      }
    }
  }
}