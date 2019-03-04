<?php
class GlobalSettings {
    public static function getCurrentYear() {
        $sql = "select `val` from global_settings where `key` = 'current_year'";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['val'];
    }
    
    public static function getCurYearDates() {
        $dates = array();
        $sql = "select `val` from global_settings where `key` = 'cur_year_start'";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $dates['start'] = $row['val'];
        $sql = "select `val` from global_settings where `key` = 'cur_year_end'";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        $dates['end'] = $row['val'];
        return $dates;
    }
    
    public static function getRegistrationYear( $school_id = false ) {
        $year = self::getCurrentYear();
        if ( self::isAustralian( $school_id ) ) {
            // find out current month
            $month = date('m');
            if ( $month > 8 ) return --$year;
        } 
        return $year;
    }
    
    public static function getChidonYear() {
        $sql = "select `val` from global_settings where `key` = 'chidon_year'";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['val'];
    }
    
    public static function getBirthdayYear() {
        $sql = "select `val` from global_settings where `key` = 'birthday_year'";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['val'];
    }

    public static function getCharidyYear() {
        $sql = "select `val` from global_settings where `key` = 'charidy_year'";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['val'];
    }

    public static function getPointsDates() {
        $dates = [];
        $sql = "select * from global_settings where `key` in ('points_start','points_start_australia')";
        $result = mysql_query($sql);
        while ( $row = mysql_fetch_assoc($result) ) {
            $dates[$row['key']] = $row['val'];
        }
        return $dates;
    }

    /**
     * GlobalSettings::getRegCost
     * 
     * returns current registration costs for the given school type, and early bird specials.
     * 
     * Accepts optional school paramater to return the rate that the school pays.
     *
     * @param int $type
     * @param boolean $early_bird
     * @param boolean $school
     * @return integer
     */
    public static function getRegCost( $type ) {
        if ( $type == 1 ) { // In Tuition
            return 45.0;
        } else if ( $type == 2 ) { // Guarranteed, they get a bit of a discount ($45, calculated elsewhere )
            return 55.0;
        }
        // everyone else / default return
        return 55.0;
    }

    /**
     * calculateChildFee
     * 
     * calculates the child registration fee with all discounts applied
     *
     * @param integer $type
     * @param integer $fee
     * @param boolean $is_soldier
     * @param boolean $early_bird
     * @param boolean $no_discount
     * @return integer
     */
    public static function calculateChildFee(
        $type,  $fee = null,    $is_soldier = false, 
        $early_bird = false,    $no_discount = false
    ) {
        // type 1 soldiers pay nothing
        if ( $type == 1 && $is_soldier ) {
            $fee = 0;
        // if the fee is null, get the default fee
        } else if ( is_null( $fee ) ) {
            $fee = self::getRegCost( $type );
        }
        // cast it to a float value
        $fee = floatval( $fee );
        // return the final rate if requested
        if ( $no_discount )
            return $fee >= 0 ? $fee : 0;
        // add early bird discount ( not for type 1 )
        if ( $type != 1 && $early_bird )
            $fee -= self::getEarlyBird();
        // add type 2 discount
        if ( $type == 2 && $early_bird ) {
            $fee -= self::getGuaranteedDiscount();
        }
        // do not allow negative numbers
        return $fee >= 0 ? $fee : 0;
    }

    /**
     * getChidonCost
     * 
     * return the current price for chidon registration
     *
     * @return int
     */
    public static function getChidonCost( $school_id = false ) {
        // Anash kinder has $40 fee
        if ( in_array( $school_id, [ 269 ] ) )
            return 40;
        return 5;
    }

    /**
     * getEarlyBird
     * 
     * return the current early bird discount
     *
     * @return int
     */
    public static function getEarlyBird(){
        return 5;
    }

    /**
     * getEarlyBird
     * 
     * return the current early bird discount
     *
     * @return int
     */
    public static function getGuaranteedDiscount(){
        return 5;
    }

    public static function isAustralian( $school_id ) {
        $australian = [ 55, 66, 110, 112, 180, 256 ];
        return in_array( $school_id, $australian );
    }
}
?>