<?
require '../../db.php';

$counselors = array(
	4 => array(
			'Mendel Rapoport' => 'Division Head',
'Menachem Muller' => 'Bunk 1',
'Eli Echstein' => 'Bunk 2',
'Mendy Donin' => 'Bunk 3',
'Chaim Shemtov' => 'Bunk 3',
'Mendy Druin' => 'Bunk 4',
'Yitzchok Pruss' => 'Bunk 5'
		),
	3 => array(
			'Shneur Wolff' => 'Division Head',
'Mendel Raichik' => 'Bunk 1',
'Levi Cohen' =>	'Bunk 2',
'Sholom Sapo' => 'Bunk 3',
'Mendel Dinnerman' => 'Bunk 4',
'Mendel Gurkov' => 'Bunk 4',
'Mendel Schmukler' => 'Bunk 5'
		),
	2 => array(
			'Getzy Rubashkin' => 'Division Head',
'Yisroel Rosenbaum' => 'Bunk 1',
'Shmuel Wilansky' => 'Bunk 2',
'Meir Guigue' => 'Bunk 3',
'Abba Prager' => 'Bunk 4',
'Eli Weiss' => 'Bunk 5'
		),
	1 => array(
			'Yisroel Ohana' => 'Division Head',
'Shloime Neparstek' => 'Bunk 1',
'Mendy Wagner' => 'Bunk 2',
'Shimmy Russel' => 'Bunk 3',
'Yechiel Fayershteyn' => 'Bunk 3',
'Mottel Gordon' => 'Bunk 4',
'Mendel Chein' => 'Bunk 5'
		),
	6 => array(
			'Dudi Slapochnik' => 'Division Head',
'Shimmy Klein' => 'Bunk 1',
'Dovid Nimni' => 'Bunk 2',
'Nati Kohen' => 'Bunk 3',
'Simcha Haberman' => 'Bunk 4',
'Eli Shomer' => 'Bunk 5'
		),
	5 => array(
			'Yitzi Oster' => 'Division Head',
'Eliyahu Ezagui' => 'Bunk 1',
'Yosef Yitzchak Gotlleib' => 'Bunk 2',
'Yechezkel Kohn' => 'Bunk 3',
'Leibel Orenstein' => 'Bunk 4',
'Levi Waren' =>	'Bunk 5'
		)
);
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
				text-align: center;
			}
			.info {
				font-family: 'Georgia';
				font-size: 16px;
				line-height: 1.4;
			}
			.info .name {
				font-weight: bold;
				font-size: 18px;
			}
		</style>
	</head>
	
	<body>
		<?
		foreach ($counselors as $team => $info) {
			foreach ($info as $name => $grade) {
				?>
				<div class="card">
					<div class="cardTop"></div>
					<img src="<?=$team?>0.jpg" />
					<div class="inner">
						<div class="info">
							<span class="name"><?=$name?></span><br />
							<?=$grade?><br />
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