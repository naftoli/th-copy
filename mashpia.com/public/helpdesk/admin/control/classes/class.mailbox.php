<?php

class mailBox {

  public $settings;
  public $datetime;

  public function getRecipient($id, $user) {
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `staffID` FROM `" . DB_PREFIX . "mailassoc`
         WHERE `mailID` = '{$id}'
		     AND `staffID` != '{$user}'
		     LIMIT 1
		     ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $MA = mysqli_fetch_object($q);
    $U  = mswGetTableData('users', 'id', (isset($MA->staffID) ? $MA->staffID : '0'));
    return (isset($U->name) ? $U->name : 'N/A');
  }

  public function autoPurge($staff, $days) {
    mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "mailassoc`
    WHERE `staffID` = '{$staff}'
    AND `folder`    = 'bin'
    AND DATEDIFF(NOW(),DATE(FROM_UNIXTIME(`lastUpdate`))) >= {$days}
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // Any messages not attached to folders are removed..
    mailBox::assocChecker();
  }

  public function getLastReply($id) {
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `ts`,`staffID` FROM `" . DB_PREFIX . "mailreplies`
         WHERE `mailID` = '{$id}'
		     ORDER BY `id` DESC
		     ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $R = mysqli_fetch_object($q);
    if (isset($R->ts)) {
      $A    = mswGetTableData('users', 'id', $R->staffID);
      $info = array(
        (isset($A->name) ? $A->name : 'N/A'),
        $R->ts
      );
      return $info;
    }
    return array(
      '0',
      '0'
    );
  }

  public function add($data) {
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "mailbox` (
    `ts`,
    `staffID`,
    `subject`,
    `message`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '{$data['staff']}',
    '" . mswSafeImportString($data['subject']) . "',
    '" . mswSafeImportString($data['message']) . "'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
    // Association..
    mailBox::assoc(array(
      'staff' => $data['staff'],
      'id' => $id,
      'folder' => 'outbox',
      'status' => 'read'
    ));
    mailBox::assoc(array(
      'staff' => $data['to'],
      'id' => $id,
      'folder' => 'inbox',
      'status' => 'unread'
    ));
    return $id;
  }

  public function reply($data) {
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "mailreplies` (
    `ts`,
    `mailID`,
    `staffID`,
    `message`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '{$data['id']}',
    '{$data['staff']}',
    '" . mswSafeImportString($data['message']) . "'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
    // Association..
    mailBox::assoc(array(
      'staff' => $data['staff'],
      'id' => $data['id'],
      'folder' => 'outbox',
      'status' => 'read'
    ));
    mailBox::assoc(array(
      'staff' => $data['to'],
      'id' => $data['id'],
      'folder' => 'inbox',
      'status' => 'unread'
    ));
    return $id;
  }

  public function assoc($data) {
    if (mswRowCount('mailassoc WHERE `staffID` = \'' . $data['staff'] . '\' AND `mailID` = \'' . $data['id'] . '\'') == 0) {
      mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "mailassoc` (
      `staffID`,
      `mailID`,
      `folder`,
      `status`,
      `lastUpdate`
      ) VALUES (
      '{$data['staff']}',
      '{$data['id']}',
      '{$data['folder']}',
      '{$data['status']}',
      UNIX_TIMESTAMP()
      )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    } else {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "mailassoc` SET
      `folder`        = '{$data['folder']}',
      `status`        = '{$data['status']}',
      `lastUpdate`    = UNIX_TIMESTAMP()
      WHERE `staffID` = '{$data['staff']}'
      AND `mailID`    = '{$data['id']}'
	    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  public function folders($staff) {
    $deleted = 0;
    $folders = array(
      "'inbox'",
      "'outbox'",
      "'bin'"
    );
    // Existing..
    if (!empty($_POST['folder'])) {
      // Update..
      foreach ($_POST['folder'] AS $fK => $fV) {
        if ($fV) {
          mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "mailfolders` SET
          `folder`      = '" . mswSafeImportString($fV) . "'
          WHERE `id`    = '" . mswSafeImportString($fK) . "'
          AND `staffID` = '{$staff}'
          ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
          $folders[] = "'" . $fK . "'";
        }
      }
      // Delete messages if folder no longer exists..
      if (!empty($folders)) {
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "mailassoc`
	      WHERE `staffID`   = '{$staff}'
	      AND `folder` NOT IN(" . mswSafeImportString(implode(',', $folders)) . ")
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        $deleted = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        if (mswRowCount('mailassoc') == 0) {
          @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "mailassoc`");
        }
        // Now delete folders not in array..
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "mailfolders`
	      WHERE `staffID`   = '{$staff}'
	      AND `id`     NOT IN(" . mswSafeImportString(implode(',', $folders)) . ")
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        if (mswRowCount('mailfolders') == 0) {
          @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "mailfolders`");
        }
      }
    }
    // New..
    if (!empty($_POST['new'])) {
      foreach ($_POST['new'] AS $fV) {
        if ($fV) {
          mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "mailfolders` (
          `staffID`,
          `folder`
          ) VALUES (
          '{$staff}',
          '" . mswSafeImportString($fV) . "'
          )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        }
      }
    }
    // Any messages not attached to folders are removed..
    mailBox::assocChecker();
    return $deleted;
  }

  public function mark($mark, $staff, $ids = array()) {
    $flag = substr($mark, 2);
    $fid  = (!empty($ids) ? implode(',', $ids) : (!empty($_POST['del']) ? implode(',', $_POST['del']) : '0'));
    // If status is unread, move to inbox..
    switch($flag) {
      case 'unread':
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "mailassoc` SET
        `status`        = '{$flag}',
        `folder`        = 'inbox'
        WHERE `mailID` IN(" . mswSafeImportString($fid). ")
        AND `staffID`   = '{$staff}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        break;
      default:
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "mailassoc` SET
        `status`        = '{$flag}'
        WHERE `mailID` IN(" . mswSafeImportString($fid). ")
        AND `staffID`   = '{$staff}'
        ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        break;
    }
    return mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
  }

  public function moveTo($folder, $staff) {
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "mailassoc` SET
    `folder`        = '" . mswSafeImportString($folder) . "'
    WHERE `mailID` IN(" . (!empty($_POST['del']) ? mswSafeImportString(implode(',', $_POST['del'])) : '0'). ")
    AND `staffID`   = '{$staff}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    return mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
  }

  public function delete($staff) {
    mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "mailassoc`
    WHERE `mailID` IN(" . (!empty($_POST['del']) ? mswSafeImportString(implode(',', $_POST['del'])) : '0'). ")
    AND `staffID`   = '{$staff}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
    // Any messages not attached to folders are removed..
    mailBox::assocChecker();
    return $rows;
  }

  public function emptyBin($staff) {
    mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "mailassoc`
    WHERE `staffID` = '{$staff}'
    AND `folder`    = 'bin'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // Any messages not attached to folders are removed..
    mailBox::assocChecker();
  }

  public function assocChecker() {
    mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "mailbox`
    WHERE (SELECT count(*) FROM `" . DB_PREFIX . "mailassoc`
     WHERE `" . DB_PREFIX . "mailassoc`.`mailID` = `" . DB_PREFIX . "mailbox`.`id`
    ) = 0
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    if (mswRowCount('mailbox') == 0) {
      @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "mailbox`");
      @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . "mailassoc`");
    }
  }

  public function perms() {
    $users = array();
    $ID    = (int) $_GET['msg'];
    $qAs = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `staffID` FROM `" . DB_PREFIX . "mailassoc`
           WHERE `mailID` = '{$ID}'
           ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    while ($MA = mysqli_fetch_object($qAs)) {
      $users[] = $MA->staffID;
    }
    return $users;
  }

}

?>