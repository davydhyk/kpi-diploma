<?php

namespace BLL;

use DAL\Interfaces\IConfig;

abstract class APIClient {

  protected IConfig $config;
  protected string $baseUrl;
  protected array $headers = [];

  protected function query($path) {
    $curl = curl_init($this->baseUrl . $path);
    curl_setopt_array($curl, array(
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HTTPHEADER => $this->headers,
      // CURLOPT_VERBOSE => 1,
      // CURLOPT_STDERR => $curl_err_file
    ));
    $res = curl_exec($curl);
    curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    return json_decode($res, true);
  }
}