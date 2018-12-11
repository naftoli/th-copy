<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
	header("Location: index.php");
	exit;
}

$admin = $_SESSION['admin_id'];
$school = $_SESSION['school'];
$grade = $_SESSION['grade'];
$name = $_SESSION['name'];
$photo = $_SESSION['photo'];
$subject = $_SESSION['subject'];

switch ($subject) {
	case 2:
		$type = 'Hoo';
		break;
	case 3:
		$type = 'Friendship Circle';
		break;
	case 4:
		$type = 'Personal';
		break;
}

require_once '../db.php';
require_once '../class.achosStudent.php';
require_once 'classes/hoo.php';

$as = new AchosStudent($admin);
$hoo = new Hoo($as->getStudentID());

$points = $hoo->calcPoints();
$visits = $hoo->calcVisits();

$school_id = $as->getSchoolID();
if ($school_id == 1) {
	$img = 'http://mashpia.com/achos/mobile/images/brch.gif';
} else {
	$img = 'http://mashpia.com/achos/mobile/images/fc.png';
}
//$achos = $as->getPoints();
//$weekly = $as->getPoints('weekly');
//$daily = $as->getPoints('daily');
?>
<!doctype html>
<html class="no-js" lang="">
    <head>
    	<? include 'inc/head.php' ?>
        <title></title>
    </head>
		
    <body class="page-home">
    	<!--
        <nav class="pushy pushy-left container" role="navigation">
            <ul class="nav navbar-nav">
                <li><a href="home.php"><i class="icon"></i>Profile</a></li>
                <li><a href="goals.php"><i class="icon"></i>Goals</a></li>
                <li><a href="missions.php"><i class="icon"></i>Missions</a></li>
                <li><a href="reports.php"><i class="icon"></i>Reports</a></li>
            </ul>
        </nav>
        <div class="site-overlay"></div>
       -->
        <div class="pushy-container">
            <header class="navbar navbar-home" id="top" role="banner">
                <div class="container">
					<div align="center" style="margin: 10px;">
						<img id="logo" src="<?=$img?>" width="85" />
					</div>
					<!--
                    <div class="navbar-header">
                    	<!--
                        <button class="navbar-toggle" type="button">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="sr-only">Toggle navigation</span>
                        </button>
                        -->
						<!--
                        <a href="../" class="navbar-brand"></a>
                        <h2><?=$school?></h2>
                    </div>
					-->
                </div>
            </header>
            
            <div class="container">
                <div class="content">
                        
                    <div class="user user-large">
                        <div class="user-photo">
                            <img src="../images/staff/<?=$photo?>" alt="" class="img-responsive">
                        </div>	
                        <div class="user-meta">			
                            <h1><?=$name?></h1>
                            <h3>Grade <?=$grade?></h3>
                        </div>
                    </div>
					<br />
					<div class="score">
                        <div class="points"><b><?=$visits?></b> <?=$type?> Visits</div>
                    </div>
                    <br />
					<div class="score">
                        <div class="points"><b><?=$points?></b> <?=$type?> Points</div>
                    </div>
                    <br />
                </div>
            </div>
        </div>
         
    	<? include 'inc/footer.php' ?>

    	<? include 'inc/foot.php' ?>
        
    </body>
</html>