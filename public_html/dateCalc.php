<?
$start = gregoriantojd(05, 01, 2013);
for ( $i = 1; $i < 38; $i++ ) {
	$start += 10; 
	echo $i . ": " . jdtogregorian( $start + 7 ) . "<br />";
}
/*
echo "--------------------<br />";
$start = gregoriantojd(11, 23, 2012);
$i = 1;
while ( $start < gregoriantojd(05, 01, 2013) ) {
	$start += 14;
	echo $i++ . ": " . jdtogregorian($start) . "<br />";
}
 * 
 */
?>