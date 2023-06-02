<?php

namespace BLL\Integration;

use BLL\BAClient;
use BLL\MMKClient;
use DAL\Repository\EquipmentRepository;

class EquipmentIntegration {
  private MMKClient $mmk;
  private BAClient $ba;
  private EquipmentRepository $equipmentRepository;

  public function __construct(MMKClient $mmk, BAClient $ba, EquipmentRepository $equipmentRepository) {
    $this->mmk = $mmk;
    $this->ba = $ba;
    $this->equipmentRepository = $equipmentRepository;
  }

  private function processMMK() {
    $mmkIdMap = $this->equipmentRepository->getMap('id_mmk', 'id');
    $equipments = $this->mmk->getEquipment();
    foreach ($equipments as $equipment) {
      $id = $equipment['id'];
      $equipment['id_mmk'] = $id;
      if (!isset($mmkIdMap[$id])) {
        $this->equipmentRepository->insert($equipment);
      } else {
        $equipment['id'] = $mmkIdMap[$id];
        $this->equipmentRepository->update($equipment);
      }
    }
  }

  private function processBA() {
    $equipments = $this->ba->getEquipment();
    foreach ($equipments as $name) {
      $mapName = $this->equipmentMap[$name] ?? $name;
      $existEquipment = $this->equipmentRepository->getByName($mapName);
      if ($existEquipment === false) {
        $this->equipmentRepository->insert(['name_ba' => $name]);
      } else if (empty($existEquipment['name_ba'])) {
        $existEquipment['name_ba'] = $name;
        $this->equipmentRepository->update($existEquipment);
      }
    }
  }

  private array $equipmentMap = [
    'LCD TV' => 'TV',
    'Snorkel sets' => 'Snorkeling equipment',
    'Grill' => 'Barbecue grill in cockpit',
    'Inside cockpit speakers' => 'Cockpit speakers',
    'Radio CD / MP3 player' => 'Radio-CD player',
    'Coffee machine' => 'Coffee maker'
  ];

  public function process() {
    $this->processMMK();
    $this->processBA();
  }
}