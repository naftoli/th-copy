<?
require_once 'db.php';

$prevPerek = 0;
$mesechtoPerokim = array();
$mesechtoMishnos = array();
$perokimMishnos = array();
$mishnaLines = array();
$mesechtoLines = array();

$sql = "select * from mishnos";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$mesechto = $row['mesechto_id'];
	$perek = $row['perek'];
	$mishna = $row['mishna'];
	$lines = $row['num_lines'];
	
	if ($perek != $prevPerek) {
		$prevPerek = $perek;
		if (isset($mesechtoPerokim[$mesechto])) {
			$mesechtoPerokim[$mesechto]++;
		} else {
			$mesechtoPerokim[$mesechto] = 1;
		}		
	}
	
	if (isset($perokimMishnos[$mesechto][$perek])) {
		$perokimMishnos[$mesechto][$perek]++;
		$mishnaLines[$mesechto][$perek] += $lines;
		
	} else {
		$perokimMishnos[$mesechto][$perek] = 1;
		$mishnaLines[$mesechto][$perek] = $lines;
	}
	
	if (isset($mesechtoMishnos[$mesechto])) {
		$mesechtoMishnos[$mesechto]++;
		$mesechtoLines[$mesechto] += $lines;
	} else {
		$mesechtoMishnos[$mesechto] = 1;
		$mesechtoLines[$mesechto] = $lines;
	}
}
echo "<pre>";
//print_r($mesechtoPerokim);
//print_r($perokimMishnos);
//print_r($perokimLines);
//print_r($mishnaLines);
//print_r($mesechtoMishnos);

$sql1 = array();
foreach ($mesechtoPerokim as $mesechto => $perokim) {
	$sql1[] = "insert into mesechtos_summary 
				set mesechto_id = $mesechto, 
				total_perokim = $perokim, 
				total_mishnos = " . $mesechtoMishnos[$mesechto] . ", 
				total_lines = " . $mesechtoLines[$mesechto];
}

$sql2 = array();
foreach ($perokimMishnos as $mesechto => $info) {
	foreach ($info as $perek => $mishnos) {
		$sql2[] = "insert into perokim_summary 
					set mesechto_id = $mesechto, 
					perek = $perek, 
					total_mishnos = $mishnos,
					total_lines = " . $mishnaLines[$mesechto][$perek];
	}
}
//print_r($sql2);
echo "</pre>";

foreach ($sql1 as $sql) {
	mysql_query($sql);
}
foreach ($sql2 as $sql) {
	//mysql_query($sql);
}
?>