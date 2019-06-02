<?php
require_once '/api/header/db.php';

class CharidyEmails {

  private $db;
  private $emails = [];
  private $errors = [];

  public function __construct() {
    global $MASHPIA_DB;
    $this->db = $MASHPIA_DB;
  }

  /**
   * emails are sent at various intervals; some are sent before the campaign, some are sent during the camapaign;
   * first email is sent to all donors from donor db that have email address (regardless whether they have given in the past or not)
   * other emails are sent to those that have donated in the past
   */
  public function setRecipients( $first = false ) {
    if ( $first ) {
      $sql = "select distinct email from mashpia_charidy.donors where email != ''";
      $result = $this->db->query( $sql );
      if ( $result ) {
        $rows = $result->fetchAll();
        foreach ( $rows as $row ) {
          if ( filter_var($row['email'], FILTER_VALIDATE_EMAIL) ) {
            $this->emails[] = $row['email'];
          }
        }
      }
    } 
  }

  public function sendEmails() {
    // Subject
    $subject = 'Birthday Reminders for August';

    // Message
    $message = '
    <html>
    <head>
      <title>Birthday Reminders for August</title>
    </head>
    <body>
      <p>Here are the birthdays upcoming in August!</p>
      <table>
        <tr>
          <th>Person</th><th>Day</th><th>Month</th><th>Year</th>
        </tr>
        <tr>
          <td>Johny</td><td>10th</td><td>August</td><td>1970</td>
        </tr>
        <tr>
          <td>Sally</td><td>17th</td><td>August</td><td>1973</td>
        </tr>
      </table>
    </body>
    </html>
    ';

    // To send HTML mail, the Content-type header must be set
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';
    $headers[] = 'From: Tzivos Hashem <cth@mashpia.com>';    

    // Mail it
    foreach ( $this->emails as $to ) {
      if ( !mail($to, $subject, $message, implode("\r\n", $headers)) ) {
        $this->errors[] = "Error sending email to " . $to;
      }
    }
  }

  public function getErrors() {
    return $this->errors;
  }
}