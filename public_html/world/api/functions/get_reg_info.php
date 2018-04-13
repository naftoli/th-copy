<?php
/**
 * get_reg_info
 * 
 * returns array containing the total number of registered kids for each id in the ids array
 * 
 * expects a SQL connection to be established
 * 
 * $grade sets if it returns the registration counts by class id or by school id
 * 
 * @param array $ids
 * @param boolean $grade
 * 
 */

function get_reg_info( $ids, $grade = false ) {
    $regInfo = [];
    // concat all the ID's into one query
    $ids = "'" . implode("', '", $ids) . "'";
    // detect the type of SQL query to run
    if ( $grade ) {
        $query = mysql_query(
            "SELECT COUNT(*) AS total, class_id AS id FROM users 
            WHERE class_id IN ($ids) AND (user_registered > 0 OR yan = 1)
            GROUP BY class_id"
        );
    } else {
        $query = mysql_query(
            "SELECT COUNT(*) AS total, school_id AS id FROM users 
            WHERE school_id IN ($ids) AND (user_registered > 0 OR yan = 1)
            GROUP BY school_id"
        );
    }
    // add all the rows to the array
    while( $row = mysql_fetch_assoc( $query ) ) {
        $regInfo[$row['id']] = $row['total'];
    };

    return $regInfo;
}

?>