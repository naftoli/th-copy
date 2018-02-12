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
}
?>