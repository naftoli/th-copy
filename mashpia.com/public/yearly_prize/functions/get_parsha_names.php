<?
function get_parsha_names($start, $end) {
    $result = [];
    
    $sql = "SELECT * FROM parshos WHERE start >= $start AND end <= $end";
    $query = mysql_query($sql);
    while($row = mysql_fetch_assoc($query)){
        $result[$row['start']] = $row['name'];
    }
    return $result;
}