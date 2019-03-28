<?php

class msFAQ {

  public $settings;

  // Get parent for auto opening of subs..
  public function getParentFaqCat() {
    $ID = (isset($_GET['id']) ? (int) $_GET['id'] : '0');
    $C  = mswGetTableData('categories','id', $ID);
    if (isset($C->subcat)) {
      return ($C->subcat > 0 ? $C->subcat : $ID);
    }
    return '0';
  }

  // Determine if single category is set
  public function cat($id) {
    $cat = 0;
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `itemID` FROM `" . DB_PREFIX . "faqassign`
         WHERE `question`  = '{$id}'
         AND `desc`   = 'category'
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    if (mysqli_num_rows($q) == '1') {
      $C   = mysqli_fetch_object($q);
      $cat = $C->itemID;
    }
    return $cat;
  }

  // Voting stats..
  public function stats($id) {
    $a    = array('0%', '0%', '0');
    $KB   = mswGetTableData('faq', 'id', $id);
    if (isset($KB->kuseful)) {
      $yes  = ($KB->kviews > 0 ? @number_format(($KB->kuseful / $KB->kviews) * 100, 2) : '0.00');
      $no   = ($KB->kviews > 0 ? @number_format(($KB->knotuseful / $KB->kviews) * 100, 2) : '0.00');
      if (substr($yes, -3) == '.00') {
        $yes = substr($yes, 0, -3);
      }
      if (substr($no, -3) == '.00') {
        $no = substr($no, 0, -3);
      }
      return array($yes . '%', $no . '%', @number_format($KB->kviews));
    }
    return $a;
  }

  // Voting system..
  public function vote() {
    $id    = (int) $_GET['id'];
    $votes = array();
    if ($id > 0 && in_array($_GET['vote'], array('yes','no'))) {
      switch ($_GET['vote']) {
        case 'no':
          $table = '`knotuseful` = (`knotuseful` + 1)';
          break;
        default:
          $table = '`kuseful` = (`kuseful` + 1)';
          break;
      }
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "faq` SET
      `kviews`   = (`kviews` + 1),
      $table
      WHERE `id` = '{$id}'
      ");
      // If multiple votes aren`t allowed, set cookie..
      if ($this->settings->multiplevotes == 'no') {
        // If cookie is set, get array of ids and update with new id..
        // If not set, just add id to array..
        if (isset($_COOKIE[mswEncrypt(SECRET_KEY) . COOKIE_NAME])) {
          $votes   = unserialize($_COOKIE[mswEncrypt(SECRET_KEY) . COOKIE_NAME]);
          $votes[] = $id;
          // Clear the cookie..
          setcookie(mswEncrypt(SECRET_KEY) . COOKIE_NAME, '');
          unset($_COOKIE[mswEncrypt(SECRET_KEY) . COOKIE_NAME]);
        } else {
          $votes[] = $id;
        }
        // Set cookie..
        setcookie(mswEncrypt(SECRET_KEY) . COOKIE_NAME, serialize($votes), time() + 60 * 60 * 24 * $this->settings->cookiedays);
      }
      return 'ok';
    }
    return 'fail';
  }

  // Attachments..
  public function attachments() {
    $html = '';
    $id   = (int) $_GET['a'];
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,
         `" . DB_PREFIX . "faqattach`.`id` AS `attachID`
		     FROM `" . DB_PREFIX . "faqassign`
         LEFT JOIN `" . DB_PREFIX . "faqattach`
         ON `" . DB_PREFIX . "faqassign`.`itemID`      = `" . DB_PREFIX . "faqattach`.`id`
         WHERE `" . DB_PREFIX . "faqassign`.`question` = '{$id}'
		     AND `" . DB_PREFIX . "faqassign`.`desc`       = 'attachment'
         AND `" . DB_PREFIX . "faqattach`.`enAtt`      = 'yes'
         GROUP BY `" . DB_PREFIX . "faqassign`.`itemID`
         ORDER BY `" . DB_PREFIX . "faqattach`.`orderBy`
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    while ($ATT = mysqli_fetch_object($q)) {
      $show = 'yes';
      $ext  = substr(strrchr(($ATT->path ? $ATT->path : $ATT->remote), '.'), 1);
      // Check local file exists..
      if ($ATT->path && !file_exists($this->settings->attachpathfaq . '/' . $ATT->path)) {
        $show = 'no';
      }
      if ($show == 'yes') {
        $html .= str_replace(array(
          '{url}',
          '{name}',
          '{name_alt}',
          '{size}',
          '{filetype}'
        ), array(
          ($ATT->remote ? $ATT->remote : '?fattachment=' . $ATT->attachID),
          ($ATT->name ? mswCleanData($ATT->name) : ($ATT->remote ? basename($ATT->remote) : $ATT->path)),
          ($ATT->name ? mswSafeDisplay($ATT->name) : ($ATT->remote ? basename($ATT->remote) : $ATT->path)),
          mswFileSizeConversion($ATT->size),
          strtoupper($ext)
        ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/faq-attachment-link.htm')) . mswDefineNewline();
      }
    }
    return ($html ? $html : '');
  }

  // FAQ questions..
  public function questions($data = array()) {
    global $msg_pkbase8;
    $str = '';
    if ($this->settings->kbase == 'no') {
      return $str;
    }
    $private = "'no'";
    if ($data['private'] == 'yes') {
      $private = "'no','yes'";
    }
    // Search mode..
    if (isset($data['search'][0], $data['search'][1])) {
      $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CALC_FOUND_ROWS *,
	         `" . DB_PREFIX . "faq`.`id` AS `faqID`,
		       `" . DB_PREFIX . "faq`.`question` AS `faqQuestion`
		       FROM `" . DB_PREFIX . "faq`
           " . $data['search'][0] . "
		       AND `" . DB_PREFIX . "faq`.`enFaq` = 'yes'
           AND `" . DB_PREFIX . "faq`.`private` IN(" . $private . ")
		       ORDER BY `" . DB_PREFIX . "faq`.`orderBy`
		       LIMIT " . $data['limit'] . "," . $this->settings->quePerPage) or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      if ($data['search'][1] == 'yes') {
        $c = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT FOUND_ROWS() AS `rows`"));
        return (isset($c->rows) ? $c->rows : '0');
      }
    } else {
      $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,
	         `" . DB_PREFIX . "faq`.`id` AS `faqID`,
		       `" . DB_PREFIX . "faq`.`question` AS `faqQuestion`
		       FROM `" . DB_PREFIX . "faq`
	         LEFT JOIN `" . DB_PREFIX . "faqassign`
		       ON `" . DB_PREFIX . "faq`.`id`       = `" . DB_PREFIX . "faqassign`.`question`
           WHERE `" . DB_PREFIX . "faq`.`enFaq` = 'yes'
		       " . (isset($data['flag']) ? $data['flag'] : '') . "
           " . ($data['id'] > 0 ? 'AND `' . DB_PREFIX . 'faqassign`.`itemID` = \'' . $data['id'] . '\'' : '') . "
		       AND `" . DB_PREFIX . "faqassign`.`desc`   = 'category'
		       AND `" . DB_PREFIX . "faq`.`private` IN(" . $private . ")
		       " . (isset($data['queryadd']) ? $data['queryadd'] : '') . "
		       ORDER BY " . (isset($data['orderor']) ? $data['orderor'] : '`orderBy`') . "
		       LIMIT " . $data['limit'] . "," . $this->settings->quePerPage) or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
    while ($LINKS = mysqli_fetch_object($q)) {
      $str .= str_replace(array(
        '{article}',
        '{url_params}',
        '{question}',
        '{count}'
      ), array(
        $LINKS->faqID,
        mswQueryParams(array(
          'a',
          'p'
        )),
        mswCleanData($LINKS->faqQuestion),
        number_format($LINKS->kviews)
      ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/faq-question-link.htm'));
    }
    return ($str ? trim($str) : str_replace('{text}', $msg_pkbase8, file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/nothing-found.htm')));
  }

  // FAQ menu links..
  public function menu($data = array()) {
    $str = '';
    if ($this->settings->kbase == 'no') {
      return $str;
    }
    $private = "'no'";
    if ($data['private'] == 'yes') {
      $private = "'no','yes'";
    }
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "categories`
         WHERE `enCat`  = 'yes'
         AND `subcat`   = '0'
         AND `private` IN(" . $private . ")
         ORDER BY `orderBy`
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    while ($CATS = mysqli_fetch_object($q)) {
      $count = '';
      if ($this->settings->faqcounts == 'yes') {
        $count = mswRowCount('faqassign LEFT JOIN `' . DB_PREFIX . 'faq` ON `' . DB_PREFIX . 'faq`.`id` = `' . DB_PREFIX . 'faqassign`.`question`
	               WHERE `itemID` = \'' . $CATS->id . '\' AND `desc` = \'category\' AND `' . DB_PREFIX . 'faq`.`enFaq` = \'yes\'');
      }
      $str .= str_replace(array(
        '{cat}',
        '{url}',
        '{category}',
        '{count}'
      ), array(
        $CATS->id,
        $this->settings->scriptpath,
        mswSafeDisplay($CATS->name),
        ($count ? ' (' . @number_format($count) . ')' : ($this->settings->faqcounts == 'yes' ? ' (0)' : ''))
      ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/faq-cat-menu-link.htm'));
      // Sub categories..
      $loadSubCats = 'yes';
      if ($loadSubCats == 'yes') {
        $qS = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "categories`
              WHERE `enCat` = 'yes'
              AND `subcat`  = '{$CATS->id}'
              ORDER BY `orderBy`
              ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        while ($SUBS = mysqli_fetch_object($qS)) {
          $count = '';
          if ($this->settings->faqcounts == 'yes') {
            $count = mswRowCount('faqassign LEFT JOIN `' . DB_PREFIX . 'faq` ON `' . DB_PREFIX . 'faq`.`id` = `' . DB_PREFIX . 'faqassign`.`question`
	                   WHERE `itemID` = \'' . $SUBS->id . '\' AND `desc` = \'category\' AND `' . DB_PREFIX . 'faq`.`enFaq` = \'yes\'');
          }
          $str .= str_replace(array(
            '{cat}',
            '{subcat}',
            '{url}',
            '{category}',
            '{category-alt}',
            '{count}'
          ), array(
            $CATS->id,
            $SUBS->id,
            $this->settings->scriptpath,
            mswSafeDisplay($SUBS->name),
            mswSafeDisplay($SUBS->name . ' (' . $CATS->name . ')'),
            ($count ? ' (' . @number_format($count) . ')' : ($this->settings->faqcounts == 'yes' ? ' (0)' : ''))
          ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/faq-sub-menu-link.htm'));
        }
      }
    }
    return trim($str);
  }

}

?>