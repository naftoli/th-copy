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
    border: 1px solid black;
    padding: 10px;
    font-size: 12px;
}
</style>
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
</head>

<body>
<? 
require_once('admin_header.php');
?>
<div class='no-print'>
    <h1>Shabbos Mevorchim Tehillim Army Summary</h1>
    <div align='center'>
        <input type='button' value='Print' onclick='window.print()'>
    </div>
</div>
<br />
<? 
require_once 'class.shabbosMevorchim.php';

$sm = new ShabbosMevorchim();
$sm->setReportDates();

$sm->setArmyResults();
$sm->setSchool( $admin->school_id );
$sm->setSchoolResults( $admin->school_id );

//show summary of current shabbos mevorchim first
$reportDates = $sm->getReportDates();
$date = end( $reportDates );
$key = key( $reportDates );
if ( isset( $_GET['date'] ) ) {
    $date = $_GET['date']; 
    $key = array_search( $date, $reportDates );
}

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
    <div id="pie"></div>
    <div class="no-print"><br /></div>
    <div id="chart"></div>
    <div class="no-print"><br /></div>
    <div id="chart2"></div>
    <div class="no-print"><br /><br /></div>
<?
$sm->setASR( $date );
echo "<input type='hidden' id='tasks' value='" . json_encode( $sm->getKeys() ) . "'>";
echo "<input type='hidden' id='kapitelach' value='" . json_encode( $sm->getAccomplishedKapitelach() ) . "'>";
echo "<input type='hidden' id='minutes' value='" . json_encode( $sm->getAccomplishedMinutes() ) . "'>";
echo "<input type='hidden' id='pieK' value='" . json_encode( $sm->getPieKapitelach() ) . "'>";
?>
</div>

</body>
</html>