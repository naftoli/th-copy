<?
//check for spammers
$ip = $_SERVER['REMOTE_ADDR']; 
if ( $ip == '39.53.201.236' ) {
    echo "unsuccessful";
    exit;
}
/*
$sql = "select * from check_ips where ip = '$ip' and timestampdiff(day, time, now()) < 1";
$result = mysql_query( $sql );
$num = mysql_num_rows( $result );
if ( $num > 6 ) {
    echo "unsuccessful";
    exit;
}

//insert current ip address and delete anything in check_ips table older than 24 hours
$sql = "insert into check_ips set ip = '$ip'";
mysql_query( $sql );
$sql = "delete from check_ips where timestampdiff(day, time, now()) >= 1";
mysql_query( $sql );
 * 
 */
?>