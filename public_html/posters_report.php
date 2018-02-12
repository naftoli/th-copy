<?
$admin_auth = array('school'); 
require('header.php');

$posters = array();
$sql = "select p.*, s.school_name 
		from posters p 
		join schools s using (school_id) 
		order by s.school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$posters[] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title>Posters Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>Posters Report</h1>
		
		<? if (!empty($posters)) { ?>
			<table>
				<tr>
					<th>School</th>
					<th>Number of Posters</th>
					<th>Date Ordered</th>
				</tr>
				<?
				foreach ($posters as $poster) {
					echo "<td>" . $poster['school_name'] . "</td><td>" . $row['posters'] . "</td><td>" . 
						jdtogregorian(unixtojd($row['date'])) . "</td></tr>";
				}
				?>
			</table>
		<? } else { ?>
			<p>No Posters Ordered.</p>
		<? } ?>
	</body>
</html>