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
        <script>
        	$(function() {
        		$(".loading").load('ajax/getYan.php', function() {
        			$(".yan2").load('ajax/getYan2.php', function() {
        				$(".yan3").load('ajax/getYan3.php');
        			});
        		});
        	});
        </script>
    </head>
    
    <body>
    	<? include('admin_header.php'); ?>
        <h1 class="no-print">Our birthday present to the Rebbe</h1>
        
        <div class="no-print">
        	<div align="center">
        		<input type="button" value="Print" onclick="window.print()" />
        	</div>
        	Click <a href="#" onclick='window.open("linesChart.php", "_blank", "width=600, height=400")'>here</a> to view our quota chart.
        </div>
        
        <div class="loading">
       		<img src="images/loading.gif" />
       	</div>
       	
       	<div class="yan2"></div>
       	
       	<div class="yan3"></div>
        
        <?
        /*
        require_once 'yan_army.php';
		echo "<br /><br /><div class='page-break></div>";
		require_once 'yan_school.php';
		require_once 'yan_class.php';
		*/
        ?>       
	        
    </body>
</html>