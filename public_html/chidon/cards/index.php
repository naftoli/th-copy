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
				margin-left: 50px;
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
			$file = '';
			if (strpos($row['file'], 'img/') !== false) {
				if (file_exists('../' . $row['file'])) {
					$file = '../' . $row['file'];
				} else if (file_exists('../../mobile/chidon/' . $row['file'])) {
					$file = '../../mobile/chidon/' . $row['file'];
				} else if (file_exists('../photos/' . $row['file'])) {
					$file = '../photos/' . $row['file'];
				}
			} else {
				if (file_exists('../photos/' . $row['file'])) {
					$file = '../photos/' . $row['file'];
				} else if (file_exists('../../' . $row['file'])) {
					$file = '../../' . $row['file'];
				}
			}
			
			if (in_array($row['chidon_schools_id'], array(72,73,102,103))) {
				$address = $row['child_city_state'];
			} else {
				$address = $row['city_state'];
			}
			?>
			<div class="card">
				<div class="cardTop"></div>
				<img src="<?=$row['team']?>.jpg" />
				<div class="inner">
					<img src="<?=$file?>" />
					<div class="info">
						<span class="name"><?=$row['name'] . ' ' . $row['last_name']?></span><br />
						<?=$row['school_name']?><br />
						Grade <?=$row['grade']?><br />
						<?=$address?>
					</div>
				</div>
			</div>
			<div style="page-break-after: always"></div>
			<div style="clear: both"></div>
		<? } ?>	
	</body>
</html>	