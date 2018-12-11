<?
$admin_auth = array('school');
require_once 'header.php';
require_once 'class.mishnaReport.php';

$id = isset( $_GET['id'] ) ? $_GET['id'] : $admin_user['auths']['school'][0];
$sql = "select * from schools where school_id = " . mysql_real_escape_string($id);
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$logo = $row['logo'];
$schoolName = $row['school_name'];
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Base Mishna Report</title>
		<meta charset="UTF-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<link href="mishna_report.css" rel="stylesheet" type="text/css">
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
		<h1 class="noPrint">Base Mishna Report</h1>
		
		<div align='center' class='noPrint'>
	        <input type='button' value='Print' onclick='window.print()' />
	    </div>
				
		<div class="surround">
			<div class="top">
				<table style="width: 450px;">
					<tr>
						<td class="bp">
							תורה בעל פה
						</td>
					</tr>
					<tr>
						<td class="topInfo">
							<?=$schoolName?> 
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
					$tanya = $t->getTotalLearned('school', $id);
					require_once 'class.schoolClasses.php';
					$s = new SchoolClasses($id);
					$grades = $s->getClasses();
					
					foreach ($grades as $grade) {
						$r = MishnaReport::getInstance('platoon', $grade['class_id']);
						$g = $grade['class_grade'] . (empty($grade['class_sub']) ? '' : '-' . $grade['class_sub']);
						echo "<tr><td class='line'><a href='mishna_platoon_report.php?id=" . $grade['class_id'] . "'>" . 
							$g . "</td><td>" . $r->numMesechtos . "</td><td class='line'>" . 
							$r->numMesechtosAtOnce . "</td><td>" . $r->numPerokim . "</td><td class='line'>" . 
							$r->numPerokimAtOnce . "</td><td class='line'>" . $r->numMishnos . 
							"</td><td class='line'>" . $r->numLines . "</td><td>" . $tanya . "</td></tr>";
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
	</body>
	<script>
		$(function() {
			var w = $(".info").width();
			$(".top table").width(w-100);
		});
	</script>
</html>