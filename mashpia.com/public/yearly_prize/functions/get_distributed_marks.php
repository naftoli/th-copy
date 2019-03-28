<?php

function get_distributed_marks($school_id) {
    $distributed_marks = []; // the resutling array
    $marked_sql = "SELECT type, id, distributed FROM yearly_prize_shipping "; // sql to get the rows
    if($school_id) $marked_sql .= "JOIN users ON id=user_id WHERE school_id=$school_id";
    $marked_query = mysql_query($marked_sql); // run the query
    // load all the rows
    while($marked_row = mysql_fetch_assoc($marked_query)){
        // set the id under the type
        $distributed_marks[$marked_row['type']][$marked_row['id']] = $marked_row['distributed'];
    }
    return $distributed_marks;
}