<?php
class Points
{
    private $user_id;
    private $usercode;
    private $store_reset;
    
    const YEARSTART = 2457934; // also need to update kiosk controller getHebrewPoints function with proper dates when year changes
    const YEARSTARTAUSTRALIA = 2457629; // also need to update kiosk controller getHebrewPoints function with proper dates when year changes
    private $debug;
    private $school_id;
    
    public function __construct( $id ) {
        $this->user_id = $id;
        $result = mysql_query(
            "SELECT store_reset, user_code FROM users LEFT JOIN schools USING (school_id) WHERE user_id = "
            . mysql_real_escape_string( $id )
        );
        $row = mysql_fetch_assoc($result);		
        $this->usercode = $row['user_code'];
        $this->school_id = $row['school_id'];
        $australian = array(55,66,110,112,180);
        if (in_array($this->school_id, $australian)) $this->australian = true;
        $this->debug = false;
    }
    
    public function setDebugOn() {
        $this->debug = true;
    }
    
    public function getTotalPoints() {
        $points = floor(mysql_result(mq(totalMarks("WHERE user_id = $this->user_id")), 0));
        $arrParams['user_code'] = $this->usercode;
        $arrPoints = header_total_points( $arrParams );
        $points += $arrPoints[$arrParams['user_code']];
        return $points;
    }
    
    public function getTotalThisYear() {
        if ( $this->store_reset ){
            $points = $this->getTotalMarks("WHERE user_id = $this->user_id and mark_date >= " . $this->store_reset );
            $arrParams = [
                'user_code' => $this->usercode,
                'start_date' => $this->store_reset
            ];

            $arrPoints = header_total_points( $arrParams );
            $points += $arrPoints[$arrParams['user_code']];
            return $points;
        } else {
            return $this->getTotalPoints();
        }
        $points += $arrPoints[$arrParams['user_code']];
        return $points;
    }
    
    public function getAuctionPoints( $auction_start_date ) {
        // limit the auciton to the store start date
        if ( $this->store_reset && $auction_start_date < $this->store_reset ) {
            $auction_start_date = $this->store_reset;
        }
        $points = floor(mysql_result(mq(totalMarks("WHERE user_id = $this->user_id and mark_date >= $auction_start_date")), 0));
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
        $points = floor(mysql_result(mq(totalMarks("WHERE user_id = $this->user_id and mark_date >= " . $reset_date)), 0));
        $arrParams['user_code'] = $this->usercode;
        $arrParams['start_date'] = $reset_date;
        $arrPoints = header_store_points( $arrParams );
        if ($this->debug) {
            echo totalMarks("WHERE user_id = $this->user_id and mark_date >= " . $reset_date);
            echo "<pre>";
            print_r($arrPoints);
            echo "</pre>";
        }
        $points += $arrPoints[$arrParams['user_code']];
        return $points;
    }
    
    public function getMashpiaStorePoints() {
        if ( $this->store_reset ) {
            return $this->getTotalMarks("WHERE user_id = $this->user_id and mark_date >= " . $this->store_reset );
        } else {
            return $this->getTotalMarks("WHERE user_id = $this->user_id ");
        }
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
        // find out if school has a store reset date set
        $sql = "select store_reset from schools where school_id = " . $this->school_id;
		$result = mysql_query( $sql );
		$row = mysql_fetch_assoc( $result );
		$store_date = $row['store_reset'];
		if ($store_date > 0 && $store_date <= unixtojd()) { // make sure we are now after the start date set by the school
            $reset_date = $store_date;
        } else {
            if ($this->australian) $reset_date = self::YEARSTARTAUSTRALIA;
            else $reset_date = self::YEARSTART;
        }
        return $reset_date;
    }
}