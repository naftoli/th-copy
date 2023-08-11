<?php
class GlobalSettings {

    private static function getHelper($key) {
        $sql = "select `val` from global_settings where `key` = '$key'";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['val'];
    }

    public static function getCurrentYear() {
//        $sql = "select `val` from global_settings where `key` = 'current_year'";
//        $result = mysql_query($sql);
//        $row = mysql_fetch_assoc($result);
//        return $row['val'];
        return self::getHelper('current_year');
    }
    
    public static function getCurYearDates() {
//        $dates = array();
//        $sql = "select `val` from global_settings where `key` = 'cur_year_start'";
//        $result = mysql_query($sql);
//        $row = mysql_fetch_assoc($result);
//        $dates['start'] = $row['val'];
//        $sql = "select `val` from global_settings where `key` = 'cur_year_end'";
//        $result = mysql_query($sql);
//        $row = mysql_fetch_assoc($result);
//        $dates['end'] = $row['val'];
        $dates['start'] = self::getHelper('cur_year_start');
        $dates['end'] = self::getHelper('cur_year_end');
        return $dates;
    }
    
    public static function getRegistrationYear( $school_id = false ) {
//        $sql = "select `val` from global_settings where `key` = 'registration_year'";
//        $result = mysql_query($sql);
//        $row = mysql_fetch_assoc($result);
//        $year =  $row['val'];
        $year = self::getHelper('registration_year');

        if ( self::isAustralian( $school_id ) ) {
            // find out current month
            $month = date('m');
            if ( $month > 8 ) return --$year;
        } 
        return $year;
    }
    
    public static function getChidonYear() {
//        $sql = "select `val` from global_settings where `key` = 'chidon_year'";
//        $result = mysql_query($sql);
//        $row = mysql_fetch_assoc($result);
//        return $row['val'];
        return self::getHelper('chidon_year');
    }

    public static function getChidonRegYear() {
//        $sql = "select `val` from global_settings where `key` = 'chidon_reg_year'";
//        $result = mysql_query($sql);
//        $row = mysql_fetch_assoc($result);
//        return $row['val'];
        return self::getHelper('chidon_reg_year');
    }
    
    public static function getBirthdayYear() {
//        $sql = "select `val` from global_settings where `key` = 'birthday_year'";
//        $result = mysql_query($sql);
//        $row = mysql_fetch_assoc($result);
//        return $row['val'];
        return self::getHelper('birthday_year');
    }

    public static function getCharidyYear() {
//        $sql = "select `val` from global_settings where `key` = 'charidy_year'";
//        $result = mysql_query($sql);
//        $row = mysql_fetch_assoc($result);
//        return $row['val'];
        return self::getHelper('charidy_year');
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
    public static function getRegCost($type, bool $early_bird) {
        $cost = 60;
        if ($early_bird) {
            switch ($type) {
                case 1:
                case 2:
                    $cost = 50;
                    break;
                case 3:
                    $cost = 55;
                    break;
            }
        }
        return $cost;
    }

    /**
     * have one source of truth for the date of expiry for early bird
     */
    public static function earlyBird() {
        return new DateTime('2022-09-15 04:00:00');
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
        $early_bird = false,    $no_discount = false, 
        $ckids = false
    ) {
        // ckids has no fee
        if ( $ckids ) return 0;

        // type 1 soldiers pay nothing in parent acct
        if ( $type == 1 && $is_soldier ) {
            return 0;
        } else if (!is_null($fee)) {
            return intval($fee);
        }

        $fee = self::getRegCost( $type, $early_bird );
        return $fee > 0 ? $fee : 0;

        // cast it to a float value
//        $fee = intval( $fee );
//        // return the final rate if requested
//        if ( $no_discount )
//            return $fee >= 0 ? $fee : 0;
//        // add early bird discount ( not for type 1 )
//        if ( $type != 1 && $early_bird && $is_soldier )
//            $fee -= self::getEarlyBird();
//        // add type 2 discount
//        if ( $type == 2 && $early_bird ) {
//            $fee -= self::getGuaranteedDiscount();
//        }
        // do not allow negative numbers
//        return $fee >= 0 ? $fee : 0;
    }

    /**
     * getChidonCost
     * 
     * return the current price for chidon registration
     *
     * @return int
     */
    public static function getChidonCost( $school_id = false ) {
        // Anash kinder has a different fee
        if ( in_array( $school_id, [ 269 ] ) ) {
            return 50;
        }
//        $today = new DateTime();
        return 20;
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
     * getGuaranteedDiscount
     * 
     * return the current early bird discount
     *
     * @return int
     */
    public static function getGuaranteedDiscount(){
        return 5;
    }

    public static function isAustralian( $school_id ) {
        $australian = [ 55, 66, 110, 112, 180, 256, 643, 709, 713, 690 ];
        return in_array( $school_id, $australian );
    }

     /**
     * getYahadusBookCost
     * 
     * return the current price for yahadus book purchase including shipping cost
     *
     * @return int
     */
    public static function getYahadusBookFee( $school_id = false ) {
        // $cost = [
        //     'bookFee'   =>  40, 
        //     'shipping'  =>  0
        // ];
        // // Anash kinder and MyShliach has $15 shipping fee
        // if ( in_array( $school_id, [ 61, 269 ] ) ) {
        //     $cost['shipping'] = 15;
        // }
        // return $cost;
        return 40;
    }
}
?>