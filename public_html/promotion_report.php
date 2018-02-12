<? 
$admin_auth = array('school','user'); 
require('header.php'); 

//get dates from class.report.php
//if australian school use class.reportAustralia.php
//if (in_array($admin_user['auths']['school'][0], array(66,110,112)))
//	require_once 'class.reportAustralia.php';
//elseif ($admin_user['auths']['school'][0] == 55)
//	require_once 'class.reportAustralia2.php';
//else 
require_once 'class.report.php';
$r = new Report();

$previous = false;
if ( isset($_POST['go']) && $_POST['go'] == 'back' ) {
    $previous = true; 
    $r->setPreviousDates();
}

$dates = $r->getReportDates();
$heDates = $r->getHeReportDates();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Promotion Picture Report</title>
<style type='text/css'>
table, tr, th, td {
	font-size: 14px; 
	border: 1px dashed black;
}
th, td {
	padding: 8px;
}
.list ol{
	list-style-type: decimal;
}
.page-break {
    page-break-after: always;
}
@media print {
    .no-print {
        display: none;
    }
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin_auth[0] == 'school') : ?>
<? include_once('db.php'); ?>

<h1 class="no-print">Promotions for Next Rally</h1>

<?
if (isset($_POST['submit'])) {
            
    require_once 'class.adminSchools.php';      
    $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
    $schools = $as->getSchools();
       
	switch ($_POST['sort']) { 
		case 'grade': 
			$sort = " c.class_grade, c.class_sub, u.last, u.first";
			break;
		case 'rank':
			$sort = " rank_ord, c.class_grade, c.class_sub, u.last, u.first";
			break;
		default:
			$sort = '';
			break;
	}

	$users = array();
    foreach ( $schools as $school_id => $name ) {
        $sql = "
            SELECT rm.date_promoted, r.rank_name, u.user_id, u.last, u.first, c.class_grade, c.class_sub, c.class_teacher
            FROM `rank_marks` rm
            JOIN ranks r
            USING ( rank_ord )
            JOIN users u
            USING ( user_id )
            JOIN classes c ON ( u.class_id = c.class_id )
            WHERE u.school_id = $school_id ";
		if (!in_array($school_id, array(3,21,37,185))) $sql .= "AND u.user_registered > 0 ";
		$sql .= " 
            AND rm.rank_ord != 1 
            AND rm.date_promoted >= $dates[start]      
            AND rm.date_promoted <= $dates[end]        
            ORDER BY $sort
        ";
		echo "<input type='hidden' name='SQL' value='" . $sql . "' />";
        $result = mysql_query($sql);
        while ($row = mysql_fetch_assoc($result)) {
            $users[$school_id][] = $row;
        }
    }

	if (count($users) == 0) {
		echo "No students in this school.";
		exit;
	}
	
	?>
	<div>
	<p>Click <a href="https://docs.google.com/document/d/1GIFYrddcmW81NUDuSj5S3eWMQyMl2f2Rm3Ja5mvtwv0/edit" target="_blank">here</a> for the Promotion Pictures Complete Manual</p>
	<p>Click <a href="https://vimeo.com/86615168">here</a> to show the Chayolim the Medals Ceremony Video before handing out their hard earned Medals.</p>
	<h3>Directions</h3>
    Please upload all your promotion pictures to our dropbox account and under your school's folder.<br />
    If your school is not listed please create a new folder for your school with your school name and city.<br />
    Username: rallypromotionpictures@gmail.com<br />    
    Password: cthrallypromotions<br />    
	<br />
	<?
	
	echo "<div align='center'>";
	echo "<p>This report includes chayolim who were promoted from " . $heDates['start_he'] . " to " . $heDates['end_he'] . "</p>";
	
    if ( $previous ) {  
        echo "Click <a href='promotion_report.php'>here</a> to show next report dates.<br /><br />";
    } else {  
        echo "Click <a href='promotion_report.php?go=back'>here</a> to show previous report dates.<br /><br />";
    }
	
	$jd = ($dates['end'] + 7);
	$sendDate = iconv('WINDOWS-1255', 'UTF-8', jdtojewish($jd, true, CAL_JEWISH_ADD_GERESHAYIM));
	echo "<h3>Any schools who do not send in their promotion pictures by " . $sendDate . " , will not be mentioned during the promotion segment.
	    Past promotions will not be able to be made up in future rallies.</h3>";
	echo "</div>";
	
	echo "<div class='list'>";
	echo "<br />";
	echo "<h3>Picture Guidlines</h3>";
	echo "<ul>";
	echo "<ol>1. use a good camera, on the highest quality settings";
	echo "<ol>2. the camera should be held at landscape";
	echo "<ol>3. there should be <b>one</b> picture per school, including all the Chayolim who were promoted to that rank";
	echo "<ol>4. chayolim should be holding their new rank books and smiling 
	(please note: the rank books are not to be awarded until the rally, 
	however for the purposes of the picture you can distribute rank books)";
	echo "<ol>5. the picture should be taken in front of a plain white wall";
	echo "</ul>";
	echo "</div>";
    echo "<br />";
    
    ?>
    <div class="list">
        <h3>Uploading your Pictures</h3>
        <ul>
            <ol>1. To upload your promotion picture go to dropbox.com</ol>
            <ol>2. Open the Promotion Photos folder</ol>
            <ol>3. Find your school’s folder (or create one if it is missing)</ol>
            <ol>4. Upload your photos or drag and drop the files</ol>
			<ol>5. Re-name the picture file as your school name initials (Ex: Bais Chaya Mushka Los Angeles should be named as BCMLA)</ol>
            <!--<ol>5. Please name the photos properly. The photo name should include [the number of promoted kids] + [the initials of the rank (S, SM, SL, FL, M, CN, CL, G)]
    Example: for 7 students promoted to Captain label the photo 7CN</ol>-->
        </ul>
    </div>
    <div class="page-break"></div>
    <?
	
	echo "<div align='center' class='no-print'>";
	echo "<input type='button' value='Print' onclick='window.print()'><br />";
	echo "</div>";
    echo "</div>";
	
	foreach ( $users as $school_id => $info ) {
	    echo "<h2>" . $schools[$school_id] . "</h2>";
	    echo "<div align='center'>";
    	echo "<table>";
		if ($_POST['sort'] == 'grade') {
    		echo "<tr><th>Grade</th><th>Teacher</th><th>Name</th><th>Rank</th><th>Time</th><th>Date</th></tr>";
    	} else if ($_POST['sort'] == 'rank') {
    		echo "<tr><th>Rank</th><th>Grade</th><th>Teacher</th><th>Name</th><th>Time</th><th>Date</th></tr>";
    	}
    	foreach ($info as $user) { 
    		$grade = $user['class_sub'] == '' ? $user['class_grade'] : $user['class_grade'] . "-" . $user['class_sub'];
    
    		if ($_POST['sort'] == 'grade') {
    			echo "<tr><td>" . $grade . "</td><td>" . $user['class_teacher'] . "</td><td>" . 
    			$user['last'] . ", " . $user['first'] . "</td><td>" . $user['rank_name'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
    		} 
    		else if ($_POST['sort'] == 'rank') {
    			echo "<tr><td>" . $user['rank_name'] . "</td><td>" . $grade . "</td><td>" . $user['class_teacher'] . "</td><td>" . 
    			$user['last'] . ", " . $user['first'] . "</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
    		}
    	}
    	echo "</table>";
    	echo "</div>";
        echo "<div class='page-break'></div>";
    }
} else {
?>
<p>How would you like your report?</p>
<form action='promotion_report.php' method='post'>
<input type='radio' name='sort' value='grade'> By Grade<br />
<input type='radio' name='sort' value='rank'> By Rank<br /><br />
<? if ( isset( $_GET['go'] ) ) { ?>
    <input type="hidden" name="go" value="back" />
<? } ?>
<input type='submit' value='submit' name='submit'>
</form>
<? } ?>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>