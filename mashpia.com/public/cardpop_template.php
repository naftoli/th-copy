<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="chrome=1">
		<title>Tzivos Hashem Kiosk</title>

		<script type="text/javascript">
		$(document).ready(function()
		{
			$('input[type=checkbox]').checkbox();
			$('.scroll-pane').jScrollPane({showArrows:true, scrollbarWidth: 42, arrowSize: 42});
		});
		</script>
		 
		<script type="text/javascript" src="http://mashpia.com/v2/js/back-end/admin/jquery2.js"></script>
		
		<link href="http://mashpia.com/v2/css/back-end/kiosk/reset.css" rel="stylesheet" type="text/css" />
		<link href="http://mashpia.com/v2/css/back-end/kiosk/style.css" rel="stylesheet" type="text/css" />
		<link href="http://mashpia.com/v2/css/back-end/kiosk/print.css" rel="stylesheet" type="text/css" media="print" />

		<!--[if IE]>
		<link href="http://mashpia.com/v2/css/back-end/kiosk/style_ie.css" rel="stylesheet" type="text/css" />
		<![endif]-->
		
        <style type="text/css">
			.fronts, .backs {
				margin:-.05in .125in;
				page-break-after: always;
			}

			.fronts td, .backs td {
				border: 1px dashed black;
				-webkit-box-sizing: border-box;
				-moz-box-sizing: border-box;
				vertical-align: middle;
				height: 2in;
				width: 3.5in;
			}

			.fronts td td, .backs td td {
				width: auto;
				height: auto;
				border: none;
			}
			.cards {
				width:672px;
				page-break-after:always;
				margin:-0.1875in auto 0;
				font-family:'Myriad Pro',Arial,sans-serif;
			}
			.cards .card {
				width:3.5in;
				height:2in;
				text-align:center;
	
				width:306px;
				height:162px;
				padding:15px;
				float:left;
				
				background:#fff;
				color:#000;
				text-shadow:none;
			}
			.cards .card .container {
				border:2px solid #000;
				border-radius:10px;
				-moz-border-radius:10px;
				height:100%;
			}
	
			.cards .logo {
				float:left;
				margin-left:30px;
				height:60px;
			}
			.cards .institution {
				float:right;
				margin-right:30px;
			}
			.cards .logo img, .cards .institution img {
				margin:5px 0;
				width:55px;
				height:55px;
			}
			.cards .clear {
				clear:both;
			}
	
			.cards .title img {
				width:250px;
				height:34px;
			}
			
			.cards .campaign {
				clear:both;
				font-weight:bold;
				position:absolute;
				width:302px;
				font-size:14px;
			}
	
			.cards .name {
				font-size:12px;
				margin:6px 0;
				height:15px;
				font-weight:normal;
			}
	
			.cards .barcode {
				font-size:9px;
			}
	
			.cards .points {
				font-size:12px;
				width:50px;
				font-weight:bold;
				border:2px solid #000000;
				border-radius:7px;
				-moz-border-radius:7px;
				padding:3px;
				margin:-55px auto 10px;
			}
			
			
			.card_box {
				width:336px;
				padding:0 18px;
				background-position:-1px -10px;
			}
			.cards {
				width:auto;
				margin:0;
			}
			.cards .card {
				background:#fff;
				color:#000;
				text-shadow:none;
			}
			.cards .title {
				padding:0;
			}
	
	
	        @media print {
	          	.fronts td, .backs td {
	           		border: none;
	          	}
				hr {
					display: none;
				}
	        }

		</style>
	</head>

	<body class="green cardpop">
		<div id="scancardbox" style="position:absolute;z-index:-100;filter:alpha(opacity=0);opacity:0;">
			<form method="POST" name="scancard" onSubmit="return parent.loadShadow(this.scantext)">
				<input name="scantext" id="scantext" type="text" autocomplete="off" />
			</form>
		</div>
		<script type="text/javascript">
			window.onload = scanFocus;
			window.onkeydown = scanFocus;
			function scanFocus() {
				document.scancard.scantext.focus();
			}
			scanFocus();
		</script>
		<div id="wrapper">
			<div class="padding_left padding_top">
				<div id="close_pop"><a onClick="window.parent.Shadowbox.close()">Close</a></div>
				<div id="page_title"><?php print $_GET["title"]; ?></div>
				<div style="text-align:center; display:block;padding:10px;">
					<span style="font-family: Times New Roman sans-serif; font-size:20px; color: #fff;"><?php print $_GET["msg"]; ?></span>
				</div>
		  	</div>
		</div>
	</body>

</HTML>

