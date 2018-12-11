<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Global template vars. Available in ALL .tpl.php files..
$tpl->assign('SETTINGS', $SETTINGS);
$tpl->assign('LOGGED_IN', (MS_PERMISSIONS != 'guest' && isset($LI_ACC->id) ? 'yes' : 'no'));
$tpl->assign('USER_DATA', (isset($LI_ACC->id) ? $LI_ACC : ''));
$tpl->assign('SYS_BASE_HREF', $SETTINGS->scriptpath . '/content/' . MS_TEMPLATE_SET . '/');
$tpl->assign('FILE_LOADER', (isset($ms_js_css_loader) ? $ms_js_css_loader : ''));
$tpl->assign('DROPZONE', (isset($mswUploadDropzone) ? $mswUploadDropzone : array()));

// Custom page loader..
$cs_html = array('','');
if (isset($MSYS) && method_exists($MSYS, 'customPages')) {
  $cs_html = $MSYS->customPages((MS_PERMISSIONS != 'guest' && isset($LI_ACC->id) ? $LI_ACC->id : '0'), $msadminlangpublic);
}
$tpl->assign('OTHER_PAGES', $cs_html[0]);
$tpl->assign('OTHER_PAGES_MENU', $cs_html[1]);

?>