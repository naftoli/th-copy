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
<? if ($admin->auth == 'super') : ?>
<? 
$no_pledges = array(105, 58, 60, 21, 89, 19, 42, 162, 54, 14, 5, 84, 2);
?>
<h1>Maos Chitim Campaign</h1>
<table>
<tr>
<th>Teacher</th>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Grade</th>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tanya Lines</th>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Mishna Lines</th>
<? 
$id = $_GET['id'];
if (!in_array($id, $no_pledges)) { 
?>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pledges</th>
<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Collected</th>
<? } ?>
</tr>
<?
include_once('db.php');

$classes = array();
$sql = "select class_id, class_grade, class_sub, class_teacher 
		from classes 
		where school_id = $id 
		and class_era = 0 
		order by class_grade, class_sub";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$classes[] = $row;
}

$total_tanya = 0;
$total_mishna = 0;
$total_pledges = 0;
$total_collected = 0;
for ($i = 0; $i < count($classes); $i++) {

	$class_id = $classes[$i]['class_id'];
	$grade = $classes[$i]['class_grade'];
	$sub = $classes[$i]['class_sub'];
	$teacher = $classes[$i]['class_teacher'];
	
	$sql = "select sum(t.pledges) as pledges, sum(t.collected) as collected, sum(t.tanya_lines) as tanya_lines, sum(t.mishna_lines) as mishna_lines   
			from tanya_users as t, users as u, classes as c 
			where u.user_id = t.user_id 
			and u.class_id = c.class_id 
			and c.class_id = $class_id 
			#and u.user_registered > 0";
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$tanya_lines = number_format($row['tanya_lines']);
	$mishna_lines = number_format($row['mishna_lines']);
	$pledges = "$" . number_format($row['pledges']);
	$collected = "$" . number_format($row['collected']);

	echo "<tr><td><a href='maos_chitim_user.php?id=$class_id'>$teacher</a></td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$grade";
	if ($sub != "") echo "-$sub";
	
	if (in_array($id, $no_pledges))
		echo "</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$tanya_lines</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$mishna_lines</td></tr>";
	else
		echo "</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$tanya_lines</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$mishna_lines</td>
			<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$pledges</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$collected</td></tr>";
	
	$total_tanya += $row['tanya_lines'];
	$total_mishna += $row['mishna_lines'];
	$total_pledges += $row['pledges'];
	$total_collected += $row['collected'];
}
if (in_array($id, $no_pledges))
	echo "<tr><td>&nbsp;</td><td align='right'><b>Total:</b></td><td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . number_format($total_tanya) . 
	"</b></td><td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . number_format($total_mishna) . "</b></td></tr>";
else
	echo "<tr><td>&nbsp;</td><td align='right'><b>Total:</b></td><td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . number_format($total_tanya) . 
		"</b></td><td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . number_format($total_mishna) . 
		"</b></td><td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$" . number_format($total_pledges) . 
		"</b></td><td><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$" . number_format($total_collected) . "</b></td></tr>";
?>
</table>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>
