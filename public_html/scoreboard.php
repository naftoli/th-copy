<!doctype html>
<html>
	<head>
		<meta charset="UTF-8" />
		<style>
			@font-face {
				font-family: digital;
				src: url('fonts/Open 24 Display St.ttf');
			} 
			@font-face {
				font-family: computer;
				src: url('fonts/Computerfont.ttf');
			}
			.container {
				background-color: grey;
				width: 900px;
				height: 100px;
				border: 1px solid black;
				margin: auto;
			}
			.group {
				float: left;
				padding: 10px;
				padding-top: 16px;
				width: 200px;
				height: 100px;
			}
			.group:nth-child(1) {
				padding-left: 20px;
			}
			.name { 
				color: white;
				font-family: "sans-serif";
				font-size: 18px;
				text-align: center;
				font-family: "sans-serif";
			}
			.score {
				background-color: black;
				color: red;
				font-size: 36px;
				text-align: right;
				font-family: digital;
			}
		</style>
	</head>
	
	<body>
		<div class="container">
			<div class="group reg">
				<div class="name">Chayolim Registered</div>
				<div class="score"></div>
			</div>
			
			<div class="group medals">
				<div class="name">Medals Earned</div>
				<div class="score"></div>
			</div>
			
			<div class="group missions">
				<div class="name">Missions Accomplished</div>
				<div class="score"></div>
			</div>
			
			<div class="group tasks">
				<div class="name">Tasks Accomplished</div>
				<div class="score"></div>
			</div>
		</div>
	</body>
	
	<script src="jquery-1.8.1.min.js"></script>
	<script>
		$(function() {
			setInterval(getData(), 1000);
		});
		
		function getData() {
			$.get('ajax/getTest.php', function(data) {
				var info = $.parseJSON(data);
				var reg = info[0];
				var medals = info[1];
				var missions = info[2];
				var tasks = info[3];
				
				var regScore = $(".reg").find('.score');
				var medalScore = $(".medals").find('.score');
				var missionScore = $(".missions").find('.score');
				var taskScore = $(".tasks").find('.score');
				
				if ($(regScore).text().val() != '') {
					$(regScore).fadeOut('slow');
				}
				$(regScore).fadeIn('slow', function() {
					$(this).text(reg);
				});
			});
		}
	</script>
</html>