<?
$admin_auth = array('school');
require_once 'header.php';
require_once 'class.mishnaReport.php';

$reports = array();
$id = $_GET['id'];
$idType = (isset($_GET['idType']) ? $_GET['idType'] : 0);

$grades = array();
if ($idType === 0) {
	$sql = "select school_id from classes where class_id = " . mysql_real_escape_string($id);
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$schoolID = $row['school_id'];
	$grades[] = $id;
} else if ($idType == 'school') {
	$schoolID = $id;
	$sql = "select class_id from classes where school_id = " . mysql_real_escape_string($id) . " 
			and class_era = 0";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$grades[] = $row['class_id'];
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
		<title>Platoon Mishna Report</title>
		<meta charset="UTF-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<link href="mishna_report.css" rel="stylesheet" type="text/css">
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1 class="noPrint">Platoon Mishna Report</h1>
		
		<div align='center' class='noPrint'>
	        <input type='button' value='Print' onclick='window.print()' />
	    </div>
		
		<?
		foreach ($grades as $id) {
			$sql = "select class_grade, class_sub, class_teacher from classes where class_id = " . mysql_real_escape_string($id);
			$result = mysql_query($sql);
			$row = mysql_fetch_assoc($result);
			$grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
			$teacher = $row['class_teacher'];
			?>
			<div class="surround">
				<div class="top">
					<table>
						<tr>
							<td class="bp">
								תורה בעל פה
							</td>
						</tr>
						<tr>
							<td class="topInfo">
								<?=$schoolName?> 
								&nbsp;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&nbsp;<?=$grade?>
								&nbsp;&nbsp;&nbsp;&#10022;&nbsp;&nbsp;&nbsp;
								<?=iconv('WINDOWS-1255', 'UTF-8', jdtojewish(unixtojd(), true, CAL_JEWISH_ADD_GERESHAYIM));?>
							</td>
						</tr>
					</table>
					<img src="schoolLogos/<?=empty($logo) ? 'TH-Blank Logo.gif' : $logo?>" />
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
							<th>שורות תניא</th>
						</tr>
						<?
						require_once 'class.tanya.php';
						$t = new Tanya(6);
						$colspan = 8;
						$tanya = $t->getTotalLearned('class', $id);
						
						require_once 'class.schoolsUsers.php';
						$s = new SchoolsUsers($schoolID);
						$s->setClasses(array($id));
						$u = $s->getUsers();
						$users = $s->getUserNames();
						
						foreach ($users as $grade => $other) {
							foreach ($other as $userID => $user) {
								$r = MishnaReport::getInstance('soldier', $userID);
								echo "<tr><td class='line'><a href='mishna_user_report.php?id=" . $userID . "'>" . $user . 
									"</a></td><td>" . $r->numMesechtos . "</td><td class='line'>" . 
									$r->numMesechtosAtOnce . "</td><td>" . $r->numPerokim . "</td><td class='line'>" . 
									$r->numPerokimAtOnce . "</td><td class='line'>" . $r->numMishnos . "</td><td class='line'>" . 
									$r->numLines . "</td><td>" . $tanya . "</td></tr>";
							}
						}
						?>
						<tr>
							<td colspan="<?=$colspan?>" style="text-align: center">
								תניא בעל פה לע״נ התמים נתן נטע בן הרה"ח ר' זלמן יודא דייטש ע"ה
								<br />
								משניות בעל פה לע"נ זאב ארי' ע"ה בן יבלט"א הרה"ח ר' שניאור זלמן שי' גליק
							</td>
						</tr>
					</table>
				</div>
			</div>
			<div class="break">&nbsp;</div>
		<? } ?>
	</body>
	<script>
		$(function() {
			var w = $(".info").width();
			$(".top table").width(w-100);
		});
	</script>
</html>