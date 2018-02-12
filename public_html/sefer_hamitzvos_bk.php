<?
$admin_auth = array('school');
require_once 'header.php';

if (isset($_GET['user_id'])) {
	$user_id = $_GET['user_id'];
} else {
	$user_id = 0;
}

//create sefer hamitzvos class
require_once 'class.seferHamitzvos_bk.php';
$sh = new seferHamitzvos($user_id);
$mitzvos = $sh->getAllMitzvos();

//function to create tables of mitzvos
function createTable($user, $arr, $keys, $to, $from = 0) {
	?>
	<table>
		<tr>
			<th>Mission</th>
			<th>Mitzvos</th>
			<th>Done</th>
		</tr>
		<?
		for ($i = $from; $i < $to; $i++) {
			echo "<tr>";
			echo "<td>" . $keys[$i] . "</td>";
			echo "<td>" . $arr[$keys[$i]] . "</td>";
			//check if mission was done
			$sql = "select * from user_sefer_hamitzvos where user_id = " . $user . " and mission = " . $keys[$i];
			$result = mysql_query($sql);
			if (mysql_num_rows($result) > 0) 
				echo "<td><input type='checkbox' name='" . $keys[$i] . "' id='" . $keys[$i] . "' checked='checked' /></td>";
			else 
				echo "<td><input type='checkbox' name='" . $keys[$i] . "' id='" . $keys[$i] . "' /></td>";
			echo "</tr>";
		}
		?>
	</table>
	<?
}
?>
<html>
	
	<head>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type="text/css">
			table, tr, th, td {
				border: 1px dashed grey;
				padding: 10px;
				text-align: center;
			}
			.left {
				float: left;
				width: 30%;
				position: relative;
			}
			.middle {
				margin-left: 35%;
				position: relative;
			}
			.right {
				float: right;
				width: 30%;
				position: relative;
			}
		</style>

	</head>
	
	<body>
		<? require_once 'admin_header.php';	?>
		
		<script type="text/javascript">
			$(document).ready(function() {
				//create click/unclick functionality for each checkbox
				<? foreach ($mitzvos as $key => $val) {	?>
				
					$("#<?=$key?>").click(function() {
						//check if it's being checked or unchecked
						//and update db accordingly
						
						if ($(this).attr('checked')) {
							//mark mission and update medals/rank		
							var id = $(this).attr('name');
							var user_id = <?=$user_id?>;
							var params = 'user_id=' + user_id + '&function=updateMission&mission=' + id;
							$.get('ajax_functions.php', params, function(data) {
								alert(data);
							});
						} else {
							//delete mission and update medals/rank
							var id = $(this).attr('name');
							var user_id = <?=$user_id?>;
							var params = 'user_id=' + user_id + '&function=deleteMission&mission=' + id;
							$.get('ajax_functions.php', params, function(data) {
								alert(data);
							});
						}
						
					});
					
				<? } ?>
			});
		</script>
		
		<h1>Sefer Hamitzvos Campaign</h1>
		
		<form action="sefer_hamitzvos.php" method="post" name="sefer_hamitzvos">
			<?
			//split table into 3 for better space on page
			$num = count($mitzvos) / 3;
			$keys = array_keys($mitzvos);

			echo "<div class='left'>";
			createTable($user_id, $mitzvos, $keys, $num);
			
			echo "</div><div class='right'>";
			createTable($user_id, $mitzvos, $keys, $num+$num+$num, $num+$num);
			
			echo "</div><div class='middle'>";
			createTable($user_id, $mitzvos, $keys, $num+$num, $num);
			echo "</div>";
			?>						
		</form>

	</body>

</html>