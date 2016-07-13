<?php

class pipedrive
{
  private $apikey;

  public $errors = array();

  public function __construct($apikey) {
    $this->apikey = $apikey;
  }

  public function curl_get($url, $query = array()) {
    $query = http_build_query(array_merge($query, array('api_token' => $this->apikey)));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.pipedrive.com/v1/' . $url . '?' . $query);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($ch);
    if ($result === false) {
      $this->_error(curl_error($ch));
    }
    curl_close($ch);

    return $this->_process_request($result);
  }

  private function _process_request($curl_result) {
    return json_decode($curl_result, true);
  }

  private function _error($msg) {
    $this->errors[] = 'Pipedrive - ' . date('d.m.Y H:i:s') . ': ' . $msg;
  }
}
