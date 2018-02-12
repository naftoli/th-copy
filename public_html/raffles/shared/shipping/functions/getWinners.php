<?
/***************** getWinners wrapper class for functions **********************/

class getWinners{
    
    public static function get_winners_raffles($raffle_ids, $sorting, $shipping_filter, $school_id = false, $group_student_ids = false){
        $shipping_filter = self::get_shipped($shipping_filter);
        // make sure we have an array and that it has data in it
        if(!is_array($raffle_ids) || count($raffle_ids) == 0) return false;
        // escape the raffle id's in single quotes. not good enough I know but it will do for now
        $filter = "raffle_id in ('".implode("', '", $raffle_ids)."')";
        if($school_id) $filter .= " AND users.school_id = '$school_id'";
        if($shipping_filter) $filter .= " AND $shipping_filter";
        
        $order_by = self::get_order_by($sorting);
        return [self::get_winners($filter, $order_by, $group_student_ids), self::get_prize_counts($filter, $order_by)];
    }
    // returns the list of winners or false
    public static function get_winners_dates($start, $end, $sorting, $shipping_filter, $school_id = false, $group_student_ids = false, $type=false){
        $shipping_filter = self::get_shipped($shipping_filter);
        $start = date("Y-m-d", strtotime($start)); // convert to the correct format
        $end = date("Y-m-d", strtotime($end)); // convert to the correct format
        // just return false if input is invalid
        if(!($start && $end)) return false;
        // add the time to the end to include kids registered on that date
        $filter = "date_ran >= '$start' AND date_ran <= '$end 24:59:59' ";
        if($type) $filter .= " AND raffles.type = '$type' ";
        if($school_id) $filter .= " AND users.school_id = '$school_id'";
        if($shipping_filter) $filter .= " AND $shipping_filter";
        
        $order_by = self::get_order_by($sorting);
        return [self::get_winners($filter, $order_by, $group_student_ids), self::get_prize_counts($filter, $order_by)];
    }
    
    public static function mark_winner_shipped($shipped, $user_id, $raffle_id, $prize_id){
        // sanitize the input
        $user_id = mysql_real_escape_string($user_id);
        $raffle_id = mysql_real_escape_string($raffle_id);
        $prize_id = mysql_real_escape_string($prize_id);
        $shipped = $shipped == "true" ? "1" : "0";
        // generate the query
        $update_sql = "UPDATE raffle_winners SET shipped=$shipped ";
        $update_sql .= "WHERE user_id = $user_id AND raffle_id = $raffle_id AND prize_id = $prize_id;";
        
        if(!$shipped && mysql_query($update_sql)) {
            return !!mysql_query("DELETE FROM shipment_details WHERE type='prize' AND item_id=$user_id AND item_extra_id=$raffle_id");
        } else {
            // return the status of the call
            return !!mysql_query($update_sql);
        }
    }

    private static function get_winners($filter, $order_by, $group_student_ids = false){
        $winners = []; // array to be returned
        
        $winners_sql = "SELECT user_id, raffle_id, raffle_winners.prize_id, users.school_id, first, last, shipped, "
            ."class_grade, class_sub, prizes.name as prize, raffles.name as raffle, school_name "
            ."FROM raffle_winners JOIN users USING (user_id) JOIN raffles USING (raffle_id) "
            ."JOIN (SELECT prize_id, 'weekly' AS type, name COLLATE utf8_unicode_ci as name FROM prizes "
            ."UNION SELECT prize_id, 'monthly' AS type, prize_name AS name FROM prizes_auction) prizes "
            ."ON prizes.prize_id = raffle_winners.prize_id AND prizes.type = raffles.type "
            ."JOIN schools USING (school_id) JOIN classes USING (class_id) ";
        // add the filter and sorting if applicable
        if($filter) $winners_sql .= "WHERE $filter ";
        if($order_by) $winners_sql .= "ORDER BY $order_by;";
        
        // echo $winners_sql;
        // run the query, then fill and return the array
        $winners_query = mysql_query($winners_sql);
        while($row = mysql_fetch_assoc($winners_query)){
            if($group_student_ids){
                $winners[$row['school_id']][$row['user_id']][] = $row;
            } else {
                $winners[$row['school_id']][] = $row;
            }
        }
        return $winners;
    }
    
    private static function get_prize_counts($filter, $order_by){
        $prizes = []; // array to be returned
        
        $prizes_sql = "SELECT COUNT(*) as total, raffle_winners.prize_id, users.school_id, prizes.name as prize, picture "
            ."FROM raffle_winners JOIN users USING (user_id) JOIN raffles USING (raffle_id) "
            ."JOIN (SELECT prize_id, 'weekly' AS type, picture, name COLLATE utf8_unicode_ci as name FROM prizes "
            ."UNION SELECT prize_id, 'monthly' AS type, prize_image_id as picture, prize_name AS name FROM prizes_auction) prizes "
            ."ON prizes.prize_id = raffle_winners.prize_id AND prizes.type = raffles.type "
            ."JOIN schools USING (school_id) JOIN classes USING (class_id) ";
        // add the filter and sorting if applicable
        if($filter) $prizes_sql .= "WHERE $filter ";
        $prizes_sql .= "GROUP BY school_id, prize_id ";
        if($order_by) $prizes_sql .= "ORDER BY $order_by;";
        
        // echo $prizes_sql."\n\n";
        // run the query, then fill and return the array
        $prizes_query = mysql_query($prizes_sql);
        while($row = mysql_fetch_assoc($prizes_query)){
            $prizes[$row['school_id']][] = $row;
        }
        return $prizes;
    }
    
    private static function get_order_by($sorting){
        if($sorting == "prize") return "prize, last, first";
        if($sorting == "grade") return "class_grade, class_sub, last, first";
        
        return "last, first"; // the default option
    }
    
    private static function get_shipped($filter){
        if($filter == "shipped") return "shipped = 1";
        if($filter == "not-shipped") return "shipped = 0";
        
        return false; // invalid filtering option
    }
}

