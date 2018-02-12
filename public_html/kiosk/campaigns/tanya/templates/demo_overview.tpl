<title>Hachayal Kiosk - Overview</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=8" />
<link rel="stylesheet" type="text/css" href="./scripts/shadowbox/shadowbox.css">
<link href="./styles/reset.css" rel="stylesheet" type="text/css" />
<link href="./styles/style.css" rel="stylesheet" type="text/css" />
<link href="./styles/print.css" rel="stylesheet" type="text/css" media="print" />

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
	function sliderPage() {
		return false;
	}
</script>
<script type="text/javascript" src="./scripts/jquery.keypad.js"></script>
<link href="./styles/jquery.keypad.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="./scripts/jquery.scroll.js"></script>
<script type="text/javascript">

	

</script>
<link href="./styles/jquery.scroll.css" rel="stylesheet" type="text/css" />
<script>

	google.load("jqueryui", "1.7.2");

</script>
<script type="text/javascript" src="./scripts/jquery.ui.spinner.2009.js"></script>
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
	
	window.onload = function ()
	{

		$("#spinner-ladder-week").spinner({
		}).bind('spinchange', function(event, element, ui) {
			//document.form01.ladder.value = ui.value+1;
			$("#spinner-ladder-year").spinner('goTo',ui.value);
			intLadder1 = intLadder2 = arrUserLadderJSON[ui.value];
			proc_ladder_details();
		});
		$("#spinner-ladder-year").spinner({
		}).bind('spinchange', function(event, element, ui) {
			//document.form01.ladder.value = ui.value+1;
			$("#spinner-ladder-week").spinner('goTo',ui.value);
			intLadder1 = intLadder2 = arrUserLadderJSON[ui.value];
			proc_ladder_details();
		});

		
		

		/*
		$("#spinner-ladder-week, #spinner-ladder-year").spinner({
			start:1
		}).bind('spinchange', function(event, element, ui) {
			//document.form01.ladder.value = $("#spinner-ladder-week").val()+1;
			intLadder1 = intLadder2 = arrUserLadderJSON[$("#spinner-ladder-week").val()];
		});
		*/
		$('.keypad').keypad();
		$('.scroll-pane').jScrollPane({showArrows:true, scrollbarWidth: 42, arrowSize: 42});
		$("#chapter_offset").bind("change", function () {
			proc_ladder_details();
		});

		intLadder1 = intLadder2 = arrUserLadderJSON[$("#spinner-ladder-week").val()];
		intLineOffset = $("#chapter_offset").val();
		proc_ladder_details();
	};

	var arrLadderJSON = __!JSON Ladder!__;
	var intYearsRemaining = __!intYearsRemaining!__;
	var intCurrentLine = __!intCurrentLine!__;
	var intRemainingWeeks = __!intRemainingWeeks!__;
	var arrChapterJSON = __!Chapter JSON List!__;
	var arrPagesJSON = __!Page JSON List!__;
	var arrUserLadderJSON = __!User Ladder JSON!__;
	var intLineOffset = 0;
	var intLadder1 = 0;
	var intLadder2 = 0;

	function proc_ladder_details() {
		// Dynamics
		intLineOffset = $("#chapter_offset").val();
		// Text
		var intTotal = arrLadderJSON[intLadder1]["EndGoal"]*1 + intLineOffset*1;
		$("#ladder_complete_lines").html(intLineOffset);
		$("#ladder_complete_learning").html(Math.round(arrLadderJSON[intLadder1]["Line"] / 416 * 10) / 10);
		$('#ladder_complete_perek').html(chapter_from_line(intTotal));
		$('#ladder_complete_page').html(page_from_line(intTotal));
		$('#ladder_complete_total').html(intTotal);
		var strUrl = '__!post_url!__&mission_ajax=true&start_line=' + $('#chapter_offset').val() + '&lines_per_mission=' + (Math.round(arrLadderJSON[arrUserLadderJSON[$('#spinner-ladder-week').val()]]["Line"]/416*100)/100);
		//alert(strUrl);
		$.ajax({
			type : "POST",
			cache : false,
			start: $("#processing_status").html('Loading...').fadeIn("slow"),
			url : strUrl + '&medal=1',
			dataType : "text",
			data: $('#form01').serialize(),
			success : function(strResponse) {
				//alert(strResponse);
				$("#overview_medal_1").html(strResponse);
			}
		});

	}
	
	function chapter_from_line(intLine)
	{
		for (var intItr=0;intItr!=arrChapterJSON.length;intItr++)
		{
			if (arrChapterJSON[intItr]["line"] >= intLine)
				return arrChapterJSON[intItr]["name"];
		}
	}
	
	function page_from_line(intLine)
	{
		for (var intItr=0;intItr!=arrPagesJSON.length;intItr++)
		{
			if (arrPagesJSON[intItr]["line"] >= intLine)
				return arrPagesJSON[intItr]["page"];
		}
	}
	
	function validate_form() {
		$.ajax({
			type : "POST",
			cache : false,
			start: $("#processing_status").html('Loading...').fadeIn("slow"),
			url : '__!post_url!__&enroll=true&line_goal=' + $('#ladder_complete_total').html(),
			dataType : "text",
			data: $('#form01').serialize(),
			success : function(strResponse) {
				//alert(strResponse);
				
				if (strResponse == "1")
				{
					$("#processing_status").html('Please wait...');
					$.ajax({
						type : "GET",
						cache : false,
						start: $("#processing_status").html('Loading...').fadeIn("slow"),
						url : 'http://mashpia.com/kiosk/camp_enroll_4.php?subject=%D7%AA%D7%A0%D7%99%D7%90%20%D7%91%D7%A2%D7%9C%20%D7%A4%D7%94%20Bonus',
						dataType : "text",
						success : function(strResponse) {
							window.location.href = "__!BASE_URI!__";
						}
					});
				}
				else
				{
					alert("Sorry, there was an error: KCTT-TO103-54456S");
				}
			}
		});
	}
</script>
<link href="./styles/jquery.spinner.css" rel="stylesheet" type="text/css" />
<style>
<!--
.iconl_finish {
	background-image:url("images/iconl_finish.png");
}
.iconl_quota {
	background-image:url("images/iconl_quota.png");
}
.iconl_brain {
	background-image:url("images/iconl_brain.png");
}
.ladder_box_full {
	margin-top:-130px;
}
.ladder_box_full icon_finish {
	position:absolute;
}
.ladder_box_full p {
	margin-bottom:12px !important;
	line-height:1.4;
}
.overview .ladder .ladder_box_full .ladder_box {
	width:auto;
	float:none;
}
-->
</style>
</head>

<body class="blue">
<form method="POST" name="form01" id="form01">
<div id="wrapper">
	<div id="header">
		<div class="org">
			<div class="nav">
				<ul>
					<li class="icon_back"><a href="camp_goal.php?subject=תניא בעל פה&i=2b3ec3fbc6ff09833c2172bd040487ce">Back</a></li>
					<li class="icon_home"><a href="../statement.php">Home</a></li>
					<li class="icon_logout"><a href="../logout.php?n=statement.php">Logout</a></li>
				</ul>
			</div>
			__!TanyaTitle!__
		<noscript>
		<p class="js_alert">Notice: You have javascript disabled.<br />
			Some parts of the site will not function without javascript.</p>
		</noscript>
	</div>
	<div id="main">
		<div id="page_title">Tanya Baal Peh Campaign</div>
		<div class="three_column padding_top">
			<div class="content">
				<div id="slider">
					<ul class="overview">
						<li>
							<div class="slider_title">Welcome to Tanya Baal Peh</div>
							<div class="mainbox ladder clearfix">
								<div class="col2_image iconl_finish"></div>
								<div class="scroll-pane">

									<div class="col2_text">
										<p>Welcome to the Tanya Baal Peh Campaign.</p>
										<div class="question">
											<div class="col2_list">
												<div>Your goal is to learn as much Tanya by heart as possible by your Bar/Bas Mitzvah.</div>
												<div>You can achieve this goal by memorizing small weekly &quot;quotas&quot; of Tanya.</div>

											</div>
										</div>
										<p>Next: Choose the amount of Tanya you can learn each week.</p>
									</div>
								</div>
							</div>
						</li>
						<li>
							<div class="slider_title">Choose Your Weekly Quota</div>

							<div class="mainbox ladder clearfix">
								<div class="col2_image iconl_quota"></div>
								<div class="scroll-pane">
									<div class="col2_text">
										<p class="small">Choose the amount of Tanya you can learn by heart each week. This will be your weekly mission.</p>
										<div class="ladder_box">
											<p>I am able to learn</p>
											<!--
											<div class="vertical">
												<div id="spinner-ladder-week" class="v-spinner">
													__!User Ladder Option List!__
												</div>
											</div>
											-->
											<select id="spinner-ladder-week" name="spinner-ladder-week">
												__!User Ladder Option ListHTML!__
											</select>
											<p>new lines by heart each week.</p>
										</div>

										<div class="clear"></div>
										<p class="small"><br/>Tip: Start with a smaller quota. You can always upgrade!</p>
									</div>
								</div>
							</div>
						</li>
						<li>
							<div class="slider_title">Choose a Starting Point</div>

							<div class="mainbox ladder clearfix">
								<div class="col2_image iconl_brain"></div>
								<div class="scroll-pane">
									<div class="col2_text">
										<p>How many lines of Tanya do you already know by heart?</p>
										<p class="small">Your first Tanya mission will begin after those lines.</p>
										<p class="small">Note: Each time you are tested, you will begin from &ldquo;Perek Alef: Tanya&hellip;&rdquo;. Only enter lines you know very well.</p>

										<div class="question icon_book">
											<div class="input">
												<input type="text" maxlength="3" class="keypad" name="chapter_offset" id="chapter_offset" value="0" onFocus="this.select()">
											</div>
											<p>I already know:</p>
										</div>
										<p class="small">&nbsp;</p>
									</div>

								</div>
								<div class="ladder_box_full">
									<div class="ladder_box">
										<div class="icon icon_finish"></div>
										<p>
											<span class="ladder_text">Wow! You already know </span>
											<span id="ladder_complete_lines">10</span>
											<span class="ladder_text">lines and can learn</span>
											<span id="ladder_complete_learning">1.01</span>
											<span class="ladder_text">lines each week.</span>
										</p>

										<p>
											<span class="ladder_text">By your Bar Mitzvah you will know<br /></span>
											<span id="ladder_complete_perek"></span>
											Perokim,
											<span id="ladder_complete_page">25</span>
											pages,
											<span id="ladder_complete_total">2541</span>
											lines
											<span class="ladder_text"><br />of Tanya by heart.</span></p></div>
								</div>
							</div>
						</li>
						<li>
							<div class="slider_title">Complete a Mission</div>
							<div class="mainbox ladder clearfix">
								<div class="col2_image iconl_taskslist"></div>
								<div class="scroll-pane">
									<div class="col2_text">
										<p>When you are ready, your commander will test you on all the lines you know. </p>

										<p class="small">Your commander will enter the lines into your account and your mission will be marked completed.</p>
										<p>NOTE: You will always be tested from &ldquo;Perek Alef: Tanya&hellip;&rdquo;</p>
									</div>
								</div>
							</div>
						</li>
						<li>
							<div class="slider_title">Earn Your First Medal</div>
							<div class="mainbox">
								<p>You will earn your first Tanya medal after just 15 completed missions!</p>
								<p>Here's your schedule:</p>
								<div class="task_items_box small" id="overview_medal_1">
									Sorry, you are viewing this because there was an error with our javascript: KCTT-TO101-SD3F21.
								</div>
								<div class="math_big"></div>
								<div class="medalImage medal121"><span class="badge">15</span></div>
							</div>
						</li>
						<li>
							<div class="slider_title">Earn More Medals</div>
							<div class="mainbox">
								<div class="col2_image iconl_medals"></div>
								<div class="scroll-pane">
									<div class="col2_text">
										<p>You can earn up to __!medal_count!__ medals<br />
											for Tanya Baal Peh!</p>

										<div class="medals">
											__!earn_medals!__
										</div>

										<p class="small"><img src="images/page_number.png" style="margin-bottom:-10px;"/>= The blue circles show the number of completed missions needed to earn each medal. <!--Click icons to see the full schedule.--></p>
									</div>
								</div>
							</div>
						</li>
						<li>
							<div class="slider_title">Earn Miles</div>
							<div class="mainbox">

								<div class="col2_image iconl_mileage"></div>
								<div class="scroll-pane">
									<div class="col2_text">
										<p>You can earn nearly 400 miles a year for the Tanya Baal Peh Campaign!</p>
										<p class="small">Miles may be used in the online prize store and Global Chinese Auction.</p>
										<div class="task_items mission_boxes task_row">
											<div class="mission">
												<div class="number">#14</div>

												<div class="date">כ"ט שבט</div>
												<div class="date">Lines 1-5</div>
												<div class="check_on"></div>
											</div>
											<div class="math_big">=</div>
											<div class="mission">
												<div class="miles">2 Miles</div>

											</div>
											<div class="clear"></div>
										</div>
										<p>Earn 2 miles for each mission that you complete.</p>
									</div>
								</div>
							</div>
						</li>
						<li>
							<div class="slider_title">Earn Bonus Miles</div>
							<div class="mainbox">
								<div class="col2_image iconl_mileage"></div>
								<div class="scroll-pane">
									<div class="col2_text">
										<div class="question icon_book">Earn a ½ mile each day that you learn Tanya Baal Peh for 5 minutes.</div>
										<div class="question icon_book">Earn 2 miles each week for reviewing all the Tanya Baal Peh that you know.</div>

									</div>
								</div>
							</div>
						</li>
						<li>
							<div class="slider_title">Enroll</div>
							<div class="mainbox">
								<div class="col2_image iconl_enroll"></div>

								<div class="scroll-pane">
									<div class="col2_text">
										<p>Now you have seen how the Tanya Baal Peh Campaign works. Are you ready to join?</p>
										<p>I would like to join the Tanya Baal Peh campaign!</p>
										<div class="button button_icons">
											<div> <a onclick="validate_form();" class="icon_enroll" href="#">Enroll</a> </div>

										</div>
									</div>
								</div>
							</div>
						</li>
					</ul>
					</form>
				</div>
			</div>
		</div>
	</div>
	<script>
	/*
		$(document).ready(function(){
			$(".spinner").spinner({min:0,start:4});
			
			$("#spinner-ladder-week").spinner({
			}).bind('spinchange', function(event, element, ui) {
				//document.form01.ladder.value = ui.value+1;
				$("#spinner-ladder-year").spinner('goTo',ui.value);
			});
			
			$("#spinner-ladder-year").spinner({
			}).bind('spinchange', function(event, element, ui) {
				//document.form01.ladder.value = ui.value+1;
				$("#spinner-ladder-week").spinner('goTo',ui.value);
			});
		});
		 */
	</script>

	<div id="footer">
		<div class="footer_logo"> </div>
		<div class="footer_logout"> </div>
	</div>
</div>
<div id="processing_status"></div>
</form>
</body>
</html>

</body>
</html>
