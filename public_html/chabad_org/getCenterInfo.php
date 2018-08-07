<?php
require '../db.php';
$id = mysql_real_escape_string( $_POST['id'] );

$mosdos = array();
$sql = "select * from chabad_mosdos where (
        mosad_id = " . $id . " or primary_mosad_id = " . $id . ")";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $mosdos['info'][] = $row;
}
echo json_encode( $mosdos );
?>