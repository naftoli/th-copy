<?
require '../../db.php';

$info = array();
$sql = "select * from chidon_reg cr 
		join chidon_schools cs using (chidon_schools_id) 
		where cs.year = 5776 
		and cr.paid > 0 
		and cs.gender = 'boys' 
		order by school_name, last_name, name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	// if (empty($row['bus_number']) || empty($row['walking_group'])) continue;
	$info[$row['school_name']][] = $row;
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
				padding: 10px;
			}
			.card .inner img {
				width: 75px;
				float: left;
			}
			.card .inner img.img1 {
				border: 5px solid #330033;
			}
			.card .inner .info {
				margin-left: 10px;
			}
			.info {
				font-family: 'Georgia';
				font-size: 12px;
				line-height: 1.3;
			}
			.info .name {
				font-weight: bold;
				font-size: 14px;
			}
			.card .school {
				text-align: center;
				padding: 20px;
				vertical-align: middle;
				margin: auto;
				font-size: 40px;
			}
		</style>
	</head>
	
	<body>
		<? 
		foreach ($info as $school => $other) {
			foreach ($other as $row) { 
				?>
				<div class="card">
					<div class="cardTop"></div>
					<div class="inner">
						<div class="info">
							<span class="name"><?=$row['name'] . ' ' . $row['last_name']?></span><br />
							<?=$row['child_city_state']?><br />
							Team: <br />
							Round #: <br />
							Plaque #: <br />
							Medal #:
						</div>
					</div>
				</div>
				<div style="page-break-after: always"></div>
				<div style="clear: both"></div>
				<? 
			} 
		}
		?>		
	</body>
</html>	