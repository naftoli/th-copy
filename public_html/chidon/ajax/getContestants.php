<?
require '../../db.php';
$gender = mysql_real_escape_string($_POST['gender']);
$year = mysql_real_escape_string($_POST['year']);

$contestants = array();
$sql = "select tc.user_id, first, last from th_chidon tc 
		join users u using (user_id)  
		where tc.year = " . $year . "
		and tc.shabbaton = 1 
		and u.gender = '" . strtoupper($gender) . "' 
		order by u.last, u.first"; 
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$contestants[][$row['user_id']] = ucwords(strtolower($row['first'] . ' ' . $row['last']));
}
echo json_encode($contestants);
?>