<?php

namespace Controllers;

use BLL\Services\BaseService;

class BaseController {

  private $baseService;

  public function __construct(BaseService $baseService) {
    $this->baseService = $baseService;
  }

  public function getById($params) {
    $id = $params['id'];
    $base = $this->baseService->getById($id);
    if ($base === false) {
      return ['status' => 404];
    }
    return ['data' => $base];
  }

  public function getByFilter() {
    $filter = [
      'worldRegionId' => !empty($_GET['worldRegionId']) ? $_GET['worldRegionId'] : false,
      'sailingAreaId' => !empty($_GET['sailingAreaId']) ? $_GET['sailingAreaId'] : false,
      'countryId' => !empty($_GET['countryId']) ? $_GET['countryId'] : false,
    ];
    $bases = $this->baseService->getByFilter($filter);
    return [
      'data' => $bases
    ];
  }
}