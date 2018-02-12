<? 
$admin_auth = array('school','user'); 
require('header.php'); 

$start = 2456224; //October 21, 2012
//$end = unixtojd();
$end = 2456259; //November 27, 2012

$str1 = jdtojewish($start, true, CAL_JEWISH_ADD_GERESHAYIM);
$start_he = iconv('WINDOWS-1255', 'UTF-8', $str1);

$str2 = jdtojewish($end, true, CAL_JEWISH_ADD_GERESHAYIM);
$end_he = iconv('WINDOWS-1255', 'UTF-8', $str2);
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
</style>
</head>

<body>
<? include('admin_header.php');?>
<? if ($admin_auth[0] == 'school') : ?>
<? include_once('db.php'); ?>

<h1>Promotions for Chanuka Rally</h1>

<?
if (isset($_POST['submit'])) {
    
    $super = false;
    if ( $admin->auth == 'super' )
        $super = true; 

    switch ($_POST['sort']) { 
        case 'grade': 
            $sort = " c.class_grade, c.class_sub, u.last, u.first";
            $header = "<tr>" . ($super ? "<th>School Name</th>" : "") . "<th>Grade</th><th>Teacher</th><th>Name</th><th>Rank</th></tr>";
            break;
        case 'rank':
            $sort = " rank_ord, c.class_grade, c.class_sub, u.last, u.first";
            $header = "<tr>" . ($super ? "<th>School Name</th>" : "") . "<th>Rank</th><th>Grade</th><th>Teacher</th><th>Name</th></tr>";
            break;
        default:
            $sort = '';
            break;
    }

    $users = array();
    
    if ( $super ) {
        $sql = "select school_id from schools 
                where school_era is null 
                and school_id not in (82) 
                order by school_name";
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $schools[] = $row['school_id'];
        }
    } else {
        $schools[] = $admin->school_id;
    }
    $str = implode( ',', $schools );

    if ($admin->admin_id == 20) {
        $school = "in (19, 42) ";
    } else {
        $school = "in ($str) ";
    }

    if ( $super ) {
        $sql = "
            SELECT rm.date_promoted, r.rank_name, s.school_name, u.user_id, u.last, u.first, c.class_grade, c.class_sub, c.class_teacher
            FROM `rank_marks` rm
            JOIN ranks r
            USING ( rank_ord )
            JOIN users u
            USING ( user_id ) 
            JOIN schools s 
            USING (school_id) 
            JOIN classes c ON ( u.class_id = c.class_id )
            WHERE u.school_id $school
            AND u.user_registered > 0 
            AND rm.rank_ord != 1 
            AND rm.date_promoted >= $start      
            AND rm.date_promoted <= $end        
            ORDER BY s.school_name,$sort
        ";
    } else {
        $sql = "
            SELECT rm.date_promoted, r.rank_name, u.user_id, u.last, u.first, c.class_grade, c.class_sub, c.class_teacher
            FROM `rank_marks` rm
            JOIN ranks r
            USING ( rank_ord )
            JOIN users u
            USING ( user_id )
            JOIN classes c ON ( u.class_id = c.class_id )
            WHERE u.school_id $school
            AND u.user_registered > 0 
            AND rm.rank_ord != 1 
            AND rm.date_promoted >= $start      
            AND rm.date_promoted <= $end        
            ORDER BY $sort
        ";
    }
    //echo $sql;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $users[] = $row;
    }

    if (count($users) == 0) {
        echo "No students in this class.";
        exit;
    }
    
    echo "<div align='center'>";
    echo "<p>This report includes chayolim who were reported from " . $start_he . " to " . $end_he . "</p>";
    echo "<h3>Any schools who do not send in their promotion pictures by ט\"ז כסלו התשע\"ג, 
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
    
    echo "<div align='center'>";
    echo "<br /><input type='button' value='Print' onclick='window.print()'><br /><br />";
    echo "<table>";
    echo $header;
    foreach ($users as $user) { 
        $grade = $user['class_sub'] == '' ? $user['class_grade'] : $user['class_grade'] . "-" . $user['class_sub'];

        if ($_POST['sort'] == 'grade') {
            echo "<tr><td>" . ($super ? $user['school_name'] . "</td><td>" : "") . $grade . "</td><td>" . $user['class_teacher'] . "</td><td>" . 
            substr( $user['first'], 0, 1 ) . substr( $user['last'], 0, 1 ) . "</td><td>" . $user['rank_name'] . "</td><td>" . 
            "<a href='http://mashpia.com/admin_user.php?action=edit&user_id=" . $user['user_id'] . "&school_id=" . 
            $admin->school_id . "'>picture</a></td></tr>";
        } 
        else if ($_POST['sort'] == 'rank') {
            echo "<tr><td>" . ($super ? $user['school_name'] . "</td><td>" : "") . $user['rank_name'] . "</td><td>" . $grade . "</td><td>" . $user['class_teacher'] . "</td><td>" . 
            substr( $user['first'], 0, 1 ) . substr( $user['last'], 0, 1 ) . "</td><td>" . 
            "<a href='http://mashpia.com/admin_user.php?action=edit&user_id=" . $user['user_id'] . "&school_id=" . 
            $admin->school_id . "'>picture</a></td></tr>";
        }
        
    }
    echo "</table>";
    echo "</div>";

} else {
?>
<p>How would you like your report?</p>
<form action='promotion_report_ini.php' method='post'>
<input type='radio' name='sort' value='grade'> By Grade<br />
<input type='radio' name='sort' value='rank'> By Rank<br /><br />
<input type='submit' value='submit' name='submit'>
</form>
<? } ?>
<? else : ?>
no permission to view this page
<? endif; ?>
</body>
</html>