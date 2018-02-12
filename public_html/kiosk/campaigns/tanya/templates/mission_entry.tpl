	<title>Hachayal Kiosk - Mission Entry</title>
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
<script type="text/javascript" src="./scripts/jquery.checkbox.js"></script>
<script type="text/javascript">
$(function () {
	  $('input[type=checkbox]').checkbox();
});
</script>
<link href="./styles/jquery.checkbox.css" rel="stylesheet" type="text/css" />

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
            <div id="page_title">Mission Entry </div>
            <div class="three_column padding_top">
              <div class="content">
<form method="post" name="form01" />
	<input type="hidden" name="mission_entry" value="true" />
	<div id="slider">
		<ul class="report">
			__!Mission Pages!__
		</ul>
	</div>
</form>
              </div>
            </div>
        </div>
        <div id="footer">
			<div class="footer_logo">
</div>

<div class="footer_logout">
</div>      </div>
    </div>
</body>


</html>