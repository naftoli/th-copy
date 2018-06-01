<?php
require_once(dirname(__FILE__)."/../classes/Shipment.php"); // load up the shipments class....

/******************** GET_RANKS() FUNCTION ********************/
/*
 * This function gets the information from the rank_marks table and normalizes it for the unified shipping report
 * Normalizaition pattern
 *   shipped => checks if shipped is null or not
 *   item => rank name and card/book
 *   ajax => rank:<rank_ord>:<user_id>:<date_promoted>:<card|book>
 * Notes:
 *  nested by school id and user id for quick access
 */

function get_ranks($school_id, $start_date, $end_date){
    $shipments = shipping\Shipment::getRankShipmentDetails($school_id);
    
    $rank_sql = "SELECT rank_marks.*, school_id, rank_name, rank_image_id FROM rank_marks "
        ."JOIN users USING (user_id) JOIN ranks USING (rank_ord) WHERE date_promoted >= $start_date AND date_promoted <= $end_date ";
    if($school_id) $rank_sql .= "AND school_id = $school_id";
    // $rank_sql .= "ORDER BY user_id , rank_ord";
    $result = [];
    // run the query
    $rank_query = mysql_query($rank_sql);
    // sepearate the book and card
    while($row  = mysql_fetch_assoc($rank_query)){
        $ajax = "rank:".$row['rank_ord'].":".$row['user_id'].":".$row['date_promoted'];
        
        // add the card...
        if($row['date_card_shipped'] && isset($shipments[$row['user_id']]) &&
           isset($shipments[$row['user_id']][$row['rank_ord']]) &&  isset($shipments[$row['user_id']][$row['rank_ord']]['card'])
        ){
            $shipment_info = $shipments[$row['user_id']][$row['rank_ord']]['card'];
            $shipment = $shipment_info["name"]; $shipment_id = $shipment_info["shipment_id"];
        } else {
            $shipment = "N/A"; $shipment_id = false;
        }
        // add it to the result set....
        $result[$row['school_id']][$row['user_id']][] = [
            'shipped' => $row['date_card_shipped'], 'item' => $row['rank_name']." Rank Card",
            'ajax' => $ajax.":card",    "shipment" => $shipment,   "shipment_id" => $shipment_id
        ];
        
        // add the book...
        if($row['date_book_shipped'] && isset($shipments[$row['user_id']]) &&
           isset($shipments[$row['user_id']][$row['rank_ord']]) &&  isset($shipments[$row['user_id']][$row['rank_ord']]['book'])
        ){
            $shipment_info = $shipments[$row['user_id']][$row['rank_ord']]['book'];
            $shipment = $shipment_info["name"]; $shipment_id = $shipment_info["shipment_id"];
        } else {
            $shipment = "N/A"; $shipment_id = false;
        }
        // add it to the result set....
        $result[$row['school_id']][$row['user_id']][] = [
            'shipped' => $row['date_book_shipped'], 'item' => $row['rank_name']." Rank Book",
            'ajax' => $ajax.":book",    "shipment" => $shipment,   "shipment_id" => $shipment_id
        ];
    }
    
    return $result; // return the results...
}

/******************** MARK_RANK() FUNCTION ********************/
/*
 * This function updates the mark for if a rank was shipped in the database
 * params
 *   $shipped => was it shipped?
 *   $rank_ord => the rank
 *   $user_id => user_id of the row
 *   $date_promoted => the date the rank was awarded
 *   $shipment_type => <card|book>
 * Notes:
 *  espcapes all input against mysql injection with mysql_real_escape_string
 */
function mark_rank($shipped, $rank_ord, $user_id, $date_promoted, $shipment_type){
    // sanitize the input
    $rank_ord = mysql_real_escape_string($rank_ord);
    $user_id = mysql_real_escape_string($user_id);
    $date_promoted = mysql_real_escape_string($date_promoted);
    // known inputs (a.k.a. we generate them)
    $shipment_type_col = $shipment_type == "book" ? "date_book_shipped" : "date_card_shipped";
    $shipped_date = $shipped ? date("'Y-m-d H:i:s'") : "NULL" ;
    //create the query
    $rank_sql = "UPDATE rank_marks SET $shipment_type_col='$shipped_date' WHERE user_id=$user_id AND date_promoted=$date_promoted AND rank_ord=$rank_ord;";
    
    if(!$shipped && mysql_query($rank_sql)){ // if it is being marked as unshipped remove from shipment....
        $rank_shipment_detail_sql = "DELETE FROM shipment_details WHERE type='rank' AND item_type='$shipment_type' AND item_id='$user_id' AND item_ord='$rank_ord'";
        return !!mysql_query($rank_shipment_detail_sql);
    } elseif (!$shipped) {
        return false;
    } else { // return the status
        return !!mysql_query($rank_sql);
    } // end removing shipping_details....
}