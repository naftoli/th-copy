<?php

class tickets extends msSystem {

  public $parser;
  public $settings;
  public $datetime;
  public $fields;
  public $system;
  public $upload;

  const ATTACH_FILE_NAME_TRUNCATION = 30;

  public $internal = array(
    'chmod' => 0777,
    'chmod-after' => 0644
  );

  public function updateIP($id, $type = 'ticket') {
    switch ($type) {
      case 'ticket':
        mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
        `ipAddresses` = '" . mswIPAddresses() . "'
        WHERE `id`    = '{$id}'
        ");
        break;
      case 'reply':
        break;
    }
  }

  public function size($size) {
    if ($this->settings->maxsize == 0 || $this->settings->maxsize == '') {
      return true;
    }
    return ($size <= $this->settings->maxsize ? true : false);
  }

  public function historyLog($ticket, $action) {
    if ($this->settings->ticketHistory == 'yes') {
      mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "tickethistory` (
      `ts`,
      `ticketID`,
      `action`,
      `ip`
      ) VALUES (
      UNIX_TIMESTAMP(),
      '{$ticket}',
      '" . mswSafeImportString($action) . "',
      '" . mswIPAddresses() . "'
      )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  public function type($file) {
    if ($this->settings->filetypes == '') {
      return true;
    }
    $types = array_map('trim', explode('|', strtolower($this->settings->filetypes)));
    $ext   = strrchr(strtolower($file), '.');
    return (in_array($ext, $types) ? true : false);
  }

  public function preFill($id) {
    $html = array();
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `dept_subject`,`dept_comments` FROM `" . DB_PREFIX . "departments`
         WHERE `showDept` = 'yes'
         AND `id`         = '{$id}'
         ORDER BY `name`
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $DEPT        = mysqli_fetch_object($q);
    $html['sub'] = (isset($DEPT->dept_subject) && $DEPT->dept_subject ? mswCleanData($DEPT->dept_subject) : '');
    $html['msg'] = (isset($DEPT->dept_comments) && $DEPT->dept_comments ? mswCleanData($DEPT->dept_comments) : '');
    return $html;
  }

  public function disputeUserNames($t, $name) {
    $html  = '';
    $users = array(
      mswSafeDisplay($name)
    );
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `name`,`email`,`" . DB_PREFIX . "portal`.`id` AS `pID` FROM `" . DB_PREFIX . "disputes`
         LEFT JOIN `" . DB_PREFIX . "portal`
		     ON `" . DB_PREFIX . "disputes`.`visitorID`  = `" . DB_PREFIX . "portal`.`id`
         WHERE `" . DB_PREFIX . "disputes`.`ticketID` = '{$t->id}'
		     ORDER BY `" . DB_PREFIX . "portal`.`name`
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    while ($U = mysqli_fetch_object($q)) {
      $users[$U->pID] = mswSafeDisplay($U->name);
    }
    return $users;
  }

  public function disputeUsers($ticket) {
    $u = array();
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `visitorID` FROM `" . DB_PREFIX . "disputes`
         WHERE `ticketID` = '{$ticket}'
		     GROUP BY `visitorID`
		     ORDER BY `id`
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    while ($U = mysqli_fetch_object($q)) {
      $u[] = $U->visitorID;
    }
    return $u;
  }

  public function openclose($id, $action = 'open') {
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
    `lastrevision`  = UNIX_TIMESTAMP(),
    `ticketStatus`  = '{$action}'
    WHERE `id`      = '{$id}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    return mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
  }

  public function replies($id, $name, $userid) {
    global $msg_showticket21, $msg_viewticket39, $msg_viewticket40, $msg_add2;
    $data = '';
    $none = str_replace('{text}', $msg_viewticket39, file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-message.htm'));
    $sig  = str_replace('{text}', $msg_viewticket39, file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-signature.htm'));
    $sub  = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-reply-sublink.htm');
    $flk  = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-reply-field-link.htm');
    $alk  = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-reply-attachment-link.htm');
    $reps = 0;
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "replies`
         WHERE `ticketID` = '{$id}'
         ORDER BY `id`
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    if (mysqli_num_rows($q) > 0) {
      while ($R = mysqli_fetch_object($q)) {
        $siggie   = '';
        $sublinks = array();
        if ($R->disputeUser > 0) {
          $R->replyType = 'dispute';
        }
        switch ($R->replyType) {
          // Reply by admin..
          case 'admin':
            $USER      = mswGetTableData('users', 'id', $R->replyUser);
            $replyName = (isset($USER->name) ? mswSafeDisplay($USER->name) : $msg_viewticket43);
            $label     = 'panel panel-warning';
            $icon      = 'users';
            // Does this user have a siggie..
            if ($USER->signature) {
              $siggie = str_replace('{signature}', mswNL2BR($this->parser->mswAutoLinkParser(mswSafeDisplay($USER->signature))), $sig);
            }
            break;
          // Reply by original ticket creator..
          case 'visitor':
            if ($userid == $R->replyUser) {
              $replyName = $name;
            } else {
              $USER       = mswGetTableData('portal', 'id', $R->replyUser, '', '`name`');
              $replyName  = (isset($USER->name) ? mswSafeDisplay($USER->name) : $msg_viewticket43);
            }
            $label     = 'panel panel-default';
            $icon      = 'user';
            break;
          // Reply by other user viewing same ticket..
          case 'dispute':
            $D            = mswGetTableData('portal', 'id', $R->disputeUser);
            $replyName    = (isset($D->name) ? mswSafeDisplay($D->name) : 'N/A');
            $R->replyType = 'visitor';
            $label        = 'panel panel-default';
            $icon         = 'user';
            break;
        }
        // Custom field data..
        $fields   = $this->fields->display($id, $R->id, 0, $label);
        $fields_c = $this->fields->display($id, $R->id, 1);
        if ($fields_c > 0) {
          $sublinks[] = str_replace(array(
            '{id}',
            '{text}',
            '{count}'
          ),
          array(
            $R->id,
            $msg_add2,
            $fields_c
          ), $flk);
        }
        // Attachments..
        $attach   = tickets::attachments($id, $R->id);
        $attach_c = tickets::attachments($id, $R->id, 1);
        if ($attach_c > 0) {
          $sublinks[] = str_replace(array(
            '{id}',
            '{text}',
            '{count}'
          ),
          array(
            $R->id,
            $msg_viewticket40,
            $attach_c
          ), $alk);
        }
        $data .= str_replace(array(
          '{id}',
          '{type}',
          '{comments}',
          '{signature}',
          '{text}',
          '{name}',
          '{date}',
          '{time}',
          '{attachments}',
          '{info}',
          '{fields}',
          '{label}',
          '{count}',
          '{display}',
          '{display2}',
          '{display3}',
          '{icon}',
          '{sublinks}'
        ), array(
          $R->id,
          $R->replyType,
          $this->parser->mswTxtParsingEngine($R->comments, ($this->settings->enableBBCode == 'no' && $R->replyType == 'admin' ? true : false)),
          $siggie,
          $msg_showticket21,
          $replyName,
          $this->datetime->mswDateTimeDisplay($R->ts, $this->settings->dateformat),
          $this->datetime->mswDateTimeDisplay($R->ts, $this->settings->timeformat),
          $attach,
          mswCleanData($R->ipAddresses),
          $fields,
          $label,
          (++$reps),
          (!$siggie ? ' style="display:none"' : ''),
          (!$fields ? ' style="display:none"' : ''),
          (!$attach ? ' style="display:none"' : ''),
          $icon,
          (!empty($sublinks) ? str_replace('{links}', implode(SUBLINK_SEPARATOR, $sublinks), $sub) : '')
        ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-reply.htm'));
      }
    }
    return ($data ? trim($data) : $none);
  }

  // Rename attachment..
  public function rename($name, $ticket, $reply, $incr) {
    $rand = substr(md5(uniqid(rand(), 1)), 3, 20);
    $ext  = substr(strrchr(strtolower($name), '.'), 1);
    return $ticket . ($reply > 0 ? '_' . $reply : '') . '-' . $incr . '-' . $rand . '.' . ($ext ? $ext : 'txt');
  }

  // Add attachment..
  public function addAttachment($data = array()) {
    if (is_dir($this->settings->attachpath) && is_writeable($this->settings->attachpath)) {
      if ($this->upload->isUploaded($data['temp'])) {
        $FN = ($this->settings->rename == 'yes' ? tickets::rename($data['name'], $data['tID'], $data['rID'], $data['incr']) : $data['name']);
        $U  = $this->settings->attachpath . '/' . $FN;
        $Y  = date('Y', $this->datetime->mswTimeStamp());
        $M  = date('m', $this->datetime->mswTimeStamp());
        // Attempt to create folder if it doesn`t exist..
        if (!is_dir($this->settings->attachpath . '/' . $Y)) {
          $this->upload->folderCreation($this->settings->attachpath . '/' . $Y, $this->internal['chmod']);
        }
        if (is_dir($this->settings->attachpath . '/' . $Y)) {
          if (!is_dir($this->settings->attachpath . '/' . $Y . '/' . $M)) {
            $this->upload->folderCreation($this->settings->attachpath . '/' . $Y . '/' . $M, $this->internal['chmod']);
          }
          if (is_dir($this->settings->attachpath . '/' . $Y . '/' . $M)) {
            $U = $this->settings->attachpath . '/' . $Y . '/' . $M . '/' . $FN;
          }
        }
        // Upload temp file..
        $this->upload->moveFile($data['temp'], $U);
        // Required by some servers to make image viewable and accessible via FTP..
        $this->upload->chmodFile($U, $this->internal['chmod-after']);
      }
      if (file_exists($U)) {
        // Add to database..
        mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "attachments` (
        `ts`,
        `ticketID`,
        `replyID`,
        `department`,
        `fileName`,
        `fileSize`,
        `mimeType`
        ) VALUES (
        UNIX_TIMESTAMP(),
        '{$data['tID']}',
        '{$data['rID']}',
        '{$data['dept']}',
        '" . basename($U) . "',
        '{$data['size']}',
        '{$data['mime']}'
        )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        $ID = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
        // Remove temp file if it still exists..
        if (file_exists($data['temp'])) {
          @unlink($data['temp']);
        }
        return array(
          $ID,
          $U
        );
      }
    }
  }

  public function attachments($ticket, $reply = 0, $count = 0) {
    $data = '';
    $wrap = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-attachment-wrapper.htm');
    // Are attachments enabled?
    if ($this->settings->attachment == 'no') {
      return '';
    }
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT *,DATE(FROM_UNIXTIME(`ts`)) AS `addDate` FROM `" . DB_PREFIX . "attachments`
         WHERE `ticketID`  = '{$ticket}'
         AND `replyID`     = '{$reply}'
         ORDER BY `id`
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    if ($count) {
      return mysqli_num_rows($q);
    }
    if (mysqli_num_rows($q) > 0) {
      while ($ATT = mysqli_fetch_object($q)) {
        $split = explode('-', $ATT->addDate);
        $base  = $this->settings->attachpath . '/';
        // Check for newer folder structure..
        if (file_exists($this->settings->attachpath . '/' . $split[0] . '/' . $split[1] . '/' . $ATT->fileName)) {
          $base = $this->settings->attachpath . '/' . $split[0] . '/' . $split[1] . '/';
        }
        $fileName = substr($ATT->fileName, 0, strpos($ATT->fileName, '.'));
        // Only show file if it exists..
        if (file_exists($base . $ATT->fileName)) {
          $data .= str_replace(array(
            '{ext}',
            '{id}',
            '{file}',
            '{size}',
            '{file_name}'
          ), array(
            substr(strrchr(strtoupper($ATT->fileName), '.'), 1),
            $ATT->id,
            substr($ATT->fileName, 0, strpos($ATT->fileName, '.')),
            mswFileSizeConversion($ATT->fileSize),
            (tickets::ATTACH_FILE_NAME_TRUNCATION > 0 ? (strlen($fileName) > tickets::ATTACH_FILE_NAME_TRUNCATION ? substr($fileName, 0, tickets::ATTACH_FILE_NAME_TRUNCATION) . '..' : $fileName) : $fileName)
          ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/ticket-attachment.htm'));
        }
      }
    }
    return ($data ? str_replace('{attachments}', trim($data), $wrap) : '');
  }

  public function add($tdata = array()) {
    $spam = (isset($tdata['spam']) && $tdata['spam'] == 'yes' ? 'yes' : 'no');
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "tickets` (
    `ts`,
    `lastrevision`,
    `department`,
    `assignedto`,
    `visitorID`,
    `subject`,
    `mailBodyFilter`,
    `comments`,
    `priority`,
    `replyStatus`,
    `ticketStatus`,
    `ipAddresses`,
    `ticketNotes`,
    `isDisputed`,
    `source`,
    `spamFlag`
    ) VALUES (
    UNIX_TIMESTAMP(),
    UNIX_TIMESTAMP(),
    '{$tdata['dept']}',
    '{$tdata['assigned']}',
    '{$tdata['visitor']}',
    '" . mswSafeImportString($tdata['subject']) . "',
    '" . mswSafeImportString($tdata['quoteBody']) . "',
    '" . mswSafeImportString($tdata['comments']) . "',
    '" . mswSafeImportString($tdata['priority']) . "',
    '{$tdata['replyStatus']}',
    '{$tdata['ticketStatus']}',
    '" . mswSafeImportString($tdata['ip']) . "',
    '" . mswSafeImportString($tdata['notes']) . "',
    '{$tdata['disputed']}',
    '" . (isset($tdata['source']) ? $tdata['source'] : 'standard') . "',
    '{$spam}'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
    // If assigned, enable department assign option automatically..
    // Possibly from admin created ticket..
    if ($tdata['assigned'] != '') {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "departments` SET
      `manual_assign` = 'yes'
      WHERE `id`      = '{$tdata['dept']}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
    // Custom fields..
    if (!empty($_POST['customField'])) {
      // Check to see if any checkboxes arrays are now blank..
      // If there are, create empty array to prevent ommission in loop..
      if (!empty($_POST['hiddenBoxes'])) {
        foreach ($_POST['hiddenBoxes'] AS $hb) {
          if (!isset($_POST['customField'][$hb])) {
            $_POST['customField'][$hb] = array();
          }
        }
      }
      foreach ($_POST['customField'] AS $k => $v) {
        $fdata = '';
        // If value is array, its checkboxes..
        if (is_array($v)) {
          if (!empty($v)) {
            $fdata = implode('#####', $v);
          }
        } else {
          $fdata = $v;
        }
        $k = (int) $k;
        // If data exists, update or add entry..
        // If blank or 'nothing-selected', delete if exists..
        if ($fdata != '' && $fdata != 'nothing-selected' && mswRowCount('ticketfields WHERE `ticketID` = \'' . $id . '\' AND `fieldID` = \'' . $k . '\' AND `replyID` = \'0\'') == 0) {
          mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "ticketfields` (
          `fieldData`,`ticketID`,`fieldID`,`replyID`
          ) VALUES (
          '" . mswSafeImportString($fdata) . "','{$id}','{$k}','0'
          )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        }
      }
    }
    // Return new ticket id..
    return $id;
  }

  public function reply($rdata = array()) {
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "replies` (
    `ts`,
    `ticketID`,
    `comments`,
    `mailBodyFilter`,
    `replyType`,
    `replyUser`,
    `ipAddresses`,
    `disputeUser`
    ) VALUES (
    UNIX_TIMESTAMP(),
    '{$rdata['ticket']}',
    '" . mswSafeImportString($rdata['comments']) . "',
    '" . mswSafeImportString($rdata['quoteBody']) . "',
    '{$rdata['repType']}',
    '{$rdata['visitor']}',
    '{$rdata['ip']}',
    '{$rdata['disID']}'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $id = ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
    // Update ticket revision date
    // If ticket is waiting assignment it must remain in the start position..
    if ($id > 0) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
      `lastrevision`  = UNIX_TIMESTAMP(),
	    `ticketStatus`  = 'open',
      `replyStatus`   = '". (isset($rdata['assigned']) && $rdata['assigned'] == 'waiting' ? 'start' : 'admin') . "'
      WHERE `id`      = '{$rdata['ticket']}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
    // Custom fields..
    if (!empty($_POST['customField'])) {
      // Check to see if any checkboxes arrays are now blank..
      // If there are, create empty array to prevent ommission in loop..
      if (!empty($_POST['hiddenBoxes'])) {
        foreach ($_POST['hiddenBoxes'] AS $hb) {
          if (!isset($_POST['customField'][$hb])) {
            $_POST['customField'][$hb] = array();
          }
        }
      }
      foreach ($_POST['customField'] AS $k => $v) {
        $data = '';
        // If value is array, its checkboxes..
        if (is_array($v)) {
          if (!empty($v)) {
            $data = implode('#####', $v);
          }
        } else {
          $data = $v;
        }
        $k = (int) $k;
        // If data exists, update or add entry..
        // If blank or 'nothing-selected', delete if exists..
        if ($data != '' && $data != 'nothing-selected' && mswRowCount('ticketfields WHERE `ticketID` = \'' . $rdata['ticket'] . '\' AND `fieldID` = \'' . $k . '\' AND `replyID` = \'' . $id . '\'') == 0) {
          mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "ticketfields` (
          `fieldData`,`ticketID`,`fieldID`,`replyID`
          ) VALUES (
          '" . mswSafeImportString($data) . "','{$rdata['ticket']}','{$k}','{$id}'
          )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
        }
      }
    }
    return $id;
  }

  public function getLastReply($id) {
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `ts`,`replyType`,`replyUser`,`disputeUser` FROM `" . DB_PREFIX . "replies`
         WHERE `ticketID` = '{$id}'
		     ORDER BY `id` DESC
		     LIMIT 1
		     ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    $R = mysqli_fetch_object($q);
    if (isset($R->ts)) {
      switch ($R->replyType) {
        case 'admin':
          $A    = mswGetTableData('users', 'id', $R->replyUser);
          $info = array(
            (isset($A->name) ? mswSafeDisplay($A->name) : 'N/A'),
            $R->ts,
            $R->replyType
          );
          break;
        case 'visitor':
          if ($R->disputeUser > 0) {
            $U    = mswGetTableData('portal', 'id', $R->disputeUser, '', '`name`');
            $info = array(
              (isset($U->name) ? mswSafeDisplay($U->name) : 'N/A'),
              $R->ts,
              $R->replyType
            );
          } else {
            $U    = mswGetTableData('portal', 'id', $R->replyUser, '', '`name`');
            $info = array(
              (isset($U->name) ? mswSafeDisplay($U->name) : 'N/A'),
              $R->ts,
              $R->replyType
            );
          }
          break;
      }
      return $info;
    }
    return array(
      '0',
      '0',
      ''
    );
  }

  public function disputeList($email, $visID, $lv, $count = false, $queryAdd = '') {
    global $msg_portal8, $msg_public_history9, $msg_public_history10, $msg_portal21, $msg_showticket23, $msg_showticket24, $msg_script30, $msg_showticket30, $msg_public_dashboard6, $msg_public_dashboard8;
    $data = '';
    $IDs  = tickets::disID($visID);
    $sch  = '';
    $qft  = array();
    $oft  = 'ORDER BY `' . DB_PREFIX . 'tickets`.`id` DESC';
    // Check for search mode..
    if (isset($_GET['qd'])) {
      // Load the skip words array..
      include(PATH . 'control/skipwords.php');
      $chop = array_map('trim', explode(' ', urldecode($_GET['qd'])));
      if (!empty($chop)) {
        foreach ($chop AS $word) {
          if (!in_array($word, $searchSkipWords) && strlen($word) > 1) {
            $word = strtolower($word);
            $sch .= (!$sch ? '' : 'OR ') . "LOWER(`subject`) LIKE '%" . mswSafeImportString(mswCleanData($word)) . "%' OR LOWER(`comments`) LIKE '%" . mswSafeImportString(mswCleanData($word)) . "%'";
          }
        }
        if ($sch) {
          $qft[] = 'AND (' . $sch . ')';
        }
      }
    }
    // Order filters..
    if (isset($_GET['order'])) {
      switch ($_GET['order']) {
        // Subject (ascending)..
        case 'subject_asc':
          $oft = 'ORDER BY `subject`';
          break;
        // Subject (descending)..
        case 'subject_desc':
          $oft = 'ORDER BY `subject` desc';
          break;
        // TicketID (ascending)..
        case 'id_asc':
          $oft = 'ORDER BY `ticketID`';
          break;
        // TicketID (descending)..
        case 'id_desc':
          $oft = 'ORDER BY `ticketID` desc';
          break;
        // Priority (ascending)..
        case 'pr_asc':
          $oft = 'ORDER BY `levelName`';
          break;
        // Priority (descending)..
        case 'pr_desc':
          $oft = 'ORDER BY `levelName` desc';
          break;
        // Department (ascending)..
        case 'dept_asc':
          $oft = 'ORDER BY `deptName`';
          break;
        // Department (descending)..
        case 'dept_desc':
          $oft = 'ORDER BY `deptName` desc';
          break;
        // Date Updated (ascending)..
        case 'rev_asc':
          $oft = 'ORDER BY `lastrevision`';
          break;
        // Date Updated (descending)..
        case 'rev_desc':
          $oft = 'ORDER BY `lastrevision` desc';
          break;
        // Date Added (ascending)..
        case 'date_asc':
          $oft = 'ORDER BY `' . DB_PREFIX . 'tickets`.`ts`';
          break;
        // Date Added (descending)..
        case 'date_desc':
          $oft = 'ORDER BY `' . DB_PREFIX . 'tickets`.`ts` desc';
          break;
      }
    }
    // Service level and department filters..
    if (isset($_GET['filter'])) {
      $qft[] = 'AND `priority` = \'' . mswSafeImportString($_GET['filter']) . '\'';
    }
    if (isset($_GET['dept'])) {
      $qft[] = 'AND `department` = \'' . mswSafeImportString($_GET['dept']) . '\'';
    }
    $lWrap = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/tickets/tickets-last-reply-date.htm');
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CALC_FOUND_ROWS *,
         `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
		     `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
	       `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
	       `" . DB_PREFIX . "departments`.`name` AS `deptName`,
	       `" . DB_PREFIX . "levels`.`name` AS `levelName`,
		     (SELECT count(*) FROM `" . DB_PREFIX . "disputes`
	         WHERE `" . DB_PREFIX . "disputes`.`ticketID` = `" . DB_PREFIX . "tickets`.`id`
	       ) AS `disputeCount`
		     FROM `" . DB_PREFIX . "tickets`
		     LEFT JOIN `" . DB_PREFIX . "departments`
	       ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
		     LEFT JOIN `" . DB_PREFIX . "portal`
	       ON `" . DB_PREFIX . "tickets`.`visitorID`  = `" . DB_PREFIX . "portal`.`id`
	       LEFT JOIN `" . DB_PREFIX . "levels`
	       ON (`" . DB_PREFIX . "tickets`.`priority`   = `" . DB_PREFIX . "levels`.`id`
	        OR `" . DB_PREFIX . "tickets`.`priority`  = `" . DB_PREFIX . "levels`.`marker`)
		     WHERE (`" . DB_PREFIX . "portal`.`email`   = '{$email}'
         AND `isDisputed` = 'yes'
			   AND `spamFlag`   = 'no'
			    " . $queryAdd . "
			    " . (!empty($qft) ? implode(mswDefineNewline(), $qft) : '') . "
         ) OR (
          `" . DB_PREFIX . "tickets`.`id` IN(" . (!empty($IDs) ? mswSafeImportString(implode(',', $IDs)) : '0') . ")
          AND `isDisputed` = 'yes'
			    AND `spamFlag`   = 'no'
			    " . $queryAdd . "
			    " . (!empty($qft) ? implode(mswDefineNewline(), $qft) : '') . "
         )
         $oft
		     LIMIT " . $lv[0] . "," . $lv[1] . "
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    if ($count) {
      $c = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT FOUND_ROWS() AS `rows`"));
      return (isset($c->rows) ? $c->rows : '0');
    }
    while ($T = mysqli_fetch_object($q)) {
      $last    = tickets::getLastReply($T->ticketID);
      // Ticket starter..
      $starter = mswSafeDisplay($T->ticketName);
      $lastRep = '';
      $replyBy = '- - - -';
      if ($last[0] != '0') {
        $lastRep = str_replace(array(
          '{date}',
          '{time}'
        ), array(
          $this->datetime->mswDateTimeDisplay($last[1], $this->settings->dateformat),
          $this->datetime->mswDateTimeDisplay($last[1], $this->settings->timeformat)
        ), $lWrap);
        $replyBy = $last[0];
      }
      $data .= str_replace(array(
        '{ticket_id}',
        '{subject}',
        '{priority}',
        '{dept}',
        '{started_by}',
        '{url}',
        '{text_alt}',
        '{start_date}',
        '{start_time}',
        '{last_reply}',
        '{status}',
        '{icon}',
        '{users_in_dispute}',
        '{view}',
        '{last_reply_dashboard}'
      ), array(
        mswTicketNumber($T->ticketID),
        mswSafeDisplay($T->subject),
        tickets::levels($T->priority),
        $this->system->department($T->department, $msg_script30),
        $starter,
        $this->settings->scriptpath . '/?d=' . $T->ticketID,
        mswCleanData($msg_portal8),
        $this->datetime->mswDateTimeDisplay($T->ticketStamp, $this->settings->dateformat),
        $this->datetime->mswDateTimeDisplay($T->ticketStamp, $this->settings->timeformat),
        $replyBy . $lastRep,
        ($T->ticketStatus == 'open' ? mswSafeDisplay($msg_showticket23) : mswSafeDisplay($msg_showticket24)),
        ($T->ticketStatus == 'open' ? 'check-square' : ($T->ticketStatus == 'closed' ? 'lock' : 'minus-square')),
        str_replace(array(
          '{text}'
        ), array(
          str_replace('{count}', ($T->disputeCount + 1), $msg_showticket30)
        ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/tickets/tickets-dispute-users.htm')),
        $msg_public_dashboard6,
        tickets::dashboardStatus($T, 'yes')
      ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/tickets/' . ($queryAdd ? 'tickets-dashboard' : 'ticket-list-entry') . '.htm'));
    }
    return ($data ? trim($data) : str_replace('{text}', ($sch ? $msg_portal21 : ($queryAdd ? $msg_public_dashboard8 : $msg_public_history10)), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/tickets/tickets-no-data.htm')));
  }

  public function ticketList($email, $lv, $count = false, $queryAdd = '') {
    global $msg_portal8, $msg_public_history7, $msg_portal7, $msg_portal21, $msg_showticket23, $msg_showticket24, $msg_script30, $msg_public_dashboard6, $msg_public_dashboard7;
    $data = '';
    $sch  = '';
    $qft  = array();
    $oft  = 'ORDER BY `' . DB_PREFIX . 'tickets`.`id` DESC';
    // Check for search mode..
    if (isset($_GET['qt'])) {
      // Load the skip words array..
      include(PATH . 'control/skipwords.php');
      $chop = array_map('trim', explode(' ', urldecode($_GET['qt'])));
      if (!empty($chop)) {
        foreach ($chop AS $word) {
          if (!in_array($word, $searchSkipWords) && strlen($word) > 1) {
            $word = strtolower($word);
            $sch .= (!$sch ? '' : 'OR ') . "LOWER(`subject`) LIKE '%" . mswSafeImportString(mswCleanData($word)) . "%' OR LOWER(`comments`) LIKE '%" . mswSafeImportString(mswCleanData($word)) . "%'";
          }
        }
        if ($sch) {
          $qft[] = 'AND (' . $sch . ')';
        }
      }
    }
    // Order filters..
    if (isset($_GET['order'])) {
      switch ($_GET['order']) {
        // Subject (ascending)..
        case 'subject_asc':
          $oft = 'ORDER BY `subject`';
          break;
        // Subject (descending)..
        case 'subject_desc':
          $oft = 'ORDER BY `subject` desc';
          break;
        // TicketID (ascending)..
        case 'id_asc':
          $oft = 'ORDER BY `ticketID`';
          break;
        // TicketID (descending)..
        case 'id_desc':
          $oft = 'ORDER BY `ticketID` desc';
          break;
        // Priority (ascending)..
        case 'pr_asc':
          $oft = 'ORDER BY `levelName`';
          break;
        // Priority (descending)..
        case 'pr_desc':
          $oft = 'ORDER BY `levelName` desc';
          break;
        // Department (ascending)..
        case 'dept_asc':
          $oft = 'ORDER BY `deptName`';
          break;
        // Department (descending)..
        case 'dept_desc':
          $oft = 'ORDER BY `deptName` desc';
          break;
        // Date Updated (ascending)..
        case 'rev_asc':
          $oft = 'ORDER BY `lastrevision`';
          break;
        // Date Updated (descending)..
        case 'rev_desc':
          $oft = 'ORDER BY `lastrevision` desc';
          break;
        // Date Added (ascending)..
        case 'date_asc':
          $oft = 'ORDER BY `' . DB_PREFIX . 'tickets`.`ts`';
          break;
        // Date Added (descending)..
        case 'date_desc':
          $oft = 'ORDER BY `' . DB_PREFIX . 'tickets`.`ts` desc';
          break;
      }
    }
    // Service level and department filters..
    if (isset($_GET['filter'])) {
      $qft[] = 'AND `priority` = \'' . mswSafeImportString($_GET['filter']) . '\'';
    }
    if (isset($_GET['dept'])) {
      $qft[] = 'AND `department` = \'' . mswSafeImportString($_GET['dept']) . '\'';
    }
    $lWrap = file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/tickets/tickets-last-reply-date.htm');
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT SQL_CALC_FOUND_ROWS *,
         `" . DB_PREFIX . "tickets`.`id` AS `ticketID`,
		     `" . DB_PREFIX . "tickets`.`ts` AS `ticketStamp`,
	       `" . DB_PREFIX . "portal`.`name` AS `ticketName`,
	       `" . DB_PREFIX . "departments`.`name` AS `deptName`,
	       `" . DB_PREFIX . "levels`.`name` AS `levelName`
		     FROM `" . DB_PREFIX . "tickets`
		     LEFT JOIN `" . DB_PREFIX . "departments`
	       ON `" . DB_PREFIX . "tickets`.`department` = `" . DB_PREFIX . "departments`.`id`
		     LEFT JOIN `" . DB_PREFIX . "portal`
	       ON `" . DB_PREFIX . "tickets`.`visitorID`  = `" . DB_PREFIX . "portal`.`id`
	       LEFT JOIN `" . DB_PREFIX . "levels`
	       ON (`" . DB_PREFIX . "tickets`.`priority`   = `" . DB_PREFIX . "levels`.`id`
	        OR `" . DB_PREFIX . "tickets`.`priority`  = `" . DB_PREFIX . "levels`.`marker`)
         WHERE `" . DB_PREFIX . "portal`.`email`    = '{$email}'
		     AND `isDisputed`                       = 'no'
		     AND `spamFlag`                         = 'no'
		     " . $queryAdd . "
		     " . (!empty($qft) ? implode(mswDefineNewline(), $qft) : '') . "
         $oft
		     LIMIT " . $lv[0] . "," . $lv[1] . "
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    if ($count) {
      $c = mysqli_fetch_object(mysqli_query($GLOBALS["___mysqli_ston"], "SELECT FOUND_ROWS() AS `rows`"));
      return (isset($c->rows) ? $c->rows : '0');
    }
    while ($T = mysqli_fetch_object($q)) {
      $last    = tickets::getLastReply($T->ticketID);
      // Ticket starter..
      $starter = mswSafeDisplay($T->ticketName);
      $lastRep = '';
      $replyBy = '- - - -';
      if ($last[0] != '0') {
        $lastRep = str_replace(array(
          '{date}',
          '{time}'
        ), array(
          $this->datetime->mswDateTimeDisplay($last[1], $this->settings->dateformat),
          $this->datetime->mswDateTimeDisplay($last[1], $this->settings->timeformat)
        ), $lWrap);
        $replyBy = $last[0];
      }
      $data .= str_replace(array(
        '{ticket_id}',
        '{subject}',
        '{priority}',
        '{dept}',
        '{started_by}',
        '{url}',
        '{text_alt}',
        '{start_date}',
        '{start_time}',
        '{last_reply}',
        '{status}',
        '{icon}',
        '{users_in_dispute}',
        '{view}',
        '{last_reply_dashboard}'
      ), array(
        mswTicketNumber($T->ticketID),
        mswSafeDisplay($T->subject),
        tickets::levels($T->priority),
        $this->system->department($T->department, $msg_script30),
        $starter,
        $this->settings->scriptpath . '/?t=' . $T->ticketID,
        mswCleanData($msg_portal8),
        $this->datetime->mswDateTimeDisplay($T->ticketStamp, $this->settings->dateformat),
        $this->datetime->mswDateTimeDisplay($T->ticketStamp, $this->settings->timeformat),
        $replyBy . $lastRep,
        ($T->ticketStatus == 'open' ? mswSafeDisplay($msg_showticket23) : mswSafeDisplay($msg_showticket24)),
        ($T->ticketStatus == 'open' ? 'check-square' : ($T->ticketStatus == 'closed' ? 'lock' : 'minus-square')),
        '',
        $msg_public_dashboard6,
        tickets::dashboardStatus($T, 'no')
      ), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/tickets/' . ($queryAdd ? 'tickets-dashboard' : 'ticket-list-entry') . '.htm'));
    }
    return ($data ? trim($data) : str_replace('{text}', ($sch ? $msg_portal21 : ($queryAdd ? $msg_public_dashboard7 : $msg_portal7)), file_get_contents(PATH . 'content/' . MS_TEMPLATE_SET . '/html/tickets/tickets-no-data.htm')));
  }

  public function dashboardStatus($t, $dispute) {
    global $msg_public_dashboard9, $msg_public_dashboard10, $msg_public_dashboard13;
    if ($t->assignedto == 'waiting') {
      $t->replyStatus = 'waiting';
    }
    switch ($t->replyStatus) {
      case 'admin':
      case 'start':
        return $msg_public_dashboard9;
        break;
      case 'waiting':
        return $msg_public_dashboard9;
        break;
      default:
        return ($dispute == 'yes' ? $msg_public_dashboard13 : $msg_public_dashboard10);
        break;
    }
  }

  public function status($t) {
    global $msg_public_history4, $msg_public_history5, $msg_public_history6, $msg_public_history8;
    if ($t->assignedto == 'waiting') {
      return $msg_public_history8;
    }
    switch ($t->ticketStatus) {
      case 'open':
        return (in_array($t->replyStatus, array(
          'admin',
          'start'
        )) ? $msg_public_history4 : $msg_public_history5);
        break;
      case 'close':
      case 'closed':
        return $msg_public_history6;
        break;
    }
  }

  public function disID($id) {
    $ids = array();
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `ticketID` FROM `" . DB_PREFIX . "disputes`
         WHERE `visitorID` = '{$id}'
         GROUP BY `ticketID`
		     ORDER BY `id`
         ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    while ($U = mysqli_fetch_object($q)) {
      $ids[] = $U->ticketID;
    }
    return $ids;
  }

}

?>