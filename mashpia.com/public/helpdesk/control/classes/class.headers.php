<?php

class htmlHeaders {

  public function err403($admin = false, $text = '') {
    global $msg_charset, $html_lang, $lang_dir, $msg_home11, $msg_script52, $msg_script53, $msg_script54, $SETTINGS;
    $msg_charset = ($msg_charset ? $msg_charset : 'utf-8');
    $html_dir    = ($lang_dir ? $lang_dir : 'ltr');
    $html_lang   = ($html_lang ? $html_lang : 'en');
    header('HTTP/1.0 403 Forbidden');
    header('Content-type: text/html; charset=' . $msg_charset);
    if ($admin) {
      $f = array(
        '{charset}',
        '{lang}',
        '{dir}',
        '{back}',
        '{error}',
        '{oops}'
      );
      $r = array(
        $msg_charset,
        $html_lang,
        $lang_dir,
        $msg_script53,
        ($text ? $text : $msg_home11),
        $msg_script52
      );
      echo (file_exists(PATH . 'templates/system/headers/403.php') ? str_replace($f, $r, file_get_contents(PATH . 'templates/system/headers/403.php')) : '403: Forbidden');
    } else {
      if (!class_exists('Savant3')) {
        include(PATH . 'control/lib/Savant3.php');
      }
      $tpl = new Savant3();
      $tpl->assign('LANG', $html_lang);
      $tpl->assign('DIR', $html_dir);
      $tpl->assign('CHARSET', $msg_charset);
      $tpl->assign('SETTINGS', $SETTINGS);
      $tpl->assign('TXT', array(
        $msg_script52,
        $msg_home11,
        $msg_script54
      ));

      // Global vars..
      include(PATH . 'control/lib/global.php');

      $tpl->display('content/' . (defined(MS_TEMPLATE_SET) ? MS_TEMPLATE_SET : '_default_set') . '/headers/403.tpl.php');
    }
    exit;
  }

  public function err404($admin = false, $text = '') {
    global $msg_charset, $html_lang, $lang_dir, $msg_script6, $msg_script52, $msg_script54, $SETTINGS;
    $msg_charset = ($msg_charset ? $msg_charset : 'utf-8');
    $html_dir    = ($lang_dir ? $lang_dir : 'ltr');
    $html_lang   = ($html_lang ? $html_lang : 'en');
    header('HTTP/1.0 404 Not Found');
    header('Content-type: text/html; charset=' . $msg_charset);
    if ($admin) {
      $f = array(
        '{charset}',
        '{lang}',
        '{dir}',
        '{back}',
        '{error}',
        '{oops}'
      );
      $r = array(
        $msg_charset,
        $html_lang,
        $lang_dir,
        $msg_script54,
        ($text ? $text : $msg_script6),
        $msg_script52
      );
      echo (file_exists(PATH . 'templates/system/headers/404.php') ? str_replace($f, $r, file_get_contents(PATH . 'templates/system/headers/404.php')) : '404: Page Not Found');
    } else {
      if (!class_exists('Savant3')) {
        include(PATH . 'control/lib/Savant3.php');
      }
      $tpl = new Savant3();
      $tpl->assign('LANG', $html_lang);
      $tpl->assign('DIR', $html_dir);
      $tpl->assign('CHARSET', $msg_charset);
      $tpl->assign('SETTINGS', $SETTINGS);
      $tpl->assign('TXT', array(
        $msg_script52,
        $msg_script6,
        $msg_script54
      ));

      // Global vars..
      include(PATH . 'control/lib/global.php');

      $tpl->display('content/' . (defined(MS_TEMPLATE_SET) ? MS_TEMPLATE_SET : '_default_set') . '/headers/404.tpl.php');
    }
    exit;
  }

}

?>