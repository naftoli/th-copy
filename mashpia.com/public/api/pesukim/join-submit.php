<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/globals.php';

function clean(&$value) {
  if (is_array($value)) {
    foreach ($value as $k => &$v) {
      clean($v);
    }
  } else {
    $value = mysql_real_escape_string($value);
  }
}

function sendEmailConfirmation($email, $name) {
  $subject = 'Welcome to Tzivos Hashem';

  $msg = 'Welcome to Tzivos Hashem!<br /><br />';
  $msg .= 'Your username is ' . $email . ' and your password is 1234<br /><br />';
  $msg .= 'Your child ' . $name . ' is now a part of Tzivos Hashem!<br /><br />';
  $msg .= 'To login, please use the following link: <a href="https://tzivoshashem.com/mobile">https://tzivoshashem.com/mobile</a><br /><br />';
  $msg .= 'If you have any questions, please feel free to contact us at <a href="mailto:support@tzivoshashem.org">support@tzivoshashem.org</a>.<br /><br />';
  $msg .= 'Thank you for joining Tzivos Hashem!<br /><br />';
  $msg .= 'Best regards,<br /><br />';
  $msg .= 'The Tzivos Hashem Team';
  $msg .= '<br /><br />';
  $msg .= 'To unsubscribe from these emails, please click <a href="https://tzivoshashem.com/mobile">here</a>.<br /><br />';
  $msg .= 'Copyright © 2025 Tzivos Hashem. All rights reserved.';
  $msg .= '<br /><br />';

  // To send HTML mail, the Content-type header must be set
  $headers[] = 'MIME-Version: 1.0';
  $headers[] = 'Content-type: text/html; charset=iso-8859-1';
  // Additional headers
  $headers[] = 'From: Tzivos Hashem <admin@tzivoshashem.org>';
  $headers[] = 'Reply-To: Tzivos Hashem <admin@tzivoshashem.org>';
  return @mail($email, $subject, $msg, implode("\r\n", $headers));
}

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'OPTIONS') { http_response_code(204); exit; }
if ($method !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => ['code' => 'METHOD_NOT_ALLOWED', 'message' => 'Use POST']]);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
clean($input);

$errors = [];
if (empty($input['firstName']))     $errors['firstName'] = 'Required';
if (empty($input['lastName']))      $errors['lastName']  = 'Required';
// Expecting native <input type="date"> → YYYY-MM-DD
if (empty($input['dob']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['dob'])) $errors['dob'] = 'Use YYYY-MM-DD';
if (empty($input['parentEmail']) || !filter_var($input['parentEmail'], FILTER_VALIDATE_EMAIL))
  $errors['parentEmail'] = 'Invalid email';
// Keep phone simple for the mock; backend can tighten later
if (empty($input['parentPhone']))   $errors['parentPhone'] = 'Invalid phone';

if ($errors) {
  http_response_code(422);
  echo json_encode([
    'ok' => false,
    'error' => [
      'code' => 'VALIDATION_ERROR',
      'message' => 'Fix the fields below',
      'fields' => $errors
    ]
  ]);
  exit;
}

$MASHPIA_DB->beginTransaction();

// create a new user
require_once $_SERVER['DOCUMENT_ROOT'] . '/newClasses/newParent.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/newClasses/newSoldier.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.pesukim.php';

try {
  // first find out if there's an admin already that exists with this email
  $sql = "select * from admins where admin_email = '" . $input['parentEmail'] . "'";
  $result = mysql_query($sql);
  if (mysql_num_rows($result) > 0) {
      $parent = mysql_fetch_object($result);
  } else {
      $parent = new NewParent();
      $created = $parent->action([
          'username' => $input['parentEmail'],
          'hashed_pass' => password_hash('1234', PASSWORD_DEFAULT),
          'password' => encryptPassword('1234', ENCRYPTION_KEY),
          'admin_email' => $input['parentEmail']
      ]);
      if (!$created) {
        throw new Exception('Failed to create parent account');
      }
      $parent->admin_id = $parent->getAdminID();
  }

  if ($parent->admin_id) {
      $admin_id = $parent->admin_id;
      $child = new NewSoldier($parent, $input['firstName'], $input['lastName'], $input['dob'], '', 836, 7677, '', '');
      $child->setSchoolType( 8 ); // unaffiliated child
      if (
          $child->create(false)
      ) {
          $user_id = $child->getUserID();
          // create private rank for child and update start date in users table
          $sql1 = "update users set user_start_date = " . unixtojd() . " where user_start_date is null and user_id = " . $user_id;
          $sql2 = "insert ignore into rank_marks set rank_ord = 1, user_id = " . $user_id . ", date_promoted = " . unixtojd();
          $res1 = mysql_query($sql1);
          $res2 = mysql_query($sql2);
          if (!$res1 || !$res2) {
            throw new Exception('Failed to create rank marks');
          }
          if ($input['referral']) {
            $p = new Pesukim($user_id);
            $addedRecruiter = $p->addRecruiter($input['referral']);
            if (!$addedRecruiter) {
              throw new Exception('Failed to add recruiter');
            }
          }
          $emailSent = sendEmailConfirmation($input['parentEmail'], ($input['firstName'] . ' ' . $input['lastName']));
          if (!$emailSent) {
            throw new Exception('Failed to send email confirmation');
          }
      } else {
          throw new Exception('Failed to create child account');
      }
  } else {
    throw new Exception('Failed to create parent account');
  }
} catch (Exception $e) {
  $MASHPIA_DB->rollBack();
  http_response_code(500);
  echo json_encode([
    'ok' => false,
    'error' => ['code' => 'SERVER_ERROR', 'message' => 'Internal server error: ' . $e->getMessage()]
  ]);
  exit;
}

$MASHPIA_DB->commit();
http_response_code(201);
echo json_encode([
  'ok' => true
]);
