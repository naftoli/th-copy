<?php

/*
 *  get_tracking_numbers($school_id, $type = false, $include_shipped = false) function
 *
 *  gets the tracking numbers for a school based on the provided type
 *
 *  params:
 *      $school_id => limit to a single school_id ****REQUIRED****
 *      $type => the type of shipment that the tracking number is for
 *      $include_delivered => include tracking numbers that have already been delivered (defaults to false. accepts true and special input "only")
 */
function get_tracking_numbers($school_id, $type = false, $include_delivered = false){ // TODO class_id
    $result = [];
    // escape to prevent SQL injection
    $school_id = mysql_real_escape_string($school_id);  $type = mysql_real_escape_string($type);
    // generate the sql
    $tracking_sql = "SELECT * FROM tracking_numbers WHERE school_id = $school_id ";
    if($type) $tracking_sql .= "AND type='$type' ";
    if(!$include_delivered) $tracking_sql .= "AND delivered_at IS NULL";
    if($include_delivered === "only") $tracking_sql .= "AND delivered_at IS NOT NULL";
    // echo $tracking_sql;
    $query = mysql_query($tracking_sql); // run the query....
    while($row = mysql_fetch_assoc($query)){$result[] = $row;} // ... and append all the results into an array called $result ...
    return $result; // ... and return that array
}

/*
 *  create_tracking_number($school_id, $type, $tracking_number, $description) function
 *
 *  saves the tracking number to the database
 *
 *  params:
 *      $school_id => the school id for the tracking number
 *      $type => the type of shipment (essentially a filter)
 *      $tracking_number => the new tracking number
 *      $description => the desctiption for the shipment
 */
function create_tracking_number($school_id, $type, $tracking_number, $description) {
    // escape to prevent SQL injection
    $school_id = mysql_real_escape_string($school_id);  $type = mysql_real_escape_string($type);
    $tracking_number = htmlspecialchars(mysql_real_escape_string($tracking_number)); // escape html as well as these are put in value feilds
    $description = htmlspecialchars(mysql_real_escape_string($description)); // escape html as well as these are put in value feilds
    // generate the query and return the id if it was inserted
    $tracking_sql = "INSERT INTO tracking_numbers (school_id, type, tracking_number, description) VALUES ($school_id, '$type', '$tracking_number', '$description');";
    if(mysql_query($tracking_sql)){
        $tracking_number_id = mysql_fetch_assoc(mysql_query("SELECT LAST_INSERT_ID() as id;"))['id'];
        return $tracking_number_id;
    } else {
        return false; // it was not inserted...
    }
}


/*
 *  save_tracking_number($tracking_number_id, $school_id, $type, $tracking_number, $description) function
 *
 *  saves the tracking number to the database
 *
 *  params:
 *      $tracking_number_id => the id of the tracking number
 *      $tracking_number => the new tracking number
 *      $description => the desctiption for the shipment
 */
function save_tracking_number($tracking_number_id, $tracking_number, $description) {
    // prevent sql and html injection
    $tracking_number_id = htmlspecialchars(mysql_real_escape_string($tracking_number_id));
    $tracking_number = htmlspecialchars(mysql_real_escape_string($tracking_number)); // escape html as well as these are put in value feilds
    $description = htmlspecialchars(mysql_real_escape_string($description)); // escape html as well as these are put in value feilds
    
    $tracking_sql = "UPDATE tracking_numbers SET tracking_number='$tracking_number', description='$description' WHERE tracking_number_id='$tracking_number_id';";
    //echo $tracking_sql;
    return !!mysql_query($tracking_sql);
}

/*
 *  deliver_tracking_number($tracking_number_id) function
 *
 *  marks the tracking number provided as delivered at the current time
 *
 *  params:
 *      $tracking_number_id => the tracking_number_id of the tracking number
 */
function deliver_tracking_number($tracking_number_id) {
    $delivered_at = date("Y-m-d H:i:s"); // generate the current timestamp
    $tracking_number_id = htmlspecialchars(mysql_real_escape_string($tracking_number_id)); // escape the provided input....
    // generate and run the query
    $tracking_sql = "UPDATE tracking_numbers SET delivered_at='$delivered_at' WHERE tracking_number_id='$tracking_number_id';";
    //echo $tracking_sql;
    return !!mysql_query($tracking_sql);
}