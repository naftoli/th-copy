	<title>Hachayal Kiosk</title>
	<link rel="stylesheet" type="text/css" href="./scripts/shadowbox/shadowbox.css">
	<link href="./styles/reset.css" rel="stylesheet" type="text/css" />
	<link href="./styles/style.css" rel="stylesheet" type="text/css" />
	<link href="./styles/print.css" rel="stylesheet" type="text/css" media="print" />
	<meta http-equiv="X-UA-Compatible" content="IE=8" />
	<!--[if lt IE 8]>
	<link href="./styles/style_ie.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<script src="http://www.google.com/jsapi"></script>
	<script>
		// Load jQuery
		  google.load("jquery", "1.3.2");
	</script>
	<script type="text/javascript" src="./scripts/easySlider1.7.js"></script>
	<script type="text/javascript">
		var arrLadderJSON = __!JSON Ladder!__;
		var intYearsRemaining = __!intYearsRemaining!__;
		var l = __!intLadder!__;
		var y = __!intYearsRemaining!__-1;
		var intCurrentLine = __!intCurrentLine!__;
		var intRemainingWeeks = __!intRemainingWeeks!__;

		$(document).ready(function(){
			$("#slider").easySlider({
				numeric: true, 
				controlsBefore:	'<div class="page_dots">',
				controlsAfter:	'</div>'
			});
			$("#spinner-ladder").spinner({
				start:l
			}).bind('spinchange', function(event, element, ui) {
				//spin_change("l",ui.value);
				l = ui.value;
				proc_ladder_details();
			});
			$("#spinner-year").spinner({
				start:y
			}).bind('spinchange', function(event, element, ui) {
				//spin_change("y",ui.value);
				y = ui.value;
				proc_ladder_details();
			});
			proc_ladder_details();
		});	

		$(window).load(function(){
			sliderPage();
		});

		function sliderPage() {
			return false;
		}
		
		function proc_ladder_details() {
			var intLadder = l+1;
			var intYears = y+1;
			var intLadderLines = arrLadderJSON[intLadder]["Line"]; // Eight year campaign value
			var intEndGoalX = (arrLadderJSON[intLadder]["EndGoal"]-intCurrentLine)/intYearsRemaining;
			$("#ladder_this_1").html(Math.round(intLadderLines / 8) + " Lines");
			$("#ladder_this_2").html((Math.round(intLadderLines / 416 * 100) / 100) + " Lines");
			$("#ladder_year_1").html(Math.round(intLadderLines / 8) + " New Lines");
			$("#ladder_year_2").html((Math.round(intEndGoalX * intYears) + intCurrentLine) + " Lines");
			$("#ladder_complete_lines").html(arrLadderJSON[intLadder]["EndGoal"] + " Lines");
			$("#ladder_complete_perek").html(arrLadderJSON[intLadder]["Perek"]);
			$("#ladder_complete_page").html(arrLadderJSON[intLadder]["Page"]);
			document.form01.ladder.value = intLadder;
			document.form01.line_goal.value = intLadderLines;
		}
	</script>
	<script type="text/javascript" src="./scripts/jquery.scroll.js"></script>
	<script type="text/javascript">
		$(function() {
			$('.scroll-pane').jScrollPane({showArrows:true, scrollbarWidth: 42, arrowSize: 42});
		});
	</script>
	<link href="./styles/jquery.scroll.css" rel="stylesheet" type="text/css" />
	<script>
		  google.load("jqueryui", "1.7.2");
	</script>
	<script type="text/javascript" src="./scripts/jquery.ui.spinner.js"></script>
	<script type="text/javascript">
		$(document).ready(function() {
			$(".spinner").spinner({min:0,start:4});
		});
	</script>
	<link href="./styles/jquery.spinner.css" rel="stylesheet" type="text/css" />
</head>
<body class="blue">

    <div id="wrapper">
        <div id="header">
          <div class="org">

			<div class="nav">
				<ul>
					<li class="icon_back"><a href="__!BASE_URI!__">Back</a></li>
					<li class="icon_home"><a href="../statement.php">Home</a></li>
					<li class="icon_logout"><a href="../logout.php?n=statement.php">Logout</a></li>
				</ul>
			</div>

			__!TanyaTitle!__
		<noscript><p class="js_alert">Notice: You have javascript disabled.<br />Some parts of the site will not function without javascript.</p></noscript>
    </div>
    <div id="main">

		<div id="page_title">Tanya Baal Peh Campaign</div>
		<div class="three_column padding_top">
		  <div class="content">
				<div class="sticker">Sample</div>
				<div id="slider">
				<ul class="overview">
						<li>
							<div class="slider_title">Ladder Overview</div>
							<div class="mainbox ladder">
		
								<div class="ladder_box">
									<div class="ladder_info_box">
										The first ladder is:
										<div class="vertical">

											<div id="spinner-ladder-sample" class="v-spinner">
												<div class="active">Ladder 1</div>
											</div>
										</div>
										Your weekly quota will be:
										<div class="ladder_info icon_book">0.15 Lines</div>
										Your yearly quota will be:<br />
										<div class="ladder_info icon_book">8 Lines</div>

										By age 13 you will know:<br />
										<div class="ladder_info"><script>document.write(arrLadderJSON[1]["EndGoal"]);</script> Lines - Perek <script>document.write(arrLadderJSON[1]["Perek"]);</script></div>
									</div>
								</div>
								<div class="ladder_box">
									<div class="ladder_info_box">
										The highest ladder is:
										<div class="vertical">

											<div id="spinner-ladder-sample2" class="v-spinner">
												<div class="active">Ladder 20</div>
											</div>
										</div>
										Your weekly quota will be:
										<div class="ladder_info icon_book">9 Lines</div>
										Your yearly quota will be:<br />
										<div class="ladder_info icon_book">471 Lines</div>

										By age 13 you will know:<br />
										<div class="ladder_info"><script>document.write(arrLadderJSON[20]["EndGoal"]);</script> Lines - Perek <script>document.write(arrLadderJSON[20]["Perek"]);</script></div>
									</div>
								</div>
							</div>
						</li>
						<li>
							<form method="POST" name="form01">
							<input type="hidden" name="ladder" value="0" />
							<input type="hidden" name="line_goal" value="0" />
							<div class="slider_title">Ladder Simulator</div>
							<div class="mainbox ladder">							
								<div class="ladder_box">
									<div class="ladder_info_box">
										If you upgrade to:
										<div class="vertical">
											<div id="spinner-ladder" class="v-spinner">
												__!User Ladder Option List!__
											</div>
										</div>
										Your yearly quota will be:<br />
										<div id="ladder_this_1" class="ladder_info icon_book"></div>
										Your weekly quota will be:<br />

										<div id="ladder_this_2" class="ladder_info icon_book"></div>
									</div>
								</div>
								<div class="ladder_box">
									<div class="ladder_info_box">
										By the time you are:
										<div class="vertical">
											<div id="spinner-year" class="v-spinner">
												__!Remaining Years List!__
											</div>
										</div>
										Your yearly quota will be:<br />
										<div id="ladder_year_1" class="ladder_info icon_book"></div>
										Your total will be:<br />
										<div id="ladder_year_2" class="ladder_info icon_book"></div>

									</div>

								</div>
								<div class="ladder_box_bottom">
									<div class="ladder_box">
										<nobr>
											<div class="icon icon_finish"></div>
											By age 13 you will know:
											<div class="ladder_text ladder_date">
												<span id="ladder_complete_lines"></span> Page: <span id="ladder_complete_page"></span> Perek: <span id="ladder_complete_perek"></span>

											</div>
										</nobr>
									</div>
								</div><br>&nbsp;<br>
								<div class="ladder_box_bottom">
									<input type="submit" value="Submit" />
								</div>
							</div>
							</form>
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
</div>      </div>
    </div>

