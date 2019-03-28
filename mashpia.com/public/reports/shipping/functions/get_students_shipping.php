<?php

/*
 *  get_students($school_id, $sort = "name", $order = "ASC", class_id = false) function
 *
 *  params:
 *      $school_id => limit to a single school_id ****REQUIRED****
 *      $sort => sorting method. Defaults to "last, first",
 *      $order => the order to use for the sort. (ASC/DESC)
 *      $class_id => optional limit to a single class_id // TODO
 */

function get_students_shipping($school_id, $sort = "name", $order = "ASC", $class_id = false){ // TODO class_id
    // basic SQL
    $students_sql = "SELECT * FROM users LEFT JOIN classes USING (class_id) ";
    if($sort == "prize") $students_sql = "SELECT users.*, classes.*, ".
            "IF(prizes.name IS NOT NULL OR prizes_auction.prize_name IS NOT NULL, CONCAT_WS('', prizes.name, prizes_auction.prize_name), NULL) AS combined_prize_name ".
            "FROM users LEFT JOIN classes USING (class_id) LEFT JOIN raffle_winners USING (user_id) LEFT JOIN raffles USING (raffle_id) ".
            "LEFT JOIN prizes on raffle_winners.prize_id = prizes.prize_id AND raffles.type = 'weekly' ".
            "LEFT JOIN prizes_auction on raffle_winners.prize_id = prizes_auction.prize_id AND raffles.type = 'monthly'";
    $students_sql .= "WHERE users.school_id=$school_id AND user_registered IS NOT NULL "; // left join in case they are not registered
    if($class_id) $students_sql .= "AND class_id=$class_id ";
    // handle sorting
    if($sort == "name") $students_sql .= "ORDER BY last $order, first $order";
    if($sort == "grade-name") $students_sql .= "ORDER BY class_grade $order, class_sub $order, last $order, first $order";
    // others which do not effect us here....
    if($sort == "prize") $students_sql .= "ORDER BY ISNULL(combined_prize_name), combined_prize_name $order, last $order, first $order";
    if($sort == "status") $students_sql .= "ORDER BY last $order, first $order";
    // echo $students_sql;
    // run the query and fetch the results
    $students_query = mysql_query($students_sql);
    $result = [];
    while($row = mysql_fetch_assoc($students_query)){$result[] = $row;}
    return $result; // return the result from the function
}