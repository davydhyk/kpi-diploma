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

  public function getBoatAvailability(string $slug, $years) {
    $res = $this->query('/availability/' . $slug);
    $availability = '';
    foreach ($years as $year) {
      $availability .= str_repeat('0', cal_days_in_year($year));
    }
    if (isset($res['data'][0]['bad_content']) && $res['data'][0]['bad_content']) {
      $availability = '';
      foreach ($years as $year) {
        $availability .= str_repeat('1', cal_days_in_year($year));
      }
      return $availability;
    }
    if (empty($res['data'][0]['availabilities'])) {
      return $availability;
    }
    $avs = $res['data'][0]['availabilities'];
    foreach ($avs as $av) {
      $checkIn = \DateTime::createFromFormat('Y-m-d', $av['chin']);
      $checkOut = \DateTime::createFromFormat('Y-m-d', $av['chout']);
      $dayFrom = intval($checkIn->format('z'));
      $daysDiff = $checkOut->diff($checkIn)->days;
      $regExp = sprintf('/^([01]{%d})([01]{%d})(.*)/', $dayFrom, $daysDiff);
      $replacement = '${1}' . str_repeat('1', $daysDiff) . '${3}';
      $availability = preg_replace($regExp, $replacement, $availability);
    }
    return $availability;
  }

  public function getBoatPrice(string $slug, $checkIn, $checkOut) {
    $query = [
      'checkIn' => $checkIn->format('Y-m-d'),
      'checkOut' => $checkOut->format('Y-m-d')
    ];
    $reqUrl = sprintf('/price/%s?%s', $slug, http_build_query($query));
    $res = $this->query($reqUrl);
    $prices = [];
    if (empty($res['data'][0]['data'][0])) {
      return $prices;
    }
    $pricesSource = $res['data'][0]['data'];
    foreach ($pricesSource as $p) {
      $prices[] = [
        'price' => $p['totalPrice'],
        'startPrice' => $p['price'],
        'discountPercentage' => $p['discount'],
        'currency' => $p['currency']
      ];
    }
    return $prices;
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