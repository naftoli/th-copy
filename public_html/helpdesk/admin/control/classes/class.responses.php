<?php

class standardResponses {

  public $settings;

  public function rebuildSequence() {
    $seq = 0;
    $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "responses` ORDER BY IF(`orderBy`>0,`orderBy`,9999)");
    while ($RB = mysqli_fetch_object($q)) {
      $n = (++$seq);
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "responses` SET
	    `orderBy`  = '{$n}'
	    WHERE `id` = '{$RB->id}'
	    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  public function orderSequence() {
    foreach ($_POST['order'] AS $k => $v) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "responses` SET
	    `ts`       = UNIX_TIMESTAMP(),
      `orderBy`  = '{$v}'
      WHERE `id` = '{$k}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  public function enableDisable() {
    $_GET['id'] = (int) $_GET['id'];
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "responses` SET
    `ts`         = UNIX_TIMESTAMP(),
    `enResponse` = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id`   = '{$_GET['id']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  public function batchImportSR() {
    $count = 0;
    $dept  = (empty($_POST['dept']) ? implode(',', $_POST['deptall']) : implode(',', $_POST['dept']));
    // Clear current responses..
    if (isset($_POST['clear'])) {
      $SQL  = '';
      $chop = (empty($_POST['dept']) ? $_POST['deptall'] : $_POST['dept']);
      for ($i = 0; $i < count($chop); $i++) {
        $SQL .= ($i > 0 ? ' OR ' : ' WHERE ') . "FIND_IN_SET(" . mswSafeImportString($chop[$i]) . ",`departments`) > 0";
      }
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "responses`" . $SQL) or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      if (mswRowCount('responses') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "responses`");
      }
    }
    // Upload CSV file..
    if (isset($_SESSION['upload']['file']) && file_exists($_SESSION['upload']['file'])) {
      $handle = fopen($_SESSION['upload']['file'], 'r');
      if ($handle) {
        while (($CSV = fgetcsv($handle, CSV_MAX_LINES_TO_READ, CSV_IMPORT_DELIMITER, CSV_IMPORT_ENCLOSURE)) !== false) {
          // Clean array..
          $CSV = array_map('trim', $CSV);
          mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "responses` (
          `ts`,
          `title`,
          `answer`,
          `departments`
          ) VALUES (
          UNIX_TIMESTAMP(),
          '" . mswSafeImportString($CSV[0]) . "',
          '" . mswSafeImportString($CSV[1]) . "',
          '" . mswSafeImportString($dept) . "'
          )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          ++$count;
        }
        fclose($handle);
      }
      // Clear session file..
      if (file_exists($_SESSION['upload']['file'])) {
        @unlink($_SESSION['upload']['file']);
      }
      // Rebuild sequence..
      standardResponses::rebuildSequence();
    }
    return $count;
  }

  public function addResponse() {
    $dept = (empty($_POST['dept']) ? implode(',', $_POST['deptall']) : implode(',', $_POST['dept']));
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "responses` (
    `ts`,
    `title`,
    `answer`,
    `departments`,
    `enResponse`,
    `orderBy`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '" . mswSafeImportString($_POST['title']) . "',
    '" . mswSafeImportString($_POST['answer']) . "',
    '" . mswSafeImportString($dept) . "',
    '" . (isset($_POST['enResponse']) ? 'yes' : 'no') . "',
    '0'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // Rebuild sequence..
    standardResponses::rebuildSequence();
  }

  public function updateResponse() {
    $ID   = (int) $_POST['update'];
    $dept = (empty($_POST['dept']) ? implode(',', $_POST['deptall']) : implode(',', $_POST['dept']));
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "responses` SET
    `ts`          = UNIX_TIMESTAMP(),
    `title`       = '" . mswSafeImportString($_POST['title']) . "',
    `answer`      = '" . mswSafeImportString($_POST['answer']) . "',
    `departments` = '" . mswSafeImportString($dept) . "',
    `enResponse`  = '" . (isset($_POST['enResponse']) ? 'yes' : 'no') . "'
    WHERE `id`    = '{$ID}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  public function deleteResponses() {
    if (!empty($_POST['del'])) {
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "responses`
      WHERE `id` IN(" . mswSafeImportString(implode(',', $_POST['del'])) . ")
	    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
      if (mswRowCount('responses') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "responses`");
      }
      // Rebuild sequence..
      standardResponses::rebuildSequence();
      return $rows;
    }
    return '0';
  }

  // Search..
  public function autoSearch() {
    $dp  = (isset($_GET['dept']) ? (int) $_GET['dept'] : '0');
    $ar  = array();
    $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`title` FROM `" . DB_PREFIX . "responses`
           WHERE LOWER(`title`) LIKE '%" . strtolower(mswSafeImportString($_GET['term'])) . "%'
           AND (`departments` IS NULL OR `departments` = '' OR FIND_IN_SET('{$dp}', `departments`) > 0)
           AND `enResponse` = 'yes'
           ORDER BY `title`
           ");
    while ($R = mysqli_fetch_object($q)) {
      $ar[] = array(
        'value' => $R->id,
        'label' => mswSafeDisplay($R->title)
      );
    }
    return $ar;
  }

}

?>