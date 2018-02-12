<?
require_once 'db.php';

require_once 'class.mishnaLines.php';
$m = new MishnaLines();
$sedorim = $m->getSedorim();

$mesechtos = array();
foreach ($sedorim as $seder => $name) {
	$mesechtos[$seder] = $m->getMesechtos( $seder );
}

$perokim = array();
foreach ($mesechtos as $seder => $info) {
	foreach ($info as $mesechto => $name) {
		$perokim[$seder][$mesechto] = $m->getPerokim( $seder, $mesechto );
	}
}

$mishnos = array();
foreach ($perokim as $seder => $info) {
	foreach ($info as $mesechto => $other) {
		foreach ($other as $perek) {
			$mishnos[$seder][$mesechto][$perek] = $m->getMishnos( $seder, $mesechto, $perek );
		}
	}
}
echo "<pre>"; 
//print_r($mishnos); 
echo "</pre>"; 
//exit;

$hebrew = array(
	1	=>	'א',
	2	=>	'ב',
	3	=>	'ג',
	4	=>	'ד',
	5	=>	'ה',
	6	=>	'ו',
	7	=>	'ז',
	8	=>	'ח',
	9	=>	'ט',
	10	=>	'י',
	11	=>	'יא',
	12	=>	'יב',
	13	=>	'יג',
	14	=>	'יד',
	15	=>	'טו',
	16	=>	'טז',
	17	=>	'יז',
	18	=>	'יח',
	19	=>	'יט',
	20	=>	'כ',
	21	=>	'כא',
	22	=>	'כב',
	23	=>	'כג',
	24	=>	'כד',
	25	=>	'כה',
	26	=>	'כו',
	27	=>	'כז',
	28	=>	'כח',
	29	=>	'כט',
	30	=>	'ל',
	31	=>	'לא',
	32	=>	'לב',
	33	=>	'לג',
	34	=>	'לד',
	35	=>	'לה', 
	36	=>	'לו',
	37	=>	'לז',
	38	=>	'לח',
	39	=>	'לט',
	40	=>	'מ',
	41	=>	'מא',
	42	=>	'מב',
	43	=>	'מג',
	44	=>	'מד',
	45	=>	'מה',
	46	=>	'מו',
	47	=>	'מז',
	48	=>	'מח',
	49	=>	'מט',
	50	=>	'נ',
	51	=>	'נא',
	52	=>	'נב',
	53	=>	'נג',
	53	=>	'נד',
	55	=>	'נה',
	56	=>	'נו',
	57	=>	'נז',
	58	=>	'נח',
	59	=>	'נט',
	60	=>	'ס',
	61	=>	'סא',
	62	=>	'סב',
	63	=>	'סג',
	64	=>	'סד',
	65	=>	'סה'
);
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Mishna</title>
	</head>
	
	<body>
		<table>
			<tr>
				<th>Seder</th>
				<th>Mesechto</th>
				<th>Perek</th>
				<th>Mishna</th>
				<th>Lines</th>
			</tr>
			<?
			foreach ($mishnos as $seder => $info) {
				foreach ($info as $mesechto => $other) {
					foreach ($other as $perek => $info) {
						foreach ($info as $mishna => $lines) {
							echo "<tr><td>" . $sedorim[$seder] . "</td><td>" . $mesechtos[$seder][$mesechto] . 
								"</td><td>פרק " . $hebrew[$perek] . "</td><td>משנה " . $hebrew[$mishna] . 
								"</td><td>" . $lines . "</td></tr>";
						}
					}
				}
			}
			?>
		</table>
	</body>
</html>