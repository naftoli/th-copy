<?php

function get_parshos($year, $start = false, $end = false){
    $parsha_sql = "SELECT * FROM parshos WHERE year=$year ";
    if ($start) $parsha_sql .= "AND start >= $start ";
    if ($end) $parsha_sql .= "AND end <= $end ";
    
    $parsha_query = mysql_query($parsha_sql);
    
    $parshos = [];
    while ($row = mysql_fetch_assoc($parsha_query)){
        $parshos[] = $row;
    }
    
    return $parshos;
}