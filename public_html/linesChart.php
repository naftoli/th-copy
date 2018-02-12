<?
$pledges = array(
	'Pre1a'	=>	11, 
	'1'		=>	22,
	'2'		=>	44,
	'3'		=>	66,
	'4'		=>	77,
	'5'		=>	88,
	'6'		=>	100, 
	'7'		=>	113,
	'8'		=>	113
);
?>
<html>
	<head>
		<style>
			.wrapper {
				width: 80%;
				margin: 20px auto 40px auto;
			}
			
			.datatable {
				width: 100%;
				border: 1px solid #d6dde6;
				border-collapse: collapse;
			}
			
			.datatable td {
				border: 1px solid #d6dde6;
				padding: 0.3em;
				font-size: 14px;
			}
			
			.datatable th {
				border: 1px solid #828282;
				background-color: #bcbcbc;
				font-weight: bold;
				text-align: left;
				padding-left: 0.3em;
			}
			
			.datatable caption {
				font: bold 110% Arial, Helvetica, sans-serif;
				color: #33517a;
				text-align: left;
				padding: 0.4em 0 0.8em 0;
			}
			
			.datatable tr:nth-child(odd) {
				background-color: #dfe7f2;
				color: #000000;
			}
		</style>
	</head>
	<body>
		<div class="wrapper">
			<table class="datatable">
				<caption>Tanya / Mishna Lines</caption>
				<tr>
					<th>Grade</th>
					<th>Lines</th>
				</tr>
				<?
				foreach ($pledges as $grade => $lines) {
					echo "<tr><td>" . $grade . "</td><td>" . $lines . "</td></tr>";
				}
				?>
			</table>
		</div>
	</body>
</html>