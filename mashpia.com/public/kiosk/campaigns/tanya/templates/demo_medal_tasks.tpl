	<title>Tasks - Tzivos Hashem Management System</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!--<meta http-equiv="X-UA-Compatible" content="chrome=1">-->
	<meta http-equiv="X-UA-Compatible" content="IE=8" />
	<!--<link rel="alternate" media="print" href="../withdraw_print.php">-->
	<link rel="stylesheet" type="text/css" href="./scripts/shadowbox/shadowbox.css">

	<link href="./styles/reset.css" rel="stylesheet" type="text/css" />
	<link href="./styles/style.css" rel="stylesheet" type="text/css" />
	<link href="./styles/print.css" rel="stylesheet" type="text/css" media="print" />
	<!--[if lt IE 8]>
	<link href="./styles/style_ie.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<script src="./scripts/jquery.core.js" type="text/javascript"></script>
	<script src="./scripts/jquery.ui.js" type="text/javascript"></script>
	<script type="text/javascript">
		function sliderPage() {
			return false;
		}
	</script>
	<script type="text/javascript" src="./scripts/easySlider1.7.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){    
			$("#slider").easySlider({
				numeric: true, 
				controlsBefore:    '<div class="page_dots">',
				controlsAfter:    '</div>'
				});
		});  
	</script>
	<script type="text/javascript" src="./scripts/easySlider1.7.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$("#slider").easySlider({
				numeric: true, 
				controlsBefore:	'<div class="page_dots">',
				controlsAfter:	'</div>'
				});
		});	

		$(window).load(function(){
			sliderPage();
		});	

		function sliderPage() {
					return false;
		}
	</script>
	<script type="text/javascript" src="./scripts/jquery.scroll.js"></script>
	<script type="text/javascript">
	$(function() {
		$('.scroll-pane').jScrollPane({showArrows:true, scrollbarWidth: 42, arrowSize: 42});
	});
	</script>
	<link href="./styles/jquery.scroll.css" rel="stylesheet" type="text/css" />
</head>

<body class="blue">
		<div id="wrapper">
			
			<div id="header">
				<div class="org">
    <div class="nav">

        <ul>
            <li class="icon_back"><a href="__!BASE_URI!__&action=medal_missions&medal=__!medal_param!__">Back</a></li>
            <li class="icon_home"><a href="../statement.php">Home</a></li>
            <li class="icon_logout"><a href="../logout.php?n=kiosk.php">Logout</a></li>
        </ul>
    </div>
	__!TanyaTitle!__
			</div>	
			<div id="main">
				
				<div id="page_title">
					Tasks				</div>

					
				<div class="three_column padding_top">
					
					<div class="content">
						
						<div id="slider">
						
							<ul class="tasks">
							
<li>
	<div class="slider_title">__!medal_pending!____!medal_title!__</div>

	<div class="scroll-pane">


		<div class="boxes mainbox mission_icon">

			<div class="title">Mission Tasks</div>

			__!medal_tasks!__
		</div>
	</div>

</li>
								
							</ul>
							
						</div>
						
					</div>
					
				</div>

				
			</div>
		
			<div id="footer">
				<div class="footer_logo">
</div>
<div class="footer_logout">
</div>			</div>
		  
		</div>
		
	</body>
	
	
</html>	