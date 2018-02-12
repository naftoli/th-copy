<?php

switch ($cmd) {
  // Main screen..
  case 'home':
    include(PATH . 'control/system/main.php');
    break;
  // Account system..
  case 'history':
  case 'profile':
  case 'dashboard':
  case 'login':
  case 'disputes':
  case 'search-tickets':
  case 'tk-msg':
    include(PATH . 'control/system/accounts/account-' . ($cmd == 'tk-msg' ? 'ticket-create-msg' : $cmd) . '.php');
    break;
  // View ticket/dispute..
  case 'ticket':
  case 'dispute':
    include(PATH . 'control/system/accounts/account-view-' . $cmd . '.php');
    break;
  // Create account..
  case 'create':
    include(PATH . 'control/system/create-account.php');
    break;
  // Open new ticket..
  case 'open':
    include(PATH . 'control/system/create-ticket.php');
    break;
  // FAQ system..
  case 'faq':
  case 'que';
  case 'search':
    include(PATH . 'control/system/faq/faq-' . ($cmd == 'que' ? 'question' : ($cmd == 'search' ? 'search' : 'cat')) . '.php');
    break;
  // Custom Page..
  case 'custom-page':
    include(PATH . 'control/system/custom-page.php');
    break;
  // API..
  case 'xml':
  case 'api':
    include(PATH . 'control/system/api.php');
    break;
  // Imap..
  case $SETTINGS->imap_param:
    include(PATH . 'control/system/imap.php');
    break;
  // Ajax routines..
  case 'ajax':
    include(PATH . 'control/system/ajax-handler.php');
    break;
  // Default..
  default:
    $HEADERS->err404();
    break;
}

?>