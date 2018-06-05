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
    public static function getRegCost( $type, $early_bird, $school = false ) {
        if ( $type == 1 ) { // In Tuition
            return $school ? 50 : 0; // schools get billed $50 for each kid
        } else if ( $type == 2 ) { // Guarranteed, they get a bit of a discount
            return $early_bird ? 50 : 55;
        }
        // everyone else / default return
        return $early_bird ? 55 : 60;
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
}
?>