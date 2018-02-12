<?php

class imapRoutine {

  public $settings;
  public $datetime;
  public $upload;
  private $log_folder = 'logs';

  const ATTACH_CHMOD_VALUE = 0777;

  public function __construct($imap) {
    $this->imapController = $imap;
  }

  public function filters($data = array(), $filters) {
    $flagged = array(
      'txt' => 'no',
      'matches' => array()
    );
    // Are there any filters.
    if ($filters) {
      $chop = array_map('trim', explode(',', $filters));
      foreach ($data AS $k => $v) {
        foreach ($chop AS $skip) {
          if (strpos(strtolower($v), strtolower($skip)) !== false) {
            $flagged['matches'][] = 'Match found for "' . $skip . '" skip filter in "' . strtoupper($k) . '".';
          }
        }
      }
    }
    // Did we find matches?
    if (!empty($flagged['matches'])) {
      $flagged['txt'] = 'yes';
    }
    return $flagged;
  }

  // Logs messages..
  public function log($msg) {
    if ($this->settings->imap_debug == 'yes') {
      $id       = $this->imapController->id;
      $existing = (file_exists(PATH . $this->log_folder . '/imap-debug-log-' . $id . '.txt') ? trim(@file_get_contents(PATH . $this->log_folder . '/imap-debug-log-' . $id . '.txt')) : '');
      if ($existing == '') {
        $message = '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -' . mswDefineNewline();
        $message .= 'IMAP DEBUG LOG: ' . date('d/F/Y @ H:iA', $this->datetime->mswTimeStamp()) . mswDefineNewline();
        $message .= '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -' . mswDefineNewline() . mswDefineNewline();
        $message .= 'Imap ID: ' . $id . mswDefineNewline();
        $message .= 'Imap Host: ' . $this->imapController->im_host . mswDefineNewline();
        $message .= 'Imap User: ' . $this->imapController->im_user . mswDefineNewline();
        $message .= 'Imap Port: ' . $this->imapController->im_port . mswDefineNewline();
        $message .= 'Imap SSL: ' . ucfirst($this->imapController->im_ssl) . mswDefineNewline();
        $message .= 'Imap Folder: ' . $this->imapController->im_name . mswDefineNewline();
        $message .= mswDefineNewline() . '= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =' . mswDefineNewline() . mswDefineNewline();
      } else {
        $message = '';
      }
      $message .= '[' . mswIPAddresses() . '-' . date('d/F/Y @ H:i:s', $this->datetime->mswTimeStamp()) . '] Action/Info: ' . str_replace('{nl}', mswDefineNewline(), $msg) . mswDefineNewline();
      $message .= mswDefineNewline() . '= = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =' . mswDefineNewline() . mswDefineNewline();
      @file_put_contents(PATH . $this->log_folder . '/imap-debug-log-' . $id . '.txt', $message, FILE_APPEND);
    }
  }

  // Decode string..does nothing..
  public function decodeString($instr) {
    return $instr;
  }

  // Connect to mailbox..
  public function connectToMailBox() {
    global $msg_piping, $msg_piping2, $msg_piping3, $msg_piping4, $msg_piping9;
    $connect = @imap_open('{' . $this->imapController->im_host . ':' . $this->imapController->im_port . '/' . $this->imapController->im_protocol . ($this->imapController->im_ssl == 'yes' ? '/ssl' : '') . ($this->imapController->im_flags ? $this->imapController->im_flags : '') . '}' . $this->imapController->im_name, $this->imapController->im_user, $this->imapController->im_pass);
    if (!$connect) {
      if ($this->settings->imap_debug == 'yes') {
        @imap_close($connect);
        // Silent errors..
        @imap_errors();
        @imap_alerts();
      } else {
        $connect = '';
      }
    }
    // Calling imap_errors here clears stack and prevents notice errors of empty mailbox..
    @imap_errors();
    return $connect;
  }

  // Add attachment to database..
  public function addAttachmentToDB($ticket, $reply, $n, $s, $mime) {
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
    '{$this->imapController->im_dept}',
    '{$n}',
    '{$s}',
    '{$mime}'
    )");
    return ((is_null($___mysqli_res = mysqli_insert_id($GLOBALS["___mysqli_ston"]))) ? false : $___mysqli_res);
  }

  // Upload email attachment..
  public function uploadEmailAttachment($file, $attachment) {
    $folder = '';
    $U      = $this->settings->attachpath . '/' . $file;
    $Y      = date('Y', $this->datetime->mswTimeStamp());
    $M      = date('m', $this->datetime->mswTimeStamp());
    // Create folders..
    if (!is_dir($this->settings->attachpath . '/' . $Y)) {
      $this->upload->folderCreation($this->settings->attachpath . '/' . $Y, imapRoutine::ATTACH_CHMOD_VALUE);
    }
    if (is_dir($this->settings->attachpath . '/' . $Y)) {
      if (!is_dir($this->settings->attachpath . '/' . $Y . '/' . $M)) {
        $this->upload->folderCreation($this->settings->attachpath . '/' . $Y . '/' . $M, imapRoutine::ATTACH_CHMOD_VALUE);
      }
      if (is_dir($this->settings->attachpath . '/' . $Y . '/' . $M)) {
        $U      = $this->settings->attachpath . '/' . $Y . '/' . $M . '/' . $file;
        $folder = $Y . '/' . $M . '/';
      }
    }
    // Is this a base 64 attachment via the api?
    file_put_contents($U, trim($attachment), FILE_APPEND);
    return $folder;
  }

  // Read mailbox..
  public function readMailBox($connection, $msg) {
    $other             = array();
    $headers           = imapRoutine::extractHeaderData(imap_header($connection, $msg));
    $enc               = imapRoutine::getParams(imap_fetchstructure($connection, $msg));
    $other['ticketID'] = imapRoutine::getTicketID($headers['subject'], $headers['email']);
    $other['body']     = imapRoutine::getMessageBody($msg, $connection);
    // Attempt to clean out some of the quoted data..
    $other['body']     = preg_replace('/(^\w.+:\n)?(^>.*(\n|$))+/mi', '', $other['body']);
    return array_merge($headers, $enc, $other);
  }

  // Move mail..
  public function moveMail($connection, $msg) {
    @imap_mail_move($connection, $msg, $this->imapController->im_move);
  }

  // Extract header data..
  public function extractHeaderData($h) {
    global $msg_piping6;
    $sender = $h->from[0];
    return array(
      'from' => imapRoutine::mimeDecode($sender->personal),
      'email' => strtolower($sender->mailbox) . '@' . $sender->host,
      'subject' => ($h->subject ? imapRoutine::mimeDecode($h->subject) : $msg_piping6),
      'messageID' => (isset($h->message_id) ? $h->message_id : '0'),
      'timestamp' => strtotime($h->date)
    );
  }

  // Get ticket id from email subject..
  public function getTicketID($subject, $email) {
    $ticketid = 0;
    if (preg_match("[[#][0-9]{1,12}]", $subject, $regs)) {
      $ticketid = mswReverseTicketNumber(trim(preg_replace('/[^0-9]/', '', $regs[0])));
      $PORTAL   = mswGetTableData('portal', 'email', mswSafeImportString($email), '', '`id`');
      if (isset($PORTAL->id) && mswRowCount('tickets WHERE `id` = \'' . (int) $ticketid . '\' AND `visitorID` = \'' . $PORTAL->id . '\' AND `spamFlag` = \'no\'') > 0) {
        return array(
          'yes',
          $ticketid
        );
      }
    }
    return array(
      'no',
      0
    );
  }

  // Assign mail parameters..
  public function getParams($h) {
    global $msg_pipe_charset;
    $mimeTypes = array(
      'TEXT',
      'MULTIPART',
      'MESSAGE',
      'APPLICATION',
      'AUDIO',
      'IMAGE',
      'VIDEO',
      'OTHER'
    );
    $params = (property_exists($h,'parameters') && isset($h->parameters[0]) ? $h->parameters[0] : '');
    return array(
      'charset' => (property_exists($h,'ifparameters') && property_exists($params,'value') ? $params->value : $msg_pipe_charset),
      'bytes' => (property_exists($h,'bytes') ? $h->bytes : ''),
      'encoding' => (property_exists($h,'encoding') ? $h->encoding : ''),
      'type' => (property_exists($h,'type') ? $h->type : ''),
      'attribute' => (property_exists($h,'attribute') ? $params->attribute : ''),
      'mime' => (!property_exists($h,'subtype') || $h->subtype == '' ? 'TEXT/PLAIN' : (isset($mimeTypes[$h->type]) ? $mimeTypes[$h->type] . '/' . (property_exists($h,'subtype') ? $h->subtype : 'TEXT/PLAIN') : 'TEXT/PLAIN'))
    );
  }

  // Attempt to remove reply quote..
  public function removeReplyQuote($text, $reply) {
    if (strrpos($text, trim($reply)) !== FALSE) {
      return substr($text, 0, strrpos($text, trim($reply)));
    } else {
      return $text;
    }
  }

  // Get message body of email..
  public function getMessageBody($msg, $connection) {
    global $msg_pipe_charset;
    $message = '';
    $message = imapRoutine::getPart($msg, 'TEXT/PLAIN', $connection, $msg_pipe_charset, '', 1.1);
    if ($message == '') {
      $message = imapRoutine::getPart($msg, 'TEXT/PLAIN', $connection, $msg_pipe_charset);
    }
    // If this is a base 64 encoded body, decode it..
    if (base64_encode(base64_decode($message, true)) === $message) {
      $message = base64_decode(chunk_split($message));
    }
    if (strpos($message, ' ') === false && base64_decode($message, true)) {
      $message = base64_decode($message);
    }
    if (!$message) {
      $message = imapRoutine::getPart($msg, 'TEXT/HTML', $connection, $msg_pipe_charset);
      $message = str_replace('</DIV><DIV>', "\n", $message);
      $message = str_replace(array(
        '<br>',
        '<br>',
        '<BR>'
      ), "\n", $message);
    }
    return strip_tags(html_entity_decode(trim($message)));
  }

  // Read mail..
  public function getPart($mid, $mimeType, $connection, $encoding = false, $struct = '', $partNumber = '') {
    if (!$struct && $mid) {
      $struct = imap_fetchstructure($connection, $mid);
    }
    if ($struct && !$struct->ifdparameters && in_array($mimeType, array(
      'TEXT/PLAIN',
      'TEXT/HTML'
    ))) {
      $partNumber = ($partNumber ? $partNumber : 1);
      if ($text = imap_fetchbody($connection, $mid, $partNumber)) {
        if (in_array($struct->encoding, array(0,3,4))) {
          $text    = imapRoutine::decodeText($struct->encoding, $text);
          $charset = null;
          if ($encoding) {
            if ($struct->ifparameters) {
              // Get the original charset of the message if it exists..
              if (isset($struct->parts[0]->parameters[0]->value)) {
                $charset = $struct->parts[0]->parameters[0]->value;
              } else {
                if (isset($struct->parts[0]->parameters[0]->attribute) && strcasecmp($struct->parts[0]->parameters[0]->attribute, 'US-ASCII')) {
                  $charset = trim($struct->parameters[0]->value);
                }
              }
              $text = imapRoutine::mimeEncode($text, $charset, $encoding);
            }
          }
        }
        return $text;
      }
      // Do recursive search
      $text = '';
      if ($struct && !empty($struct->parts)) {
        while (list($i, $substruct) = each($struct->parts)) {
          if ($partNumber) {
            $prefix = $partNumber . '.';
            if (($result = $this->getPart($mid, $mimeType, $connection, $encoding, $substruct, $prefix . ($i + 1), $partNumber))) {
              $text .= $result;
            }
          }
        }
      }
      return $text;
    }
  }

  // Close mailbox..
  public function closeMailbox($connection) {
    imap_expunge($connection);
    imap_close($connection);
    @imap_errors();
    @imap_alerts();
  }

  // Flag message..
  public function flagMessage($connection, $msg) {
    imap_setflag_full($connection, imap_uid($connection, $msg), "\\Seen", ST_UID);
    // Delete if move option not set..
    imap_delete($connection, $msg);
  }

  // Assign mime encoding..
  public function mimeEncode($text, $charset = '', $enc = 'utf-8') {
    global $msg_pipe_charset;
    if ($enc == '' || $enc == '0') {
      $enc = $msg_pipe_charset;
    }
    if ($charset == '') {
      $charset = $msg_pipe_charset;
    }
    $encodings = array(
      'UTF-8',
      'WINDOWS-1251',
      'ISO-8859-15',
      'ISO-8859-1',
      'ISO-8859-5',
      'KOI8-R'
    );
    if (function_exists('iconv') && function_exists('mb_detect_encoding')) {
      return iconv(mb_detect_encoding($text, $encodings), $enc, $text);
    } else if (function_exists('iconv') && $charset) {
      return @iconv($charset, $enc . '//IGNORE', $text);
    } else {
      return utf8_encode(quoted_printable_decode($text));
    }
  }

  // Mime encoding..
  public function mimeDecode($text) {
    $a   = imap_mime_header_decode($text);
    $str = '';
    foreach ($a as $k => $part) {
      $str .= $part->text;
    }
    return $str ? $str : imap_utf8($text);
  }

  // Decode text..
  public function decodeText($encoding, $text) {
    switch ($encoding) {
      case 1:
        $text = quoted_printable_decode(imap_8bit($text));
        break;
      case 2:
        $text = imap_binary($text);
        break;
      case 3:
        $text = imap_base64($text);
        break;
      case 0:
      case 4:
        $text = quoted_printable_decode($text);
        break;
      case 5:
      default:
        break;
    }
    return $text;
  }

  // Read mail attachments into array..
  public function readAttachments($connection, $msg) {
    $attachments = array();
    $att         = imapRoutine::extractAttachments($connection, $msg);
    $count       = 0;
    if (!empty($att)) {
      for ($j = 0; $j < count($att); $j++) {
        if (isset($att[$j]['is_attachment']) && isset($att[$j]['attachment'])) {
          if ($att[$j]['is_attachment'] == 'yes' && $att[$j]['attachment'] != '') {
            ++$count;
            if (LICENCE_VER == 'locked' && $count <= RESTR_ATTACH) {
              $attachments[$count]['file']       = $att[$j]['filename'];
              $attachments[$count]['attachment'] = $att[$j]['attachment'];
              $attachments[$count]['ext']        = (strpos($att[$j]['filename'], '.') !== FALSE ? strrchr(strtolower($att[$j]['filename']), '.') : '.txt');
            } else {
              if (LICENCE_VER == 'unlocked') {
                $attachments[$count]['file']       = $att[$j]['filename'];
                $attachments[$count]['attachment'] = $att[$j]['attachment'];
                $attachments[$count]['ext']        = (strpos($att[$j]['filename'], '.') !== FALSE ? strrchr(strtolower($att[$j]['filename']), '.') : '.txt');
              }
            }
          }
        }
      }
    }
    return $attachments;
  }

  // Extract attachments from email..
  public function extractAttachments($connection, $message_number) {
    $attachments = array();
    $i           = -1;
    $structure   = imap_fetchstructure($connection, $message_number);
    if (isset($structure->parts) && count($structure->parts) > 0) {
      $flatparts = imapRoutine::flattenParts($structure->parts);
      if (!empty($flatparts)) {
        foreach ($flatparts AS $fK => $fV) {
          ++$i;
          $attachments[$i] = array(
            'is_attachment' => 'no',
            'filename' => '',
            'name' => '',
            'attachment' => ''
          );
          if ($fV->ifdparameters > 0) {
            for ($k = 0; $k < count($fV->dparameters); $k++) {
              if (strtolower($fV->dparameters[$k]->attribute) == 'filename') {
                $attachments[$i]['is_attachment'] = 'yes';
                $attachments[$i]['filename']      = $fV->dparameters[$k]->value;
              }
            }
          }
          if ($attachments[$i]['is_attachment'] == 'no' && $fV->ifparameters > 0) {
            for ($j = 0; $j < count($fV->parameters); $j++) {
              if (strtolower($fV->parameters[$j]->attribute) == 'name') {
                $attachments[$i]['is_attachment'] = 'yes';
                $attachments[$i]['filename']      = $fV->parameters[$j]->value;
              }
            }
          }
          if ($attachments[$i]['is_attachment'] == 'yes') {
            $attachments[$i]['attachment'] = imap_fetchbody($connection, $message_number, $fK);
            if ($fV->encoding == 3) { // 3 = BASE64
              $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
            } elseif ($fV->encoding == 4) { // 4 = QUOTED-PRINTABLE
              $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
            }
          } else {
            unset($attachments[$i]);
          }
        }
      }
    }
    // Rebuild indices..
    if (!empty($attachments)) {
      $attachments = array_values($attachments);
    }
    return $attachments;
  }

  // Flatten the structure parts..
  public function flattenParts($messageParts, $flattenedParts = array(), $prefix = '', $index = 1, $fullPrefix = true) {
    if (!empty($messageParts)) {
      foreach ($messageParts as $part) {
        $flattenedParts[$prefix . $index] = $part;
        if (isset($part->parts)) {
          if ($part->type == 2) {
            $flattenedParts = imapRoutine::flattenParts($part->parts, $flattenedParts, $prefix . $index . '.', 0, false);
          } elseif ($fullPrefix) {
            $flattenedParts = imapRoutine::flattenParts($part->parts, $flattenedParts, $prefix . $index . '.');
          } else {
            $flattenedParts = imapRoutine::flattenParts($part->parts, $flattenedParts, $prefix);
          }
          unset($flattenedParts[$prefix . $index]->parts);
        }
        $index++;
      }
    }
    return $flattenedParts;

  }

}

?>