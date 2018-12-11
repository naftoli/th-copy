<?php

class accounts {

  public $settings;
  public $timezones;

  const ACC_EXP_FILENAME = 'accounts-{date}.csv';

  public function purgeAccounts() {
    $days = (isset($_POST['days3']) ? (int) $_POST['days3'] : '0');
    if ($days > 0) {
      $acc = array();
      $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `" . DB_PREFIX . "portal`.`id` AS `accID`,`" . DB_PREFIX . "portal`.`language` AS `lang`,`name`,`email` FROM `" . DB_PREFIX . "portal`
             WHERE DATEDIFF(NOW(),DATE(FROM_UNIXTIME(`ts`))) >= " . $days . "
             HAVING(SELECT count(*) FROM `" . DB_PREFIX . "tickets` WHERE `" . DB_PREFIX . "portal`.`id` = `" . DB_PREFIX . "tickets`.`visitorID` AND `spamFlag` = 'no') = 0
             ");
      while ($A = mysqli_fetch_object($q)) {
        $acc[$A->accID] = array(
          'name' => $A->name,
          'email' => $A->email,
          'lang' => $A->lang
        );
      }
      // Delete..
      if (!empty($acc)) {
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "portal` WHERE `id` IN(" . mswSafeImportString(implode(',', array_keys($acc))) . ")");
      }
    }
    return $acc;
  }

  public function export($head, $head2, $dl) {
    if (!is_writeable(PATH . 'export')) {
      return 'err';
    }
    $file         = PATH . 'export/' . str_replace('{date}', date('dmY-his'), accounts::ACC_EXP_FILENAME);
    $sep          = ',';
    $csv          = array();
    $searchParams = '';
    if (!isset($_POST['orderby'])) {
      $_POST['orderby'] = 'order_asc';
    }
    $orderBy = 'ORDER BY `name`';
    if (isset($_POST['orderby'])) {
      switch ($_POST['orderby']) {
        // Name (ascending)..
        case 'name_asc':
          $orderBy = 'ORDER BY `name`';
          break;
        // Name (descending)..
        case 'name_desc':
          $orderBy = 'ORDER BY `name` desc';
          break;
        // Email Address (ascending)..
        case 'email_asc':
          $orderBy = 'ORDER BY `email`';
          break;
        // Email Address (descending)..
        case 'email_desc':
          $orderBy = 'ORDER BY `email` desc';
          break;
        // Most tickets..
        case 'tickets_asc':
          $orderBy = 'ORDER BY `tickCount` desc';
          break;
        // Least tickets..
        case 'tickets_desc':
          $orderBy = 'ORDER BY `tickCount`';
          break;
      }
    }
    // Filters..
    if (isset($_POST['keys']) && $_POST['keys']) {
      $_POST['keys'] = mswSafeImportString(strtolower($_POST['keys']));
      $filters[]    = "LOWER(`" . DB_PREFIX . "portal`.`name`) LIKE '%" . $_POST['keys'] . "%' OR LOWER(`" . DB_PREFIX . "portal`.`email`) LIKE '%" . $_POST['keys'] . "%' OR LOWER(`" . DB_PREFIX . "portal`.`notes`) LIKE '%" . $_POST['keys'] . "%'";
    }
    if (isset($_POST['from'], $_POST['to']) && $_POST['from'] && $_POST['to']) {
      $from      = $MSDT->mswDatePickerFormat($_POST['from']);
      $to        = $MSDT->mswDatePickerFormat($_POST['to']);
      $filters[] = "DATE(FROM_UNIXTIME(`ts`)) BETWEEN '{$from}' AND '{$to}'";
    }
    // Build search string..
    if (!empty($filters)) {
      for ($i = 0; $i < count($filters); $i++) {
        $searchParams .= ($i ? ' AND (' : 'WHERE (') . $filters[$i] . ')';
      }
    }
    // Disputes
    $sqlDisputes = '';
    if ($this->settings->disputes == 'yes') {
      $sqlDisputes = ',
       (SELECT count(*) FROM `' . DB_PREFIX . 'disputes`
        WHERE `' . DB_PREFIX . 'portal`.`id` = `' . DB_PREFIX . 'disputes`.`visitorID`
       ) AS `dispCount`';
      $head = $head2;
    }
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `name`,`email`,`ip`,`timezone`,
         (SELECT count(*) FROM `" . DB_PREFIX . "tickets`
          WHERE `" . DB_PREFIX . "portal`.`id` = `" . DB_PREFIX . "tickets`.`visitorID`
          AND `spamFlag`   = 'no'
          AND `isDisputed` = 'no'
         ) AS `tickCount`
         $sqlDisputes
         FROM `" . DB_PREFIX . "portal`
         $searchParams
		     $orderBy
		     ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    if (mysqli_num_rows($q) > 0) {
      while ($ACC = mysqli_fetch_object($q)) {
        $csv[] = mswCleanCSV($ACC->name, $sep) . $sep . mswCleanCSV($ACC->email, $sep) . $sep . mswCleanCSV($ACC->ip, $sep) . $sep . mswCleanCSV($ACC->timezone, $sep) . $sep . mswCleanCSV($ACC->tickCount, $sep) . ($this->settings->disputes == 'yes' ? $sep . mswCleanCSV($ACC->dispCount, $sep) : '');
      }
      // Download...
      if (!empty($csv)) {
        // Save file to server and download..
        $dl->write($file, $head . mswDefineNewline() . implode(mswDefineNewline(), $csv));
        if (file_exists($file)) {
          return $file;
        }
      }
    }
    return 'none';
  }

  public function import() {
    $data  = array();
    // Upload CSV file..
    if (isset($_SESSION['upload']['file']) && file_exists($_SESSION['upload']['file'])) {
      $handle = fopen($_SESSION['upload']['file'], 'r');
      if ($handle) {
        while (($CSV = fgetcsv($handle, CSV_MAX_LINES_TO_READ, CSV_IMPORT_DELIMITER, CSV_IMPORT_ENCLOSURE)) !== false) {
          // Add account..
          $_POST['name']     = (isset($CSV[0]) && $CSV[0] ? trim($CSV[0]) : '');
          $_POST['email']    = (isset($CSV[1]) && mswIsValidEmail($CSV[1]) ? trim($CSV[1]) : '');
          $_POST['userPass'] = (isset($CSV[2]) && $CSV[2] ? trim($CSV[2]) : substr(md5(uniqid(rand(), 1)), 0, $this->settings->minPassValue));
          $_POST['enabled']  = 'yes';
          $_POST['timezone'] = (isset($CSV[3]) && in_array($CSV[3], array_keys($this->timezones)) ? trim($CSV[3]) : $this->settings->timezone);
          $_POST['ip']       = '';
          // If name and email are ok and email doesn`t exist, we can add user..
          if (trim($_POST['name']) && trim($_POST['email']) && accounts::check(strtolower($_POST['email'])) == 'accept') {
            // Add to db..
            accounts::add(array(
              'name' => $_POST['name'],
              'email' => $_POST['email'],
              'userPass' => $_POST['userPass'],
              'enabled' => 'yes',
              'timezone' => $_POST['timezone'],
              'ip' => $_POST['ip'],
              'notes' => '',
              'language' => $this->settings->language,
              'enableLog' => $this->settings->enableLog
            ));
            // Add to array..
            $data[] = array(
              $_POST['name'],
              $_POST['email'],
              $_POST['userPass']
            );
          }
        }
        fclose($handle);
      }
      // Clear session file..
      if (file_exists($_SESSION['upload']['file'])) {
        @unlink($_SESSION['upload']['file']);
      }
    }
    return $data;
  }

  public function search() {
    $f   = (isset($_GET['field']) && in_array($_GET['field'], array(
      'name',
      'email',
      'dest_email'
    )) ? $_GET['field'] : 'name');
    $acc = array();
    if ($f == 'dest_email') {
      $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `name`,`email` FROM `" . DB_PREFIX . "portal`
           WHERE (`name` LIKE '%" . mswSafeImportString($_GET['term']) . "%' OR
           `email` LIKE '%" . mswSafeImportString($_GET['term']) . "%')
           AND `enabled`  = 'yes'
           AND `verified` = 'yes'
           " . ((int) $_GET['id'] > 0 ? 'AND `id` != \'' . (int) $_GET['id'] . '\'' : '') . "
           GROUP BY `email`
	         ORDER BY `name`,`email`
		       ");
    } else {
      $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `name`,`email` FROM `" . DB_PREFIX . "portal`
           WHERE `" . $f . "` LIKE '%" . mswSafeImportString($_GET['term']) . "%'
           AND `enabled` = 'yes'
		       AND `verified` = 'yes'
		       " . ((int) $_GET['id'] > 0 ? 'AND `id` != \'' . (int) $_GET['id'] . '\'' : '') . "
		       GROUP BY `email`
	         ORDER BY `name`,`email`
		       ");
    }
    while ($A = mysqli_fetch_object($q)) {
      $n          = array();
      $n['name']  = mswCleanData($A->name);
      $n['email'] = mswCleanData($A->email);
      $acc[]      = $n;
    }
    return $acc;
  }

  public function enable() {
    $_GET['id'] = (int) $_GET['id'];
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "portal` SET
    `enabled`  = '" . ($_GET['changeState'] == 'fa fa-flag fa-fw msw-green cursor_pointer' ? 'no' : 'yes') . "'
    WHERE `id` = '{$_GET['id']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  public function add($add = array()) {
    // Add override..
    if (!empty($add)) {
      foreach ($add AS $k => $v) {
        $_POST[$k] = $v;
      }
    }
    // Populate default password if blank..
    if ($_POST['userPass'] == '') {
      $_POST['userPass'] = substr(md5(uniqid(rand(), 1)), 3, 13);
    }
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "portal` (
    `name`,
    `ts`,
    `email`,
    `userPass`,
    `enabled`,
    `verified`,
    `timezone`,
    `ip`,
    `notes`,
    `reason`,
    `language`,
    `enableLog`
    ) VALUES (
    '" . mswSafeImportString($_POST['name']) . "',
    UNIX_TIMESTAMP(),
    '" . mswSafeImportString(strtolower($_POST['email'])) . "',
    '" . mswEncrypt(SECRET_KEY . $_POST['userPass']) . "',
    '" . (isset($_POST['enabled']) ? 'yes' : 'no') . "',
    'yes',
    '" . mswSafeImportString($_POST['timezone']) . "',
    '" . mswSafeImportString($_POST['ip']) . "',
    '" . mswSafeImportString($_POST['notes']) . "',
    '" . (isset($_POST['reason']) ? mswSafeImportString($_POST['reason']) : '') . "',
    '" . (isset($_POST['language']) ? mswSafeImportString($_POST['language']) : 'english') . "',
    '" . (isset($_POST['enableLog']) ? 'yes' : 'no') . "'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    return ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
  }

  public function update() {
    $_POST['update'] = (int) $_POST['update'];
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "portal` SET
    `name`      = '" . mswSafeImportString($_POST['name']) . "',
    `email`     = '" . mswSafeImportString(strtolower($_POST['email'])) . "',
    `userPass`  = '" . ($_POST['userPass'] ? mswEncrypt(SECRET_KEY . $_POST['userPass']) : $_POST['old_pass']) . "',
    `enabled`   = '" . (isset($_POST['enabled']) ? 'yes' : 'no') . "',
    `timezone`  = '" . mswSafeImportString($_POST['timezone']) . "',
    `ip`        = '" . mswSafeImportString($_POST['ip']) . "',
    `notes`     = '" . mswSafeImportString($_POST['notes']) . "',
    `reason`    = '" . mswSafeImportString($_POST['reason']) . "',
    `language`  = '" . mswSafeImportString($_POST['language']) . "',
    `enableLog` = '" . (isset($_POST['enableLog']) ? 'yes' : 'no') . "'
    WHERE `id`  = '{$_POST['update']}'
    ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
  }

  public function move($from, $to) {
    $rows    = 0;
    $toID    = mswGetTableData('portal', 'email', mswSafeImportString(strtolower($to)));
    $fromID  = mswGetTableData('portal', 'email', mswSafeImportString(strtolower($from)));
    if (isset($toID->id, $fromID->id)) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "tickets` SET
      `lastrevision`     = UNIX_TIMESTAMP(),
      `visitorID`        = '{$toID->id}'
      WHERE `visitorID`  = '{$fromID->id}'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      $rows = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
    }
    return $rows;
  }

  public function delete($t_class) {
    if (!empty($_POST['del'])) {
      $uIDs    = implode(',', $_POST['del']);
      // Get all tickets related to the users that are going to be deleted..
      $tickets = array();
      $q       = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "tickets`
                 WHERE `visitorID` IN({$uIDs})
		             ORDER BY `id`
		             ");
      while ($T = mysqli_fetch_object($q)) {
        $tickets[] = $T->id;
      }
      // If there are tickets, delete all information..
      // We can use the delete operation from the ticket class..
      if (!empty($tickets)) {
        $_POST['ticket'] = $tickets;
        $t_class->deleteTickets();
      }
      // Users info..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "portal`
      WHERE `id` IN({$uIDs})
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      // Delete disputes..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "disputes` WHERE `visitorID` IN({$uIDs})") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      // Log entries..
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "log`
      WHERE `userID` IN({$uIDs})
	    AND `type`      = 'acc'
      ") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
      // Truncate tables to start at 1..
      foreach (array(
        'tickets',
        'attachments',
        'replies',
        'cusfields',
        'ticketfields',
        'disputes',
        'tickethistory',
        'portal'
      ) AS $tables) {
        if (mswRowCount($tables) == 0) {
          @mysqli_query($GLOBALS["___mysqli_ston"], "TRUNCATE TABLE `" . DB_PREFIX . $tables . "`");
        }
      }
      return count($uIDs);
    }
    return '0';
  }

  // Does data exist..
  public function check($data = '', $field = 'email') {
    $SQL = '';
    if (isset($_POST['currID']) && (int) $_POST['currID'] > 0) {
      $_POST['currID'] = (int) $_POST['currID'];
      $SQL             = "AND `id` != '{$_POST['currID']}'";
    }
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "portal`
         WHERE `" . $field . "` = '" . mswSafeImportString(($data ? $data : $_POST['checkEntered'])) . "'
	       $SQL
         LIMIT 1
         ");
    $P = mysqli_fetch_object($q);
    return (isset($P->id) ? 'exists' : 'accept');
  }

  // Search accounts..
  public function searchAccounts($f, $v, $e) {
    $ar  = array();
    $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`name`,`email` FROM `" . DB_PREFIX . "portal`
           WHERE (LOWER(`name`) LIKE '%" . strtolower(mswSafeImportString($v)) . "%'
            OR LOWER(`email`) LIKE '%" . strtolower(mswSafeImportString($v)) . "%')
           AND `enabled` = 'yes'
           AND `verified` = 'yes'
           AND LOWER(`email`) != '" . strtolower(mswSafeImportString($e)) . "'
           ORDER BY `name`, `email`
           ");
    while ($A = mysqli_fetch_object($q)) {
      $ar[] = array(
        'name' => mswSafeDisplay($A->name),
        'email' => $A->email
      );
    }
    return $ar;
  }

  // Search accounts..
  public function searchAccountsPages($v) {
    $ar  = array();
    $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`name`,`email` FROM `" . DB_PREFIX . "portal`
           WHERE (LOWER(`name`) LIKE '%" . strtolower(mswSafeImportString($v)) . "%'
            OR LOWER(`email`) LIKE '%" . strtolower(mswSafeImportString($v)) . "%')
           AND `enabled` = 'yes'
           AND `verified` = 'yes'
           ORDER BY `name`, `email`
           ");
    while ($A = mysqli_fetch_object($q)) {
      $ar[] = array(
        'value' => $A->id,
        'label' => mswSafeDisplay($A->name) . ' (' . $A->email . ')',
        'name' => mswSafeDisplay($A->name),
        'email' => $A->email
      );
    }
    return $ar;
  }

  // Search..
  public function autoSearch($access) {
    $ds  = (isset($_GET['dispute']) ? (int) $_GET['dispute'] : '0');
    $s   = array();
    // All users in current dispute..
    $tk  = mswGetTableData('tickets', 'id', $ds);
    $s[] = $tk->visitorID;
    $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `visitorID` FROM `" . DB_PREFIX . "disputes`
           WHERE `ticketID` = '{$ds}'
           ");
    while ($DU = mysqli_fetch_object($q)) {
      $s[] = $DU->visitorID;
    }
    $ar  = array();
    $q   = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`name`,`email` FROM `" . DB_PREFIX . "portal`
           WHERE (LOWER(`name`) LIKE '%" . strtolower(mswSafeImportString($_GET['term'])) . "%'
            OR LOWER(`email`) LIKE '%" . strtolower(mswSafeImportString($_GET['term'])) . "%'
            OR LOWER(`notes`) LIKE '%" . strtolower(mswSafeImportString($_GET['term'])) . "%'
           AND `enabled` = 'yes'
           AND `verified` = 'yes')
           AND (`id` NOT IN(" . (!empty($s) ? mswSafeImportString(implode(',', $s)) : '0') . "))
           ORDER BY `name`, `email`
           ");
    while ($A = mysqli_fetch_object($q)) {
      $ar[] = array(
        'value' => $A->id,
        'label' => mswSafeDisplay($A->name) . ' (' . $A->email . ')',
        'name' => mswSafeDisplay($A->name),
        'email' => $A->email,
        'access' => $access
      );
    }
    return $ar;
  }

}

?>