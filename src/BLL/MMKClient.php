<?php

namespace BLL;

use DAL\Interfaces\IConfig;

class MMKClient extends APIClient {

  public function __construct(IConfig $config) {
    $this->headers[] = 'Authorization: Bearer ' . $config->get('mmkToken');
    $this->baseUrl = $config->get('mmkBaseUrl');
  }

  public function getWorldRegions() {
    return $this->query('/worldRegions');
  }

  public function getWorldRegion(int $id) {
    return $this->query('/worldRegion/' . $id);
  }

  public function getCountries() {
    return $this->query('/countries');
  }

  public function getCountry(int $id) {
    return $this->query('/country/' . $id);
  }

  public function getSailingAreas() {
    return $this->query('/sailingAreas');
  }

  public function getSailingArea(int $id) {
    return $this->query('/sailingArea/' . $id);
  }

  public function getBases() {
    return $this->query('/bases');
  }

  public function getBase(int $id) {
    return $this->query('/base/' . $id);
  }

  public function getShipyards() {
    return $this->query('/shipyards');
  }

  public function getShipyard(int $id) {
    return $this->query('/shipyard/' . $id);
  }

  public function getEquipment() {
    return $this->query('/equipment');
  }

  public function getCompanies() {
    return $this->query('/companies');
  }

  public function getCompany(int $id) {
    return $this->query('/company/' . $id);
  }

  public function getBoats(int $companyId) {
    return $this->query('/yachts?companyId=' . $companyId);
  }

  public function getBoat(int $boatId) {
    return $this->query('/yacht/' . $boatId);
  }

  public function getBoatAvailability(int $boatId, $years) {
    $availability = '';
    foreach ($years as $year) {
      $av = $this->query('/shortAvailability/' . $year . '?yachtId=' . $boatId);
      if (empty($av[0]['bs'])) {
        $availability .= str_repeat('0', cal_days_in_year($year));
      } else {
        $availability .= $av[0]['bs'];
      }
    }
    return $availability;
  }

  public function getBoatPrice(int $boatId, $checkIn, $checkOut) {
    $query = [
      'dateFrom' => $checkIn->format('Y-m-d\T00:00:00'),
      'dateTo' => $checkOut->format('Y-m-d\T00:00:00'),
      'yachtId' => $boatId
    ];
    $prices = $this->query('/prices?' . http_build_query($query));
    foreach ($prices as &$price) {
      unset($price['yachtId'], $price['dateFrom'], $price['dateTo']);
    }
    return $prices;
  }
}