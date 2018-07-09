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
    
    public static function getRegistrationYear() {
        $sql = "select `val` from global_settings where `key` = 'registration_year'";
        $result = mysql_query($sql);
        $row = mysql_fetch_assoc($result);
        return $row['val'];
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
    public static function getRegCost( $type, $school = false ) {
        if ( $type == 1 ) { // In Tuition
            return $school ? 45 : 0;
        } else if ( $type == 2 ) { // Guarranteed, they get a bit of a discount
            return 55;
        }
        // everyone else / default return
        return 55;
    }

    /**
     * calculateChildFee
     * 
     * calculates the child registration fee with all discounts applied
     *
     * @param integer $type
     * @param integer $fee
     * @param boolean $is_school
     * @param boolean $early_bird
     * @param boolean $no_discount
     * @return integer
     */
    public static function calculateChildFee( $type, $fee = 0, $is_school = false, $early_bird = false, $no_discount = false ) {
        // get the default fee
        $fee = $fee > 0 ? $fee : self::getRegCost( $type, $is_school );
        // return the final rate if requested
        if ( $no_discount ) return $fee >= 0 ? $fee : 0;
        // add early bird discount ( not for type 1 )
        if ( $type != 1 && $early_bird )
            $fee -= self::getEarlyBird();
        // add type 2 discount
        if ( $type == 2 && $early_bird ) {
            $fee -= self::getGuarenteedDiscount();
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
    public static function getChidonCost(){
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
    public static function getGuarenteedDiscount(){
        return 5;
    }
}
?>