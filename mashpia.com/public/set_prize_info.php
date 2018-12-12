<?php
require 'db.php';

$prizes = fopen("prizeInfo.csv", "r");
$content = stream_get_contents($prizes);
$arrRows = preg_split("/[\n\r]+/", $content);

$qrys = array();
foreach ($arrRows as $strLine) {
	$data =explode(",", $strLine);	
    $points = $data[0];
    $prize_id = $data[1];
    $qty = $data[2];
    $qrys[] = "update prizes_auction set prize_points = " . $points . ", in_stock = " . $qty . " where prize_id = " . $prize_id;
}

foreach ($qrys as $qry) mysql_query($qry);
?>