<?php

class levels {

  // Re-order..
  public function orderSequence() {
    foreach ($_POST['order'] AS $k => $v) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "levels` SET
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  // Rebuild sequence..
  public function rebuildSequence() {
    $seq = 0;
    $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "levels` ORDER BY IF(`orderBy`>0,`orderBy`,9999)");
    while ($RB = mysqli_fetch_object($q)) {
      $n = (++$seq);
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "levels` SET
	    `orderBy`  = '{$n}'
	    WHERE `id` = '{$RB->id}'
	    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  // Add level..
  public function addLevel() {
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "levels` (
    `name`,`display`,`orderBy`
    ) VALUES (
    '" . mswSafeImportString($_POST['name']) . "',
    '" . (isset($_POST['display']) ? 'yes' : 'no') . "',
    '0'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // Rebuild order sequence..
    levels::rebuildSequence();
  }

  // Update level..
  public function updateLevel() {
    $_GET['edit'] = (int) $_POST['update'];
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "levels` SET
    `name`     = '" . mswSafeImportString($_POST['name']) . "',
    `display`  = '" . (isset($_POST['display']) ? 'yes' : 'no') . "'
    WHERE `id` = '{$_GET['edit']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  // Delete level..
  public function deleteLevels() {
    if (!empty($_POST['del'])) {
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "levels`
      WHERE `id` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
	    AND `id`   NOT IN(1,2,3)
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
      // Rebuild order sequence..
      levels::rebuildSequence();
      return $rows;
    }
    return '0';
  }

}

?>