<?php
class GlobalSettings {

    private static function getHelper($key) {
        $sql = "select `val` from global_settings where `key` = '$key'";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['val'];
    }

    public static function getCurrentYear() {
        return self::getHelper('current_year');
    }
    
    public static function getCurYearDates() {
        $dates['start'] = self::getHelper('cur_year_start');
        $dates['end'] = self::getHelper('cur_year_end');
        return $dates;
    }
    
    public static function getRegistrationYear( $school_id = false ) {
        $year = self::getHelper('registration_year');
        if ( self::isAustralian( $school_id ) ) {
            // find out current month
            $month = date('m');
            if ( $month > 8 ) return --$year;
        } else if ( in_array( $school_id, [ 61, 269 ] ) ) {
            // $year++;
        }
        return $year;
    }
    
    public static function getChidonYear() {
        return self::getHelper('chidon_year');
    }

    public static function getChidonRegYear() {
        return self::getHelper('chidon_reg_year');
    }

    public static function getCharidyYear() {
        return self::getHelper('charidy_year');
    }

    public static function getSummerMissionsStart() {
        return self::getHelper('summer_missions_start');
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
    public static function getRegCost(int $type, bool $early_bird, $myshliach = false, $anashKinder = false) {
        $fee = 70;
        if ($type == 1) $fee = 60;
        else if ($anashKinder) $fee = 70;
        else if ($early_bird) {
            switch ($type) {
                case 2:
                    $fee = 60;
                    break;
                case 3:
                    $fee = $myshliach ? 60 : 65;
                    break;
            }
        }
        return $fee;
    }

    /**
     * have one source of truth for the date of expiry for early bird
     */
    public static function earlyBird() {
        return new DateTime('2026-09-17 00:00:00', new DateTimeZone('America/New_York'));
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
        $ckids = false, $myshliach = false, $anashKinder = false
    ) {
        // ckids has no fee
        if ( $ckids ) return 0;

        // type 1 soldiers pay nothing in parent acct
        if ( $type == 1 && $is_soldier ) {
            return 0;
        } else if ( $type == 1 && !$is_soldier && !is_null($fee) ) {
            return intval($fee);
        } else if ( intval($fee) > 0 ) {
            return intval($fee);
        }

        $fee = self::getRegCost( $type, $early_bird, $myshliach, $anashKinder );
        return max($fee, 0);
    }

    /**
     * getChidonCost
     * 
     * return the minimum price for chidon enrollment
     *
     * @return int
     */
    public static function getChidonCost( $school_id = false ) {
        // Anash kinder has a different fee
        if ( $school_id && $school_id == 269 ) {
            return 55;
        } else if ( $school_id && $school_id == 61 ) {
            return 36;
        }
        return 25; // default to 25
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
        $australian = self::getAustralian();
        return in_array( $school_id, $australian );
    }

    public static function getAustralian() {
        return [ 66, 110, 112, 180, 690, 713, 709 ];
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