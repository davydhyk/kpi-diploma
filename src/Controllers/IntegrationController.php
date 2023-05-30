<?php

namespace Controllers;

use DAL\Interfaces\IConfig;

class IntegrationController {

  private $config;

  public function __construct(IConfig $config) {
    $this->config = $config;
  }

  public function aggregate() {
//    $password = $this->config->get('integrationPassword');
//    if (empty($_GET['password']) || md5($_GET['password']) !== $password) {
//      return ['status' => 403];
//    }

    $integrations = [
//      \BLL\Integration\WorldRegionIntegration::class,
//      \BLL\Integration\CountryIntegration::class,
//      \BLL\Integration\SailingAreaIntegration::class,
//      \BLL\Integration\BaseIntegration::class,
//      \BLL\Integration\ShipyardIntegration::class,
//      \BLL\Integration\EquipmentIntegration::class,
//      \BLL\Integration\CompanyIntegration::class,
      \BLL\Integration\BoatIntegration::class
    ];

    global $container;
    set_time_limit(0);

    $results = [];
    foreach ($integrations as $integrationClass) {
      $start = microtime(true);
      $integration = $container->get($integrationClass);
      $integration->process();
      $results[$integrationClass] = microtime(true) - $start;
    }
    return [
      'data' => $results
    ];
  }
}