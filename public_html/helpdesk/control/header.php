<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err404();
}

$tpl = new Savant3();
$tpl->assign('CHARSET', $msg_charset);
$tpl->assign('LANG', $html_lang);
$tpl->assign('DIR', $lang_dir);
$tpl->assign('TITLE', ($title ? mswSafeDisplay($title) . ': ' : '') . str_replace('{website}', mswSafeDisplay($SETTINGS->website), $msg_header));
$tpl->assign('TOP_BAR_TITLE', str_replace('{website}', mswSafeDisplay($SETTINGS->website), $msg_header));
$tpl->assign('TOP_BAR_TITLE_MB', $msadminlangpublic[0]);
$tpl->assign('SCRIPTPATH', $SETTINGS->scriptpath);
$tpl->assign('LOGGED_IN', (MS_PERMISSIONS != 'guest' ? 'yes' : 'no'));
$tpl->assign('TXT', array(
  $msg_header8,
  $msg_main2,
  $msg_header3,
  $msg_header11,
  $msg_header12,
  $msg_header2,
  (MS_PERMISSIONS != 'guest' && isset($LI_ACC->name) ? str_replace('{name}', mswSafeDisplay($LI_ACC->name), $msg_header6) : ''),
  $msg_header13,
  $msg_header14,
  $msg_header15,
  $msg_header16,
  $msg_header4
));
$tpl->assign('FILES', $MSYS->jsCSSBlockLoader($ms_js_css_loader, 'head'));

// Global vars..
include(PATH . 'control/lib/global.php');

// Load template..
$tpl->display('content/' . MS_TEMPLATE_SET . '/header.tpl.php');

?>