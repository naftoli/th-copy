<? 
ini_set('max_execution_time', 500);
ini_set('display_errors', 1);
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
    }
    .percent {
        color: red;
        font-weight: bold;
    }
    .loader {
		position: fixed;
		left: 0px;
		top: 0px;
		width: 100%;
		height: 100%;
		z-index: 9999;
		background: url('images/page-loader.gif') 50% 50% no-repeat rgb(249,249,249);
	}
	tr, th, td {
	    border: 1px solid black;
	    padding: 10px;
	    font-size: 12px;
	}
}
@media print {
    .page-break {
        display: block;
        page-break-after: always;
    }
    tr, th, td {
        font-size: 11px;
        padding: 4px;
    }
    .no-print {
        display: none;
    }
    hr {
        display: none;
    }
    #smPage {
    	/*margin-left: 1in;*/
    }
}
</style>

</head>

<body>
	<!--<div class="loader"></div>-->
<? 
require_once('admin_header.php');
require_once 'class.shabbosMevorchim.php';

$sm = new ShabbosMevorchim();
$sm->setReportDates();
$reportDates = $sm->getReportDatesAll();

if (isset($_POST['submit']) || isset($_GET['date'])) {
    $date = isset($_GET['date']) ? $_GET['date'] : $_POST['date'];

    $sm->setReportDates($date);
    
    //show summary of current shabbos mevorchim first
    $reportDates = $sm->getReportDates();
    $date = end( $reportDates );
    $key = key( $reportDates );
    ?>
    <div class='no-print'>
        <h1>Shabbos Mevorchim Tehillim Army Summary</h1>
        <div align='center'>
            <input type='button' value='Print' onclick='window.print();'>
        </div>
        <!--
        <div>
            Click <a href="choose_sm_report.php">here</a> for base and platoon reports
        </div>
        -->
    </div>
    <br />
    <div align="center" id="smPage">
        <div class="main" id="main">
        <?
        $sm->setASR( $date );
		$schools = $sm->getSchools();
		foreach ($schools as $sid => $school) $sm->setStudentResults($sid);
        $sm->generateHQReport(true);
        ?>
        </div>
    </div>
<? } else { ?>
    <h1>Shabbos Mevorchim HQ Report</h1>
    <form method="post" action="shabbos_mevorchim_hq_test.php">
        For: <select name="date">
            <? 
            $i = 0;
            $num = count($reportDates);
            foreach ($reportDates as $month => $d) {
                if (++$i == $num) 
                    echo "<option value=" . $d . " selected='selected'>Shabbos Mevorchim " . $month . "</option>";
                else 
                    echo "<option value=" . $d . ">Shabbos Mevorchim " . $month . "</option>"; 
            }
            ?> 
        </select><br /><br />
        <input type="submit" name="submit" id="submit" value="generate report">
    </form>
<? } ?>

<script>
	$( function() {
		$(".loader").fadeOut("slow");
	});
</script>

</body>
</html>