<?
require 'db.php';
require 'class.bpSummary.php';

$campaigns = array(
	7 => 'tanya',
	8 => 'mishna'
); //tanya, mishna yud alef nissan 5776

$schools = array();
$sql = "select school_id, school_name from schools where tanya = 1 order by tanya_ord";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[] = $row['school_id'];
}

//$schools = array(246,272,243,244,313,283,288,284,249,247,240,422);
foreach ($campaigns as $id => $campaign) {
	$bp = new BpSummary( $id, 'school' );
	foreach ($schools as $school) {
		$bp->updateSummary( $school );
	}	
}