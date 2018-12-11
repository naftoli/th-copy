<?php

class accountSystem {

  public $settings;

  public function updateIP($id) {
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "portal` SET
    `ip`       = '" . mswIPAddresses() . "',
    `system1`  = '',
    `system2`  = ''
    WHERE `id` = '{$id}'
    ");
  }

  public function clearSystemFlags($id) {
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "portal` SET
    `system1`  = '',
    `system2`  = ''
    WHERE `id` = '{$id}'
    ");
  }

  public function log($user) {
    $defLogs = ($this->settings->defKeepLogs ? unserialize($this->settings->defKeepLogs) : array());
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "log` (
    `ts`,`userID`,`ip`,`type`
    ) VALUES (
    UNIX_TIMESTAMP(),'{$user}','" . mswIPAddresses() . "','acc'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    // Clear previous..
    if (isset($defLogs['acc']) && $defLogs['acc'] > 0) {
      mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "log` WHERE `userID` = '{$user}' AND `id` <
	    (SELECT min(`id`) FROM
      (SELECT `id` FROM `" . DB_PREFIX . "log`
	    WHERE `userID` = '{$user}'
	    AND `type`     = 'acc'
	    ORDER BY `id` DESC LIMIT " . $defLogs['acc'] . "
	    ) AS `" . DB_PREFIX . "log`)") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    }
  }

  public function activate($data = array()) {
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "portal` SET
    `userPass` = '" . mswEncrypt(SECRET_KEY . $data['pass']) . "',
    `verified` = 'yes',
    `enabled`  = 'yes'
    WHERE `id` = '{$data['id']}'
    ");
    return mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
  }

  public function add($add = array()) {
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
    `system1`,
    `system2`,
    `language`,
    `enableLog`
    ) VALUES (
    '" . mswSafeImportString($add['name']) . "',
    UNIX_TIMESTAMP(),
    '" . mswSafeImportString(strtolower($add['email'])) . "',
    '" . mswEncrypt(SECRET_KEY . $add['pass']) . "',
    '{$add['enabled']}',
    '{$add['verified']}',
    '" . mswSafeImportString($add['timezone']) . "',
    '" . mswSafeImportString($add['ip']) . "',
    '" . mswSafeImportString($add['notes']) . "',
    '" . (isset($add['system1']) ? mswSafeImportString($add['system1']) : '') . "',
    '" . (isset($add['system2']) ? mswSafeImportString($add['system2']) : '') . "',
    '" . mswSafeImportString($add['language']) . "',
    '{$this->settings->enableLog}'
    )") or die(mswMysqlErrMsg(((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_errno($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_errno()) ? $___mysqli_res : false)), ((is_object($GLOBALS["___mysqli_ston"])) ? mysqli_error($GLOBALS["___mysqli_ston"]) : (($___mysqli_res = mysqli_connect_error()) ? $___mysqli_res : false)), __LINE__, __FILE__));
    return ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
  }

  public function ban() {
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id` FROM `" . DB_PREFIX . "ban`
         WHERE `type` = 'login'
	       AND `ip`     = '" . mswIPAddresses() . "'
         LIMIT 1
         ");
    $B = mysqli_fetch_object($q);
    // If entry found, increment count, else create new entry..
    if (isset($B->id)) {
      mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "ban` SET
      `count`      = (`count`+1)
      WHERE `type` = 'login'
      AND `ip`     = '" . mswIPAddresses() . "'
      LIMIT 1
      ");
    } else {
      mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "ban` (
      `type`,
      `ip`,
      `count`,
      `banstamp`
      ) VALUES (
      'login',
      '" . mswIPAddresses() . "',
      '1',
      UNIX_TIMESTAMP()
      )");
    }
  }

  public function clearban() {
    mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "ban`
    WHERE `type` = 'login'
    AND `ip`     = '" . mswIPAddresses() . "'
    ");
  }

  public function checkban($s, $dt) {
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT `id`,`banstamp` FROM `" . DB_PREFIX . "ban`
         WHERE `type` = 'login'
	       AND `ip`     = '" . mswIPAddresses() . "'
	       AND `count` >= '{$s->loginLimit}'
         LIMIT 1
         ");
    $B = mysqli_fetch_object($q);
    // If found, check ban time against current timestamp..
    if (isset($B->id)) {
      $now     = $dt->mswUTC();
      $bantime = $B->banstamp;
      $elapsed = (int) ($now - $bantime) / 60;
      if ($s->banTime > 0 && $elapsed >= $s->banTime) {
        // Remove..
        mysqli_query($GLOBALS["___mysqli_ston"], "DELETE FROM `" . DB_PREFIX . "ban`
        WHERE `type` = 'login'
	      AND `ip`     = '" . mswIPAddresses() . "'
	      ");
        return 'ok';
      }
      return 'fail';
    }
    return 'ok';
  }

  public function ms_generate() {
    $pass = '';
    // Check min password isn`t zero by mistake..
    // If it is, set a default..
    if ($this->settings->minPassValue == 0) {
      $this->settings->minPassValue = 8;
    }
    $sec = array(
      'A',
      'B',
      'C',
      'D',
      'E',
      'F',
      'G',
      'H',
      'I',
      'J',
      'K',
      'L',
      'M',
      'N',
      'O',
      'P',
      'Q',
      'R',
      'S',
      'T',
      'U',
      'V',
      'W',
      'X',
      'Y',
      'Z',
      'a',
      'b',
      'c',
      'd',
      'e',
      'f',
      'g',
      'h',
      'i',
      'j',
      'k',
      'l',
      'm',
      'n',
      'o',
      'p',
      'q',
      'r',
      's',
      't',
      'u',
      'v',
      'w',
      'x',
      'y',
      'z',
      '0',
      '1',
      '2',
      '3',
      '4',
      '5',
      '6',
      '7',
      '8',
      '9',
      '[',
      ']',
      '&',
      '*',
      '(',
      ')',
      '#',
      '!',
      '%'
    );
    for ($i = 0; $i < count($sec); $i++) {
      $rand = rand(0, (count($sec) - 1));
      $char = $sec[$rand];
      $pass .= $char;
      if ($this->settings->minPassValue == ($i + 1)) {
        return $pass;
      }
    }
    return $pass;
  }

  public function ms_user() {
    $q = mysqli_query($GLOBALS["___mysqli_ston"], "SELECT * FROM `" . DB_PREFIX . "portal`
         WHERE `email`  = '" . strtolower(MS_PERMISSIONS) . "'
	       AND `verified` = 'yes'
         LIMIT 1
         ");
    $P = mysqli_fetch_object($q);
    return $P;
  }

  public function ms_update($data = array()) {
    // Update portal..
    $ID = (int) $data['id'];
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "portal` SET
    `name`      = '" . mswSafeImportString($data['name']) . "',
    `email`     = '" . mswSafeImportString(strtolower($data['email'])) . "',
    `userPass`  = '{$data['pass']}',
    `timezone`  = '" . mswSafeImportString($data['timezone']) . "',
    `language`  = '" . mswSafeImportString($data['language']) . "'
    WHERE `id`  = '{$ID}'
    ");
    // Update login so we don`t log visitor out..
    if (!isset($data['nologin'])) {
      $_SESSION[mswEncrypt(SECRET_KEY) . '_msw_support'] = strtolower($data['email']);
    }
    return mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
  }

  public function ms_password($email, $password = '') {
    $pass = ($password ? $password : accountSystem::ms_generate());
    mysqli_query($GLOBALS["___mysqli_ston"], "UPDATE `" . DB_PREFIX . "portal` SET
    `userPass`     = '" . mswEncrypt(SECRET_KEY . $pass) . "'
    WHERE LOWER(`email`)  = '" . strtolower($email) . "'
    LIMIT 1
    ");
    return $pass;
  }

}

?>