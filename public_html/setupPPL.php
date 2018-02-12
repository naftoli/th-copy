<?
require 'db.php';

$schools = array();
$sql = "select school_id from schools";
$result = mysql_query($sql);
while ($row = mysql_fetch_array($result)) {
	$schools[] = $row['school_id'];
}

$qrys = array();
foreach ($schools as $id) {
	$qrys[] = "insert into mishna_ppl 
				set points = 1, 
				p_points = 2, 
				m_points = 3, 
				s_points = 4, 
				shas_points = 5, 
				school_id = " . $id;
}

foreach ($qrys as $qry) {
	mysql_query($qry);
}
?>