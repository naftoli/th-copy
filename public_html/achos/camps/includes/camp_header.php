<?php
$camp_id = $_GET['camp_id'];
$user_id = $_GET['user_id'];
$sql = "SELECT u.*, r.rank_name, c.camp_number, c.camp_name, c.camp_logo_id ";
$sql = $sql . "FROM users AS u ";
$sql = $sql . "JOIN rank_marks AS rm USING (user_id) ";
$sql = $sql . "JOIN ranks AS r USING (rank_ord) ";
$sql = $sql . "JOIN camps AS c USING (camp_id) ";
$sql = $sql . "WHERE user_id=" . $user_id;
$user = mysql_fetch_assoc((mysql_query($sql)));
?>

			<div id="header">
			
				<div class="org">
				
					<div class="nav">
						<ul>
							<li class="icon_back"><a onclick="javascript:history.back(); return false" href="#">Back</a></li>
							<li class="icon_home"><a href="../statement.php">Home</a></li>
							<li class="icon_logout"><a href="../logout.php?n=kiosk.php">Logout</a></li>
						</ul>
					</div>					
					
					<div class="org_photo">
						<img src="file_view.php?id=<?=$user['camp_logo_id'];?>" width="100" height="100" alt="My Shliach">
					</div>
					
					Base: #<?=$user['camp_number'];?><br>
					<?=$user['camp_name'];?><br>
					<?=$user['rank_name'];?> <?=$user['first'];?> <?=$user['last'];?>
					
				</div>
				
				<noscript>				
					<p class="js_alert">Notice: You have javascript disabled.<br />Some parts of the site will not function without javascript.</p>
				</noscript>
				
			</div> <!-- HEADER -->
		
