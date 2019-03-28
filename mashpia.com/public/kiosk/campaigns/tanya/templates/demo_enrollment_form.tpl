	<title>Hachayal Kiosk - Enrollment</title>
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
	<script type="text/javascript" src="./scripts/jquery.keypad.js"></script>
	<script type="text/javascript">
		$(function () {
		
			$('.keypad').keypad();
		
		});
	</script>
	<link href="./styles/jquery.keypad.css" rel="stylesheet" type="text/css" />
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
    <noscript>
    <p class="js_alert">Notice: You have javascript disabled.<br />
      Some parts of the site will not function without javascript.</p>
    </noscript>
  </div>
  <script>
	function validate_form() {
		if (!document.form01.chapter_offset.value.match(/^[0-9]{1,3}$/)) {
			document.form01.chapter_offset.value = 0;
		}
		return true;
	}
	</script>
  <div id="main">
    <div id="page_title">Tanya Baal Peh Campaign</div>
    <div class="three_column padding_top">
      <div class="content">
        <div id="slider">
          <ul class="overview">
            <li>
              <div class="slider_title">Tanya Enrollment</div>
              <div class="scroll-pane">
                <form method="POST" name="form01" onSubmit="return validate_form()">
                  <div class="mainbox clearfix">
                        <div class="question icon_book">
							<div class="input">
								<div class="vertical">
									<div id="spinner-ladder-week" class="v-spinner">
										__!User Ladder Option List!__
									</div>
								</div>
							</div>
                            <p>How many lines are you able to learn each week?
                        </div>
                        <div class="question icon_book">
							<div class="input"><input type="text" maxlength="3" class="keypad" name="chapter_offset" value="0" onFocus="this.select()"></div>
                            <p>How many lines of Tanya do you already know?
							<span class="mission_quota">Your weekly mission will begin from after the line you already know.</span></p>
                        </div>
                  </div>
                    <div class="button button_icons">
                      <div><a href="#" class="icon_enroll" onClick="document.form01.submit();">Enroll</a></div>
                    </div>
                </form>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <script>

		$(document).ready(function(){

			$("#spinner-ladder-week").spinner({

				start:1

			}).bind('spinchange', function(event, element, ui) {

				document.form01.ladder.value = ui.value+1;

			});

		});

	</script>
  <div id="footer">
    <div class="footer_logo"> </div>
    <div class="footer_logout"> </div>
  </div>
</div>
</body>
</html>