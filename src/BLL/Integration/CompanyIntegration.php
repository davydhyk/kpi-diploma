<?php

namespace BLL\Integration;

use BLL\BAClient;
use BLL\MMKClient;
use DAL\Repository\CompanyRepository;

class CompanyIntegration {
  private MMKClient $mmk;
  private BAClient $ba;
  private CompanyRepository $companyRepository;

  public function __construct(MMKClient $mmk, BAClient $ba, CompanyRepository $companyRepository) {
    $this->mmk = $mmk;
    $this->ba = $ba;
    $this->companyRepository = $companyRepository;
  }

  private function processMMK() {
    $mmkIdMap = $this->companyRepository->getMap('id_mmk', 'id');
    $baIdMap = $this->companyRepository->getMap('id', 'id_ba');
    $companies = $this->mmk->getCompanies();
    foreach ($companies as $company) {
      $mmkId = $company['id'];
      unset($company['id']);
      $company['id_mmk'] = $mmkId;
      $company['name'] = trim($company['name']);
      if (!isset($mmkIdMap[$mmkId])) {
        $this->companyRepository->insert($company);
      } else {
        $id = $mmkIdMap[$mmkId];
        $company['id'] = $id;
        if (isset($baIdMap[$id])) {
          $company['id_ba'] = $baIdMap[$id];
        }
        $this->companyRepository->update($company);
      }
    }
  }

  private function processBA() {
    $baToIdMap = $this->companyRepository->getMap('id_ba', 'id');
    $companies = $this->ba->getCompanies();
    foreach ($companies as $company) {
      $baId = $company['_id'];
      if (isset($baToIdMap[$baId])) continue;
      $existCompany = $this->companyRepository->getByName($company['name']);
      if ($existCompany !== false) {
        $existCompany['id_ba'] = $baId;
        $this->companyRepository->update($existCompany);
      } else {
        $this->companyRepository->insert([
          'id_ba' => $baId,
          'name' => $company['name']
        ]);
      }
    }
  }

  public function process() {
    $this->processMMK();
    $this->processBA();
  }
}