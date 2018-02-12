<?php

class csPages {

  public $settings;

  public function rebuildSequence() {
    $seq = 0;
    $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "pages` ORDER BY IF(`orderBy`>0,`orderBy`,9999)");
    while ($RB = mysqli_fetch_object($q)) {
      $n = (++$seq);
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "pages` SET
	    `orderBy`  = '{$n}'
	    WHERE `id` = '{$RB->id}'
	    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  public function orderSequence() {
    foreach ($_POST['order'] AS $k => $v) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "pages` SET
	    `ts`       = UNIX_TIMESTAMP(),
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  public function enableDisable() {
    $_GET['id'] = (int) $_GET['id'];
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "pages` SET
    `ts`       = UNIX_TIMESTAMP(),
    `enPage`   = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id` = '{$_GET['id']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  public function addPage() {
    $acc = (!empty($_POST['acc']) ? implode(',', $_POST['acc']) : 'all');
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "pages` (
    `ts`,
    `title`,
    `information`,
    `accounts`,
    `enPage`,
    `secure`,
    `orderBy`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '" . mswSafeImportString($_POST['title']) . "',
    '" . mswSafeImportString($_POST['information']) . "',
    '" . (isset($_POST['secure']) ? mswSafeImportString($acc) : '') . "',
    '" . (isset($_POST['enPage']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['secure']) ? 'yes' : 'no') . "',
    '0'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // Rebuild sequence..
    csPages::rebuildSequence();
  }

  public function updatePage() {
    $ID   = (int) $_POST['update'];
    $acc = (!empty($_POST['acc']) ? implode(',', $_POST['acc']) : 'all');
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "pages` SET
    `ts`          = UNIX_TIMESTAMP(),
    `title`       = '" . mswSafeImportString($_POST['title']) . "',
    `information` = '" . mswSafeImportString($_POST['information']) . "',
    `accounts`    = '" . (isset($_POST['secure']) ? mswSafeImportString($acc) : '') . "',
    `enPage`      = '" . (isset($_POST['enPage']) ? 'yes' : 'no') . "',
    `secure`      = '" . (isset($_POST['secure']) ? 'yes' : 'no') . "'
    WHERE `id`    = '{$ID}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  public function deletePages() {
    if (!empty($_POST['del'])) {
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "pages`
      WHERE `id` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
	    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
      if (mswRowCount('pages') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "pages`");
      }
      // Rebuild sequence..
      csPages::rebuildSequence();
      return $rows;
    }
    return '0';
  }

}

?>