<?
require 'db.php';

$qrys = array();
$sql = "select max(rank_ord) as rank, rm.user_id, date_printed from rank_marks rm 
		join users u on u.user_id = rm.user_id 
		where u.user_serial in (7751084,
		7737463,
		7751481,
		7747140,
		7745305,
		7745304,
		7748970,
		7752092,
		7751364,
		7752319,
		7752320,
		7752005,
		7752924,
		7751236,
		7751237,
		7751933,
		7751081,
		7752911,
		7751270,
		7751980,
		7752951,
		7751111,
		7751110,
		7751109,
		7741418,
		7751482,
		7751484,
		7751053,
		7752952,
		7752936,
		7752937,
		7751046,
		7748283,
		7751117,
		7752028,
		7751048,
		7752938,
		7752391,
		7751044,
		7751958,
		7751062,
		7751244,
		7751060,
		7750663,
		7746916,
		7744623,
		7750683,
		7744294,
		7745886,
		7752680,
		7752696,
		7752206,
		7751113,
		7752709,
		7749771,
		7750607,
		7739876,
		7743211,
		7751104,
		7748207,
		7752935,
		7752941,
		7752893,
		7752894,
		7752694) 
		group by rm.user_id";

$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$qrys[] = "update rank_marks set date_printed = null 
			where rank_ord = " . $row['rank'] . " 
			and user_id = " . $row['user_id'];
}

echo "<pre>";
//print_r($qrys);
echo "</pre>";

$updated = 0;
foreach ($qrys as $qry) {
	if (mysql_query($qry))
		$updated++;
}
echo "Updated: " . $updated;
