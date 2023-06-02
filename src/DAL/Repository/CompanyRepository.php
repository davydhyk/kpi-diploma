<?php

namespace DAL\Repository;

use DAL\Interfaces\IDBContext;

class CompanyRepository {
  private $db;

  public function __construct(IDBContext $db) {
    $this->db = $db;
  }

  public function getByName($name) {
    $query = $this->db->prepare("
      SELECT * FROM companies
      WHERE name = :name
    ");
    $query->execute(['name' => $name]);
    return $query->fetch(\PDO::FETCH_ASSOC);
  }

  public function getAllIds() {
    $results = $this->db->query("SELECT id FROM companies");
    return $results->fetchAll(\PDO::FETCH_COLUMN);
  }

  public function insert($company) {
    $query = $this->db->prepare("
      INSERT INTO companies (id_mmk, id_ba, name, city, country, telephone, telephone2, mobile, vatCode, email, web, bankAccountNumber, termsAndConditions, checkoutNote)
      VALUES (:id_mmk, :id_ba, :name, :city, :country, :telephone, :telephone2, :mobile, :vatCode, :email, :web, :bankAccountNumber, :termsAndConditions, :checkoutNote)
    ");
    $this->bindCompanyToParams($query, $company);
    return $query->execute();
  }

  public function update($company) {
    $company['hash'] = md5(json_encode($company));
    $query = $this->db->prepare("
      UPDATE companies
      SET id_mmk = :id_mmk,
          id_ba = :id_ba,
          name = :name,
          city = :city,
          country = :country,
          telephone = :telephone,
          telephone2 = :telephone2,
          mobile = :mobile,
          vatCode = :vatCode,
          email = :email,
          web = :web,
          bankAccountNumber = :bankAccountNumber,
          termsAndConditions = :termsAndConditions,
          checkoutNote = :checkoutNote
      WHERE id = :id
    ");
    $query->bindParam('id', $company['id'], \PDO::PARAM_INT);
    $this->bindCompanyToParams($query, $company);
    return $query->execute();
  }

  public function getMap($key, $value) {
    $results = $this->db->query("SELECT {$key}, {$value} FROM companies");
    return $results->fetchAll(\PDO::FETCH_KEY_PAIR);
  }

  private function bindCompanyToParams(&$query, $company) {
    $params = [
      'id_mmk', 'id_ba', 'name', 'city', 'country', 'telephone', 'telephone2', 'mobile', 'vatCode',
      'email', 'web', 'bankAccountNumber', 'termsAndConditions', 'checkoutNote'
    ];
    foreach ($params as $param) {
      $query->bindParam($param, $company[$param], !empty($company[$param]) ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
    }
  }
}