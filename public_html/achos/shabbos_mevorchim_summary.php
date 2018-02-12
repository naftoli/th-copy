<? 
//ini_set('max_execution_time', 500); 
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Shabbos Mevorchim Tehillim Army Summary</title>
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
        clear: both;
    }
    .percent {
        color: red;
        font-weight: bold;
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
require_once 'class.shabbosMevorchim.php';

$sm = new ShabbosMevorchim();
$sm->setReportDates();
$sm->setArmyResults();

//show summary of current shabbos mevorchim first
$reportDates = $sm->getReportDates();
$date = end( $reportDates );
$key = key( $reportDates );
if ( isset( $_POST['date'] ) ) {
    $date = $_POST['date']; 
    $key = array_search( $date, $reportDates );
}
?>
<div class='no-print'>
    <h1>Shabbos Mevorchim Tehillim Army Summary</h1>
    <form action="shabbos_mevorchim_summary.php" method="post">
        If you want, you can choose a different month's summary:<br />
        <select name="date">
            <? 
            foreach ( $reportDates as $month => $d ) {
                echo "<option value=" . $d . ">" . $month . "</option>"; 
            }
            ?>    
        </select>
        <input type="submit" name="submit" value="go" />
    </form>
    <div align='center'>
        <input type='button' value='Print' onclick='window.print()'>
    </div>
</div>
<br />
<? 
require_once 'class.adminSchools.php';      
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

foreach ( $schools as $school_id => $name ) {
    $sm->setSchool( $school_id );
    $sm->setSchoolResults( $school_id );
    
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
    
    <div class='hayomYom'>
    <p align="right"> היום יום - כ"ה שבט </p>
    <p align="right"> 
    גּוֹמֵר זַיין דעֶם תְּהִלִּים שַׁבָּת מְבָרְכִים - דאָס דאַרף מעֶן אָפּהִיטעֶן, דאָס אִיז נוֹגֵעַ אִיהם, זַיינעֶ קִינְדעֶר אוּן קִינְדס קִינְדעֶר
    </p>    
    <p>
        "One should be careful to say Tehillim everyday and to say the whole Tehillim on Shabbos Mevorchim.
        These things are important for every person, his children and grandchildren."
    </p>
    <p>
        <br />
        <a href='shabbos_mevorchim_summary2.php?date=<?=$date?>'>Click here for visual charts!</a>
    </p>
    </div>
    
    <p><?=$sm->getSchoolName()?></p>
    <p>Shabbos Mevorchim <?=$key?></p>
    
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
    
    <div class="main" id="main">
    <?
    $sm->setASR( $date );
    echo "<div class='page-break'></div>";
    $sm->generateArmyAccomplishedReport();
    ?>
    </div>
    <div class="page-break"></div>
<? } ?>
</body>
</html>