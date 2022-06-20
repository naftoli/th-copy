<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/email.php';

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

    public function numRecruits() {
        global $MASHPIA_DB;

        try {
            // get number of recruits
            $stmt = $MASHPIA_DB->prepare("
                SELECT user_id, count(recruited_by) AS total 
                FROM th_chidon 
                WHERE user_id = :user 
                AND year >= 5782
            ");
            $stmt->execute(['user' => $this->user->user_id]);
            $row = $stmt->fetch();
            return $row['total'];
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function sendEmail($recruited) {
        $msg = <<<MSG
Mazel Tov!
$this-user->first $this->user->last has recruited $recruited to start their journey of learning all 613 Mitzvos; 
What a great accomplishment!
MSG;
        $num = $this->numRecruits();
        if (is_numeric($num)) {
            switch ($num) {
                case 1:
                    $msg .= <<<MORE
    Wow! You will be receiving the Chidon Book Light.
    Recruit a 2nd friend to also get the Chidon Rechargeable Fan.
    Recruit a 3rd friend to also get the Chidon Watch.
    Recruit a 4th friend to also get the Chidon Neck Pillow.
    Recruit 5+ friends to also get the Chidon Mini Duffle Bag.

MORE;
                    break;
                case 2:
                    $msg .= <<<MORE
    Wow! In addition to the Chidon Book Light, you will also be receiving the Chidon Rechargeable Fan.
    Recruit a 3rd friend to also get the Chidon Watch.
    Recruit a 4th friend to also get the Chidon Neck Pillow.
    Recruit 5+ friends to also get the Chidon Mini Duffle Bag.

MORE;
                    break;
                case 3:
                    $msg .= <<<MORE
    Wow! In addition to the Chidon Book Light & Rechargeable Fan, you will also be receiving the Chidon Watch.
    Recruit a 4th friend to also get the Chidon Neck Pillow.
    Recruit 5+ friends to also get the Chidon Mini Duffle Bag.

MORE;
                    break;
                case 4:
                    $msg .= <<<MORE
    Wow! In addition to the Chidon Book Light, Rechargeable Fan & Watch, you will also be receiving the Chidon Neck Pillow.
    Recruit 5+ friends to also get the Chidon Mini Duffle Bag.

MORE;
                    break;
                default:
                    $msg .= "Wow! In addition to the Chidon Book Light, Rechargeable Fan, Watch & Neck Pillow, you will also be receiving the Chidon Mini Duffle Bag.<br />";
                    break;
            }

            $msg .= <<<END
    How many more will you recruit to learn 613 Mitzvos!?
    
    P.S. All Recruit a Friend prizes will be shipped at the end of the Chidon together with the rest of the items for the Chidon Experience.
END;
            $params = [];
            $params['to'] = $this->user->parentAccount()['email'];
            $params['from'] = 'chidon@tzivoshashem.org';
            $params['fromAlias'] = 'Chidon Office';
            $params['subject'] = 'You Recruited a Friend to Chidon!';
            $params['msg'] = $msg;

            $mail = new Email;
            $sent = $mail->sendEmail($params);
            if ($sent) return true;
            else return $mail->getError();
        }
        else return $num;
    }
}