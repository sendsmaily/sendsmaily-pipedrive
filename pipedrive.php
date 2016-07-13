<?php
$limit = 10000;
$smly_username = '';
$smly_password = '';
$smly_client = '';
$pipedrive_key = '';

require_once 'smly_requests.php';
$smly = new smly($smly_username, $smly_password, $smly_client);

require_once 'pipedrive_requests.php';
$pipedrive = new Pipedrive($pipedrive_key);

$offset = 0;
$list = array();
while (true) {
  $list = $pipedrive->curl_get('persons', array(
    'start' => $offset * $limit,
    'limit' => $limit,
  ));
  if (count($list['data']) === 0) {
    break;
  }

  $query = array();
  foreach ($list['data'] as $person) {
    if (isset($person['email'][0]) && !empty($person['email'][0]['value'])) {
      // print_r($person); exit;
      $query[] = array(
        'email' => $person['email'][0]['value'],
        // ...
      );
    }
  }

  if (!empty($query)) {
    $smly->curl_post('contact.php', $query);
    $query = array();
  }

  $query = NULL;
  $offset++;
}
