<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Session vars..exist only on initial load..
$ID    = (isset($_SESSION['create']['id']) ? (int) $_SESSION['create']['id'] : '0');
$pass  = (isset($_SESSION['create']['pass']) ? $_SESSION['create']['pass'] : '');
$email = (isset($_SESSION['create']['email']) ? $_SESSION['create']['email'] : '');

if ($ID > 0) {

  $title = $msg_main2 . ' (' . $msg_public_ticket4 . ')';

  include(PATH . 'control/header.php');

  $tpl = new Savant3();
  $tpl->assign('TXT', array(
    $msg_public_ticket4,
    $msg_newticket13,
    str_replace(array(
      '{ticket}',
      '{ticket_long}'
    ), array(
      $ID,
      mswTicketNumber($ID)
    ), $msg_public_ticket5),
    $msg_public_ticket6
  ));
  $tpl->assign('ADD_TXT', ($pass ? str_replace(array(
    '{email}',
    '{pass}',
    '{url}'
  ), array(
    mswSafeDisplay($email),
    mswSafeDisplay($pass),
    $SETTINGS->scriptpath
  ), $msg_public_ticket7) : ''));
  $tpl->assign('ID', $ID);

  // Global vars..
  include(PATH . 'control/lib/global.php');

  $tpl->display('content/' . MS_TEMPLATE_SET . '/ticket-create-message.tpl.php');

  include(PATH . 'control/footer.php');

  // Reset session vars..
  $_SESSION['create'] = array();
  unset($_SESSION['create']);

} else {

  $HEADERS->err403();

}

?>