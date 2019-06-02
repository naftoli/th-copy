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
    $subject = 'Tzivos Hashem Conquering the World!';

    // Message
    $message = '
    <html>
    <head>
    </head>
    <body>
    We invite you to celebrate <a href="https://issuu.com/tzivoshashem/docs/year_of_tzivos_hashem_book_5779_lr">a year in Hashem’s Army</a>. Thanks to you (and others like you), Tzivos Hashem has had an incredible year. Take a glimpse into the life of a child, a chayol in Tzivos Hashem. 
    <br /><br />
    How do you transform the world? Ancient philosophers and companies investing in ‘next big thing’ have posed this question—as have myriads of generations in between.
    <br /><br />
    The Rebbe gives the most effective answer.  
    <br /><br />
    Harnessing the innate power and goodness of youth, the Rebbe established an army and inspires them as forerunners in the race to Geulah.
    <br /><br />
    Tzivos Hashem, with your help, is bringing the Rebbe’s vision to life. 
    <br /><br />
    This Tuesday, Rosh Chodesh Sivan (June 4) join us again on the front lines of transformation at <a href="http://www.charidy.com/th">www.charidy.com/th</a>.
    <br /><br />
    <img src="Sticker Charidy 5779.png" />
    <br /><br />    
    Spread the word!<br /> 
    web: <a href="http://www.charidy.com/th">www.charidy.com/th</a><br />  
    phone: 718.907.8884 <br />
    email: <a href="mailto:cth@tzivoshashem.org">cth@tzivoshashem.org</a><br />
    facebook:Tzivos Hashem <br />
    instagram: tzivos_hashem_international<br />
    #THTransforms<br />
    </body>
    </html>
    ';

    // To send HTML mail, the Content-type header must be set
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';
    $headers[] = 'From: Tzivos Hashem <cth@mashpia.com>';    

    // Mail it
    //foreach ( $this->emails as $to ) {
      $to = "naftoli@tzivoshashem.org";
      if ( !mail($to, $subject, $message, implode("\r\n", $headers)) ) {
        $this->errors[] = "Error sending email to " . $to;
      }
    //}
  }

  public function getErrors() {
    return $this->errors;
  }
}