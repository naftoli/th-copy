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
            "SELECT store_reset, school_id, user_code FROM users LEFT JOIN schools USING (school_id) WHERE user_id = "
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
        $points = $this->getTotalMarks("WHERE user_id = $this->user_id");
        $arrParams['user_code'] = $this->usercode;
        $arrPoints = header_total_points( $arrParams );
        $points += $arrPoints[$arrParams['user_code']];
        return $points;
    }
    
    public function getTotalThisYear() {
        if ($this->australian) $points = $this->getTotalMarks("WHERE user_id = $this->user_id and mark_date >= " . self::YEARSTARTAUSTRALIA);
        else $points = $this->getTotalMarks("WHERE user_id = $this->user_id and mark_date >= " . self::YEARSTART);
        $arrParams['user_code'] = $this->usercode;
        $arrParams['start_date'] = $this->australian ? self::YEARSTARTAUSTRALIA : self::YEARSTART;
        $arrPoints = header_total_points( $arrParams );
        if ($this->debug) {
            echo "<pre>";
            print_r($arrPoints);
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
		$this->store_reset;
		if ($this->store_reset > 0 && $this->store_reset <= unixtojd()) { // make sure we are now after the start date set by the school
            $reset_date = $this->store_reset;
        } else {
            if ($this->australian) $reset_date = self::YEARSTARTAUSTRALIA;
            else $reset_date = self::YEARSTART;
        }
        return $reset_date;
    }
}