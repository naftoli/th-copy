<?
$admin_auth = array('school');
require_once 'header.php';
require_once 'class.mishnaReport.php';

$reports = array();
$id = $_GET['id'];
$idType = (isset($_GET['idType']) ? $_GET['idType'] : 0);

$users = array();
if ($idType === 0) {
	$users[] = $id;
	$sql = "select school_id from users where user_id = " . mysql_real_escape_string($id);
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$schoolID = $row['school_id'];
} else {
	if ($idType == 'school') {
		$field = 'school';
		$schoolID = $id;
	} else if ($idType == 'grade') {
		$field = 'class';
		$sql = "select school_id from classes where class_id = " . mysql_real_escape_string($id);
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$schoolID = $row['school_id'];
	}
	
	$sql = "select user_id from users where {$field}_id = " . mysql_real_escape_string($id) . " 
			and user_registered > 0";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$users[] = $row['user_id'];
	}
}

$sql = "select * from schools where school_id = " . $schoolID;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$logo = $row['logo'];
$schoolName = $row['school_name'];
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Student Mishna Report</title>
		<meta charset="UTF-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<link href="mishna_report.css" rel="stylesheet" type="text/css">
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1 class="noPrint">Student Mishna Report</h1>
		
		<div align='center' class='noPrint'>
	        <input type='button' value='Print' onclick='window.print()' />
	    </div>
		
		<? 
		foreach ($users as $id) {
			$r = MishnaReport::getInstance('soldier', $id);
			if ($r->numLines > 0) {
				$sql = "select c.class_grade, c.class_sub, c.class_teacher, u.first, u.last 
						from users u 
						join classes c on (c.class_id = u.class_id) 
						where user_id = " . mysql_real_escape_string($id);
				$result = mysql_query($sql);
				$row = mysql_fetch_assoc($result);
				$grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
				$teacher = $row['class_teacher'];
				$user = $row['first'] . ' ' . $row['last'];
				?>
				<div class="surround">
					<div class="top">
						<table style="width: 700px; text-align: center">
							<tr>
								<td class="bp" style="text-align: center">
									תורה בעל פה
								</td>
							</tr>
							<tr>
								<td class="topInfo" style="text-align: center">
									<?=$schoolName?> 
									&nbsp;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&nbsp;<?=$grade?>
									&nbsp;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&nbsp;<?=$teacher?>
									&nbsp;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&nbsp;<?=$user?>
									&nbsp;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&nbsp;
									<?=iconv('WINDOWS-1255', 'UTF-8', jdtojewish(unixtojd(), true, CAL_JEWISH_ADD_GERESHAYIM));?>
								</td>
							</tr>
						</table>
					</div>
					<div style="clear: both"></div>
					
					<div class="main">
						<table class="info" dir="rtl">
							<tr>
								<th class="line"></th>
								<th>מסכת</th>
								<th class="line">בבת אחת</th>
								<th>פרקים</th>
								<th class="line">בבת אחת</th>
								<th class="line">משניות</th>
								<th class="line">שורות</th>
								<th class='line'>נקודות</th>
								<th>שורות תניא</th>
							</tr>
							<?
							require_once 'class.tanya.php';
							$t = new Tanya(6);
							$colspan = 8;
							$tanya = $t->getTotalLearned('user', $id);
							
							echo "<tr><td class='line'>" . $user . "</td><td>" . $r->numMesechtos . "</td><td class='line'>" . 
								$r->numMesechtosAtOnce . "</td><td>" . $r->numPerokim . "</td><td class='line'>" . 
								$r->numPerokimAtOnce . "</td><td class='line'>" . $r->numMishnos . "</td><td class='line'>" . 
								$r->numLines . "</td><td class='line'>" . $r->getPoints() . "</td><td>" . 
								$tanya . "</td></tr>";
							$colspan = 9;
							?>
						</table>
					</div>
				</div>
				<div style="clear: both"></div>
				<?
				//figure out mishnas to display
				$mishnas = array();
				foreach ($r->mishnos as $mesechto => $other) {
					foreach ($other as $perek => $rest) {
						$lastMishna = 0;
						$str = '';
						$first = true;
						$contiguous = false;
						foreach ($rest as $mishna) {
							if ($first) {
								$first = false;
								$str .= $r->heChars[$mishna];
							}
							if (($lastMishna+1) != $mishna) {
								if ($contiguous) {
									$str .= "-" . $r->heChars[$lastMishna];
									$contiguous = false;
								}
								$str .= "," . $r->heChars[$mishna];
							} else {
								$contiguous = true;
							}
							$lastMishna = $mishna;
						}
						if ($contiguous) {
							$str .= "-" . $r->heChars[$lastMishna];
						}
						$mishnas[$mesechto][$perek] = $str;
					}
				}
				echo "<br />";
				echo "<table dir='rtl' style='float: right'>";
				echo "<tr><th>מסכת</th><th>פרק</th><th>משנה</th>";
				$num = 0;
				foreach ($r->mishnos as $mesechto => $other) {
					foreach ($other as $perek => $rest) {
						if ($num++ == 30) {
							//new table
							echo "</table>";
							echo "<table dir='rtl' style='float: right; border-right: 1px solid black'>";
							echo "<tr><th>מסכת</th><th>פרק</th><th>משנה</th>";
							$num = 1;
						}
						echo "<tr><td>" . $r->mesechtoNames[$mesechto] . "</td><td>" . 
							$r->heChars[$perek] . "</td><td style='text-align: right'>" . 
							$mishnas[$mesechto][$perek] . "</td></tr>";
					}
				}
				echo "</table>";
				echo "<div class='break'>&nbsp;</div>";
			}
		}
		?>
	</body>
	<script>
		$(function() {
			var w = $(".info").width();
			$(".top table").width(w);
		});
	</script>
</html>