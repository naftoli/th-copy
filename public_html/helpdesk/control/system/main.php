<?php

if (!defined('PARENT') || !defined('MS_PERMISSIONS')) {
  $HEADERS->err403();
}

// Show BBCode help..
if (isset($_GET['bbcode'])) {

  $tpl = new Savant3();
  $tpl->assign('CHARSET', $msg_charset);
  $tpl->assign('LANG', $html_lang);
  $tpl->assign('DIR', $lang_dir);
  $tpl->assign('TITLE', ($title ? mswSafeDisplay($title) . ': ' : '') . $msg_bbcode . ': ' . str_replace('{website}', mswCleanData($SETTINGS->website), $msg_header));
  $tpl->assign('TOP_BAR_TITLE', str_replace('{website}', mswCleanData($SETTINGS->website), $msg_header));

  // Global vars..
  include(PATH . 'control/lib/global.php');

  // Load template..
  $tpl->display('content/' . MS_TEMPLATE_SET . '/bb-code-help.tpl.php');

} else {

  include(PATH . 'control/header.php');

  $tpl = new Savant3();
  $tpl->assign('CATEGORIES', $FAQ->menu(array(
    'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
  )));
  $tpl->assign('TXT', array(
    $msg_public_main,
    str_replace('{name}', mswCleanData($SETTINGS->website), $msg_public_main2),
    str_replace('{count}', $SETTINGS->popquestions, $msg_main10),
    str_replace('{count}', $SETTINGS->popquestions, $msg_public_main3),
    $msadminlangpublic[7],
    $msg_pkbase7,
    $msg_pkbase,
    mswSafeDisplay($msadminlang3_1faq[4])
  ));
  $tpl->assign('FEATURED', $FAQ->questions(array(
    'id' => 0,
    'limit' => 0,
    'search' => array(),
    'orderor' => '`' . DB_PREFIX . 'faq`.`orderBy`',
    'queryadd' => 'GROUP BY `' . DB_PREFIX . 'faqassign`.`question`',
    'flag' => 'AND `' . DB_PREFIX . 'faq`.`featured` = \'yes\'',
    'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
  )));
  $tpl->assign('POPULAR', $FAQ->questions(array(
    'id' => 0,
    'limit' => 0,
    'search' => array(),
    'orderor' => '`' . DB_PREFIX . 'faq`.`kviews` DESC',
    'queryadd' => 'GROUP BY `' . DB_PREFIX . 'faqassign`.`question`',
    'flag' => 'AND `' . DB_PREFIX . 'faq`.`featured` = \'no\'',
    'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
  )));
  $tpl->assign('LATEST', $FAQ->questions(array(
    'id' => 0,
    'limit' => 0,
    'search' => array(),
    'orderor' => '`' . DB_PREFIX . 'faq`.`ts` DESC',
    'queryadd' => 'GROUP BY `' . DB_PREFIX . 'faqassign`.`question`',
    'flag' => 'AND `' . DB_PREFIX . 'faq`.`featured` = \'no\'',
    'private' => (isset($LI_ACC->id) ? 'yes' : 'no')
  )));

  // Global vars..
  include(PATH . 'control/lib/global.php');

  // Load template..
  $tpl->display('content/' . MS_TEMPLATE_SET . '/main.tpl.php');

  include(PATH . 'control/footer.php');

}

?>