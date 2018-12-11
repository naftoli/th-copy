<? 
ini_set('max_execution_time', 600);
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
$sm->setReportDates($_GET['date']);
$sm->setArmyResults();

//show summary of current shabbos mevorchim first
$reportDates = $sm->getReportDates();
$date = end( $reportDates );
$key = key( $reportDates );
?>
<div class='no-print'>
    <h1>Shabbos Mevorchim Tehillim Army Summary</h1>
    
    <div class="infobox" style="line-height: 1.2">
      SMT Reports are pulled out right after the Shabbos Mevorchim Tehillim Deadline. If a parent or base commander entered an amount
      on their account after this deadline, it will not show on this report and it will not be used to determine the winning schools/classes,
      however they will still receive their miles and mission.
    </div>
    
    <div align='center'>
        <input type='button' value='Print' onclick='window.print()'>
    </div>
    <div>
    	Click <a href="choose_sm_report.php">here</a> for base and platoon reports
    </div>
</div>
<br />
<div align="center" id="smPage">
<? 
require_once 'class.adminSchools.php';      
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

foreach ( $schools as $school_id => $name ) {
    $sm->setSchool( $school_id );
    $sm->setSchoolResults( $school_id );
    /*
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
	 * 
	 */
    ?>
    <!--
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
    -->    
    <div>
    	<?=$sm->getSchoolName()?> - שבת מברכים <?=$sm->getHebrewMonth($key)?><br /><br />
    </div>
    <? 
    //if ( !$sm->showDone( $date ) ) 
        //echo "<div style='float: left'>";
    
    //if ( !$sm->showDone( $date ) ) 
        //echo "</div>";
    //else 
        //echo "<br />";
	
	echo "<div style='float: left; width: 50%'>";
	$sm->generateArmyTable( $key, $date );
	echo "</div>";
	
    echo "<div style='float: right; width: 50%'>";
    $sm->generateBaseTable( $key, $date );
	echo "</div>";
    ?>
    <div style="clear: both"></div>
    <br />
    <!--
    <? if ( !is_null($row['school_logo_id']) ) { ?>
    <br />
    <br />
    <br />
    <? } ?>
    -->
    <div class="main" id="main">
    <?
    $sm->setASR( $date );
    $sm->generateArmyAccomplishedReport();
    ?>
    </div>
    <div class="page-break"></div>
    
    <div id="pie"></div>
    <div id="chart"></div>
    <div id="chart2"></div>
    <br />
    <div class="page-break"></div>
    <?
    echo "<input type='hidden' id='tasks' value='" . json_encode( $sm->getKeys() ) . "'>";
	echo "<input type='hidden' id='kapitelach' value='" . json_encode( $sm->getAccomplishedKapitelach() ) . "'>";
	echo "<input type='hidden' id='minutes' value='" . json_encode( $sm->getAccomplishedMinutes() ) . "'>";
	echo "<input type='hidden' id='pieK' value='" . json_encode( $sm->getPieKapitelach() ) . "'>";
	?>
	
<? } ?>
</div>

<script>
	$( function() {
		$(".loader").fadeOut("slow");
	});
</script>

<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
  google.load("visualization", "1", {packages:["corechart"]});
  google.setOnLoadCallback(drawCharts);
  function drawCharts() {
      
    var data = new google.visualization.DataTable();
    data.addColumn( 'string', 'School' );   
    data.addColumn( 'number', 'Goal' );
    data.addColumn( 'number', 'Accomplished' );
    
    var kapitelach = $.parseJSON( $('#kapitelach').val() );    
    $.each( kapitelach, function( name, value ) { 
        $.each( value, function( goal, done ) { 
            goal = parseInt(goal);
            done = parseInt(done);
           // alert( name + goal + done );
            data.addRow( ["" + name + "", goal, done] );
        });
    });

    var options = {
      title: 'Kapitelach',
      titleTextStyle: {fontSize: 16}, 
      height: 800, 
      fontSize: 10, 
      fontName: 'Verdana', 
      chartArea: {left: 300, top: 50, bottom: 50, height: '90%'}, 
      legend: {position: 'in', alignment: 'end', textStyle: {fontSize: 14}}
    };

    var chart = new google.visualization.BarChart(document.getElementById('chart'));
    chart.draw(data, options);
    
    var data2 = new google.visualization.DataTable();
    data2.addColumn( 'string', 'School' );   
    data2.addColumn( 'number', 'Goal' );
    data2.addColumn( 'number', 'Accomplished' );
    
    var minutes = $.parseJSON( $('#minutes').val() );    
    $.each( minutes, function( name, value ) { 
        $.each( value, function( goal, done ) { 
            goal = parseInt(goal);
            done = parseInt(done);
           // alert( name + goal + done );
            data2.addRow( ["" + name + "", goal, done] );
        });
    });

    var options2 = {
      title: 'Minutes', 
      titleTextStyle: {fontSize: 16}, 
      height: 800, 
      fontSize: 10, 
      fontName: 'Verdana', 
      chartArea: {left: 300, top: 50, bottom: 50, height: '90%'}, 
      legend: {position: 'in', alignment: 'end', textStyle: {fontSize: 14}}
    };

    var chart2 = new google.visualization.BarChart(document.getElementById('chart2'));
    chart2.draw(data2, options2);
    
    var data3 = new google.visualization.DataTable();
    data3.addColumn( 'string', 'School' );
    data3.addColumn( 'number', 'Percent' );
    
    var p = $.parseJSON( $('#pieK').val() );
    $.each( p, function( name, value ) {
        value = parseFloat( value ); 
        data3.addRow( ["" + name + "", value] );
    });

    var options3 = {
      title: 'Kapitalech Goal' 
    };

    var chart3 = new google.visualization.PieChart(document.getElementById('pie'));
    chart3.draw(data3, options3);
  }
</script>

</body>
</html>