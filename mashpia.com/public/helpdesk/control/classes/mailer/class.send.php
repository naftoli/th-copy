<?php

define('SYS_ROOT_PATH', substr(dirname(__file__), 0, strpos(dirname(__file__), 'control') - 1) . '/');
include(dirname(__file__) . '/class.smtp.php');
include(dirname(__file__) . '/class.phpmailer.php');

class msMail extends PHPMailer {

  // Host..
  public $smtp_host = 'localhost';

  // Port..
  public $smtp_port = '';

  // User/Pass..
  public $smtp_user = '';
  public $smtp_pass = '';

  // Security..
  public $smtp_sec = '';

  // Debug..
  public $debug = 'no';

  // Mail switch..
  public $mailSwitch = 'yes';

  // Charset..
  public $charset = 'utf-8';

  // Mail tags array...
  public $vars = array();

  // Custom mail headers..
  public $xheaders = array();

  // Attachments..
  public $attachments = array();

  // Settings..
  public $config = array();

  // Mail debug log..
  private $debug_log_file = 'mail_debug_log.log';

  // Allow insecure connections..
  // Use at your own risk..
  // yes or no value
  private $allowInsecure = 'no';

  // Converts entities..plain text only..
  public function convertChar($data, $type = 'html') {
    $find    = array(
      '&#039;',
      '&quot;',
      '&amp;',
      '&lt;',
      '&gt;'
    );
    $replace = array(
      '\'',
      '"',
      '&',
      '<',
      '>'
    );
    $data    = htmlspecialchars_decode($data);
    if ($type == 'plain') {
      return str_replace($find, $replace, mswCleanData($data));
    } else {
      return mswCleanData($data);
    }
  }

  // Loads tags into array..
  public function addTag($placeholder, $data) {
    $this->vars[$placeholder] = mswSafeDisplay($data);
  }

  // Clears data vars..
  public function clearVars() {
    $this->vars = array();
  }

  // Converts tags..
  public function convertTags($data) {
    if (!empty($this->vars)) {
      foreach ($this->vars AS $tags => $value) {
        $data = str_replace($tags, $value, $data);
      }
    }
    return $data;
  }

  // Cleans spam/form injection input..
  public function injectionCleaner($data) {
    $find    = array(
      "\r",
      "\n",
      "%0a",
      "%0d",
      "content-type:",
      "Content-Type:",
      "BCC:",
      "CC:",
      "boundary=",
      "TO:",
      "bcc:",
      "to:",
      "cc:"
    );
    $replace = array();
    return str_replace($find, $replace, $data);
  }

  // Loads email template..
  public function template($file) {
    // Is this a template or just text?
    if (substr(strtolower($file), -4) == '.txt') {
      return (file_exists($file) ? trim(file_get_contents($file)) : 'An error occurred opening the "' . $file . '" file. Check that this file exists in the correct "content/language/*/mail-templates/" folder.');
    }
    return $file;
  }

  // HTML mail wrapper..
  public function htmlWrap($tmp) {
    global $MSPARSER;
    $msg   = $this->convertTags($this->template($tmp['template']));
    $parse = explode('<-{separater}->', $msg);
    // Check for 3 slots, eg: 2 separators..
    if (count($parse) == 3) {
      $head = trim($parse[0]);
      $cont = trim($parse[1]);
      $foot = trim($parse[2]);
    } else {
      $head = mswCleanData($this->config['website']);
      $cont = str_replace('<-{separater}->', '', trim($msg));
      $foot = mswCleanData($this->config['scriptpath']);
    }
    // Auto parse hyperlinks..
    $head = $this->convertChar($MSPARSER->mswAutoLinkParser($head));
    $cont = $this->convertChar($MSPARSER->mswAutoLinkParser($cont));
    $foot = $this->convertChar($MSPARSER->mswAutoLinkParser($foot));
    // Auto parse line breaks..
    $head = mswNL2BR($head);
    $cont = mswNL2BR($cont);
    $foot = mswNL2BR($foot);
    // Parse html message with wrapper..
    $find = array(
      '{CHARSET}',
      '{TITLE}',
      '{HEADER}',
      '{CONTENT}',
      '{FOOTER}'
    );
    $repl = array(
      $this->charset,
      mswSafeDisplay($this->config['website']),
      $head,
      $cont,
      $foot . mswNL2BR($this->appendFooterToEmails('html'))
    );
    // Language override..
    if (isset($tmp['language'])) {
      $this->config['language'] = $tmp['language'];
    }
    $html = str_replace($find, $repl, file_get_contents(SYS_ROOT_PATH . 'content/language/' . $this->config['language'] . '/mail-templates/html-wrapper.html'));
    return $html;
  }

  // Plain text separator..
  public function plainTxtSep() {
    return '---------------------------------------------';
  }

  // Plain text mail wrapper..
  public function plainWrap($tmp) {
    $msg   = $this->convertChar($this->convertTags($this->template($tmp['template']), 'plain'));
    $parse = explode('<-{separater}->', $msg);
    // Check for 3 slots, eg: 2 separators..
    if (count($parse) == 3) {
      $head = trim(strip_tags($parse[0]));
      $cont = trim(strip_tags($parse[1]));
      $foot = trim(strip_tags($parse[2]));
    } else {
      $head = mswCleanData(strip_tags($this->config['website']));
      $cont = trim(strip_tags($msg));
      $foot = mswCleanData(strip_tags($this->config['scriptpath']));
    }
    return $head . mswDefineNewline() . $this->plainTxtSep() . mswDefineNewline() . mswDefineNewline() . $cont . mswDefineNewline() . mswDefineNewline() . $this->plainTxtSep() . mswDefineNewline() . $foot . $this->appendFooterToEmails();
  }

  // Footer for free version..
  // Please don`t remove the footer unless you have purchased a licence..
  // https://www.maiansupport.com/purchase.html
  public function appendFooterToEmails($type = 'plain') {
    if (LICENCE_VER == 'unlocked') {
      return '';
    }
    switch($type) {
      case 'plain':
        $string  = mswDefineNewline() . mswDefineNewline();
        $string .= 'Free HelpDesk System Powered by ' . SCRIPT_NAME . mswDefineNewline();
        $string .= 'https://www.' . SCRIPT_URL;
        break;
      case 'html':
        $string  = mswDefineNewline() . mswDefineNewline();
        $string .= 'Free HelpDesk System Powered by ' . SCRIPT_NAME . mswDefineNewline();
        $string .= '<a href="https://www.' . SCRIPT_URL . '">https://www.' . SCRIPT_URL . '</a>';
        break;
    }
    return $string;
  }

  // Sends mail..
  public function sendMSMail($mail = array()) {
    if ($this->mailSwitch == 'yes') {
      $this->IsSMTP();
      $this->Port       = $this->smtp_port;
      $this->Host       = $this->smtp_host;
      $this->SMTPAuth   = ($this->smtp_user && $this->smtp_pass ? true : false);
      $this->SMTPSecure = (in_array($this->smtp_sec, array(
        '',
        'tls',
        'ssl'
      )) ? $this->smtp_sec : '');
      // Allow insecure connections?
      if ($this->allowInsecure == 'yes') {
        $this->SMTPOptions = array(
          'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
          )
        );
      }
      // Keep connection alive..
      if (isset($mail['alive'])) {
        $this->SMTPKeepAlive = true;
      }
      $this->Username = $this->smtp_user;
      $this->Password = $this->smtp_pass;
      $this->CharSet  = ($this->charset ? $this->charset : 'utf-8');
      // Enable debug..
      if ($this->debug == 'yes') {
        $this->SMTPDebug = 2;
        $this->Debugoutput = function($str, $level) {
          file_put_contents(SYS_ROOT_PATH . 'logs/' . $this->debug_log_file, $str . (function_exists('mswDefineNewline') ? mswDefineNewline() : PHP_EOL), FILE_APPEND);
        };
      }
      // Custom mail headers..
      if (!empty($this->xheaders)) {
        foreach ($this->xheaders AS $k => $v) {
          $this->AddCustomHeader($k . ':' . $v);
        }
      }
      // From/to headers..
      $this->From     = $this->injectionCleaner($mail['from_email']);
      $this->FromName = $this->injectionCleaner($this->convertChar($mail['from_name']));
      $this->AddAddress($this->injectionCleaner($mail['to_email']), $this->injectionCleaner($this->convertChar($mail['to_name'])));
      // Reply to..
      if (!empty($mail['replyto'])) {
        $this->AddReplyTo($mail['replyto']['email'], $mail['replyto']['name']);
      }
      // Additional standard addresses..
      if (isset($mail['add-emails']) && $mail['add-emails']) {
        $addEmails = array_map('trim', explode(',', $mail['add-emails']));
        if (!empty($addEmails)) {
          foreach ($addEmails AS $aAddresses) {
            $this->AddAddress($this->injectionCleaner($aAddresses), $this->injectionCleaner($this->convertChar($mail['to_name'])));
          }
        }
      }
      // Carbon copy addresses..
      if (!empty($mail['cc'])) {
        foreach ($mail['cc'] AS $cc_email => $cc_name) {
          $this->AddCC($cc_email, $cc_name);
        }
      }
      // Blind carbon copy addresses..
      if (!empty($mail['bcc'])) {
        foreach ($mail['bcc'] AS $bcc_email => $bcc_name) {
          $this->AddBCC($bcc_email, $bcc_name);
        }
      }
      $this->WordWrap = 1000;
      // Subject..
      $this->Subject  = $this->convertChar($mail['subject']);
      // Message body..
      $this->MsgHTML($this->htmlWrap($mail));
      $this->AltBody = $this->plainWrap($mail);
      // Attachments..
      if (!empty($this->attachments)) {
        foreach ($this->attachments AS $f => $n) {
          $this->AddAttachment($f, $n);
        }
      }
      // Send mail..
      $this->Send();
      // Clear all recipient data..
      $this->ClearReplyTos();
      $this->ClearAllRecipients();
    }
  }

}

//---------------------------------------------------
// Check licence ver - please do not alter or change
//---------------------------------------------------

if (!defined('LICENCE_VER') || !class_exists('mswLic')) {
  die(@base64_decode('U3lzdGVtIGVycm9yLCBwbGVhc2UgY29udGFjdCBNUyBXb3JsZCBAIDxhIGhyZWY9Im1haWx0bzpzdXBwb3J0QG1haWFuc2NyaXB0d29ybGQuY28udWsiPnN1cHBvcnRAbWFpYW5zY3JpcHR3b3JsZC5jby51azwvYT48YnI+PGJyPlNvcnJ5IGZvciB0aGUgaW5jb252ZW5pZW5jZS4='));
}

?>