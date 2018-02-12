<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// JS/CSS..
$ms_js_css_loader['bbcode']   = 'yes';
$attachRestrictions           = '';

// System messages..
if (isset($_GET['msg'])) {
  switch ($_GET['msg']) {
    case 'added':
      $ticketSystemMsg = $msg_public_ticket12;
      break;
    case 'replied':
      $ticketSystemMsg = $msg_showticket7;
      break;
  }
}

?>