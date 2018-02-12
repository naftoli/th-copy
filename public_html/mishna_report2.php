<?
$admin_auth = array('school');
require_once 'header.php';

require_once 'class.mishnaReport.php';
$m = MishnaReport::getInstance('army');
$mesechtos = $m->getMesechtos();
$numMesechtos = count($mesechtos);
$mesechtoNames = $m->getMesechtoNames();

$numPerokim = 0;
$numMishnos = 0;
foreach ($mesechtos as $mesechto) {
	$perokim = $m->getPerokim( $mesechto );
	$numPerokim += count($perokim);
	foreach ($perokim as $perek) {
		$mishnos = $m->getMishnos( $mesechto, $perek );
		$numMishnos += count($mishnos);
	}			
}

$lines = $m->getLines();
$mishnos = $m->getAllMishnos();
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Army-Wide Mishna Report</title>
		<meta charset="UTF-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
        </style>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1>Army-Wide Mishna Report</h1>
		
		<table>
			<tr>
				<td>Number of Mesechtos Learned</td>
				<td><?=$numMesechtos?></td>
			</tr>
			<tr>
				<td>Number of Perokim Learned</td>
				<td><?=$numPerokim?></td>
			</tr>
			<tr>
				<td>Number of Mishnos Learned</td>
				<td><?=$numMishnos?></td>
			</tr>
			<tr>
				<td>Number of Lines Learned</td>
				<td><?=$lines?></td>
			</tr>
		</table>
		
		<br />
		<table>
			<tr>
				<th>Mesechtos Learned</th>
				<th>Perokim / Mishnos Learned</th>
			</tr>
			<?
			$pLearned = array();
			$mLearned = array();
			foreach ($mishnos as $mesechto => $info) {
				foreach ($info as $perek => $other) {
					$pLearned[$mesechto][$perek] = 1;
					foreach ($other as $mishna) {
						$mLearned[$mesechto][$perek][$mishna] = 1;
					}
				}
			}
			foreach ($pLearned as $mesechto => $info) {
				echo "<tr><td>" . $mesechtoNames[$mesechto] . "</td><td>";
				foreach ($info as $perek => $val) {
					echo "<table><tr><td>";
					echo $m->he_chars[$perek] . "</td><td>";
					foreach ($mLearned[$mesechto][$perek] as $mishna => $val) {
						echo $m->he_chars[$mishna] . ",";
					}
					echo "</td></tr></table>";
				}				
				echo "</td></tr>";
			}
			?>
		</table>
	</body>
</html>