<?
require_once 'db.php';

$sql = "select school_id from schools where inst_id = 2 order by school_id";
$result = mysql_query( $sql );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $sql2 = "insert ignore into school_subjects values( $row[school_id], 27 )";
    mysql_query( $sql2 ) or die( mysql_error() );
}
?>