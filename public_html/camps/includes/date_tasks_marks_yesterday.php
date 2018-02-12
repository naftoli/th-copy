<?php
include("db.php");
$today = gregoriantojd((int)date("m"), (int)date("j"), (int)date("Y"));
$today = $today - 1;
$sql = "SELECT * FROM date_tasks_marks WHERE mark_date=" . $today;
$query = mysql_query($sql);

$greg_date = jdtogregorian($today);
echo "TODAY:$greg_date<br />";
?> 
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" dir="<?=$dir?>">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
		</script>
	</head>

	<body>
		<?
			$cntr = 0;
			while ($row = mysql_fetch_assoc($query)) {
				$cntr++;
				echo $cntr . ") " . $row["date_task_id"] . "<br />";
			}
		?>
	</body>
	
</html>