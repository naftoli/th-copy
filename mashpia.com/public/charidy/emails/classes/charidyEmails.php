<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
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
  public function setRecipients( $onlyThese = [] ) {
    switch ( $this->emailNum ) {
      case 2:
        // only those that have donated in past
        $sql = "
            SELECT 
                first_name, last_name, email, MAX(amount) AS highest  
            FROM
                mashpia_charidy.donors
                    JOIN
                mashpia_charidy.donations USING (donor_id)
            WHERE
                email != ''";
        break;
      case 1:
        // all donors in database
        $sql = "
            SELECT 
                first_name, last_name, email
            FROM
                mashpia_charidy.donors
            WHERE
                email != '' ";
        break;
      case 3:
        // all donors except parents with registered children
        $sql = "
          SELECT 
              first_name, last_name, email
          FROM
              mashpia_charidy.donors
          WHERE
              email != ''
                  AND (parent_admin_id IS NULL
                  OR parent_admin_id NOT IN (SELECT 
                      admin_id
                  FROM
                      admin_auths aa
                          JOIN
                      users u ON u.user_id = aa.id
                  WHERE
                      aa.auth = 'user'
                          AND u.user_registered > 0))";
        break;
      case 4:
        // all parents with registered children
        $sql = "
          SELECT 
              first_name, last_name, email, parent_admin_id 
          FROM
              mashpia_charidy.donors
          WHERE
              email != ''
                  AND parent_admin_id IN (SELECT 
                      admin_id
                  FROM
                      admin_auths aa
                          JOIN
                      users u ON u.user_id = aa.id
                  WHERE
                      aa.auth = 'user'
                          AND u.user_registered > 0)";
        break;
      case 5:
      case 7:
      case 9:
        // all donors that haven't donated yet this yr
        $sql = "
          SELECT 
              first_name, last_name, email
          FROM
              mashpia_charidy.donors d
          WHERE
              email != ''
                  AND donor_id NOT IN (SELECT 
                      donor_id
                  FROM
                      mashpia_charidy.donations
                  WHERE
                      year = 5779)";
        break;
      case 6:
      case 8:
      case 10:
        // all donors that have already donated this yr
        $sql = "
          SELECT 
              first_name, last_name, email
          FROM
              mashpia_charidy.donors d
                  JOIN
              mashpia_charidy.donations dd USING (donor_id)
          WHERE
              email != '' AND dd.year = 5779";
    }
    if ( !empty( $onlyThese ) ) $sql .= " AND email IN ('" . implode("','", $onlyThese) . "') ";
    $sql .= "GROUP BY email";
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
          if ( isset( $row['parent_admin_id'] ) ) $info['admin_id'] = $row['parent_admin_id'];
          $this->emails[] = $info;
        }
      }
    }
  }

  public function sendEmails() {
    // To send HTML mail, the Content-type header must be set
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';
    $headers[] = 'From: Tzivos Hashem <Shimmy@tzivoshashem.org>';   
    $headers[] = 'Reply-To: Tzivos Hashem <Shimmy@tzivoshashem.org>'; 

    // Mail it
    foreach ( $this->emails as $email ) {
      // needs to be in loop so that message keeps getting loaded with template words
      $info = $this->getSubjectMessage();
      $subject = $info['subject'];
      $message = $info['message'];

      $to = $email['to'];
      $name = $email['name'];
      // update message with personalized info
      if ( $this->emailNum != 4 ) $message = str_replace('FULL_NAME', $name, $message);
      $message = str_replace('EMAIL_ADDRESS', $to, $message);

      if ( isset( $email['highest'] ) ) {
        $highest = $email['highest'];
        $message = str_replace('PAST_DONATION', $highest, $message);
        // figure out current rank 
        $i = 0; // find out which index number is current rank
        foreach ( $this->ranks as $rank => $amount ) {
          $i++;
          if ( $highest <= $amount ) {
            $cur_rank = $rank;
            $new_amount = ++$amount;
            break;
          }
        }
        // find next rank
        $j = 0;
        foreach ( $this->ranks as $rank => $amount ) {
          $j++;
          if ( $j > $i ) {
            $next_rank = $rank;
            break;
          }
        }
        // deal with case where user is already 5* general or 4* general
        if ( !isset( $cur_rank ) ) {
          $cur_rank = '5* General';
          $next_rank = $cur_rank;
          $new_amount = $highest;
        } else if ( !isset( $next_rank ) ) {
          $next_rank = '5* General';
          $new_amount = 36000;
        }
        $message = str_replace('CURRENT_RANK', $cur_rank, $message);
        $message = str_replace('NEW_RANK', $next_rank, $message);
        $message = str_replace('AMOUNT', $new_amount, $message);
      }

      if ( isset( $email['admin_id'] ) ) {
        $children = [];
        // get list of registered children
        $sql = "select first from users u 
                join admin_auths aa on aa.id = u.user_id 
                where u.user_registered > 0 
                and aa.auth = 'user' 
                and aa.admin_id = " . $email['admin_id'];
        $result = $this->db->query( $sql );
        if ( $result ) {
          $rows = $result->fetchAll();
          foreach ( $rows as $row ) $children[] = ucwords( $row['first'] );
        }
        $message = str_replace('CHAYOLIM', implode("<br />", $children), $message);
      }

      if ( !mail($to, $subject, $message, implode("\r\n", $headers)) ) {
        $this->errors[] = "Error sending email to " . $to;
      }
    }
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
        OOOPS, PLEASE DISREGARD THE PREVIOUS EMAIL, THIS IS THE CORRECTED VERSION
        <br /><br />
        <img src="http://www.mashpia.com/charidy/emails/TH%20Charidy%20Email.png" style="max-width: 100%; height: auto;" />
        <br /><br />
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
        $subject = "We are LIVE!";
        $message = '
        <html><head></head><body>
        <img src="http://www.mashpia.com/charidy/emails/TH%20Charidy%20Email.png" style="max-width: 100%; height: auto;" />
        <br /><br />
        Dear FULL_NAME,
        <br /><br />
        The Tzivos Hashem world transformation campaign has begun! From <b>today, Tuesday, at 2 pm, until Wednesday at 6 pm,</b> the clock will be ticking. These <b>28 hours</b> are critical to raise $1 million dollars for Tzivos Hashem; <b>it\'s all or nothing!</b>
        <br /><br />
        Today, join us as <b>#THTransforms!</b><br />
        <a href="http://charidy.com/th">Donate now</a> and quadruple your contribution in Hashem\'s army and your impact on the world.
        <br /><br />
        Here\'s how:
        <ol>
        <li>Go to <a href="http://charidy.com">charidy.com/th</a></li>
        <li>Enter in your email address <b>EMAIL_ADDRESS</b></li>
        <li>Donate generously</li>
        <li>Donate in honor of a specific child in Tzivos Hashem, and they will earn a raffle ticket to win a dollar from the Rebbe.</li>
        <li>Donate more than in the past and go up in rank!</li>
        </ol>
        <br />
        Every $1 is $4<br />
        A Private\'s donation of $18 = $72<br />
        A Sergeant\'s donation of $126 = $504<br />
        A General\'s donation of $3,600 = $14,400
        <br /><br />
        Our future - the future of our children - depends on you,
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
      case 4:
        $subject = "Help your child win a Rebbe dollar";
        $message = '
        <html><head></head><body>
        <img src="http://www.mashpia.com/charidy/emails/TH%20Charidy%20Email.png" style="max-width: 100%; height: auto;" />
        <br /><br />
        Dear Mommy and Tatty,
        <br /><br />
        It’s easy. Please go to <a href="http://charidy.com/th">Charidy.com/TH</a> and make a donation in my honor. Make sure to use this email address EMAIL_ADDRESS so that my name will be entered into the raffle.
        <br /><br />
        Thank you for all your support for Tzivos Hashem, so that I (and my fellow soldiers) can be true chayolim of the Rebbe and all together, we can fulfill our mission of bringing Moshiach, now.
        <br /><br />
        Love,<br />
        CHAYOLIM
        <br />
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
      case 5:
        $subject = " We’ve reached 25%";
        $message = '
        <html><head></head><body>
        <img src="http://www.mashpia.com/charidy/emails/TH%20Charidy%20Email.png" style="max-width: 100%; height: auto;" />
        <br /><br />
        Dear FULL_NAME,
        <br /><br />
        We just passed 25%!
        <br /><br />
        Today, we need YOU so we can continue to provide cutting-edge educational materials and programs that engage, inspire, and unite Jewish children of all backgrounds in a united cause: to make this world a better place and bring Moshiach.
        <br /><br />
        Every dollar quadrupled!
        <br /><br />
        Every $1 is $4<br />
        A Private\'s donation of $18 = $72<br />
        A Sergeant\'s donation of $126 = $504<br />
        A General\'s donation of $3,600 = $14,400
        <br /><br />
        Donate: <a href="http://charidy.com/th">www.charidy.com/th</a>
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
      case 6:
        $subject = " We’ve reached 25%";
        $message = '
        <html><head></head><body>
        <img src="http://www.mashpia.com/charidy/emails/TH%20Charidy%20Email.png" style="max-width: 100%; height: auto;" />
        <br /><br />
        Dear FULL_NAME,
        <br /><br />
        We just passed 25%!
        <br /><br />
        Thanks to YOU, we can continue to provide cutting-edge educational materials and programs that engage, inspire, and unite Jewish children of all backgrounds in a united cause: to make this world a better place and bring Moshiach.
        <br /><br />
        But we still have a ways to go. Get your friends, coworkers, mekuravim and family to join! Every dollar quadrupled!
        <br /><br />
        Every $1 is $4<br />
        A Private\'s donation of $18 = $72<br />
        A Sergeant\'s donation of $126 = $504<br />
        A General\'s donation of $3,600 = $14,400
        <br /><br />
        Donate: <a href="http://charidy.com/th">www.charidy.com/th</a>
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
      case 7:
        $subject = "We’re halfway there!";
        $message = '
        <html><head></head><body>
        <img src="http://www.mashpia.com/charidy/emails/TH%20Charidy%20Email.png" style="max-width: 100%; height: auto;" />
        <br /><br />
        Dear FULL_NAME,
        <br /><br />
        We\'re at 50%!
        <br /><br />
        Have you joined us yet in helping transform the world? <br />
        For just a few more hours, all donations will be quadrupled so that your gifts achieve their greatest potential for our chayolim.
        <br /><br />
        Help us win the war and bring Moshiach! 
        <br /><br />
        Donate: <a href="http://charidy.com/th">www.charidy.com/th</a>
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
      case 8:
        $subject = "We’re halfway there!";
        $message = '
        <html><head></head><body>
        <img src="http://www.mashpia.com/charidy/emails/TH%20Charidy%20Email.png" style="max-width: 100%; height: auto;" />
        <br /><br />
        Dear FULL_NAME,
        <br /><br />
        We\'re at 50%!
        <br /><br />
        Thank you so much for contributing and helping the Army of Hashem transform the world.
        <br /><br />
        For just a few more hours, all donations will be quadrupled. Spread the word, so your family and friends can donate too.
        <br /><br />
        Help us win the war and bring Moshiach! 
        <br /><br />
        Donate: <a href="http://charidy.com/th">www.charidy.com/th</a>
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