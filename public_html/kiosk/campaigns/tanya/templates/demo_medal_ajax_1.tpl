	<title>Missions - Tzivos Hashem Management System</title>
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

			var itemHeight = 88;
			var itemCol = 7
			var currentTop = 0;
			var containerHeight = Math.ceil($("#slider_inside > div > div").length / itemCol) * itemHeight;

			$("a#button_up").click(function () {

				if (Math.abs(currentTop) > 0) {
					$("#slider_inside > div").animate({"top":currentTop + itemHeight},{queue:false});
					currentTop += itemHeight;
				} 
				else {
					$("#slider_inside > div").animate({"top":currentTop + (itemHeight/2)},"fast").animate({"top":currentTop},"fast");
				}

			});

			$("a#button_dn").click(function () {

				if (Math.abs(currentTop) < (containerHeight - itemHeight)) {
					$("#slider_inside > div").stop().animate({"top":currentTop - itemHeight},{queue:false});
					currentTop -= itemHeight;
				} 
				else {
					$("#slider_inside > div").stop().animate({"top":currentTop - (itemHeight/2)},"fast").animate({"top":currentTop},"fast");
				}

			});	

		 });
	</script>
</head>
<body class="blue">



					<div class="slider_box">

						<ul class="missions">					

							<li>


								<div class="slider_title">Tanya Baal Peh - __!medal name!__</div>

								<div class="mission_side">
									<div class="medalImage" style='background: transparent url(/file_view.php?id=__!medal_id!__);'>
										<span class="badge">__!mission_remaining!__</span>
									</div>
									<a id="button_up">Up</a>

									<a id="button_dn">Down</a>
								</div>

								<div id="slider_inside" class="mission_boxes">

									<div id="missions_container">
											__!mission_button_list!__
									</div> <!-- missions_container -->

								</div> <!-- mission_boxes -->

								</li>

							</ul>

					</div> <!-- slider_box -->


</body>

</html>
