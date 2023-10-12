<?php
ini_set('disable_errors', 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';

class Recruits {
    private $user;

//    private $prizes = [
//        'Chidon Book Light',
//        'Chidon Rechargeable Fan',
//        'Chidon Watch',
//        'Chidon Neck Pillow',
//        'Chidon Mini Duffle Bag'
//    ];

    public function __construct($serial) {
        global $MASHPIA_DB;

        // get user info
        $stmt = $MASHPIA_DB->prepare("SELECT * FROM users WHERE user_serial = :serial");
        $stmt->execute(['serial' => $serial]);
        $this->user = $stmt->fetch(PDO::FETCH_OBJ);
    }

    private function getEmail() {
        global $MASHPIA_DB;

        $stmt = $MASHPIA_DB->prepare("
            SELECT admin_email FROM admins WHERE admin_id IN (
                SELECT admin_id FROM admin_auths WHERE id = :user AND auth = 'user'
            )
        ");
        $stmt->execute(['user' => $this->user->user_id]);
        $row = $stmt->fetch();
        return $row['admin_email'];
    }

    public function numRecruits() {
        global $MASHPIA_DB;

        try {
            // get number of recruits
            $stmt = $MASHPIA_DB->prepare("
                SELECT user_id, count(recruited_by) AS total 
                FROM th_chidon 
                WHERE recruited_by = :serial 
                AND year >= 5782
            ");
            $stmt->execute(['serial' => $this->user->user_serial]);
            $row = $stmt->fetch();
            return $row['total'];
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function sendEmail($recruited) {
        $name = $this->user->first . ' ' . $this->user->last;
        $msg = "
Mazel Tov!<br /><br />
$name has recruited $recruited to start their journey of learning all 613 Mitzvos;<br /><br />
What a great accomplishment!<br /><br />";
        $num = $this->numRecruits();
        if (is_numeric($num)) {
            switch ($num) {
                case 1:
                    $msg .= "
    Wow! You will be receiving the Chidon Book Light.<br />
    Recruit a 2nd friend to also get the Chidon Rechargeable Fan.<br />
    Recruit a 3rd friend to also get the Chidon Watch.<br />
    Recruit a 4th friend to also get the Chidon Neck Pillow.<br />
    Recruit 5+ friends to also get the Chidon Mini Duffle Bag.<br />
    <br />";
                    break;
                case 2:
                    $msg .= "
    Wow! In addition to the Chidon Book Light, you will also be receiving the Chidon Rechargeable Fan.<br />
    Recruit a 3rd friend to also get the Chidon Watch.<br />
    Recruit a 4th friend to also get the Chidon Neck Pillow.<br />
    Recruit 5+ friends to also get the Chidon Mini Duffle Bag.<br />
    <br />";
                    break;
                case 3:
                    $msg .= "
    Wow! In addition to the Chidon Book Light & Rechargeable Fan, you will also be receiving the Chidon Watch.<br />
    Recruit a 4th friend to also get the Chidon Neck Pillow.<br />
    Recruit 5+ friends to also get the Chidon Mini Duffle Bag.<br />
    <br />";
                    break;
                case 4:
                    $msg .= "
    Wow! In addition to the Chidon Book Light, Rechargeable Fan & Watch, you will also be receiving the Chidon Neck Pillow.<br />
    Recruit 5+ friends to also get the Chidon Mini Duffle Bag.<br />
    <br />";
                    break;
                default:
                    $msg .= "Wow! In addition to the Chidon Book Light, Rechargeable Fan, Watch & Neck Pillow, you will also be receiving the Chidon Mini Duffle Bag.<br />";
                    break;
            }

            $msg .= "<br />
    How many more will you recruit to learn 613 Mitzvos!?<br /><br />    
    P.S. All Recruit a Friend prizes will be shipped at the end of the Chidon together with the rest of the items for the Chidon Experience.";

            $to = $this->getEmail();

            $subject = 'You Recruited a Friend to Chidon!';

            // To send HTML mail, the Content-type header must be set
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=iso-8859-1';

            // Additional headers
            $headers[] = 'To: ' . $to;
            $headers[] = 'From: Chidon Office <chidon@tzivoshashem.org>';

            // Mail it
            @mail($to, $subject, $msg, implode("\r\n", $headers));
        }
        else return $num;
    }
}