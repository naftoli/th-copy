<?php

class departments {

  // Re-order..
  public function order() {
    foreach ($_POST['order'] AS $k => $v) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "departments` SET
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  // Add department..
  public function add($userID) {
    // Next order sequence..
    $nextOrder = (mswRowCount('departments') + 1);
    $days      = (!empty($_POST['days']) ? implode(',', $_POST['days']) : '');
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "departments` (
    `name`,`showDept`,`dept_subject`,`dept_comments`,`orderBy`,`manual_assign`,`days`
    ) VALUES (
    '" . mswSafeImportString($_POST['name']) . "',
    '" . (isset($_POST['showDept']) ? 'yes' : 'no') . "',
    '" . mswSafeImportString($_POST['dept_subject']) . "',
    '" . mswSafeImportString($_POST['dept_comments']) . "',
    '{$nextOrder}',
    '" . (isset($_POST['manual_assign']) ? 'yes' : 'no') . "',
    '{$days}'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $last = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
    // If user isn`t global user, let this user see departments added..
    if ($userID > 1) {
      mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "userdepts` (
      `userID`,`deptID`
      ) VALUES (
      '{$userID}','{$last}'
      )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  // Update department..
  public function update() {
    $_GET['edit'] = (int) $_POST['update'];
    $days         = (!empty($_POST['days']) ? implode(',', $_POST['days']) : '');
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "departments` SET
    `name`          = '" . mswSafeImportString($_POST['name']) . "',
    `showDept`      = '" . (isset($_POST['showDept']) ? 'yes' : 'no') . "',
    `dept_subject`  = '" . mswSafeImportString($_POST['dept_subject']) . "',
    `dept_comments` = '" . mswSafeImportString($_POST['dept_comments']) . "',
    `manual_assign` = '" . (isset($_POST['manual_assign']) ? 'yes' : 'no') . "',
    `days`          = '{$days}'
    WHERE `id`      = '{$_GET['edit']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // If manual assign is not set, remove from any tickets..
    if (isset($_POST['manual_assign']) && $_POST['manual_assign'] == 'no') {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
      `assignedto`       = ''
      WHERE `department` = '{$_GET['edit']}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  // Delete department..
  public function delete() {
    if (!empty($_POST['del'])) {
      // Nuke departments..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "departments`
      WHERE `id` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
	    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
      // Nuke user department association..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "userdepts`
      WHERE `deptID` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      if (mswRowCount('departments') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "departments`");
      }
      if (mswRowCount('userdepts') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "userdepts`");
      }
      // Rebuild order sequence..
      $seq = 0;
      $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "departments` ORDER BY `orderBy`");
      while ($RB = mysqli_fetch_object($q)) {
        $n = (++$seq);
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "departments` SET
	      `orderBy`  = '{$n}'
        WHERE `id` = '{$RB->id}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      }
      return $rows;
    }
    return '0';
  }

}

?>