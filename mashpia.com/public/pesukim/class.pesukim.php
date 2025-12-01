<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

class Pesukim 
{
    private $user_id;
    private $subject_id;
    private $year;

    public function __construct($user_id) {
        $this->user_id = $user_id;
        $this->subject_id = 136;
        $this->year = GlobalSettings::getCurrentYear();
    }

    public function checkTaskCompletion($task) {
        global $MASHPIA_DB;
        $grid_id = $task['grid_id'];
        $stmt = $MASHPIA_DB->prepare("
            SELECT * from date_tasks_marks 
            WHERE grid_id = :grid_id 
            AND user_id = :user_id
        ");
        $stmt->execute([
            'grid_id' => $grid_id,
            'user_id' => $this->user_id
        ]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return count($result) > 0;
    }

    public function addRecruiter($recruiter) {
        global $MASHPIA_DB;

        // get user id from serial number
        $stmt = $MASHPIA_DB->prepare("
            SELECT user_id from users where user_serial = :id or user_id = :id
        ");
        $stmt->execute(['id' => $recruiter]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !$row['user_id']) {
            return false;
        }
        $recruiter_id = $row['user_id'];
        
        $stmt = $MASHPIA_DB->prepare("
            INSERT INTO pesukim_recruits (recruiter_id, recruited_id, year)
            VALUES (:recruiter_id, :recruited_id, :year)
        ");
        $res = $stmt->execute([
            'recruiter_id' => $recruiter_id,
            'recruited_id' => $this->user_id,
            'year' => $this->year
        ]);
        if ($res) {
            $pointsAdded = $this->addPoints(300, $recruiter_id);
            if (! $pointsAdded) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    public function addPoints($points, $user_id) {
        global $MASHPIA_DB;
        // get institution id
        $institution_id = $this->getSchoolId($user_id);
        if (!$institution_id) {
            return false;
        }
        $stmt = $MASHPIA_DB->prepare("
            INSERT INTO pointsDB.user_points(user_id, institution_id, points, resource_name, auction_only_points)
            VALUES (:user_id, :institution_id, :points, 'auction_only_points', 1)
        ");
        $res = $stmt->execute(['user_id' => $user_id, 'institution_id' => $institution_id, 'points' => $points]);
        return $res;
    }

    public function deletePoints($points, $user_id) {
        global $MASHPIA_DB;
        // get institution id
        $institution_id = $this->getSchoolId($user_id);
        if (!$institution_id) {
            return false;
        }
        $stmt = $MASHPIA_DB->prepare("
            INSERT INTO pointsDB.user_points(user_id, institution_id, points, resource_name, auction_only_points)
            VALUES (:user_id, :institution_id, :points, 'auction_only_points', 1)
        ");
        $res = $stmt->execute(['user_id' => $user_id, 'institution_id' => $institution_id, 'points' => -abs($points)]);
        return $res;
    }

    private function getSchoolId($user_id) {
        global $MASHPIA_DB;
        $stmt = $MASHPIA_DB->prepare("
            SELECT school_id from users where user_id = :user_id
        ");
        $stmt->execute(['user_id' => $user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['school_id'] ?? false;
    }

    public function addDuchRecruit($info) {
        global $MASHPIA_DB;
        $stmt = $MASHPIA_DB->prepare("
            INSERT INTO pesukim_duch_recruits (user_id, name, mothers_name, year)
            VALUES (:user_id, :name, :mothers_name, :year)
        ");
        $res = $stmt->execute([
            'user_id' => $this->user_id,
            'name' => $info['name'],
            'mothers_name' => $info['mothers_name'],
            'year' => $this->year
        ]);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    public function getDuchRecruits() {
        global $MASHPIA_DB;
        $stmt = $MASHPIA_DB->prepare("
            SELECT * from pesukim_duch_recruits
            WHERE user_id = :user_id
        ");
        $stmt->execute(['user_id' => $this->user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecruiter() {
        global $MASHPIA_DB;
        $stmt = $MASHPIA_DB->prepare("
            SELECT recruiter_id from pesukim_recruits
            WHERE recruited_id = :recruited_id
        ");
        $stmt->execute(['recruited_id' => $this->user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['recruiter_id'] ?? false;
    }

    public function addRecruit($recruited_id) {
        global $MASHPIA_DB;
        // $MASHPIA_DB->beginTransaction();
        // $success = true;

        $stmt = $MASHPIA_DB->prepare("
            INSERT INTO pesukim_recruits (recruiter_id, recruited_id, year)
            VALUES (:recruiter_id, :recruited_id, :year)
        ");
        $res = $stmt->execute([
            'recruiter_id' => $this->user_id,
            'recruited_id' => $recruited_id,
            'year' => $this->year
        ]);
        if ($res) {
            $pointsAdded = $this->addPoints(200, $this->user_id);
            if ($pointsAdded) {
                $recruiter = $this->getRecruiter();
                while ($recruiter) {
                    $pesukim->addPoints(20, $recruiter);
                    $pesukim = new Pesukim($recruiter);
                    $recruiter = $pesukim->getRecruiter();
                }
            }
        } 

        return $res && $pointsAdded;
        // if ($success) {
        //     $MASHPIA_DB->commit();
        //     return true;
        // } else {
        //     $MASHPIA_DB->rollBack();
        //     return false;
        // }
    }

    public function getRecruits() {
        global $MASHPIA_DB;
        $stmt = $MASHPIA_DB->prepare("
            SELECT * from pesukim_recruits pr 
            JOIN users u ON u.user_id = pr.recruited_id 
            WHERE pr.recruiter_id = :recruiter_id
        ");
        $stmt->execute([
            'recruiter_id' => $this->user_id
        ]);
        // $stmt->debugDumpParams();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addMechunach($info) {
        global $MASHPIA_DB;

        // $MASHPIA_DB->beginTransaction();
        // $success = true;

        // add mechunach to database
        $stmt = $MASHPIA_DB->prepare("
            INSERT INTO pesukim_mechunachim (mechanech_user_id, name, phone, email, year) 
            VALUES (:user_id, :name, :phone, :email, :year)
        ");
        $res = $stmt->execute([
            'user_id' => $this->user_id,
            'name' => $info['name'],
            'phone' => $info['phone'],
            'email' => $info['email'],
            'year' => $this->year
        ]);
        if ($res) {
            $mechunach_id = $MASHPIA_DB->lastInsertId();
        
            // create a verification code and send to parent
            $code = $this->createVerificationCode();
            $stmt = $MASHPIA_DB->prepare("
                UPDATE pesukim_mechunachim 
                SET verification_code = :code
                WHERE mechunach_id = :mechunach_id
            ");
            $res2 = $stmt->execute([
                'mechunach_id' => $mechunach_id,
                'code' => $code
            ]);
            if ($res2) {
                $this->sendVerificationCode($code);
            }
        }
        return $res && $res2;
        // if ($success) {
        //     $MASHPIA_DB->commit();
        //     return true;
        // } else {
        //     $MASHPIA_DB->rollBack();
        //     return false;
        // }
    }
    
    private function createVerificationCode() {
        // create a random 6 digit code alpha-numeric
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= chr(rand(48, 90)); // 0-9, A-Z, a-z
        }
        return $code;
    }

    private function sendVerificationCode($code) {
        // send email to parent
        $parent_info = $this->getParentInfo();
        if ($parent_info) {
            $parent_email = $parent_info['admin_email'];
            $parent_name = $parent_info['first'] . ' ' . $parent_info['last'];
            $msg = "
                <h2>Hello, $parent_name!</h2>
                <p>Your verification code is: <b>$code</b></p>
                <p>Please use this code to verify your mechanech.</p>
                <p>Thank you,</p>
                <p>Chayolei Tzivos Hashem</p>
                <br />
                <p>To unsubscribe from these emails please click <a href='https://mashpia.com/unsubscribe.php'><b>here</b></a></p>
            ";
            // To send HTML mail, the Content-type header must be set
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-type: text/html; charset=iso-8859-1';
            $headers[] = 'From: Chayolei Tzivos Hashem <admin@tzivoshashem.org>';
            $headers[] = 'CC: Tziviaweinbaum@gmail.com';
            if (! @mail($parent_email, 'Your Chayolei Tzivos Hashem Verification', $msg, implode("\r\n", $headers))) {
                // send mail to myself
                $to = 'naftoli@tzivoshashem.org';
                @mail($to, 'Error sending mechunach verification code email', $msg, implode("\r\n", $headers));
            }
        }
    }

    public function verifyMechunach($code, $mechunach_id) {
        global $MASHPIA_DB;
        $stmt = $MASHPIA_DB->prepare("
            UPDATE pesukim_mechunachim
            SET verified = 1, date_verified = NOW()
            WHERE mechanech_user_id = :user_id 
            AND mechunach_id = :mechunach_id
            AND verification_code = :code
        ");
        $stmt->execute([
            'user_id' => $this->user_id,
            'mechunach_id' => $mechunach_id,
            'code' => urldecode($code)
        ]);
        return $stmt->rowCount() > 0;
    }

    private function getParentInfo() {
        global $MASHPIA_DB;
        $stmt = $MASHPIA_DB->prepare("
            SELECT * from admins a 
            JOIN admin_auths aa USING (admin_id)
            WHERE aa.auth = 'user'
            AND aa.id = :user_id
        ");
        $stmt->execute(['user_id' => $this->user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMechunachim() {
        global $MASHPIA_DB;
        $stmt = $MASHPIA_DB->prepare("
            SELECT * from pesukim_mechunachim
            WHERE mechanech_user_id = :user_id 
        ");
        $stmt->execute(['user_id' => $this->user_id]);
        // $stmt->debugDumpParams(); // uncomment this to see the query parameters
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkMechunachTask($date_task_id, $mechunach_id) {
        global $MASHPIA_DB;
        $stmt = $MASHPIA_DB->prepare("
            SELECT 
                *
            FROM
                date_tasks_marks dtm
                    JOIN
                date_tasks dt USING (date_task_id)
            WHERE
                grid_id = (SELECT 
                        grid_id
                    FROM
                        date_tasks
                    WHERE
                        date_task_id = :date_task_id)
                    AND mechunach_id = :mechunach_id
                    AND user_id = :user_id
        ");
        $stmt->execute(['date_task_id' => $date_task_id, 'mechunach_id' => $mechunach_id, 'user_id' => $this->user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return $row['mark_date'];
        else return false;
        // $stmt->debugDumpParams();
        // return $stmt->rowCount() > 0;
    }

    public function getMinutes() {
        global $MASHPIA_DB;

        $grid_id = 15036;
        $dates = GlobalSettings::getCurYearDates();

        $sql = "SELECT IFNULL(SUM(done_qty), 0) as total 
                FROM date_tasks_marks 
                JOIN date_tasks dt USING (date_task_id) 
                WHERE dt.grid_id = :grid_id 
                AND mark_date >= :start 
                AND mark_date <= :end 
                AND user_id = :user_id";
        $stmt = $MASHPIA_DB->prepare($sql);
        $stmt->execute(['grid_id' => $grid_id, 'start' => $dates['start'], 'end' => $dates['end'], 'user_id' => $this->user_id]);
        return intval($stmt->fetch(PDO::FETCH_ASSOC)['total']);
    }
}