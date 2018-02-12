<?php

function get_shipped_marks($get_shipment = false) {
    $shipped_marks = []; // the resutling array
    $marked_sql = "SELECT yearly_prize_shipping.type, id, distributed".($get_shipment ? ", shipments.name as 'shipment', shipments.shipment_id" : "")." FROM yearly_prize_shipping "; // sql to get the rows
    if($get_shipment) {
        $marked_sql .= " LEFT JOIN shipment_details ON shipment_details.type = 'gift' AND shipment_details.item_type = yearly_prize_shipping.type AND item_id = id";
        $marked_sql .= " LEFT JOIN shipments using (shipment_id)";
    }
        
    $marked_query = mysql_query($marked_sql); // run the query
    // load all the rows
    while($marked_row = mysql_fetch_assoc($marked_query)){
        // set the id under the type
        if($get_shipment) {
            $shipped_marks[$marked_row['type']][$marked_row['id']] = [
                'shipment' => ($marked_row['shipment'] ? $marked_row['shipment'] : "N/A"),
                'shipment_id' => $marked_row['shipment_id']
            ];
        } else {
            $shipped_marks[$marked_row['type']][$marked_row['id']] = $marked_row['distributed'];
        }
    }
    return $shipped_marks;
}

function mark_shipped($shipped, $type, $id){
    // escape the params
    $type = mysql_real_escape_string($type);
    $id = mysql_real_escape_string($id);
    // run the query
    if(!$shipped){
        $sql = "DELETE FROM yearly_prize_shipping WHERE type='$type' AND id='$id';";
        if (!mysql_query($sql)) return false;
        
        $sql = "DELETE FROM shipment_details WHERE type='gift' AND item_type='$type' AND item_id='$id';";
        return mysql_query($sql) ? true : false;
    } else {
        $sql = "INSERT INTO yearly_prize_shipping (id, type, year, shipped) VALUES ('$id', '$type', 5778, 1);";
        return mysql_query($sql) ? true : false;
    }
}