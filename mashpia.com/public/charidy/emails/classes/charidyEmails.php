<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';

class CharidyEmails {

  private $db;
  private $ranks = [];
  private $emails = [];
  private $errors = [];
  private $emailNum = 0;

  public function __construct() {
    global $MASHPIA_DB;
    $this->db = $MASHPIA_DB;
    $this->ranks = [
      'Private'         =>  125, 
      'Sergeant'        =>  179, 
      'Sergeant Major'  =>  299, 
      '2nd Lieutenant ' =>  503, 
      '1st Lieutenant ' =>  772, 
      'Captain'         =>  1007, 
      'Major'           =>  1799, 
      'Colonel'         =>  3599, 
      'General'         =>  5399, 
      '1* General'      =>  10079, 
      '2* General'      =>  17999, 
      '3* General'      =>  26999, 
      '4* General'      =>  35999
    ];
  }

  public function setEmailNum( $num ) {
    $this->emailNum = $num;
  }

  /**
   * emails are sent at various intervals; some are sent before the campaign, some are sent during the camapaign;
   * some emails are sent to all donors from donor db that have email address (regardless whether they have given in the past or not)
   * other emails are sent to those that have donated in the past
   */
  public function setRecipients() {
    if ( $this->emailNum == 2 ) {
      $sql = "
          SELECT 
              first_name, last_name, email, MAX(amount) AS highest  
          FROM
              mashpia_charidy.donors
                  JOIN
              mashpia_charidy.donations USING (donor_id)
          WHERE
              email != ''
          GROUP BY email
        ";
    } else {
      $sql = "
          SELECT 
              first_name, last_name, email
          FROM
              mashpia_charidy.donors
          WHERE
              email != ''
          GROUP BY email
        ";
    }
    $result = $this->db->query( $sql );
    if ( $result ) {
      $rows = $result->fetchAll();
      foreach ( $rows as $row ) {
        if ( filter_var($row['email'], FILTER_VALIDATE_EMAIL) ) {
          $info = [
            'to'    => $row['email'], 
            'name'  => $row['first_name'] . ' ' . $row['last_name']
          ];
          if ( isset( $row['highest'] ) ) $info['highest'] = $row['highest'];
          $this->emails[] = $info;
        }
      }
    }
  }

  public function sendEmails() {
    $info = $this->getSubjectMessage();
    $subject = $info['subject'];
    $message = $info['message'];

    // To send HTML mail, the Content-type header must be set
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';
    $headers[] = 'From: Tzivos Hashem <Shimmy@tzivoshashem.org>';   
    $headers[] = 'Reply-To: Tzivos Hashem <Shimmy@tzivoshashem.org>'; 
    // Mail it
    //foreach ( $this->emails as $email ) {
    $email = [
      'to'  =>  'naftoli@tzivoshashem.org', 
      'name'=>  'Naftoli Rapoport', 
      'highest' => 126
    ];
      $to = $email['to'];
      $name = $email['name'];
      // update message with personalized info
      $message = str_replace('FULL_NAME', $name, $message);

      if ( isset( $email['highest'] ) ) {
        $highest = $email['highest'];
        $message = str_replace('PAST_DONATION', $highest, $message);
        // figure out current rank 
        foreach ( $this->ranks as $rank => $amount ) {
          if ( $highest <= $amount ) {
            $cur_rank = $rank;
            break;
          }
        }
        next($this->ranks);
        foreach ( $this->ranks as $rank => $amount ) {
          $next_rank = $rank;
          $new_amount = $amount;
          break;
        }
        if ( !isset( $cur_rank ) ) {
          $cur_rank = '5* General';
          $next_rank = $cur_rank;
          $new_amount = $highest;
        }
        $message = str_replace('CURRENT_RANK', $cur_rank, $message);
        $message = str_replace('NEW_RANK', $next_rank, $message);
        $message = str_replace('AMOUNT', $new_amount, $message);
      }

      if ( !mail($to, $subject, $message, implode("\r\n", $headers)) ) {
        $this->errors[] = "Error sending email to " . $to;
      }
    //}
  }

  private function getSubjectMessage() {
    switch ( $this->emailNum ) {
      case 1:
        // Subject
        $subject = "What are kids in for in 2020?";
        // Message
        $message = '
        <html>
        <head>
        </head>
        <body>
        We invite you to celebrate a year in Hashem\'s Army</a>. Thanks to you (and others like you), Tzivos Hashem has had an incredible year. Take a glimpse into the life of a child, a chayol in Tzivos Hashem: 
        <a href="https://bit.ly/2EO8hlP">bit.ly/2EO8hlP</a> 
        <br /><br />
        How do you transform the world? Ancient philosophers and companies investing in the \'next big thing\' have posed this question, as have myriads of generations in between.
        <br /><br />
        The Rebbe gives the most effective answer.  
        <br /><br />
        Harnessing the innate power and goodness of youth, the Rebbe established an army and inspires them as forerunners in the race to Geulah.
        <br /><br />
        Tzivos Hashem, with your help, is bringing the Rebbe\'s vision to life. 
        <br /><br />
        This Tuesday, Rosh Chodesh Sivan (June 4) join us again on the front lines of transformation at <a href="http://www.charidy.com/th">www.charidy.com/th</a>.
        <br /><br />
        <img src="http://www.mashpia.com/charidy/emails/Sticker%20Charidy%205779.png" width="250" />
        <br /><br />    
        Spread the word!<br /> 
        web: <a href="http://www.charidy.com/th">www.charidy.com/th</a><br />  
        phone: 718.907.8884 <br />
        email: <a href="mailto:cth@tzivoshashem.org">cth@tzivoshashem.org</a><br />
        facebook:Tzivos Hashem <br />
        instagram: tzivos_hashem_international<br />
        #THTransforms<br />
        <hr />
        <div align="center">
        &copy; 2019 Tzivos Hashem<br />
        <address>
          792 Eastern Pkwy, Brooklyn, NY 11213
        </address>
        <br />
        <a href="http://mashpia.com/privacy.html">Privacy Policy</a><br />
        </div>
        </body>
        </html>';
        break;
      case 2:
        $subject = "Can I count on you?";
        $message = '
        <html><head></head><body>
        Dear <b>FULL_NAME</b>,
        <br /><br />
        Tzivos Hashem has had an incredible year of growth, as we continue to transform the world, child by child and mission by mission. We are committed to fulfill the Rebbe\'s vision of a world of completely transformed with the coming of Moshiach. Will you help us continue to grow?
        <br /><br />
        In our past campaigns, you were promoted to CURRENT_RANK in Hashem\'s army with your generous donation of $PAST_DONATION. This year, you can increase your rank to NEW_RANK by donating $AMOUNT.
        <br /><br />
        Tomorrow, <b>Tuesday at 2pm EST</b>, please go to <b><a href="http://charidy.com/th">charidy.com/th</a></b> and use the email <b>EMAIL_ADDRESS</b> to donate. 
        <br /><br />
        Thank you so much, 
        <br /><br />
        Rabbi Moshe Kotlarsky <br />
        Vice Chairman, Merkos L\'inyonei Chinuch 
        <br /><br />
        Rabbi Yerachmiel Benjaminson <br />
        Executive Director of Tzivos Hashem 
        <br /><br />
        Rabbi Sholom Ber Baumgarten <br />
        Director of Tzivos Hashem 
        <br /><br />
        Rabbi Zalman Glick <br />
        Editor-in-chief, Living Lessons 
        <br /><br />
        Rabbi Shimmy and Zelda Weinbaum  <br />
        Generals of the Chayolei Tzivos Hashem Brigade  
        <br /><br />
        <img src="http://www.mashpia.com/charidy/emails/Sticker%20Charidy%205779.png" width="250" />
        <br /><br />    
        Spread the word!<br /> 
        web: <a href="http://www.charidy.com/th">www.charidy.com/th</a><br />  
        phone: 718.907.8884 <br />
        email: <a href="mailto:cth@tzivoshashem.org">cth@tzivoshashem.org</a><br />
        facebook:Tzivos Hashem <br />
        instagram: tzivos_hashem_international<br />
        #THTransforms<br />
        <a href="http://bit.ly/2EO8hlP">A Year in Hashem\'s Army</a><br />
        <hr />
        <div align="center">
        &copy; 2019 Tzivos Hashem<br />
        <address>
          792 Eastern Pkwy, Brooklyn, NY 11213
        </address>
        <br />
        <a href="http://mashpia.com/privacy.html">Privacy Policy</a><br />
        </div>
        </body>
        </html>
        ';
        break;
      case 3:
        break;
      case 4:
        break;
    }
    return [
      'subject' =>  $subject, 
      'message' =>  $message
    ];
  }

  public function getErrors() {
    return $this->errors;
  }
}