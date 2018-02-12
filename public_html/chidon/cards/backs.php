<?
require '../../db.php';

$info = array();
$sql = "select * from chidon_reg cr 
		join chidon_schools cs using (chidon_schools_id) 
		where cs.year = 5776 
		and cr.paid > 0 
		and cs.gender = 'boys' 
		and chidon_reg_id in (2276) 
		order by school_name, grade, last_name, name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$info[] = $row;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			.card {
				height: 2.13in;
				width: 3.38in;
			}
			.cardTop {
				height: 0.4;
			}
			.card img {
				width: 3.3in;
			}
			.card .inner {
				padding-left: 20px;
			}
			.card .inner img {
				width: 70px;
				float: left;
			}
			.card .inner img.img1 {
				border: 5px solid #330033;
			}
			.card .inner .info {
				margin-left: 90px;
			}
			.info {
				font-family: 'Georgia';
				font-size: 11px;
				line-height: 1.3;
			}
			.info .name {
				font-weight: bold;
				font-size: 12px;
			}
			.card .backs {
				font-size: 12px;
				margin-left: 30px;
				margin-top: 0.5in;
			}
			.card .backs .host {
				font-weight: bold;
			}
		</style>
	</head>
	
	<body>
		<? 
		foreach ($info as $row) {
			$number = $row['chaperone_phone'];
			if (!empty($row['chaperone_phone2'])) {
				$number .= ', ' . $row['chaperone_phone2'];
			}
			if (!empty($row['chaperone_phone3'])) {
				$number .= ', ' . $row['chaperone_phone3'];
			} 
			?>
			<div class="card">
				<div class="backs">
					<div class="host"><?=$row['name'] . ' ' . $row['last_name']?></div>
					Host: <?=$row['family']?><br />
					Phone Number: <?=$row['phone']?><br />
					Chaperone Number(s): <?=$number?><br />
					Walking Group #: <?=$row['walking_group']?><br />
					Meeting Point: <?=$row['meeting_point']?><br />
					Thusday Bus #: <?=$row['bus_number']?><br />
				</div>
			</div>
			<div style="page-break-after: always"></div>
			<div style="clear: both"></div>
		<? } ?>	
	</body>
</html>	