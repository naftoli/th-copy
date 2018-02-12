<?php
require_once(dirname(__FILE__)."/../classes/Shipment.php"); // load up the shipments class....
/******************** GET_MEDALS() FUNCTION ********************/
/*
 * This function gets the information from the medal_marks table and normalizes it for the unified shipping report
 * Normalizaition pattern
 *   shipped => checks if shipped is null or not
 *   item => medal name and subject
 *   ajax => medal:<medal_ord>:<user_id>:<date_awarded>:<subject_id>
 * Notes:
 *  nested by school id and user id for quick access
 */

function get_medals($school_id, $start_date, $end_date){
    $shipments = shipping\Shipment::getMedalShipmentDetails($school_id);
    
    $medals = [];
    
    $medal_sql = "SELECT user_id, school_id, date_awarded, date_shipped, medal_name, medal_ord, medal_off_image_id, subject_name, subject_image_id, subject_id "
        ."FROM medal_marks JOIN users USING (user_id) JOIN medals USING (medal_ord) JOIN subjects USING (subject_id) "
        ."WHERE date_awarded >= $start_date AND date_awarded <= $end_date ";
        if($school_id) $medal_sql .= "AND school_id = $school_id ";
    $medal_sql .= "ORDER BY user_id , medal_ord";

    $medal_query = mysql_query($medal_sql);
    while($row  = mysql_fetch_assoc($medal_query)){
        //$medals[$row['school_id']][$row['user_id']][] = $row;
        $ajax = "medal:".$row['medal_ord'].":".$row['user_id'].":".$row['date_awarded'].":".$row['subject_id'];
        // if it was shipped then get the shipment it is in
        if($row['date_shipped']&& isset($shipments[$row['user_id']]) &&
           isset($shipments[$row['user_id']][$row['medal_ord']]) &&  isset($shipments[$row['user_id']][$row['medal_ord']][$row['subject_id']])
        ) {
            $shipment_info = $shipments[$row['user_id']][$row['medal_ord']][$row['subject_id']];
            $shipment = $shipment_info["name"]; $shipment_id = $shipment_info["shipment_id"];
        } else { // if it was not shipped then it is not in any shipment....
            $shipment = "N/A"; $shipment_id = false;
        }
        $medals[$row['school_id']][$row['user_id']][] = [
            'shipped' => $row['date_shipped'],
            'item' => $row['medal_name']." Medal for ".$row['subject_name'],
            'ajax' => $ajax,
            'shipment' => $shipment,
            'shipment_id' => $shipment_id
        ];
    }
    
    return $medals;
}

/******************** MARK_MEDAL() FUNCTION ********************/
/*
 * This function updates the mark for if a rank was shipped in the database
 * params
 *   $shipped => was it shipped?
 *   $medal_ord => the rank
 *   $user_id => user_id of the row
 *   $date_awarded => the date the rank was awarded
 *   $subject_id => the id of the subject the medal was given for
 * Notes:
 *  espcapes all input against mysql injection with mysql_real_escape_string
 */

function mark_medal($shipped, $medal_ord, $user_id, $date_awarded, $subject_id){
    // sanitize the input
    $medal_ord = mysql_real_escape_string($medal_ord);
    $user_id = mysql_real_escape_string($user_id);
    $date_awarded = mysql_real_escape_string($date_awarded);
    $subject_id = mysql_real_escape_string($subject_id);
    // known inputs (a.k.a. we generate them)
    $shipped_date = $shipped ? date("'Y-m-d H:i:s'") : "NULL" ;
    //create the query
    $rank_sql = "UPDATE medal_marks SET date_shipped=$shipped_date WHERE medal_ord=$medal_ord AND user_id=$user_id AND date_awarded=$date_awarded AND subject_id = $subject_id;";
    
    if(!$shipped && mysql_query($rank_sql)) {
        $medal_shipment_detail_sql = "DELETE FROM shipment_details WHERE type='medal' AND item_id='$user_id' AND item_ord='$medal_ord' AND item_extra_id='$subject_id';";
        return !!mysql_query($medal_shipment_detail_sql);
    } elseif (!$shipped) {
        return false; // the query failed.
    } else {
        return !!mysql_query($rank_sql);// return the query status
    }
}