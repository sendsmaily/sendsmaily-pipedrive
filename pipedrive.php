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


/**
 * Helper method for formatting debug output.
 *
 * @return void
 */
function debug_log() {
  $dt = (new DateTime())->format(DateTime::ATOM);

  $args = func_get_args();
  $formatted = call_user_func_array('sprintf', $args);

  printf('[%s] %s' . PHP_EOL, $dt, $formatted);
}


$startTime = microtime(TRUE);

$offset = 0;
$list = array();
while (true) {
  debug_log('Querying Pipedrive API at offset %s...', $offset);

  $list = $pipedrive->curl_get('persons', array(
    'start' => $offset * $limit,
    'limit' => $limit,
  ));

  if ($list['success'] === FALSE) {
    debug_log('Failed to query Pipedrive API with response: %s', json_encode($list));
    exit;
  }
  elseif (!empty($pipedrive->errors)) {
    debug_log('Pipedrive API returned following errors:');
    foreach ($pipedrive->errors as $error) {
      debug_log('  * %s', $error);
    }
    $pipedrive->errors = array();
    exit;
  }
  elseif (!is_array($list['data']) or count($list['data']) === 0) {
    debug_log('Pipedrive API did not return legible data. Stopping loop...');
    break;
  }

  $query = array();
  foreach ($list['data'] as $person) {
    if (isset($person['email'][0]) && !empty($person['email'][0]['value'])) {
      $query[] = array(
        'email' => $person['email'][0]['value'],
        // ...
      );
    }
  }

  if (!empty($query)) {
    debug_log('Collected email addresses of %d people. Pushing to Smaily...', count($query));
    $smly->curl_post('contact.php', $query);

    if (!empty($smly->errors)) {
      debug_log('Smaily API returned following errors:');
      foreach ($smly->errors as $error) {
        debug_log('  * %s', $error);
      }
      $smly->errors = array();
      exit;
    }
  }

  $query = NULL;
  $offset++;
}

debug_log('Finished in %f seconds', microtime(TRUE) - $startTime);
