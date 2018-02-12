<?php

if (!defined('PARENT') || $MSTEAM->profile == 'no') {
  $HEADERS->err403(true);
}

// If global user, we should be on the main edit screen..
if ($MSTEAM->id == '1') {
  header("Location: index.php?p=team&edit=1");
  exit;
}

$title = $msg_adheader64;

include(PATH . 'templates/header.php');
include(PATH . 'templates/system/team/team-profile.php');
include(PATH . 'templates/footer.php');

?>