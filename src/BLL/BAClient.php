<?php

namespace BLL;

use DAL\Interfaces\IConfig;

class BAClient extends APIClient {

  public function __construct(IConfig $config) {
    $this->baseUrl = $config->get('baBaseUrl');
  }

  public function getCountries() {
    return $this->getLocations('country');
  }

  public function getBases() {
    return $this->getLocations('marina');
  }

  private function getLocations($type) {
    $res = $this->query('/locations/getAll?locationType=' . $type);
    return $res['data'];
  }

  public function getShipyards() {
    $res = $this->query('/manufacturers/getAll');
    return $res['data'];
  }

  public function getCompanies() {
    $res = $this->query('/charters/getAll');
    return $res['data'];
  }

  public function getBoats($page = 0, $limit = 50) {
    $res = $this->query("/search?limit=$limit&page=$page");
    return $res['data'][0]['data'];
  }

  public function getEquipment() {
    return [
      /** Cockpit - Boat interior */
      'Air condition', 'Cooker', 'Dishwasher', 'Freezer', 'Grill', 'Heating', 'Kitchen utensils', 'Microwave',
      'Oven', 'Pillows and blankets', 'Refrigerator', 'Shower', 'Sink', 'Towels', 'Coffee machine','Ice maker',
      'Washing machine',
      /** Entertainment - Boat entertainment */
      'Inside cockpit speakers', 'Karaoke', 'Kayak', 'LCD TV', 'Outside deck speakers', 'Radio CD / MP3 player',
      'Snorkel sets', 'Water skis', 'Wakeboard', 'Bathing platform', 'Stand Up Paddle', 'Bicycle', 'Jet ski',
      'Game console', 'DVD Player',
      /** Equipment - Navigation and safety */
      'Bow thruster', 'Solar panels', 'Bimini', 'Cockpit GPS autopilot', 'Deck GPS autopilot', 'Dinghy', 'Spinnaker',
      'Teak deck', 'Jacuzzi', 'Generator', 'Electric winches', 'Flybridge', 'Radar', 'Inverter', 'Autopilot',
    ];
  }
}