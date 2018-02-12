<html>
	<head>
		<meta charset="UTF-8" />
	</head>
	
	<body>
		<pre>
			<?
			require_once 'db.php';
			require_once 'class.missionMarks.php';
			$mm = new MissionMarks(394, 0, 2456970, 2456977);
			$mm->checkMissionCompletion();
			$info = $mm->getDebugInfo();
			echo $info;
			?>
		</pre>
	</body>
</html>