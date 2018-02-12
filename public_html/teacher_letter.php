<?php
$admin_auth = array('school');
require 'header.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Teachers</title>
<style>
    .letter ul {
        margin-left: 0.5in;
    }
@media print {
    .letter {
        page-break-after: always;
        height: 10in;
    }
    .no-print {
        display: none;
    }
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<h1 class="no-print">Teacher Letters</h1>
<div align="center" class="no-print">
    <button onclick="window.print()">Print</button>
</div>
<?
$teachers = array();
require_once 'class.adminSchools.php';       
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
foreach ($schools as $id => $school) {
    $s = "select principal from schools where school_id = " . $id;
	$r = mysql_query($s);
	$p = mysql_fetch_assoc($r);
    
    $sql = "select class_id, class_grade, class_sub, email, cell
			from classes
			where school_id = " . $id . "
			and class_era = 0
			order by class_grade, class_sub";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $sql2 = "select a.* from admins a 
                join admin_auths aa using (admin_id)
                where aa.id = " . $row['class_id'] . " and aa.auth = 'class'";
        $result2 = mysql_query($sql2);
        $row2 = mysql_fetch_assoc($result2);
        $grade = $row['class_grade'] . (empty($row['class_sub']) ? '' : '-' . $row['class_sub']);
        $row2['email'] = empty($row2['admin_email']) ? $row['email'] : $row2['admin_email'];
        $row2['cell'] = empty($row2['admin_phone_mobile']) ? $row['cell'] : $row2['admin_phone_mobile'];
        $row2['principal'] = $p['principal'];
		$id = $row2['admin_id'];
		if (!$id) $id = 'class' . $row['class_id'];
        $teachers[$school][$id][$grade] = $row2;
    }    
}

foreach ($teachers as $school => $info) {
    foreach ($info as $admin => $other) {
        foreach ($other as $grade => $row) {
            ?>
            <h2><?=$school . ' Grade ' . $grade?></h2>
            <div class="letter">
                <br /><br /><br />
            Dear Teacher <?=$row['first'] . ' ' . $row['last']?>, <br /><br />

Do you use <strong>chinuch.org</strong>? Do you love it?<br /><br />

Do you wish there was a resource website with ready-made <strong>Chassidishe</strong> Resources?<br /><br />

BARUCH HASHEM! YOUR WISH HAS COME TRUE !<br /><br />
<strong>Tzivos Hashem</strong> has created a special Account for YOU with incredible Resources and NEW Features!<br />
To access the resources, simply go to <strong>TzivosHashem.com</strong> and sign in with your username and password. The resources are updated weekly, so be sure to check back often. <br />
<br />
<strong>Username: <?=$row['username']?></strong><br />
<strong>Passwrod: <?=$row['password']?></strong><br />
<br />
We would love to hear your feedback. Please contact us at cth@tzivoshashem.org. <br /><br />

<strong>Your Account Features:</strong><br /><br />
<ul>
    <li><strong>Weekly Resources</strong> - Spanning from the weekly Parsha, Niggunim, YomimTovim& more!</li>
    <li><strong>All Resources for 5778</strong> - Includes teacher’s guides and student worksheets with:
        <ul>
            <li>Chassidishe Yomim Tovim Resources</li>
            <li>Yom Tov Resources</li>
            <li>Niggunim Resources</li>
            <li>Parsha Resources</li>
            <li>Tefillah Resources</li>
            <li>Rebbe Resources</li>
            <li>Sefer Hazichronos “Roots” Resources</li>
            <li>Chitas Resources</li>
            <li>Tanya Baal Peh Resources</li>
        </ul>
    </li>
    <li><strong>Teacher’s Achievement Card Incentive</strong> - Students can earn “Achievement Cards” in YOUR classroom to earn points and buy prizes on their Tzivos Hashem Store!</li>
    <li><strong>Teacher Prize Store</strong> - Teachers can upload prizes that students can purchase from their Tzivos Hashem Store!</li>
    <li><strong>Monthly Calendar</strong> - Editable Calendar with all upcoming Tzivos Hashem happenings!</li>
    <li><strong>Chidon Resources</strong></li>
    <li><strong>Daily Missions Marker</strong> - Mark off your student’s Daily/Weekly progress.</li>
</ul>
<br /><br />
Principal <?=$row['principal']?> <span style="float: right">Tzivos Hashem HQ</span>
            </div><br /><br />
            <?
        }
    }
}
?>
</body>
</html>

