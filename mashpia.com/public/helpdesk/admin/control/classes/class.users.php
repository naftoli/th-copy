<?php

class systemUsers {

  public $settings;

  public function updateDefDays($id) {
    $_GET['dd'] = (int) $_GET['dd'];
    if ($_GET['dd'] > 999) {
      $_GET['dd'] = 45;
    }
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "users` SET
    `defDays`  = '{$_GET['dd']}'
    WHERE `id` = '{$id}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  public function enable() {
    $_GET['id'] = (int) $_GET['id'];
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "users` SET
    `enabled`  = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id` = '{$_GET['id']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  public function reset($acc) {
    $changed = array();
    for ($i = 0; $i < count($_POST['id']); $i++) {
      $e  = strtolower($_POST['mail'][$i]);
      $n  = $_POST['name'][$i];
      $np = '';
      $p  = ($_POST['password'][$i] ? mswEncrypt(SECRET_KEY . $_POST['password'][$i]) : '');
      if ($p == '' && isset($_POST['autoall'])) {
        $pg                    = $acc->ms_generate();
        $_POST['password'][$i] = $pg;
        $p                     = mswEncrypt(SECRET_KEY . $pg);
      }
      $id = $_POST['id'][$i];
      if ($e && $p) {
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "users` SET
        `email`     = '{$e}',
        `accpass`   = '{$p}'
        WHERE `id`  = '{$id}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        // Was anything updated?
        if (mysqli_affected_rows($GLOBALS["___mysqli_ston"]) > 0) {
          $changed[] = array(
            'id' => $id,
            'pass' => $_POST['password'][$i]
          );
        }
      }
    }
    return $changed;
  }

  public function log($user) {
    $defLogs = ($this->settings->defKeepLogs ? unserialize($this->settings->defKeepLogs) : array());
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "log` (
    `ts`,`userID`,`ip`,`type`
    ) VALUES (
    UNIX_TIMESTAMP(),'{$user->id}','" . mswIPAddresses() . "','user'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // Clear previous..
    if (isset($defLogs['user']) && $defLogs['user'] > 0) {
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "log` WHERE `userID` = '{$user->id}' AND `id` <
	    (SELECT min(`id`) FROM
      (SELECT `id` FROM `" . DB_PREFIX . "log`
	    WHERE `userID` = '{$user->id}'
	    AND `type`     = 'user'
	    ORDER BY `id` DESC LIMIT " . $defLogs['user'] . "
	    ) AS `" . DB_PREFIX . "log`)") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  public function add() {
    $editperms = (!empty($_POST['editperms']) ? serialize($_POST['editperms']) : '');
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "users` (
    `ts`,
    `name`,
    `email`,
    `email2`,
    `accpass`,
    `signature`,
    `notify`,
    `pageAccess`,
    `emailSigs`,
    `notePadEnable`,
    `delPriv`,
    `nameFrom`,
    `emailFrom`,
    `assigned`,
    `timezone`,
    `ticketHistory`,
    `enableLog`,
    `mailbox`,
    `mailFolders`,
    `mailDeletion`,
    `mailScreen`,
    `mailCopy`,
    `mailPurge`,
    `addpages`,
    `mergeperms`,
    `digest`,
    `digestasg`,
    `profile`,
    `helplink`,
    `editperms`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '" . mswSafeImportString($_POST['name']) . "',
    '" . strtolower($_POST['email']) . "',
    '" . mswSafeImportString(strtolower($_POST['email2'])) . "',
    '" . mswEncrypt(SECRET_KEY . $_POST['accpass']) . "',
    '" . mswSafeImportString(strip_tags($_POST['signature'])) . "',
    '" . (isset($_POST['notify']) ? 'yes' : 'no') . "',
    '" . (!empty($_POST['accessPages']) ? mswSafeImportString(implode('|', $_POST['accessPages'])) : '') . "',
    '" . (isset($_POST['emailSigs']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['notePadEnable']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['delPriv']) ? 'yes' : 'no') . "',
    '" . mswSafeImportString($_POST['nameFrom']) . "',
    '" . mswSafeImportString($_POST['emailFrom']) . "',
    '" . (isset($_POST['assigned']) ? 'yes' : 'no') . "',
    '" . mswSafeImportString($_POST['timezone']) . "',
    '" . (isset($_POST['ticketHistory']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['enableLog']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['mailbox']) ? 'yes' : 'no') . "',
    '" . (int) $_POST['mailFolders'] . "',
    '" . (isset($_POST['mailDeletion']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['mailScreen']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['mailCopy']) ? 'yes' : 'no') . "',
    '" . (int) $_POST['mailPurge'] . "',
    '" . mswSafeImportString($_POST['addpages']) . "',
    '" . (isset($_POST['mergeperms']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['digest']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['digestasg']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['profile']) ? 'yes' : 'no') . "',
    '" . (isset($_POST['helplink']) ? 'yes' : 'no') . "',
    '" . mswSafeImportString($editperms) . "'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
    // Add to user departments..
    if (!empty($_POST['dept']) && !isset($_POST['assigned'])) {
      foreach ($_POST['dept'] AS $dID) {
        mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "userdepts` (
        `userID`,`deptID`
        ) VALUES (
        '{$id}','{$dID}'
        )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      }
    } else {
      // If no departments were set, add user to all as default..
      $d = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "departments` ORDER BY `orderBy`") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      while ($D = mysqli_fetch_object($d)) {
        mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "userdepts` (
        `userID`,`deptID`
        ) VALUES (
        '{$id}','{$D->id}'
        )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      }
    }
    // Determine access pages..
    if (!empty($_POST['accessPages'])) {
      foreach ($_POST['accessPages'] AS $aPage) {
        mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "usersaccess` (
        `page`,`userID`,`type`
        ) VALUES (
        '{$aPage}','{$id}','pages'
        )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      }
    }
  }

  public function profile($user) {
    $rows = 0;
    $pass = ($_POST['accpass'] ? mswEncrypt(SECRET_KEY . $_POST['accpass']) : $user->accpass);
    // This is a security check. Make sure details don`t match someone else`s account..
    if (mswRowCount('users WHERE LOWER(`email`) = \'' . mswSafeImportString(strtolower($_POST['email'])) . '\' AND `id` != \'' . $user->id . '\'') == 0) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "users` SET
      `name`           = '" . mswSafeImportString($_POST['name']) . "',
      `email`          = '" . mswSafeImportString(strtolower($_POST['email'])) . "',
      `email2`         = '" . mswSafeImportString(strtolower($_POST['email2'])) . "',
      `accpass`        = '{$pass}',
      `signature`      = '" . mswSafeImportString(strip_tags($_POST['signature'])) . "',
      `nameFrom`       = '" . mswSafeImportString($_POST['nameFrom']) . "',
      `emailFrom`      = '" . mswSafeImportString($_POST['emailFrom']) . "',
      `timezone`       = '" . mswSafeImportString($_POST['timezone']) . "'
      WHERE `id`       = '{$user->id}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
      // Update session vars..
      $_SESSION[mswEncrypt(SECRET_KEY) . '_ms_mail'] = strtolower($_POST['email']);
      if ($_POST['accpass']) {
        $_SESSION[mswEncrypt(SECRET_KEY) . '_ms_key'] = $pass;
      } // Clear cookies..
      if (isset($_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_mail'])) {
        @setcookie(mswEncrypt(SECRET_KEY) . '_msc_mail', '');
        @setcookie(mswEncrypt(SECRET_KEY) . '_msc_key', '');
        unset($_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_mail'], $_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_key']);
      }
    }
    return $rows;
  }

  public function update($user) {
    $_GET['edit']    = (int) $_POST['update'];
    $pass            = ($_POST['accpass'] ? mswEncrypt(SECRET_KEY . $_POST['accpass']) : $_POST['old_pass']);
    $editperms       = (!empty($_POST['editperms']) ? serialize($_POST['editperms']) : '');
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "users` SET
    `name`           = '" . mswSafeImportString($_POST['name']) . "',
    `email`          = '" . strtolower($_POST['email']) . "',
    `email2`         = '" . mswSafeImportString(strtolower($_POST['email2'])) . "',
    `accpass`        = '{$pass}',
    `signature`      = '" . mswSafeImportString(strip_tags($_POST['signature'])) . "',
    `notify`         = '" . (isset($_POST['notify']) ? 'yes' : 'no') . "',
    `pageAccess`     = '" . (!empty($_POST['accessPages']) ? mswSafeImportString(implode('|', $_POST['accessPages'])) : '') . "',
    `emailSigs`      = '" . (isset($_POST['emailSigs']) ? 'yes' : 'no') . "',
    `notePadEnable`  = '" . (isset($_POST['notePadEnable']) ? 'yes' : 'no') . "',
    `delPriv`        = '" . (isset($_POST['delPriv']) ? 'yes' : 'no') . "',
    `nameFrom`       = '" . mswSafeImportString($_POST['nameFrom']) . "',
    `emailFrom`      = '" . mswSafeImportString($_POST['emailFrom']) . "',
    `assigned`       = '" . (isset($_POST['assigned']) ? 'yes' : 'no') . "',
    `timezone`       = '" . mswSafeImportString($_POST['timezone']) . "',
    `enabled`        = '" . (isset($_POST['enabled']) ? 'yes' : 'no') . "',
    `ticketHistory`  = '" . (isset($_POST['ticketHistory']) ? 'yes' : 'no') . "',
    `enableLog`      = '" . (isset($_POST['enableLog']) ? 'yes' : 'no') . "',
    `mailbox`        = '" . (isset($_POST['mailbox']) ? 'yes' : 'no') . "',
    `mailFolders`    = '" . (int) $_POST['mailFolders'] . "',
    `mailDeletion`   = '" . (isset($_POST['mailDeletion']) ? 'yes' : 'no') . "',
    `mailScreen`     = '" . (isset($_POST['mailScreen']) ? 'yes' : 'no') . "',
    `mailCopy`       = '" . (isset($_POST['mailCopy']) ? 'yes' : 'no') . "',
    `mailPurge`      = '" . (int) $_POST['mailPurge'] . "',
    `addpages`       = '" . (isset($_POST['addpages']) ? mswSafeImportString($_POST['addpages']) : '') . "',
    `mergeperms`     = '" . (isset($_POST['mergeperms']) ? 'yes' : 'no') . "',
    `digest`         = '" . (isset($_POST['digest']) ? 'yes' : 'no') . "',
    `digestasg`      = '" . (isset($_POST['digestasg']) ? 'yes' : 'no') . "',
    `profile`        = '" . (isset($_POST['profile']) ? 'yes' : 'no') . "',
    `helplink`       = '" . (isset($_POST['helplink']) ? 'yes' : 'no') . "',
    `editperms`      = '" . mswSafeImportString($editperms) . "'
    WHERE `id`       = '{$_POST['update']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // Add to user departments..
    if (!empty($_POST['dept']) && !isset($_POST['assigned']) && $_POST['update'] > 1) {
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "userdepts`
      WHERE `userID` = '{$_GET['edit']}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      if (mswRowCount('userdepts') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "userdepts`");
      }
      foreach ($_POST['dept'] AS $dID) {
        mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "userdepts` (
        `userID`,`deptID`
        ) VALUES (
        '{$_GET['edit']}','{$dID}'
        )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      }
    } else {
      // If not global user, add to all departments if none set..
      if ($_GET['edit'] > 1) {
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "userdepts`
        WHERE `userID` = '{$_GET['edit']}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        // If no departments were set, add user to all as default..
        $d = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "departments` ORDER BY `orderBy`") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        while ($D = mysqli_fetch_object($d)) {
          mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "userdepts` (
          `userID`,`deptID`
          ) VALUES (
          '{$_GET['edit']}','{$D->id}'
          )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        }
      }
    }
    // Determine access pages..
    if (!empty($_POST['accessPages']) && $_GET['edit'] > 1) {
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "usersaccess`
      WHERE `userID` = '{$_GET['edit']}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      if (mswRowCount('usersaccess') == 0) {
        @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "usersaccess`");
      }
      foreach ($_POST['accessPages'] AS $aPage) {
        mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "usersaccess` (
      `page`,`userID`,`type`
      ) VALUES (
      '{$aPage}','{$_GET['edit']}','pages'
      )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      }
    }
    // If password was set and the person logged in has changed their details, change session vars..
    // We`ll update password and email session vars and reset cookies..
    if ($user == $_GET['edit']) {
      $_SESSION[mswEncrypt(SECRET_KEY) . '_ms_mail'] = strtolower($_POST['email']);
      if ($_POST['accpass']) {
        $_SESSION[mswEncrypt(SECRET_KEY) . '_ms_key'] = $pass;
      } // Clear cookies..
      if (isset($_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_mail'])) {
        @setcookie(mswEncrypt(SECRET_KEY) . '_msc_mail', '');
        @setcookie(mswEncrypt(SECRET_KEY) . '_msc_key', '');
        unset($_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_mail'], $_COOKIE[mswEncrypt(SECRET_KEY) . '_msc_key']);
      }
    }
  }

  public function delete() {
    if (!empty($_POST['del'])) {
      $uID = implode(',', $_POST['del']);
      // Users info..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "users`
      WHERE `id` IN({$uID})
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
      // Departments assigned..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "userdepts`
      WHERE `userID` IN({$uID})
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      // Access assigned..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "usersaccess`
      WHERE `userID` IN({$uID})
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      // Log entries..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "log`
      WHERE `userID` IN({$uID})
	    AND `type`      = 'user'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      // Truncate tables to start at 1..
      foreach (array(
        'users',
        'userdepts',
        'usersaccess',
        'log'
      ) AS $tables) {
        if (mswRowCount($tables) == 0) {
          @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . $tables . "`");
        }
      }
      return $rows;
    }
  }

  // Does email exist..
  public function check($entered = '') {
    $SQL = '';
    if ($entered) {
      $_POST['checkEntered'] = $entered;
    }
    if (isset($_POST['currID']) && (int) $_POST['currID'] > 0) {
      $_POST['currID'] = (int) $_POST['currID'];
      $SQL             = "AND `id` != '{$_POST['currID']}'";
    }
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "users`
         WHERE LOWER(`email`) = '" . mswSafeImportString(strtolower($_POST['checkEntered'])) . "'
	       $SQL
         LIMIT 1
         ");
    $P = mysqli_fetch_object($q);
    return (isset($P->id) ? 'exists' : 'accept');
  }

  // Reset password..
  public function password($id, $password) {
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "users` SET
    `accpass`  = '" . mswEncrypt(SECRET_KEY . $password) . "'
    WHERE `id` = '{$id}'
    ");
    return $password;
  }

}

?>