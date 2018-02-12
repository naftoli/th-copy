<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Maos Chitim Campaign</title>
</head>

<body>
<? include('admin_header.php');?>
<h1>Maos Chitim Campaign Goal - $36,000</h1>
<table>
<tr>
<th>School</th>
<th>Goal</th>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tanya</th>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mishna</th>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pledges</th>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Collected</th>
</tr>
<?
include_once('db.php');

$schools = array();
$sql = "select school_id, school_name from schools 
		where school_era is null 
		and school_id not in (82, 100, 92, 79, 78, 91, 3, 43, 71, 77)  
		order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$schools[] = $row;
}

$total_goal = 0;
$total_tanya = 0;
$total_mishna = 0;
$total_pledges = 0;
$total_collected = 0;

for ($i = 0; $i < count($schools); $i++) {
	$school_id = $schools[$i]['school_id'];
	$name = $schools[$i]['school_name'];
	$sql = "select sum(t.pledges) as pledges, sum(t.collected) as collected, sum(t.tanya_lines) as tanya_lines, sum(t.mishna_lines) as mishna_lines   
			from tanya_users as t, users as u 
			where u.user_id = t.user_id 
			and u.school_id = $school_id 
			#and u.user_registered > 0";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$tanya_lines = number_format($row['tanya_lines']);
	$mishna_lines = number_format($row['mishna_lines']);
	$pledges = "$" . number_format($row['pledges']);
	$collected = "$" . number_format($row['collected']);

	//find out how many children are registered in this school
	$sql = "select * from users where school_id = $school_id and user_registered > 0";
	$result = mysql_query($sql);
	$num = mysql_num_rows($result);
	$goal = "$" . number_format(18 * $num);

	$no_pledges = array(105, 58, 89, 60, 21, 19, 42, 162, 54, 14, 5, 84, 2);
	if (in_array($school_id, $no_pledges)) 
		echo "<tr><td><a href='maos_chitim_class.php?id=$school_id'>$name</a></td>
			<td>&nbsp;</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$tanya_lines</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$mishna_lines</td></tr>";
	else
		echo "<tr><td><a href='maos_chitim_class.php?id=$school_id'>$name</a></td>
			<td>$goal</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$tanya_lines</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$mishna_lines</td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$pledges</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$collected</td></tr>";
	
	$total_goal += (18 * $num);
	$total_tanya += $row['tanya_lines'];
	$total_mishna += $row['mishna_lines'];
	$total_pledges += $row['pledges'];
	$total_collected += $row['collected'];
}
echo "<tr><td align='right'><b>Totals:</b></td><td>&nbsp;</td><td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . number_format($total_tanya) . 
	"</td><td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . number_format($total_mishna) . "</td><td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$" . 
	number_format($total_pledges) . "</b></td><td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$" . number_format($total_collected) . "</b></td></tr>";
?>
</table>
</body>
</html>
