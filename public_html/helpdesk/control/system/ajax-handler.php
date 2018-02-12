<?php

// Check var and parent load..
if (!defined('PARENT') || !defined('MS_PERMISSIONS') || !isset($_GET['ajax'])) {
  exit;
}

define('AJAX_HANDLER', 1);

// Define array for json response..
$json = array();

include(PATH . 'control/classes/class.upload.php');
$MSUPL  = new msUpload();

// Reset captcha if user is logged in..
if ($SETTINGS->enCapLogin == 'yes' && MS_PERMISSIONS != 'guest' && isset($LI_ACC->name)) {
  $SETTINGS->recaptchaPublicKey  = '';
  $SETTINGS->recaptchaPrivateKey = '';
}

// Load mail params
include(PATH . 'control/mail-data.php');

// Handle request..
switch ($_GET['ajax']) {

  //========================
  // Ticket Reply
  //========================

  case 'tickreply':
    include(PATH . 'control/system/accounts/account-ticket-reply.php');
    break;

  //========================
  // Create Ticket
  //========================

  case 'create-ticket':
    include(PATH . 'control/system/accounts/account-ticket-create.php');
    break;

  //========================
  // Create account..
  //========================

  case 'create':
    if ($SETTINGS->createAcc == 'yes' && isset($_POST['name']) && isset($_POST['email']) && isset($_POST['email2'])) {
      if ($_POST['name'] == '') {
        $eFields[] = $msadminlangpublic[3];
      }
      if (!mswIsValidEmail($_POST['email'])) {
        $eFields[] = $msg_public_create5;
      } else {
        if (strtolower($_POST['email']) != strtolower($_POST['email2'])) {
          $eFields[] = $msadminlangpublic[2];
        } else {
          if (mswRowCount('portal WHERE LOWER(`email`) = \'' . mswSafeImportString(strtolower($_POST['email'])) . '\'') > 0) {
            $eFields[] = $msg_public_create6;
          }
        }
      }
      if (!isset($_SESSION['ggrcver']) && $SETTINGS->recaptchaPublicKey && $SETTINGS->recaptchaPrivateKey) {
        $ggres = (isset($_POST['g-recaptcha-response']) ? $GRECAP->verify() : 'fail');
        switch($ggres) {
          case 'nothing-supported':
            $eFields[] = $msadminlangpublic[4];
            break;
          case 'fail':
            $eFields[] = $msg_public_create11;
            break;
          case 'ok':
            $_SESSION['ggrcver'] = 'verified' . time();
            break;
        }
      }
      // Show errors..
      if (!empty($eFields)) {
        $json = array(
          'status' => 'err',
          'field' => implode(',', $eFields),
          'msg' => implode('<br>', $eFields)
        );
      } else {
        // Create account..
        $pass   = $MSACC->ms_generate();
        $code   = substr(md5(uniqid(rand(), 1)), 3, 23);
        $userID = $MSACC->add(array(
          'name' => $_POST['name'],
          'email' => $_POST['email'],
          'pass' => $pass,
          'enabled' => 'no',
          'verified' => 'no',
          'timezone' => '',
          'ip' => mswIPAddresses(),
          'notes' => '',
          'language' => $SETTINGS->language,
          'system1' => $code
        ));
        // Send verification email..
        if ($userID > 0) {
          $MSMAIL->addTag('{NAME}', $_POST['name']);
          $MSMAIL->addTag('{LOGIN_URL}', $SETTINGS->scriptpath);
          $MSMAIL->addTag('{CODE}', $code);
          $MSMAIL->sendMSMail(array(
            'from_email' => $SETTINGS->email,
            'from_name' => $SETTINGS->website,
            'to_email' => $_POST['email'],
            'to_name' => $_POST['name'],
            'subject' => str_replace(array(
              '{website}'
            ), array(
              $SETTINGS->website
            ), $emailSubjects['acc-verify']),
            'replyto' => array(
              'name' => $SETTINGS->website,
              'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
            ),
            'template' => PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/account-verification.txt',
            'language' => $SETTINGS->language
          ));
        }
        $json = array(
          'status' => 'ok-dialog',
          'field' => 'msg',
          'msg' => str_replace('{email}', $_POST['email'], $msg_script_action8)
        );
        if (isset($_SESSION['ggrcver'])) {
          unset($_SESSION['ggrcver']);
        }
      }
    }
    break;

  //========================
  // Update profile
  //========================

  case 'profile':
    if (!isset($LI_ACC->id)) {
      exit;
    }
    if (isset($_POST['email']) && $_POST['email']) {
      // Is email same as current = error..
      if (strtolower($_POST['email']) == $LI_ACC->email) {
        $json = array(
          'status' => 'err',
          'field' => 'email',
          'tab' => 'two',
          'msg' => $msg_portal31
        );
      } else {
        // Is email2 field blank = error..
        if ($_POST['email2'] == '') {
          $json = array(
            'status' => 'err',
            'field' => 'email2',
            'tab' => 'two',
            'msg' => $msg_portal30
          );
        } else {
          // Is new email valid = error..
          if (!mswIsValidEmail($_POST['email'])) {
            $json = array(
              'status' => 'err',
              'field' => 'email',
              'tab' => 'two',
              'msg' => $msg_portal30
            );
          } else {
            // Do mail fields match = error..
            if (strtolower($_POST['email']) != strtolower($_POST['email2'])) {
              $json = array(
                'status' => 'err',
                'field' => 'email',
                'tab' => 'two',
                'msg' => $msg_public_profile
              );
            } else {
              // Does new email exist somewhere else = error..
              if (mswRowCount('portal WHERE LOWER(`email`) = \'' . mswSafeImportString(strtolower($_POST['email'])) . '\' AND `id` != \'' . $LI_ACC->id . '\'') > 0) {
                $json = array(
                  'status' => 'err',
                  'field' => 'email',
                  'tab' => 'two',
                  'msg' => $msg_public_profile5
                );
              }
              $newEmailConfirmed = $_POST['email'];
            }
          }
        }
      }
    }
    // What about password..
    if (isset($_POST['curpass']) && $_POST['curpass']) {
      if (mswEncrypt(SECRET_KEY . $_POST['curpass']) != $LI_ACC->userPass) {
        $json = array(
          'status' => 'err',
          'field' => 'curpass',
          'tab' => 'three',
          'msg' => $msg_public_profile10
        );
      } else {
        if ($_POST['newpass'] == '' || $_POST['newpass2'] == '') {
          $json = array(
            'status' => 'err',
            'field' => 'newpass',
            'tab' => 'three',
            'msg' => $msg_public_profile11
          );
        } else {
          if ($_POST['newpass'] != $_POST['newpass2']) {
            $json = array(
              'status' => 'err',
              'field' => 'newpass',
              'tab' => 'three',
              'msg' => $msg_public_profile12
            );
          } else {
            if (strlen($_POST['newpass']) < $SETTINGS->minPassValue) {
              $json = array(
                'status' => 'err',
                'field' => 'newpass',
                'tab' => 'three',
                'msg' => str_replace('{min}', $SETTINGS->minPassValue, $msg_public_profile13)
              );
            } else {
              $newPassConfirmed = mswEncrypt(SECRET_KEY . $_POST['newpass']);
            }
          }
        }
      }
    }
    // If ok, update..
    if (!isset($json['status'])) {
      // Update profile..
      $rows = $MSACC->ms_update(array(
        'id' => $LI_ACC->id,
        'name' => (isset($_POST['name']) && $_POST['name'] ? substr($_POST['name'], 0, 200) : $LI_ACC->name),
        'email' => (isset($newEmailConfirmed) ? $newEmailConfirmed : $LI_ACC->email),
        'pass' => (isset($newPassConfirmed) ? $newPassConfirmed : $LI_ACC->userPass),
        'timezone' => (isset($_POST['timezone']) && $_POST['timezone'] != '0' ? $_POST['timezone'] : $LI_ACC->timezone),
        'language' => (isset($_POST['language']) ? $_POST['language'] : $LI_ACC->language)
      ));
      // Send email notification if something got updated..
      if ($rows > 0 && $SETTINGS->accProfNotify == 'yes') {
        // Send mail..
        $MSMAIL->addTag('{NAME}', $LI_ACC->name);
        // Check template..
        if ($LI_ACC->language && file_exists(PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/profile-updated.txt')) {
          $mailT = PATH . 'content/language/' . $LI_ACC->language . '/mail-templates/profile-updated.txt';
          $pLang = $LI_ACC->language;
        } else {
          $mailT = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/profile-updated.txt';
        }
        $MSMAIL->sendMSMail(array(
          'from_email' => $SETTINGS->email,
          'from_name' => $SETTINGS->website,
          'to_email' => $LI_ACC->email,
          'to_name' => $LI_ACC->name,
          'subject' => str_replace(array(
            '{website}'
          ), array(
            $SETTINGS->website
          ), $emailSubjects['profile-update']),
          'replyto' => array(
            'name' => $SETTINGS->website,
            'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
          ),
          'template' => $mailT,
          'language' => (isset($pLang) ? $pLang : $SETTINGS->language)
        ));
      }
      // We are done..
      $json = array(
        'status' => 'ok',
        'field' => 'msg',
        'msg' => $msg_public_profile2
      );
    }
    break;

  //========================
  // New pass request
  //========================

  case 'newpass':
    if (isset($_POST['email']) && $_POST['email']) {
      if (!mswIsValidEmail($_POST['email'])) {
        $json = array(
          'status' => 'err',
          'field' => 'email',
          'msg' => $msg_script_action6
        );
      } else {
        $ACC = mswGetTableData('portal', 'email', mswSafeImportString(strtolower($_POST['email'])), 'AND `verified` = \'yes\'');
        if (!isset($ACC->id)) {
          $json = array(
            'status' => 'err',
            'field' => 'email',
            'msg' => $msg_script_action7
          );
        } else {
          // Create new password...
          $newPass = $MSACC->ms_password($ACC->email);
          // Send mail..
          $MSMAIL->addTag('{PASSWORD}', $newPass);
          $MSMAIL->addTag('{NAME}', $ACC->name);
          $MSMAIL->addTag('{EMAIL}', $ACC->email);
          // Check template..
          if ($ACC->language && file_exists(PATH . 'content/language/' . $ACC->language . '/mail-templates/new-password.txt')) {
            $mailT = PATH . 'content/language/' . $ACC->language . '/mail-templates/new-password.txt';
            $pLang = $ACC->language;
          } else {
            $mailT = PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/new-password.txt';
          }
          $MSMAIL->sendMSMail(array(
            'from_email' => $SETTINGS->email,
            'from_name' => $SETTINGS->website,
            'to_email' => $ACC->email,
            'to_name' => $ACC->name,
            'subject' => str_replace(array(
              '{website}'
            ), array(
              $SETTINGS->website
            ), $emailSubjects['new-password']),
            'replyto' => array(
              'name' => $SETTINGS->website,
              'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
            ),
            'template' => $mailT,
            'language' => (isset($pLang) ? $pLang : $SETTINGS->language)
          ));
          $json = array(
            'status' => 'ok-dialog',
            'field' => 'msg',
            'msg' => str_replace('{email}', $ACC->email, $msg_script_action8)
          );
        }
      }
    }
    break;

  //========================
  // Account login
  //========================

  case 'login':
    $redr = 'index.php?p=dashboard';
    // If login limit and ban time is enabled, check first..
    if ($SETTINGS->loginLimit > 0) {
      $ban = $MSACC->checkban($SETTINGS, $MSDT);
      if ($ban == 'fail') {
        $json = array(
          'status' => 'err',
          'field' => 'email',
          'msg' => $msg_public_login4
        );
      }
    }
    if (!isset($json['status']) && isset($_POST['email'], $_POST['pass']) && $_POST['email'] && $_POST['pass']) {
      // Check for valid email..
      if (!mswIsValidEmail($_POST['email'])) {
        $json = array(
          'status' => 'err',
          'field' => 'email',
          'msg' => $msg_main13
        );
      } else {
        // Now check account..
        $ACC = mswGetTableData('portal', 'email', mswSafeImportString(strtolower($_POST['email'])), 'AND `userPass` = \'' . mswEncrypt(SECRET_KEY . $_POST['pass']) . '\' AND `verified` = \'yes\'');
        // Legacy..
        if (!isset($ACC->email)) {
          $ACC = mswGetTableData('portal', 'email', mswSafeImportString(strtolower($_POST['email'])), 'AND `userPass` = \'' . md5(SECRET_KEY . $_POST['pass']) . '\' AND `verified` = \'yes\'');
        }
        if (isset($ACC->email)) {
          // Check access..
          if ($ACC->enabled == 'yes') {
            $_SESSION[mswEncrypt(SECRET_KEY) . '_msw_support'] = $ACC->email;
            // Ticket/dispute redirection..
            if (isset($_SESSION['ticketAccessID']) && (int) $_SESSION['ticketAccessID'] > 0) {
              $redr = 'index.php?t=' . $_SESSION['ticketAccessID'];
              unset($_SESSION['ticketAccessID']);
            }
            if (isset($_SESSION['disputeAccessID']) && (int) $_SESSION['disputeAccessID'] > 0) {
              $redr = 'index.php?d=' . $_SESSION['disputeAccessID'];
              unset($_SESSION['disputeAccessID']);
            }
            if (isset($_SESSION['redirectPage'])) {
              $redr = 'index.php?p=open';
              unset($_SESSION['redirectPage']);
            }
            // Add entry log..
            if ($ACC->enableLog == 'yes') {
              $MSACC->log($ACC->id);
            }
            // Clear any ban logs..
            $MSACC->clearban();
            // Update IP if blank (eg: admin added)
            if (mswIPAddresses() != $ACC->ip) {
              $MSACC->updateIP($ACC->id);
            } else {
              // Clear system flags..
              $MSACC->clearSystemFlags($ACC->id);
            }
            $json = array(
              'status' => 'ok',
              'field' => 'redirect',
              'msg' => $redr
            );
          } else {
            $_SESSION[mswEncrypt(SECRET_KEY) . '_msw_support'] = $ACC->email;
            $json                                       = array(
              'status' => 'ok',
              'field' => 'redirect',
              'msg' => 'index.php'
            );
          }
        } else {
          // Is max attempts and ban time enabled?
          if ($SETTINGS->loginLimit > 0) {
            $MSACC->ban();
          }
          $json = array(
            'status' => 'err',
            'field' => 'email',
            'msg' => $msg_main8
          );
        }
      }
    }
    break;

  //========================
  // Resend Confirmation
  //========================

  case 'resend':
    $json = array(
      'status' => 'err',
      'msg' => $msadminlangpublic[6]
    );
    if (isset($_POST['code']) && ctype_alnum($_POST['code'])) {
      $A = mswGetTableData('portal', 'system1', mswSafeImportString($_POST['code']));
      if (isset($A->id) && $A->verified == 'yes') {
        $pass = $MSACC->ms_generate();
        $MSACC->ms_update(array(
          'id' => $A->id,
          'name' => $A->name,
          'email' => $A->email,
          'pass' => mswEncrypt(SECRET_KEY . $pass),
          'timezone' => $A->timezone,
          'language' => $A->language,
          'nologin' => 'yes'
        ));
        $MSMAIL->addTag('{NAME}', $A->name);
        $MSMAIL->addTag('{EMAIL}', $A->email);
        $MSMAIL->addTag('{PASS}', $pass);
        $MSMAIL->addTag('{LOGIN_URL}', $SETTINGS->scriptpath);
        $MSMAIL->sendMSMail(array(
          'from_email' => $SETTINGS->email,
          'from_name' => $SETTINGS->website,
          'to_email' => $A->email,
          'to_name' => $A->name,
          'subject' => str_replace(array(
            '{website}'
          ), array(
            $SETTINGS->website
          ), $emailSubjects['acc-verified']),
          'replyto' => array(
            'name' => $SETTINGS->website,
            'email' => ($SETTINGS->replyto ? $SETTINGS->replyto : $SETTINGS->email)
          ),
          'template' => PATH . 'content/language/' . $SETTINGS->language . '/mail-templates/account-verified.txt',
          'language' => $SETTINGS->language
        ));
        // We are done..
        $json = array(
          'status' => 'ok-dialog',
          'field' => 'msg',
          'msg' => str_replace('{email}', $A->email, $msg_script_action8)
        );
      }
    }
    break;

  case 'voting':
    $json = array(
      'status' => 'err',
      'msg' => $msadminlangpublic[6]
    );
    if ($SETTINGS->enableVotes == 'yes' && isset($_GET['id']) && isset($_GET['vote'])) {
      if ($SETTINGS->multiplevotes == 'no') {
        if (isset($_COOKIE[mswEncrypt(SECRET_KEY) . COOKIE_NAME])) {
          $votes   = unserialize($_COOKIE[mswEncrypt(SECRET_KEY) . COOKIE_NAME]);
          if (in_array($_GET['id'], $votes)) {
            define('VOTING_STOP', 1);
            $json['msg'] = $msadminlang3_1faq[10];
          }
        }
      }
      if (!defined('VOTING_STOP')) {
        $ret = $FAQ->vote();
        if ($ret == 'ok') {
          $sofar          = $FAQ->stats($_GET['id']);
          $json['status'] = 'ok';
          $json['yes']    = $sofar[0];
          $json['no']     = $sofar[1];
          $json['total']  = $sofar[2];
        }
      }
    }
    break;

  //========================
  // Department loader..
  //========================

  case 'dept':
    $pre  = array(
      'sub' => '',
      'msg' => ''
    );
    $flds = '';
    if (isset($_GET['dp'])) {
      $dep = (int) $_GET['dp'];
      if ($dep > 0) {
        $pre  = $MSTICKET->preFill($dep);
        $flds = $MSFIELDS->build('ticket', $dep);
      }
    }
    $json = array(
      'subject' => $pre['sub'],
      'comments' => $pre['msg'],
      'fields' => $flds
    );
    break;

  //=========================
  // Auto Open Sub Cats
  //=========================

  case 'loadsubcats':
    $json = array(
      'parent' => $FAQ->getParentFaqCat()
    );
    break;

}

// For version 3.1+
$other = array(
  'sys' => $msadminlang3_1[2]
);

// We are done..
echo $MSJSON->encode(array_merge($json, $other));
exit;

?>