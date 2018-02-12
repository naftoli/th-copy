<?
require '../../db.php';

$info = array();
$sql = "select * from chidon_schools 
		where year = 5776 
		and gender = 'boys' 
		order by school_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$chap1 = $row['chaperone_name'];
	$chap2 = $row['chaperone_name2'];
	$chap3 = $row['chaperone_name3'];
	$chap4 = $row['chaperone_name4'];
	
	$school = $row['school_name'];
	$city = $row['city_state'];
	
	if (!empty($chap1)) {
		$info[] = array($chap1, $school, $city);
	}
	if (!empty($chap2)) {
		$info[] = array($chap2, $school, $city);
	}
	if (!empty($chap3)) {
		$info[] = array($chap3, $school, $city);
	}
	if (!empty($chap4)) {
		$info[] = array($chap4, $school, $city);
	}
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			@font-face {
				font-family: 'Sanchez';
				src('fonts/Sanchez-Black.otf');
			}
			.card {
				height: 2.13in;
				width: 3.38in;
			}
			.card span {
				width: 3.3in;
				font-size: 46px;
				font-weight: bold;
				font-family: 'Sanchez';
				margin-left: 15px;
			}
			.card .inner {
				padding: 10px;
				border-top: 5px solid #0033cc;
				margin-top: 10px;
			}
			.card .inner img {
				width: 75px;
				float: left;
			}
			.card .inner img.img1 {
				border: 5px solid #330033;
			}
			.card .inner .info {
				margin-left: 100px;
			}
			.info {
				font-family: 'Georgia';
				font-size: 14px;
				line-height: 1.3;
			}
			.info .name {
				font-weight: bold;
				font-size: 16px;
			}
			.info span {
				margin-left: 0;
			}
		</style>
	</head>
	
	<body>
		<?
		foreach ($info as $row) {
			?>
			<div class="card">
				<span>CHAPERONE</span>
				<div class="inner">
					<img src="Chidon Logo.PNG" />
					<div class="info">
						<span class="name"><?=$row[0]?></span><br />
						<?=$row[1]?><br />
						<?=$row[2]?>
					</div>
				</div>
			</div>
			<div style="page-break-after: always"></div>
			<div style="clear: both"></div>
		<? } ?>		
	</body>
</html>	