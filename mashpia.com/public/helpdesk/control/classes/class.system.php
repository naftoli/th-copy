<?php

class msSystem {

  public $settings;
  public $datetime;

  public function languages() {
    $lang = array();
    $d    = opendir(PATH . 'content/language');
    while (false !== ($r = readdir($d))) {
      if (is_dir(PATH . 'content/language/' . $r) && !in_array($r, array(
        '.',
        '..'
      ))) {
        $lang[] = $r;
      }
    }
    closedir($d);
    return $lang;
  }

  public function token() {
    $t = substr(md5(uniqid(rand(), 1)), 3, 30);
    return mswEncrypt($t . SECRET_KEY);
  }

  // Assign ticket status based on value..
  public function status($tstatus) {
    global $msg_viewticket14, $msg_viewticket15, $msg_viewticket16;
    switch ($tstatus) {
      case 'open':
        return $msg_viewticket14;
        break;
      case 'close':
        return $msg_viewticket15;
        break;
      case 'closed':
        return $msg_viewticket16;
        break;
    }
  }

  public function department($id, $msg, $object = false) {
    $DEPT = mswGetTableData('departments', 'id', $id);
    if ($object) {
      return $DEPT;
    }
    return (isset($DEPT->name) ? mswCleanData($DEPT->name) : $msg);
  }

  public function recaptcha() {
    global $msg_newticket26;
    return str_replace(array(
      '{text}',
      '{public_key}',
      '{theme}'
    ), array(
      $msg_newticket26,
      $this->settings->recaptchaPublicKey,
      ($this->settings->recaptchaTheme && in_array($this->settings->recaptchaTheme, array('light','dark')) ? $this->settings->recaptchaTheme : 'light')
    ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/recaptcha.htm'));
  }

  public function ticketDepartments($dept = '', $arr = false) {
    $html = '';
    $arrD = array();
    $now  = $this->datetime->mswTimeStamp();
    $day  = $this->datetime->mswDateTimeDisplay($now, 'D', $this->settings->timezone);
    $q_dept = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`name` FROM `" . DB_PREFIX . "departments`
              WHERE `showDept` = 'yes'
              AND (`days` IS NULL OR `days` = '' OR FIND_IN_SET('{$day}', `days`) > 0)
              ORDER BY `orderBy`
              ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    if (mysqli_num_rows($q_dept) > 0) {
      while ($DEPT = mysqli_fetch_object($q_dept)) {
        $html .= str_replace(array(
          '{value}',
          '{selected}',
          '{text}'
        ), array(
          $DEPT->id,
          mswSelectedItem($dept, $DEPT->id),
          mswCleanData($DEPT->name)
        ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-department.htm'));
        $arrD[$DEPT->id] = mswCleanData($DEPT->name);
      }
    }
    return ($arr ? $arrD : $html);
  }

  public function customPages($user = 0, $l) {
    $html = '';
    $menu = array();
    $wrap = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-pages.htm');
    $link = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/custom-pages-link.htm');
    $q    = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`, `title` FROM `" . DB_PREFIX . "pages`
            WHERE `enPage` = 'yes'
            " . ($user > 0 ? 'AND (`secure` = \'yes\' AND (`accounts` = \'all\' OR FIND_IN_SET(\'' . $user . '\', `accounts`) > 0) OR `secure` = \'no\')' : 'AND `secure` = \'no\'') . "
            ORDER BY `orderBy`
            ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    while ($PG = mysqli_fetch_object($q)) {
      $html .= str_replace(array(
        '{id}',
        '{url}',
        '{title}'
      ),array(
        $PG->id,
        $this->settings->scriptpath,
        mswCleanData($PG->title)
      ),$link);
      // If user is logged in..
      if ($user > 0) {
        $menu[] = array(
          'id' => $PG->id,
          'name' => mswCleanData($PG->title)
        );
      }
    }
    return array(
      ($html ? str_replace(array('{pages}', '{text}'), array($html, $l[8]), $wrap) : ''),
      $menu
    );
  }

  public function levels($level, $arr = false, $keys = false, $filter = false) {
    $level  = strtolower($level);
    $levels = array();
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "levels`
         " . ($filter ? 'WHERE `display` = \'yes\'' : '') . "
         ORDER BY `orderBy`
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    while ($L = mysqli_fetch_object($q)) {
      $levels[($L->marker ? $L->marker : $L->id)] = mswCleanData($L->name);
    }
    if ($keys) {
      return array_keys($levels);
    } else {
      if ($arr) {
        return $levels;
      } else {
        return (isset($levels[$level]) ? $levels[$level] : $levels['low']);
      }
    }
  }

  public function callback($cmd) {
    // FAQ..
    if (isset($_GET['a']) || isset($_GET['c']) || isset($_GET['q']) || isset($_GET['v'])) {
      $cmd       = (isset($_GET['a']) ? 'que' : (isset($_GET['q']) ? 'search' : 'faq'));
      $_GET['p'] = (isset($_GET['a']) ? 'que' : (isset($_GET['q']) ? 'search' : 'faq'));
    }
    // Verification..
    if (isset($_GET['va'])) {
      $cmd = 'create';
    }
    // Ajax..
    if (isset($_GET['ajax'])) {
      $cmd = 'ajax';
    }
    // Logout..
    if (isset($_GET['lo'])) {
      $cmd = 'login';
    }
    // Custom Page..
    if (isset($_GET['pg'])) {
      $cmd = 'custom-page';
    }
    // View ticket..
    if (isset($_GET['t']) || isset($_GET['attachment'])) {
      $cmd = 'ticket';
    }
    // View dispute..
    if (isset($_GET['d']) || isset($_GET['qd'])) {
      $cmd = 'dispute';
    }
    // Search..
    if (isset($_GET['qt'])) {
      $cmd = 'history';
    }
    // Search Disputes..
    if (isset($_GET['qd'])) {
      $cmd = 'disputes';
    }
    // FAQ attachment..
    if (isset($_GET['fattachment'])) {
      $cmd = 'faq';
    }
    // Imap..
    if (isset($_GET[$this->settings->imap_param])) {
      $cmd = $this->settings->imap_param;
    }
    // BB code..
    if (isset($_GET['bbcode'])) {
      $cmd = 'home';
    }
    // API..
    if (isset($_GET['api']) || isset($_GET['xml'])) {
      $cmd = 'api';
    }
    return $cmd;
  }

  public function jsCSSBlockLoader($ms_js_css_loader = array(), $loc) {
    $html = '';
    $base = $this->settings->scriptpath . '/content/' . MS_TEMPLATE_SET . '/';
    switch($loc) {
      case 'head':
        if (array_key_exists('uploader', $ms_js_css_loader)) {
          $html .= '<link href="' . $base . 'css/jquery.uploader.css" rel="stylesheet" type="text/css">' . mswDefineNewline();
        }
        if (array_key_exists('ibox', $ms_js_css_loader)) {
          $html .= '<link href="' . $base . 'css/jquery.ibox.css" rel="stylesheet" type="text/css">' . mswDefineNewline();
        }
        if (array_key_exists('bbcode', $ms_js_css_loader)) {
          $html .= '<link rel="stylesheet" href="' . $base . 'css/bbcode.css" type="text/css">' . mswDefineNewline();
        }
        break;
      case 'foot':
        if (array_key_exists('ibox', $ms_js_css_loader)) {
          $html .= '<script src="' . $base . 'js/plugins/jquery.ibox.js" type="text/javascript"></script>' . mswDefineNewline();
        }
        break;
    }
    return trim($html);
  }

}

?>