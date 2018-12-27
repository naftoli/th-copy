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
        $arrParams['user_code'] = $this->usercode;
        $arrPoints = header_total_points( $arrParams );
        if ( $this->debug ) {
            echo "<pre>";
            print_r( $arrParams );
            print_r( $arrPoints );
            echo "</pre>";
        }
        $points += $arrPoints[$arrParams['user_code']];
        return $points;
    }
    
    public function getTotalThisYear() {
        $points = $this->getTotalMarks("WHERE user_id = $this->user_id and mark_date >= " . $this->yearStart);
        $arrParams['user_code'] = $this->usercode;
        $arrParams['start_date'] = $this->yearStart;
        $arrPoints = header_total_points( $arrParams );
        if ( $this->debug ) {
            echo "<pre>";
            print_r( $arrParams );
            print_r( $arrPoints );
            echo "</pre>";
        }
        $points += $arrPoints[$arrParams['user_code']];
        return $points;
    }
    
    public function getAuctionPoints( $auction_start_date ) {
        $points = $this->getTotalMarks("WHERE user_id = $this->user_id and mark_date >= $auction_start_date");
        $arrParams['user_code'] = $this->usercode;
        $arrParams['auction_date'] = $auction_start_date;
        $arrPoints = header_auction_points( $arrParams );
        $points += $arrPoints[$arrParams['user_code']];
        
        if ($points >= 1200) {
            return $this->getTotalPoints();
        } else {
            return $points;
        }
    }
    
    public function getStorePoints() {
        $reset_date = $this->getStoreResetDate();
        $points = $this->getTotalMarks("WHERE user_id = $this->user_id and mark_date >= " . $reset_date);
        $arrParams['user_code'] = $this->usercode;
        $arrParams['start_date'] = $reset_date;
        $arrPoints = header_store_points( $arrParams );
        if ( $this->debug ) {
            echo "<pre>";
            print_r( $arrParams );
            print_r( $arrPoints );
            echo "</pre>";
        }
        $points += $arrPoints[$arrParams['user_code']];
        return $points;
    }
    
    // used in statement.php line 628
    public function getMashpiaStorePoints() {
        $reset_date = $this->getStoreResetDate();
        $points = $this->getTotalMarks( "WHERE user_id = $this->user_id and mark_date >= " .$reset_date );
        return $points;
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

    private function getStoreResetDate() {
		if ($this->store_reset > 0) { 
            $reset_date = $this->store_reset;
        } else {
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
        $gregDate = jdtogregorian( $this->yearStart );
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
}