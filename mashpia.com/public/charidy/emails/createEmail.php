<?php
ini_set('display_errors',1);
require_once 'classes/charidyEmails.php';

$testEmails = [
  'shimmy@tzivoshashem.org', 
  'cth@tzivoshashem.org', 
  'mushka@tzivoshashem.org', 
  'naftoli@tzivoshashem.org', 
  'pessi@tzivoshashem.org', 
  'chidon@tzivoshashem.org',
  'hakhel@tzivoshashem.org',
  'design@tzivoshashem.org',
  'chayazirkind@gmail.com', 
  'emmegreene@tzivoshashem.org', 
  'shimmyweinbaum@gmail.com'
];
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Send Charidy Emails</title>
    <style>
      * {
        font-family: Arial;
      }
      body {
        margin: 50px;
      }
      input[type='submit'] {
        padding: 10px;
      }
      .inputs {
        margin-left: 25px;
      }
    </style>
  </head>
  <body>
    <form action="createEmail.php" method="post">
      Please choose which email you would like to send out:<br /><br />
      <div class="inputs">
        <input type="radio" name="email" value="50" />50% Email<br /><br />
        <input type="radio" name="email" value="75" />75% Email<br /><br />
        <input type="radio" name="email" value="bonus" />Bonus Round Email<br /><br />
        <input type="radio" name="email" value="success" />Successful Campaign Email<br /><br />
        <input type="radio" name="email" value="reschedule" />Campaign Rescheduled Email<br /><br />
        <input type="radio" name="email" value="wakeup" />Wakeup Call<br /><br />
        <input type="radio" name="email" value="redo" />Emergency Redo<br /><br />
      </div>

      Please choose if you would like to send a test email (to a few select individuals), or a real actual email to everyone:<br /><br />
      <div class="inputs">
        <input type="radio" name="type" value="test" /> Test Email<br /><br />
        <input type="radio" name="type" value="real" /> Real Email<br /><br />
      </div>

      <input type="submit" name="submit" value="Send Email" />
    </form>
    <br />
    <hr />
    <br />
    <div>
      <?php
      if ( isset( $_POST['submit'] ) ) {
        $email = $_POST['email'];
        $type = $_POST['type'];

        if ( !$email || !$type ) {
          echo "You must choose which email and which type";
          exit;
        }

        switch ( $email ) {
          case '50':
            echo "Sending 50% Reached Emails...<br />";
            $emailNums = [7,8];
            break;
          case '75':
            echo "Sending 75% Reached Emails...<br />";
            $emailNums = [9,10];
            break;
          case 'bonus':
            echo "Sending Bonus Round Emails...<br />";
            $emailNums = [18];
            break;
          case 'success':
            echo "Sending Successful Campaign Emails...<br />";
            $emailNums = [13];
            break;
          case 'reschedule':
            echo "Sending Campaign Rescheduled Email...<br />";
            $emailNums = [14,15];
            break;
          case 'wakeup':
            echo "Sendin Wakeup Call Email...<br />";
            $emailNums = [16];
            break;
          case 'redo':
            echo "Sending Redo Emails...<br />";
            $emailNums = [17];
            break;
        }

        foreach ( $emailNums as $num ) {
          echo "Sending Email #: " . $num . "<br />";
          $e = new charidyEmails();
          $e->setEmailNum( $num );
          if ( $type == 'test' ) $e->setRecipients( $testEmails );
          else $e->setRecipients();
          $e->sendEmails();
      
          $errors = $e->getErrors();
          if ( !empty( $errors ) ) {
            foreach ( $errors as $error ) {
              echo $error . "<br />";
            }
          } else {
            echo "done.<br />";
          }
        }
      }
      ?>
    </div>
  </body>
</html>