<? 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Shabbos Mevorchim Tehillim Report</title>
<style type='text/css'>
@media all {
    .page-break {
        display: none;
    }
    .hayomYom {
        float: right;
        width: 300px;
        padding-right: 10px; 
        line-height: 1.5em;
    }
    .logo {
        float: left;
        margin-right: 20px;
    }
    .top {
        margin-left: auto;
        margin-right: auto;
        text-align: center;
    }
    .main {
        margin-left: auto;
        margin-right: auto;
    }
}
@media print {
    .page-break {
        display: block;
        page-break-after: always;
    }
    tr, th, td {
        font-size: 14px;
    }
    .no-print {
        display: none;
    }
    hr {
        display: none;
    }
}
tr, th, td {
    border: 1px dashed black;
    padding: 10px;
    font-size: 12px;
}
</style>
</head>

<body>
<? 
require_once('admin_header.php');
?>
<div class='no-print'>
    <h1>Shabbos Mevorchim Tehillim Report</h1>
    <div align='center'>
        <input type='button' value='Print' onclick='window.print()'>
    </div>
</div>
<br />
<? 
require_once 'class.shabbosMevorchim2.php';

$sm = new ShabbosMevorchim();
$sm->setReportDates();

$sm->setArmyResults();
$sm->setSchool( $admin->school_id );
$sm->setSchoolResults( array( $admin->school_id ) );
$sm->setClassResults();

//show summary of current shabbos mevorchim first
$reportDates = $sm->getReportDates();
$date = end( $reportDates );
$key = key( $reportDates );

//get school logo
$sql = "select school_logo_id from schools where school_id = " . $admin->school_id;
$result = mysql_query( $sql );
$row = mysql_fetch_assoc( $result );
if ( !is_null($row['school_logo_id']) ) {
    require_once 'file_save.php';
    echo "<div class='logo'>";
    echo linkImgFile($row['school_logo_id'], NULL, '100'); 
    echo "</div>";
}
?>

<p><?=$sm->getSchoolName()?></p>
<p>Shabbos Mevorchim <?=$key?></p>

<div class='hayomYom'>
<p align="right"> היום יום - כ"ה שבט </p>
<p align="right"> 
גּוֹמֵר זַיין דעֶם תְּהִלִּים שַׁבָּת מְבָרְכִים - דאָס דאַרף מעֶן אָפּהִיטעֶן, דאָס אִיז נוֹגֵעַ אִיהם, זַיינעֶ קִינְדעֶר אוּן קִינְדס קִינְדעֶר
</p>    
<p>
    "One should be careful to say Tehillim everyday and to say the whole Tehillim on Shabbos Mevorchim.
    These things are important for every person, his children and grandchildren."
</p>
</div>

<? if ( !is_null($row['school_logo_id']) ) { ?>
<br />
<br />
<br />
<? } ?>

<? 
if ( !$sm->showDone( $date ) ) 
    echo "<div style='float: left'>";

$sm->generateBaseTable( $key, $date );

if ( !$sm->showDone( $date ) ) 
    echo "</div>";
else 
    echo "<br />";

$sm->generateArmyTable( $key, $date );
?>

<br />

<div class='main'>
<? $sm->generateSummary( $key, $date ) ?>
</div>

<div class='page-break'></div>
<br />
<hr />
<?
$sm->generateReport(); 

$sm->generateAccomplishedReport();
?>
</body>
</html>