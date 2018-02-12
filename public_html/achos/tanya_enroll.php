<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Tanya Hachloto</title>
<style type='text/css'>
@media all {
	.page-break {
		display: none;
	}
}
@media print {
	.page-break {
		display: block;
		page-break-before: always;
	}
	tr, th, td {
		font-size: 14px;
	} 
	hr, input {
		display: none;
	}
	.noprint {
		display: none;
	}
}
tr, th, td {
	border: 1px dashed black;
	padding: 10px;
	font-size: 12px;
}
.top {
    margin-top: -30px;
}
</style>
</head>

<body>
<? 
require_once('admin_header.php');
require_once 'class.adminSchools.php';
$as = new AdminSchools( $admin->admin_id, $admin->auth );
$schools = $as->getSchools();
if ( count( $schools ) > 1 ) {
    $school = isset( $_POST['school'] ) ? $_POST['school'] : null;
} else {
    $school = key( $schools );
}
?>

<h1 class='noprint'>Tanya Hachloto</h1>

<? if ( is_null( $school ) ) { ?>
    <form action="tanya_enroll.php" method="post">
        Choose School:
        <select name="school">
            <? foreach ( $schools as $id => $school ) {
                echo "<option value=" . $id . ">" . $school . "</option>";
            } ?>
        </select>
        <input type="submit" name="submit" value="go" />
    </form>
<? 
} else { 
    require 'class.schoolsUsers.php';
    $su = new SchoolsUsers( $school ); 
    $users = $su->getUsers();
    //print_r( $users ); 
?>

    <div align='center'>
    <input type='button' value='Print' onclick='window.print()'>
    <br /><br />
    </div>
    
    <?
    //create report for each class
    foreach ($users as $user) {
        $sql = "select s.school_name, c.class_grade, c.class_sub, c.class_teacher
                from users u 
                join schools s using (school_id) 
                join classes c using (class_id) 
                where u.user_id = " . $user['user_id'];
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        
        $sql = "select rank_name from ranks r 
                join rank_marks as rm using (rank_ord) 
                where rm.user_id = " . $user['user_id'] . " 
                order by rank_ord desc";
        $result = mysql_query( $sql );
        $rank = mysql_fetch_assoc( $result );
        
    	echo "<div align='center'>";
        echo "<h1>200,000 lines of Tanya Baal Peh</h1>";
        echo "<p align='center' class='top'>4,000 chayolim learning an average of 50 lines each!</p>";
    	echo "<b>Base:</b> " . $row['school_name'];
    	echo " <b>Platoon:</b> " . $row['class_grade'] . (empty( $row['class_sub'] ) ? '' : '-' . $row['class_sub'] ) . 
    	"<br /><b>Commander:</b> " . $row['class_teacher'];
    	echo " <b>" . $rank['rank_name'] . "</b> " . $user['first'] . " " . $user['last'] . "<br />";
    	echo "</div>";
    	?>
    	<br />
    	<p align="right">_____________________________________________________________</p>
    	<p>In honor of Shnas Hamosayim - 200 years since the histalkus of the Alter Rebbe, 
    	    I am committing to review the ___ lines I already know by heart and Bezras Hashem I will learn ___  new lines by heart.</p>
    	<p>By next Chod Daled Teves IYH I will know a total of ___ lines of Tanya Baal Peh.</p>
    	<p>B&rsquo;ezras Hashem I am undertaking to learn:</p>
    	<p>[ &nbsp; ] 1/4 of a line each week</p>
    	<p>[ &nbsp; ] 1/2 a line each week</p>
    	<p>[ &nbsp; ] 3/4 of a line each week</p>
    	<p>[ &nbsp; ] 1 line each week</p>
    	<p>[ &nbsp; ] 1 1/2 lines each week</p>
    	<p>[ &nbsp; ] 2 lines each week</p>
    	<p>[ &nbsp; ] 2 1/2 lines each week</p>
    	<p>[ &nbsp; ] 3 lines each week</p>
    	<p>[ &nbsp; ] 3 1/2 lines each week<p>
    	<p>[ &nbsp; ] 4 lines each week</p>
    	<p>[ &nbsp; ] 4 1/2 lines each week</p>
    	<p>[ &nbsp; ] *5 lines each week</p>
    	<p>[ &nbsp; ] **7 lines each week</p>
        <p>[ &nbsp; ] ***14 lines each week</p>
    	<p>* Complete 12 Prokim in three years!<p>
    	<p>** Learn one line a day & Complete 12 Prokim in two years!<p>
    	<p>*** Learn two lines a day & Complete 12 Prokim in one year!<p>
    	<p>[ &nbsp; ] B'ezras Hashem I will learn Tanya Baal Peh for (5 minutes every week day, and 10 minutes every Shabbos, a total of 2,000 minutes over the next year.</p>
    	<p>My Name ___________________________   My Mother's Name ________________________</p>
    	<p align='center'><i>This Tanya Baal Peh Program is in memory of Nosson Deitsch OBM. Nosson was a young bochur who learned and 
    	continued to review all 53 prokim of Likutei Amorim by heart until his passing on Lag BaOmer 5770 and throughout his life inspired others to do 
    	the same.</i></p>
    	<div align='center' class='noprint'>
    	-----------------------------<br /><br />
    	</div>
    	<?
    	echo "<div class='page-break'></div>";
    }
}
?>
</body>
</html>