<?php

class fields {

  public function orderSequence() {
    foreach ($_POST['order'] AS $k => $v) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "cusfields` SET
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  public function rebuildSequence() {
    $seq = 0;
    $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "cusfields` ORDER BY IF(`orderBy`>0,`orderBy`,9999)");
    while ($RB = mysqli_fetch_object($q)) {
      $n = (++$seq);
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "cusfields` SET
	    `orderBy`  = '{$n}'
	    WHERE `id` = '{$RB->id}'
	    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  public function enableDisable() {
    $_GET['id'] = (int) $_GET['id'];
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "cusfields` SET
    `enField`  = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id` = '{$_GET['id']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  public function addCustomField() {
    // Defaults if not set..
    $_POST['fieldType']  = (isset($_POST['fieldType']) && in_array($_POST['fieldType'], array(
      'textarea',
      'input',
      'select',
      'checkbox'
    )) ? $_POST['fieldType'] : 'input');
    $_POST['fieldReq']   = (isset($_POST['fieldReq']) ? 'yes' : 'no');
    $_POST['repeatPref'] = (isset($_POST['repeatPref']) ? 'yes' : 'no');
    $_POST['enField']    = (isset($_POST['enField']) ? 'yes' : 'no');
    $dept                = (empty($_POST['dept']) ? implode(',', $_POST['deptall']) : implode(',', $_POST['dept']));
    if (empty($_POST['fieldLoc'])) {
      $_POST['fieldLoc'][] = 'ticket';
    }
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "cusfields` (
    `fieldInstructions`,
    `fieldType`,
    `fieldReq`,
    `fieldOptions`,
    `fieldLoc`,
    `orderBy`,
    `repeatPref`,
    `enField`,
    `departments`
    ) VALUES (
    '" . mswSafeImportString($_POST['fieldInstructions']) . "',
    '{$_POST['fieldType']}',
    '{$_POST['fieldReq']}',
    '" . mswSafeImportString($_POST['fieldOptions']) . "',
    '" . mswSafeImportString(implode(',', $_POST['fieldLoc'])) . "',
    '0',
    '{$_POST['repeatPref']}',
    '{$_POST['enField']}',
    '{$dept}'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // Rebuild sequence..
    fields::rebuildSequence();
  }

  public function editCustomField() {
    // Defaults if not set..
    $_POST['fieldType']  = (isset($_POST['fieldType']) && in_array($_POST['fieldType'], array(
      'textarea',
      'input',
      'select',
      'checkbox'
    )) ? $_POST['fieldType'] : 'input');
    $_POST['fieldReq']   = (isset($_POST['fieldReq']) ? 'yes' : 'no');
    $_POST['repeatPref'] = (isset($_POST['repeatPref']) ? 'yes' : 'no');
    $_POST['enField']    = (isset($_POST['enField']) ? 'yes' : 'no');
    $dept                = (empty($_POST['dept']) ? implode(',', $_POST['deptall']) : implode(',', $_POST['dept']));
    if (empty($_POST['fieldLoc'])) {
      $_POST['fieldLoc'][] = 'ticket';
    }
    if ((int) $_POST['update'] > 0) {
      $_POST['update'] = (int) $_POST['update'];
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "cusfields` SET
      `fieldInstructions`  = '" . mswSafeImportString($_POST['fieldInstructions']) . "',
      `fieldType`          = '{$_POST['fieldType']}',
      `fieldReq`           = '{$_POST['fieldReq']}',
      `fieldOptions`       = '" . mswSafeImportString($_POST['fieldOptions']) . "',
      `fieldLoc`           = '" . mswSafeImportString(implode(',', $_POST['fieldLoc'])) . "',
      `repeatPref`         = '{$_POST['repeatPref']}',
      `enField`            = '{$_POST['enField']}',
      `departments`        = '{$dept}'
      WHERE `id`           = '{$_POST['update']}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  public function deleteCustomFields() {
    if (!empty($_POST['del'])) {
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "cusfields`
      WHERE `id` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
	    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "ticketfields`
      WHERE `fieldID` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
	    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      if (mswRowCount('cusfields') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "cusfields`");
      }
      if (mswRowCount('ticketfields') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "ticketfields`");
      }
      // Rebuild sequence..
      fields::rebuildSequence();
      return $rows;
    }
    return '0';
  }

}

?>