<?php
require_once( __DIR__ . '/db.php' ); // make sure that db.php is loaded in as we rely on some functions...
require_once __DIR__ . '/class.globalSettings.php';

class Points
{
    private $user_id;
    private $usercode;
    private $store_reset;
    private $debug;
    private $school_id;
    private $australian;
    private $yearStart;
    // const YEARSTART = 2457934; // also need to update kiosk controller getHebrewPoints function with proper dates when year changes
    // const YEARSTARTAUSTRALIA = 2457629; // also need to update kiosk controller getHebrewPoints function with proper dates when year changes
    
    public function __construct( $id ) {
        $this->user_id = $id;
        $result = mysql_query(
            "SELECT store_reset, school_id, user_code FROM users LEFT JOIN schools USING (school_id) WHERE user_id = "
            . mysql_real_escape_string( $id )
        );
        $row = mysql_fetch_assoc($result);	
        $this->store_reset = $row['store_reset'];
        $this->usercode = $row['user_code']; 
        $this->school_id = $row['school_id'];
        $australian = [ 55, 66, 110, 112, 180 ];
        if ( in_array( $this->school_id, $australian ) ) $this->australian = true;
        $this->debug = false;
        $this->setPointsStart();
    }

    public function setPointsStart() {
        $dates = GlobalSettings::getPointsDates();
        $this->yearStart = $this->australian ? $dates['points_start_australia'] : $dates['points_start'];
    }
    
    public function setDebugOn() {
        $this->debug = true;
    }
    
    public function getTotalPoints() {
        $points = $this->getTotalMarks("WHERE user_id = $this->user_id");
//        echo $points;
//        if ($this->debug) {
//            echo "Points from mashpia: " . $points . "<br />";
//            echo "Points from V2: " . $this->getNonMarkPoints() . "<br />";
//        }
        $points += intval($this->getNonMarkPoints());
        // $arrParams['user_code'] = $this->usercode;
        // $arrPoints = header_total_points( $arrParams );
        // if ( $this->debug ) {
        //     echo "<pre>";
        //     print_r( $arrParams );
        //     print_r( $arrPoints );
        //     echo "</pre>";
        // }
        // $points += $arrPoints[$arrParams['user_code']];
        return $points;
    }
    
    public function getTotalThisYear() {
        $points = $this->getTotalMarks("WHERE user_id = $this->user_id and mark_date >= " . $this->yearStart);
        if ($this->debug) {
            echo "Points from mashpia: " . $points . "<br />";
            echo "Points from V2: " . $this->getNonMarkPoints() . "<br />";
        }
        $points += intval($this->getNonMarkPoints($this->yearStart));
        // $arrParams['user_code'] = $this->usercode;
        // $arrParams['start_date'] = $this->yearStart;
        // $arrPoints = header_total_points( $arrParams );
        // if ( $this->debug ) {
        //     echo "Qry: " . totalMarks("WHERE user_id = $this->user_id and mark_date >= " . $this->yearStart) . "<br />";
        //     echo "<pre>";
        //     print_r( $arrParams );
        //     print_r( $arrPoints );
        //     echo "</pre>";
        // }
        return $points;
    }
    
    public function getAuctionPoints( $auction_start_date ) {
        $points = $this->getTotalMarks("WHERE user_id = $this->user_id and mark_date >= $auction_start_date");
        $arrParams['user_code'] = $this->usercode;
        $arrParams['auction_date'] = $auction_start_date;
        $arrPoints = header_auction_points( $arrParams );
        $points += intval($arrPoints[$arrParams['user_code']]);
        
        if ($points >= 1200) {
            return $this->getTotalPoints();
        } else {
            return $points;
        }
    }
    
    public function getStorePoints() {
        $reset_date = $this->getStoreResetDate();
        $points = $this->getTotalMarks("WHERE user_id = $this->user_id and mark_date >= " . $reset_date);
        if ($this->debug) echo "Store points from mashpia: " . $points . "<br />";
        $points += intval($this->getNonMarksStorePoints($reset_date));
        // $arrParams['user_code'] = $this->usercode;
        // $arrParams['start_date'] = $reset_date;
        // $arrPoints = header_store_points( $arrParams );
        // if ( $this->debug ) {
        //     echo "Qry: " . totalMarks("WHERE user_id = $this->user_id and mark_date >= " . $reset_date) . "<br />";
        //     echo "<pre>";
        //     print_r( $arrParams );
        //     print_r( $arrPoints );
        //     echo "</pre>";
        // }
        // $points += $arrPoints[$arrParams['user_code']];
        return $points;
    }

    public function getV2Points() {
        $reset_date = $this->getStoreResetDate();
        $arrParams['user_code'] = $this->usercode;
        $arrParams['start_date'] = $reset_date;
        $arrPoints = header_store_points( $arrParams );
        if ( $this->debug ) {
            echo "<pre>";
            print_r( $arrPoints );
            echo "</pre>";
        }
    }
    
    // used in statement.php line 628
    public function getMashpiaStorePoints() {
        $reset_date = $this->getStoreResetDate();
        $points = $this->getTotalMarks( "WHERE user_id = $this->user_id and mark_date >= " . $reset_date );
        return $points;
    }

    public function getMashpiaPoints($start, $end) {
        // convert to jd values
        $startDetails = explode('-', $start);
        $endDetails = explode('-', $end);
        $startJD = gregoriantojd($startDetails[1], $startDetails[2], $startDetails[0]);
        $endJD = gregoriantojd($endDetails[1], $endDetails[2], $endDetails[0]);
        if ($this->debug) {
            echo "Start: " . $startJD . "<br />";
            echo "End: " . $endJD . "<br />";
        }
        return $this->getTotalMarks("WHERE user_id = $this->user_id AND mark_date >= " . $startJD . " AND mark_date <= " . $endJD);
    }

    private function getTotalMarks( $limit ) {
        return floor(
            mysql_result(
                mq(
                    totalMarks( $limit )
                ), 0
            )
        );
    }

    public function getAchievementCardPoints($start, $end) {
        $sql = "SELECT IFNULL(sum(points), 0) as total 
                FROM pointsDB.user_points 
                WHERE achievement_card_id > 0 
                AND created >= '$start' 
                AND created <= '$end' 
                AND user_id = " . $this->user_id;
        if ($this->debug) echo $sql . "<br />";
        else {
            $result = mysql_query($sql);
            $row = mysql_fetch_assoc($result);
            return intval($row['total']);
        }
    }

    public function getUsedStorePoints($start, $end) {
        // get all points from store purchases
        $total = 0;
        $points = [];
        $sql = "select user_point_id, points from pointsDB.user_points 
                where created >= '$start' 
                and created <= '$end' 
                and user_id = " . $this->user_id . "
                and resource_name = 'store'";
        if ($this->debug) echo $sql . "<br />";
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            while ($row = mysql_fetch_assoc($result)) {
                $points[] = $row['user_point_id'];
                $total += abs($row['points']);
            }
        }
        // get all returns and remove from purchases
        if ($points) {
            $sql = "select IFNULL(sum(points), 0) as total from pointsDB.user_points 
                    where reversed_user_point_id in (" . implode(',', $points) . ")";
            if ($this->debug) echo $sql . "<br />";
            $result = mysql_query($sql);
            $row = mysql_fetch_assoc($result);
            $total -= abs($row['total']);
        }
        return $total;
    }

    private function getStoreResetDate() {
//        if (is_null($this->store_reset)) {
//            $reset_date = $this->yearStart;
//        } else if ($this->store_reset === 0) {
//            $reset_date = 2451544; // 01/01/2000
//        } else {
//            $reset_date = $this->store_reset;
//        }
        if ($this->store_reset > 0) {
            $reset_date = $this->store_reset;
        } else {
//            if ($this->australian) $reset_date = $this->yearStart;
//            else $reset_date = 2455441; // Sept 1, 2010
            $reset_date = $this->yearStart;
        }
        return $reset_date;
    }

    public function getTasksPointsDetails() {
        $details = [];
        $sql = "select * from date_tasks_marks 
                where user_id = " . $this->user_id . " 
                and mark_date >= " . $this->yearStart;
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $details[] = $row;
        }
        return $details;
    }

    public function getStorePointsDetails() {
        $details = [];
        // figure out gregorian date
        $gregDate = jdtogregorian( $this->getStoreResetDate() );
        $arrDate = explode('/', $gregDate);
        $gregorian = $arrDate[2] . '-' . $arrDate[0] . '-' . $arrDate[1];
        $sql = "select * from pointsDB.user_points  
                where user_id = " . $this->user_id . " 
                and created >= '" . $gregorian . "'";
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $details[] = $row;
        }
        return $details;
    }

    private function useBetaPoints() {
        return (isset($_REQUEST['points_beta']) && $_REQUEST['points_beta']) || $this->school_id == 5;
    }

    // originally from mashpia.com/public/v2/application/models/Points.php user_total function
    private function getNonMarkPoints($start_date = false) {
        $formatted_date = $start_date ? date("Y-m-d", jdtounix($start_date)) : '2000-01-01';
        $sql = "SELECT SUM(points) AS total
            FROM pointsDB.user_points
            WHERE user_id = '{$this->user_id}'
            AND points > 0
            AND resource_name NOT IN ('store' , 'transaction_manager_store') 
            AND created >= '$formatted_date'";
        // $GLOBALS['logger']->debug($sql);
        if ($this->debug) echo $sql . "<br />";
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        return $row ? $row['total'] : 0;
    }

    // originally from mashpia.com/public/v2/application/models/Points.php user_store function
    private function getNonMarksStorePoints($start_date = false)
    {
        $formatted_date = $start_date ? date("Y-m-d", jdtounix($start_date)) : '2000-01-01';
        // ignore transaction_manager_store reversals where the original purchase is before the start date
        $sql = "SELECT IFNULL(SUM(points), 0) AS total
                FROM pointsDB.user_points up
                WHERE up.user_id = '{$this->user_id}'
                AND up.created >= '$formatted_date' 
                AND (up.reversed_user_point_id IS NULL OR up.reversed_user_point_id not in (
                    SELECT user_point_id FROM pointsDB.user_points WHERE created < '$formatted_date' AND user_id = '{$this->user_id}'
                ))";
//        }
        if ($this->debug) echo $sql . "<br />";
        // $GLOBALS['logger']->debug($sql);
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        return $row['total'];
    }

    // originally in mashpia.com/public/v2/application/controllers/KioskMainController.php
    public function scanMiles($card) {
        $msg = '';
        $sql = "SELECT * FROM pointsDB.achievement_cards where card_serial = " . mysql_real_escape_string($card);
//        echo $sql;
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
            $row = mysql_fetch_assoc($result);
            if ($row['status'] == 'scanned') {
                $msg = "This card has already been scanned.";
            } else {
                if ($row['institution_id'] > 0 && $row['institution_id'] != $this->school_id) {
                    $msg = "You are not in the correct base to scan this card.";
                } else if ($row['class_id'] > 0) {
                    // get user class id
                    $sql2 = "SELECT class_id FROM users WHERE user_id = " . $this->user_id;
                    $result2 = mysql_query($sql2);
                    $row2 = mysql_fetch_assoc($result2);
                    $class_id = $row2['class_id'];
                    if ($row['class_id'] != $class_id) {
                        $msg = "You are not in the correct platoon to scan this card.";
                    }
                }
                if (empty($msg)) {
                    // insert into user_points
                    $sql4 = "INSERT into pointsDB.user_points SET 
                             achievement_card_id = " . $row['achievement_card_id'] . ",
                             user_id = " . $this->user_id . ", 
                             campaign_id = " . $row['campaign_id'] . ", 
                             mission_id = " . $row['mission_id'] . ", 
                             task_id = " . $row['task_id'] . ", 
                             institution_id = " . $this->school_id . ", 
                             class_id = " . $row['class_id'] . ", 
                             points = " . $row['card_points'] . ", 
                             resource_name = 'specific achievement card'";
                    // update achievement cards
                    $sql3 = "UPDATE pointsDB.achievement_cards SET status = 'scanned' WHERE card_serial = " . mysql_real_escape_string($card);
                    // make sure both qrys work or don't work
                    mysql_query('set autocommit=0');
                    mysql_query('begin');
                    if (mysql_query($sql4) && mysql_query($sql3)) {
                        $msg = "Congratulations! You have just been awarded " . $row['card_points'] . " points!";
                        mysql_query('commit');
                    } else {
                        $msg = "Error awarding points.";
                        mysql_query('rollback');
                    }
                    mysql_query('set autocommit=1');
                }
            }
        } else {
            if ( isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he' ) $msg = "הקוד הסרוק לא נמצא במערכת שלנו. אולי הבר קוד לא נסרק כראוי";
            else $msg = "The scan code was not found in our system. Maybe the bar code wasn't scanned properly.";
        }
        return $msg;
    }

    public function getPointsHistory($start, $end = 0) {
        $history = [];
        $sql = "select * from pointsDB.user_points where user_id = {$this->user_id} and created >= '$start'";
        if ($end) $sql .= " and created <= '$end'";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $history[$row['created']][] = $row;
        }
        ksort($history);
        return $history;
    }

    public function getMissionHistory($start, $end) {
        $history = [];
        $startDetails = explode('-', $start);
        $endDetails = explode('-', $end);
        $startJD = gregoriantojd($startDetails[1], $startDetails[2], $startDetails[0]);
        if ($end) $endJD = gregoriantojd($endDetails[1], $endDetails[2], $endDetails[0]);
        else $endJD = unixtojd();
        $sql = "select dt.short_name, dtm.mark_date from date_tasks dt 
                join date_tasks_marks dtm using (date_task_id) 
                where dtm.mark_date >= $startJD 
                and dtm.mark_date <= $endJD 
                and dtm.user_id = " . $this->user_id;
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $gregorian = jdtogregorian($row['mark_date']);
            $dateDetails = explode('/', $gregorian);
            $date = $dateDetails[2] . '-' . $dateDetails[0] . '-' . $dateDetails[1];
            $history[$date][] = $row;
        }
        ksort($history);
        return $history;
    }
}