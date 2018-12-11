<?php if (!defined('PATH')) { exit; }

$msTopMenu = array();

if (LICENCE_VER == 'locked' || defined('LIC_DEV')) {
  $msTopMenu[] = array(
    'url' => 'index.php?p=purchase',
    'icon' => 'fa-shopping-cart',
    'text' => 'Purchase',
    'class' => 'hidden-sm hidden-xs',
    'ext' => 'no'
  );
}

$msTopMenu[] = array(
  'url' => 'index.php',
  'icon' => 'fa-dashboard',
  'text' => mswSafeDisplay($msg_adheader11),
  'class' => 'hidden-sm hidden-xs',
  'ext' => 'no'
);

if ($MSTEAM->mailbox == 'yes') {
  $msTopMenu[] = array(
    'url' => 'index.php?p=mailbox',
    'icon' => 'fa-envelope',
    'text' => mswSafeDisplay($msg_adheader61),
    'class' => 'hidden-sm hidden-xs',
    'ext' => 'no'
  );
}

if ($MSTEAM->helplink == 'yes') {
  $msTopMenu[] = array(
    'url' => '../docs/' . (isset($_GET['p']) ? helpPageLoader($_GET['p']).'.html' : 'admin-home.html'),
    'icon' => 'fa-question-circle',
    'text' => mswSafeDisplay($msg_adheader12),
    'class' => 'hidden-sm hidden-xs',
    'ext' => 'yes'
  );
}

$msTopMenu[] = array(
  'url' => 'index.php?p=logout',
  'icon' => 'fa-unlock',
  'text' => mswSafeDisplay($msg_adheader10),
  'class' => 'hidden-sm hidden-xs',
  'ext' => 'no'
);

?>