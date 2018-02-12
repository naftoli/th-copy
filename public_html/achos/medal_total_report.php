<?php
$admin_auth = array('school','user'); 
require('header.php'); 

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">


<HTML>

	<HEAD>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Medal Report - Tzivos Hashem Management System</title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
	</HEAD>

	<BODY>
		<? include('admin_header.php');?>
		<? if ($admin->auth == 'super') : ?>
		
		<?
		include_once('db.php');
		$sql = "
			SELECT su.subject_name, m.medal_name, COUNT( mm.medal_ord ) as total 
			FROM users AS u, subjects AS su, medals AS m, medal_marks AS mm
			WHERE m.medal_ord = mm.medal_ord
			AND u.user_id = mm.user_id
			AND su.subject_id = mm.subject_id 
			and u.user_registered > 0 
			GROUP BY su.subject_name, m.medal_name";
		
		$result = mysql_query($sql);
		?>

		<DIV class="body">
			<H1>Medals Report</H1>
			
			<TABLE class="pretty_grid">
				<THEAD>
					<TR>
						<TH>Campaign</TH>
						<TH>Medal</TH>
						<TH>Total</TH>
						<th>Future</th>
					</TR>
				</THEAD>
				
				<? while ($row = mysql_fetch_assoc($result)) { ?>
				<TR>
					<TD><?=$row['subject_name'];?></TD>
					<TD><?=$row['medal_name'];?></TD>
					<TD><?=$row['total'];?></TD>
					<?
					$ord = "select medal_ord from medals where medal_name = '" . $row['medal_name'] . "'";
					$res = mysql_query($ord);
					$row2 = mysql_fetch_row($res);
					$num = $row2[0];
					$sql2 = "select medal_ord, medal_name from medals where medal_ord > " . $num . " limit 2";
					$res2 = mysql_query($sql2);
					echo "<td>";
					while ($row3 = mysql_fetch_assoc($res2)) {
						echo $row3['medal_name'] . ",";
					}
					echo "</td>";
					?>
				</TR>
				<? } ?>				
			</TABLE>
		</DIV>
		<?
		$sql = "select * from subjects where subject_type = 'WWTC' or subject_type = ''";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$id = $row['subject_id'];
			$subject = $row['subject_name'];
			echo "Subject: " . $subject;
			$sql2 = "select * from users where user_registered > 0 and user_id not in ( select user_id from medal_marks where subject_id = $id )";
			$result2 = mysql_query($sql2);
			echo " children with no medals: " . mysql_num_rows($result2) . "<br />";
		}
		?>
<? else : ?>
no permission to view this page
<? endif; ?>
	</BODY>
	
</HTML>
