<?php

class msAPI extends jsonHandler {

  public $handler;
  public $settings;
  public $datetime;
  public $upload;
  public $allowed = array();
  private $xml_charset = 'utf-8';
  private $log_folder = 'logs';

  const ATTACH_CHMOD_VALUE = 0777;

  // Logs messages..
  public function log($msg) {
    if ($this->settings->apiLog == 'yes') {
      $existing = (file_exists(PATH . $this->log_folder . '/api-debug-log.txt') ? trim(file_get_contents(PATH . $this->log_folder . '/api-debug-log.txt')) : '');
      if ($existing == '') {
        $message = '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -' . mswDefineNewline();
        $message .= 'API DEBUG LOG: ' . date('d/F/Y @ H:iA', $this->datetime->mswTimeStamp()) . mswDefineNewline();
        $message .= '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -' . mswDefineNewline() . mswDefineNewline();
        $message .= 'Handlers Enabled: ' . ($this->settings->apiHandlers ? strtoupper($this->settings->apiHandlers) : 'None') . mswDefineNewline();
        $message .= mswDefineNewline() . '= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =' . mswDefineNewline() . mswDefineNewline();
      } else {
        $message = '';
      }
      $message .= '[' . date('d/F/Y @ H:i:s', $this->datetime->mswTimeStamp()) . '] Action/Info: ' . str_replace('{nl}', mswDefineNewline(), $msg) . mswDefineNewline();
      $message .= mswDefineNewline() . '= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =' . mswDefineNewline() . mswDefineNewline();
      @file_put_contents(PATH . $this->log_folder . '/api-debug-log.txt', $message, FILE_APPEND);
    }
  }

  public function getHandler($data) {
    $handler = 'json';
    if (strpos($data, '<msapi>') !== false) {
      $handler = 'xml';
    }
    msAPI::log('Handler determined from incoming data: ' . strtoupper($handler));
    return $handler;
  }

  public function read($data) {
    msAPI::log('[' . strtoupper($this->handler) . '] Reading data into readable array supported by all formats');
    switch ($this->handler) {
      case 'json':
        if (!in_array('json', $this->allowed)) {
          msAPI::response('ERROR', 'JSON handler not enabled in settings, please enable.');
        }
        return msAPI::decode($data);
        break;
      case 'xml':
        if (!in_array('xml', $this->allowed)) {
          msAPI::response('ERROR', 'XML handler not enabled in settings, please enable.');
        }
        if (!empty($data)) {
          if (function_exists('simplexml_load_string')) {
            return simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
          } else {
            msAPI::response('ERROR', 'Simple XML functions not enabled on server. Must be enabled to read xml data.');
          }
        } else {
          msAPI::response('ERROR', 'No post data received.');
        }
        break;
    }
  }

  public function ops($data) {
    switch ($this->handler) {
      case 'json':
        return array(
          'key' => (isset($data['api']) ? trim($data['api']) : ''),
          'op' => (isset($data['op']) ? trim($data['op']) : 'ticket')
        );
        break;
      case 'xml':
        return array(
          'key' => (isset($data->api) ? trim($data->api) : ''),
          'op' => (isset($data->op) ? trim($data->op) : 'ticket')
        );
        break;
    }
  }

  public function ticket($data, $levels) {
    msAPI::log('[' . strtoupper($this->handler) . '] Parsing ticket array from received data');
    $tickets = array();
    switch ($this->handler) {
      case 'json':
        if (!empty($data['tickets'])) {
          // Check for multiple..
          if (isset($data['tickets']['ticket'][0])) {
            for ($i = 0; $i < count($data['tickets']['ticket']); $i++) {
              $attach = array();
              $t      = (array) $data['tickets']['ticket'][$i];
              if (!empty($t['attachments']['file'])) {
                foreach ($t['attachments']['file'] AS $a) {
                  $attach[] = (array) $a;
                }
              }
              $tickets[] = array(
                'name' => (isset($t['name']) && $t['name'] ? substr($t['name'], 0, 200) : ''),
                'email' => (isset($t['email']) && $t['email'] && mswIsValidEmail($t['email']) ? $t['email'] : ''),
                'dept' => (isset($t['dept']) && $t['dept'] ? (int) $t['dept'] : '0'),
                'subject' => (isset($t['subject']) && $t['subject'] ? substr($t['subject'], 0, 250) : ''),
                'comments' => (isset($t['comments']) && $t['comments'] ? $t['comments'] : ''),
                'priority' => (isset($t['priority']) && $t['priority'] && in_array($t['priority'], $levels) ? $t['priority'] : ''),
                'fields' => (!empty($t['customfields']) ? (array) $t['customfields'] : array()),
                'language' => (isset($t['language']) && $t['language'] && is_dir(PATH . 'content/language/' . $t['language']) ? $t['language'] : $this->settings->language),
                'attachments' => $attach
              );
            }
          } else {
            $attach = array();
            $t      = (isset($data['tickets']['ticket']) ? (array) $data['tickets']['ticket'] : array());
            if (!empty($t['attachments']['file'])) {
              if (count($t['attachments']['file']) > 1) {
                foreach ($t['attachments']['file'] AS $a) {
                  $attach[] = (array) $a;
                }
              } else {
                $attach[] = $t['attachments']['file'];
              }
            }
            $tickets[] = array(
              'name' => (isset($t['name']) && $t['name'] ? substr($t['name'], 0, 200) : ''),
              'email' => (isset($t['email']) && $t['email'] && mswIsValidEmail($t['email']) ? $t['email'] : ''),
              'dept' => (isset($t['dept']) && $t['dept'] ? (int) $t['dept'] : '0'),
              'subject' => (isset($t['subject']) && $t['subject'] ? substr($t['subject'], 0, 250) : ''),
              'comments' => (isset($t['comments']) && $t['comments'] ? $t['comments'] : ''),
              'priority' => (isset($t['priority']) && $t['priority'] && in_array($t['priority'], $levels) ? $t['priority'] : ''),
              'fields' => (!empty($t['customfields']) ? (array) $t['customfields'] : array()),
              'language' => (isset($t['language']) && $t['language'] && is_dir(PATH . 'content/language/' . $t['language']) ? $t['language'] : $this->settings->language),
              'attachments' => $attach
            );
          }
        }
        break;
      case 'xml':
        if (!empty($data->tickets)) {
          for ($i = 0; $i < count($data->tickets->ticket); $i++) {
            $attach = array();
            $t      = (array) $data->tickets->ticket[$i];
            if (!empty($t['attachments']->file)) {
              foreach ($t['attachments']->file AS $a) {
                $attach[] = (array) $a;
              }
            }
            $tickets[] = array(
              'name' => (isset($t['name']) && $t['name'] ? substr($t['name'], 0, 200) : ''),
              'email' => (isset($t['email']) && $t['email'] && mswIsValidEmail($t['email']) ? $t['email'] : ''),
              'dept' => (isset($t['dept']) && $t['dept'] ? (int) $t['dept'] : '0'),
              'subject' => (isset($t['subject']) && $t['subject'] ? substr($t['subject'], 0, 250) : ''),
              'comments' => (isset($t['comments']) && $t['comments'] ? $t['comments'] : ''),
              'priority' => (isset($t['priority']) && $t['priority'] && in_array($t['priority'], $levels) ? $t['priority'] : ''),
              'fields' => (!empty($t['customfields']) ? (array) $t['customfields'] : array()),
              'language' => (isset($t['language']) && $t['language'] && is_dir(PATH . 'content/language/' . $t['language']) ? $t['language'] : $this->settings->language),
              'attachments' => $attach
            );
          }
        }
        break;
    }
    return array(
      'tickets' => $tickets
    );
  }

  public function account($data, $zones) {
    msAPI::log('[' . strtoupper($this->handler) . '] Parsing account array from received data');
    $accounts = array();
    switch ($this->handler) {
      case 'json':
        if (!empty($data['accounts'])) {
          // Check for multiple..
          if (isset($data['accounts']['account'][0])) {
            for ($i = 0; $i < count($data['accounts']['account']); $i++) {
              $a          = (array) $data['accounts']['account'][$i];
              $accounts[] = array(
                'name' => (isset($a['name']) && $a['name'] ? substr($a['name'], 0, 200) : ''),
                'email' => (isset($a['email']) && $a['email'] && mswIsValidEmail($a['email']) ? $a['email'] : ''),
                'password' => (isset($a['password']) && $a['password'] ? $a['password'] : ''),
                'timezone' => (isset($a['timezone']) && $a['timezone'] && in_array($a['timezone'], $zones) ? $a['timezone'] : $this->settings->timezone),
                'ip' => (isset($a['ip']) && $a['ip'] ? substr($a['ip'], 0, 200) : ''),
                'language' => (isset($a['language']) && $a['language'] && is_dir(PATH . 'content/language/' . $a['language']) ? $a['language'] : $this->settings->language),
                'notes' => (isset($a['notes']) && $a['notes'] ? $a['notes'] : '')
              );
            }
          } else {
            $a          = (array) $data['accounts']['account'];
            $accounts[] = array(
              'name' => (isset($a['name']) && $a['name'] ? substr($a['name'], 0, 200) : ''),
              'email' => (isset($a['email']) && $a['email'] && mswIsValidEmail($a['email']) ? $a['email'] : ''),
              'password' => (isset($a['password']) && $a['password'] ? $a['password'] : ''),
              'timezone' => (isset($a['timezone']) && $a['timezone'] && in_array($a['timezone'], $zones) ? $a['timezone'] : $this->settings->timezone),
              'ip' => (isset($a['ip']) && $a['ip'] ? substr($a['ip'], 0, 200) : ''),
              'language' => (isset($a['language']) && $a['language'] && is_dir(PATH . 'content/language/' . $a['language']) ? $a['language'] : $this->settings->language),
              'notes' => (isset($a['notes']) && $a['notes'] ? $a['notes'] : '')
            );
          }
        }
        break;
      case 'xml':
        if (!empty($data->accounts)) {
          for ($i = 0; $i < count($data->accounts->account); $i++) {
            $a          = (array) $data->accounts->account[$i];
            $accounts[] = array(
              'name' => (isset($a['name']) && $a['name'] ? substr($a['name'], 0, 200) : ''),
              'email' => (isset($a['email']) && $a['email'] && mswIsValidEmail($a['email']) ? $a['email'] : ''),
              'password' => (isset($a['password']) && $a['password'] ? $a['password'] : ''),
              'timezone' => (isset($a['timezone']) && $a['timezone'] && in_array($a['timezone'], $zones) ? $a['timezone'] : $this->settings->timezone),
              'ip' => (isset($a['ip']) && $a['ip'] ? substr($a['ip'], 0, 200) : ''),
              'language' => (isset($a['language']) && $a['language'] && is_dir(PATH . 'content/language/' . $a['language']) ? $a['language'] : $this->settings->language),
              'notes' => (isset($a['notes']) && $a['notes'] ? $a['notes'] : '')
            );
          }
        }
        break;
    }
    return array(
      'accounts' => $accounts
    );
  }

  // Not supported as yet
  public function reply($data) {
    switch ($this->handler) {
      case 'json':
        break;
      case 'xml':
        break;
    }
  }

  public function response($status, $txt) {
    switch ($this->handler) {
      case 'json':
        $resp = msAPI::encode(array(
          'status' => $status,
          'message' => $txt
        ));
        break;
      case 'xml':
        $resp = '<?xml version="1.0" encoding="' . $this->xml_charset . '"?><msapi><status>' . $status . '</status><message>' . $txt . '</message></msapi>';
        break;
    }
    switch ($status) {
      case 'OK':
        msAPI::log($resp);
        break;
      default:
        msAPI::log('[' . strtoupper($this->handler) . '] ' . $txt);
        break;
    }
    echo $resp;
    exit;
  }

  // Add attachment to database..
  public function addAttachmentToDB($ticket, $reply, $n, $s, $d, $mime) {
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
    '{$ticket}',
    '{$reply}',
    '{$d}',
    '{$n}',
    '{$s}',
    '{$mime}'
    )");
    return ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
  }

  // Upload base64 encoded attachment..
  public function uploadEmailAttachment($file, $attachment) {
    $folder = '';
    $U      = $this->settings->attachpath . '/' . $file;
    $Y      = date('Y', $this->datetime->mswTimeStamp());
    $M      = date('m', $this->datetime->mswTimeStamp());
    // Create folders..
    if (!is_dir($this->settings->attachpath . '/' . $Y)) {
      $this->upload->folderCreation($this->settings->attachpath . '/' . $Y, msAPI::ATTACH_CHMOD_VALUE);
    }
    if (is_dir($this->settings->attachpath . '/' . $Y)) {
      if (!is_dir($this->settings->attachpath . '/' . $Y . '/' . $M)) {
        $this->upload->folderCreation($this->settings->attachpath . '/' . $Y . '/' . $M, msAPI::ATTACH_CHMOD_VALUE);
      }
      if (is_dir($this->settings->attachpath . '/' . $Y . '/' . $M)) {
        $U      = $this->settings->attachpath . '/' . $Y . '/' . $M . '/' . $file;
        $folder = $Y . '/' . $M . '/';
      }
    }
    // Does file already exist?
    if (file_exists($U)) {
      @unlink($U);
    }
    file_put_contents($U, base64_decode($attachment), FILE_APPEND);
    return $folder;
  }

  public function insertField($ticket, $field, $data) {
    mysqli_query($GLOBALS["___mysqli_ston"], "INSERT INTO `" . DB_PREFIX . "ticketfields` (
    `ticketID`,
    `fieldID`,
    `replyID`,
    `fieldData`
    ) VALUES (
    '{$ticket}',
    '{$field}',
    '0',
    '" . mswSafeImportString($data) . "'
    )");
  }

}

?>