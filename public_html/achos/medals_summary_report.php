<?php
$admin_auth = array('school'); 
require('header.php'); 
require_once('calendar.php');

require_once 'class.medalReport.php';
$m = new MedalReport;

$previous = false;
if ( isset($_GET['go']) && $_GET['go'] == 'back' ) {
    $previous = true; 
    $m->setPreviousDates();
}

$heDates = $m->getHeReportDates();
$m->setMedalSummary();
$summary = $m->getMedalSummary();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

	<HEAD>
		<TITLE>Medals Summary Report</TITLE>
		<LINK href="admin_styles.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<style type="text/css">
		    .page {
		        width: 8in;
		        height: 10.5in;
		    }
		    .column {
		        width: 3.5in;
		        height: 10.5in;
		        padding: .3in;
		    }
		    .label {
		        width: 2in;
		        font-size: 14px;
		    }
		    .medals {
		        width: 1.5in;
		        margin-left: .3in;
                font-size: 14px;
		    }
		    .break {
		        clear: both;
		    }
		    .page-break {
		        page-break-after: always;
		    }
		    @media screen {
		        .no-print {
		            display: block;
		        }
		    }
		    @media print {
		        .no-print {
		            display: none;
		        }
		    }
		</style>
	</HEAD>
	
	
	<BODY>
		<?php include('admin_header.php'); ?>
		
		<div class="no-print">
        <h1>Medals Report Summary</h1>        
            <div>
                Current Report is calculated from <?=$heDates['start_he']?> up to <?=$heDates['end_he']?>.<br />
                <? if ( $previous ) { ?> 
                Click <a href='medals_summary_report.php'>here</a> to show next report dates.<br /><br />
                <? } else { ?> 
                Click <a href='medals_summary_report.php?go=back'>here</a> to show previous report dates.<br /><br />
                <? } ?>
            </div>
        </div>
        
		<div id="report_div" name="report_div">
			<div class='page'>
			<? 
			foreach ($summary as $school => $line) {
			    echo "Medals Summary for " . $school . "<br /><br />"; 
				foreach ($line as $subject => $medals) {
				    echo "<div class='label'>" . $subject . "<br />";
				    echo "<div class='medals'>"; 
				    foreach ($medals as $medal => $total) {
				        echo $medal . " - " . $total . "<br />";
				    }
                    echo "</div></div>";
                    echo "<div class='break'></div>";
				} 
                echo "<br />";
                echo "<div class='page-break'></div>";
                echo "<br />";   
			}
			?>
			</div>
		</div>
				
	</BODY>
</HTML>
