<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Define loader..
define('API_LOADER', 1);

// Check if API is enabled..
$apiOpts = ($SETTINGS->apiHandlers ? explode(',', $SETTINGS->apiHandlers) : array());
if ($SETTINGS->apiKey == '' || !in_array('xml', $apiOpts) && !in_array('json', $apiOpts)) {
  die('API not enabled. Enable JSON and/or XML handlers in settings and set API Key.');
}

// Load API files..
include(PATH . 'control/classes/class.api.php');
include(PATH . 'control/classes/class.upload.php');
$MSUPL           = new msUpload();
$MSAPI           = new msAPI();
$MSAPI->settings = $SETTINGS;
$MSAPI->datetime = $MSDT;
$MSAPI->upload   = $MSUPL;

// Incoming data..
// Determine handler..
// Read data..
include(PATH . 'control/system/api/lib.php');

if (!isset($data)) {
  $HEADERS->err403();
}

$MSAPI->log('Incoming data received:{nl}{nl}' . $data);
$MSAPI->handler = $MSAPI->getHandler($data);
$MSAPI->allowed = $apiOpts;
$read           = $MSAPI->read($data);

if (!empty($read)) {
  // Determine ops..
  $ops = $MSAPI->ops($read);
  // Check key..
  if (isset($ops['key']) && $ops['key'] == $SETTINGS->apiKey) {
    $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Key successfully checked and authenticated');
    // Run operation...
    switch ($ops['op']) {
      // Create ticket..
      case 'ticket':
        $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Preparing to add new tickets');
        include(PATH . 'control/system/api/create-tickets.php');
        break;
      // Create account..
      case 'account':
        $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Preparing to add new accounts');
        include(PATH . 'control/system/api/create-accounts.php');
        break;
      // Something else?
      default:
        $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Invalid operation: ' . $ops['op'] . ' is not supported');
        break;
    }
  } else {
    $MSAPI->log('[' . strtoupper($MSAPI->handler) . '] Invalid API key');
  }
}

// If we are, there wasn`t anything..
$MSAPI->response('ERROR', 'Nothing updated, check data or view log if enabled.');

?>