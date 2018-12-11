<? 
$admin_auth = array('school','user'); 
require('header.php'); 

//get dates from class.report.php
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

<h1 class="no-print">Promotions for Gimmel Tamuz Rally</h1>

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
            WHERE u.school_id = $school_id 
            AND u.user_registered > 0 
            AND rm.rank_ord != 1 
            AND rm.date_promoted >= $dates[start]      
            AND rm.date_promoted <= $dates[end]        
            ORDER BY $sort
        ";
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
	<b>Directions:</b> Please upload all your promotion pictures to our dropbox account and under your school's folder.<br />
    Username: rallypromotionpictures@gmail.com<br />    
    Password: cthrallypromotions<br />    
    Please name the files properly: the title should include [the number of promoted kids] + [the initials of the rank (S, SM, SL, FL, M, CN, CL, G)]<br />
	<br />
	<?
	
	echo "<div align='center'>";
	echo "<p>This report includes chayolim who were reported from " . $heDates['start_he'] . " to " . $heDates['end_he'] . "</p>";
	
    if ( $previous ) {  
        echo "Click <a href='promotion_report.php'>here</a> to show next report dates.<br /><br />";
    } else {  
        echo "Click <a href='promotion_report.php?go=back'>here</a> to show previous report dates.<br /><br />";
    }
	
	echo "<h3>Any schools who do not send in their promotion pictures by Chof Ches Sivan, 
	their promotions will be excluded from the rally and will not be able to be made up in future rallies.</h3>";
	echo "</div>";
	
	echo "<div class='list'>";
	echo "<br />";
	echo "<h3>Picture Guidlines</h3>";
	echo "<ul>";
	echo "<ol>1. use a good camera, on the highest quality settings";
	echo "<ol>2. the camera should be held at landscape";
	echo "<ol>3. there should be one picture per rank, including all the Chayolim who were promoted to that rank";
	echo "<ol>4. chayolim should be holding their new rank books and smiling 
	(please note: the rank books are not to be awarded until the rally, 
	however for the purposes of the picture you can distribute rank books)";
	echo "<ol>5. the picture should be taken in front of a plain white wall";
	echo "</ul>";
	echo "</div>";
    echo "<br />";
	
	echo "<div align='center' class='no-print'>";
	echo "<input type='button' value='Print' onclick='window.print()'><br />";
	echo "</div>";
    echo "</div>";
	
	foreach ( $users as $school_id => $info ) {
	    echo "<h2>" . $schools[$school_id] . "</h2>";
	    echo "<div align='center'>";
    	echo "<table>";
    	echo "<tr><th>Grade</th><th>Teacher</th><th>Name</th><th>Rank</th><th>&nbsp;</th></tr>";
    	foreach ($info as $user) { 
    		$grade = $user['class_sub'] == '' ? $user['class_grade'] : $user['class_grade'] . "-" . $user['class_sub'];
    
    		if ($_POST['sort'] == 'grade') {
    			echo "<tr><td>" . $grade . "</td><td>" . $user['class_teacher'] . "</td><td>" . 
    			$user['last'] . ", " . $user['first'] . "</td><td>" . $user['rank_name'] . "</td><td>" . 
    			"<a href='http://mashpia.com/admin_user.php?action=edit&user_id=" . $user['user_id'] . "&school_id=" . 
    			$admin->school_id . "'>picture</a></td></tr>";
    		} 
    		else if ($_POST['sort'] == 'rank') {
    			echo "<tr><td>" . $user['rank_name'] . "</td><td>" . $grade . "</td><td>" . $user['class_teacher'] . "</td><td>" . 
    			$user['last'] . ", " . $user['first'] . "</td><td>" . 
    			"<a href='http://mashpia.com/admin_user.php?action=edit&user_id=" . $user['user_id'] . "&school_id=" . 
    			$school_id . "'>picture</a></td></tr>";
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