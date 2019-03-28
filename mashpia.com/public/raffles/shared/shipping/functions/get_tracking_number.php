<?
/***************** get_tracking_number function **********************/

function get_tracking_number($school_id, $show_delivered = false, $only_one = true){
    $school_id = mysql_real_escape_string($school_id);
    $sql = "SELECT tracking_number_id, tracking_number, description, delivered_at FROM tracking_numbers WHERE school_id=$school_id ";
    if(!$show_delivered) $sql .= "AND delivered_at IS NULL ";
    $sql .= "ORDER BY tracking_number_id DESC ";
    if($only_one) $sql .= "LIMIT 1;";
    
    $row = mysql_fetch_assoc(mysql_query($sql));
    return $row;
}
