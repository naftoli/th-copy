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
$r->overrideDates(2457732, 2457774);

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
require_once 'class.adminSchools.php';      
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();
$sort = " c.class_grade, c.class_sub, u.last, u.first";

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
        $grade = $row['class_sub'] == '' ? $row['class_grade'] : $row['class_grade'] . "-" . $row['class_sub'];
        $users[$school_id][$grade][] = $row;
    }
}

if (count($users) == 0) {
    echo "No students in this school.";
    exit;
}

?>
<div>
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

$jd = ($dates['end'] + 4);
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
echo "<ol>3. there should be <b>one</b> picture per rank, including all the Chayolim who were promoted to that rank";
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
        <ol>5. Please name the photos properly. The photo name should include [the number of promoted kids] + [the initials of the rank (S, SM, SL, FL, M, CN, CL, G)]
        Example: for 7 students promoted to Captain label the photo 7CN</ol>
    </ul>
</div>

<div align='center' class='no-print'>
    <input type='button' value='Print' onclick='window.print()'><br />
</div>
</div>
<div class="page-break"></div>
<?php
foreach ( $users as $school_id => $info ) {
    foreach ($info as $grade => $other) {
        echo "<h2>" . $schools[$school_id] . " - " . $grade . "</h2>";
        echo "<div align='center'>";
        echo "<table>";
        echo "<tr><th>Name</th><th>Rank</th></tr>";
        foreach ($other as $user) { 
            echo "<tr><td>" . $user['last'] . ", " . $user['first'] . "</td><td>" . $user['rank_name'] . "</td></tr>";
        }
        echo "</table>";
        echo "</div>";
        echo "<div class='page-break'></div>";
    }
}
?>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>