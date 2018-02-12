<? require_once 'yan_header.php'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Our birthday present to the Rebbe</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link href="yan.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script src="//use.edgefonts.net/aladin;strumpf-std.js"></script> 
    </head>
    
    <body>
    	<? include('admin_header.php'); ?>
        <h1 class="no-print">Our birthday present to the Rebbe</h1>
        
        <div class="no-print">
        	<!--Click <a href="yud_alef_nissan_report.php">here</a> for army-wide report<br />-->
        	Click <a href="yud_alef_nissan_school_report.php">here</a> for school-wide report<br />
        	Click <a href="#" onclick='window.open("linesChart.php", "_blank", "width=600, height=400")'>here</a> to view our quota chart.<br />
        	Click <a href="editSoldierLines.php">here</a> to adjust your soldier's quotas.<br />
        	<div align="center">
        		<input type="button" value="Print" onclick="window.print()" />
        	</div>
        </div>
        
        <? require_once 'yan_class.php'; ?>
               
    </body>
</html>