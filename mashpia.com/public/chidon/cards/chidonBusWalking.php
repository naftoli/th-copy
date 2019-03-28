<?
require '../../db.php';

$info = array();
$sql = "select * from chidon_reg cr 
		join chidon_schools cs using (chidon_schools_id) 
		where cs.year = 5776 
		and cr.paid > 0 
		and cs.gender = 'girls' 
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
		<? foreach ($info as $school => $other) { ?>
			<div class="card">
				<div class="school">
					<?=$school?>
				</div>
			</div>
			<div style="page-break-after: always"></div>
			<div style="clear: both"></div>
			<? foreach ($other as $row) { ?>
				<?
				$number = $row['chaperone_phone'];
				if (!empty($row['chaperone_phone2'])) {
					$number .= ', ' . $row['chaperone_phone2'];
				}
				if (!empty($row['chaperone_phone3'])) {
					$number .= ', ' . $row['chaperone_phone3'];
				}
				?>
				<div class="card">
					<div class="inner">
						<div class="info">
							<span class="name"><?=$row['name'] . ' ' . $row['last_name']?></span><br />
							Host Address: <?=$row['address']?><br />
							Host Number: <?=$row['phone']?><br />
							Chaperone Number(s): <?=$number?><br />
							Walking Group #: <?=$row['walking_group']?><br />
							Thursday Bus #: <?=$row['bus_number']?>
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