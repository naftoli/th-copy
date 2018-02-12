<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			@font-face {
				font-family: 'Sanchez';
				src('fonts/Sanchez-Black.otf');
			}
			.card {
				height: 2.13in;
				width: 3.38in;
			}
			.card span {
				width: 3.3in;
				font-size: 46px;
				font-weight: bold;
				font-family: 'Sanchez';
				margin-left: 90px;
			}
			.card .inner {
				padding: 10px;
				border-top: 5px solid #0033cc;
				margin-top: 10px;
			}
			.card .inner img {
				width: 75px;
				float: left;
			}
			.card .inner img.img1 {
				border: 5px solid #330033;
			}
			.card .inner .info {
				margin-left: 100px;
			}
			.info {
				font-family: 'Georgia';
				font-size: 14px;
				line-height: 1.3;
			}
			.info .name {
				font-weight: bold;
				font-size: 16px;
			}
			.info span {
				margin-left: 0;
			}
		</style>
	</head>
	
	<body>
		<? for ($i = 0; $i < 50; $i++) { ?>
			<div class="card">
				<span>STAFF</span>
				<div class="inner">
					<img src="Chidon Logo.PNG" />
					<div class="info">
					</div>
				</div>
			</div>
			<div style="page-break-after: always"></div>
			<div style="clear: both"></div>
		<? } ?>		
	</body>
</html>	