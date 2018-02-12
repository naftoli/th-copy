<?php
$admin_auth = array('camp');
require('../header.php'); 

$img_name = $_GET['img_name'];
$division_id = $_GET['division_id'];
$division_name = $_GET['division_name'];

$sql = "SELECT u.first, u.last FROM member_divisions AS md JOIN users AS u USING (user_id) WHERE md.division_id=" . $division_id;
$query = mq($sql);
?>
<div class="slider">

	<div class="col_title">
		<span>
			<?=$division_name;?>
		</span>
		<a class="slider_back">
			<?=$division_name;?>
		</a>
	</div>
	
	<div class="col_content">
					
		<div id="lists-bunks" class="lists">
						
			<div class="content">
				<ul>
					
					<? while ($row = mysql_fetch_assoc($query)) :?>
					<li>
						<a href="#">
							<div class="image">
								<img height="32" width="32" alt="Bunks" src="images/<?=$img_name;?>.png">
							</div>
							<div class="name"><?=$row['first'];?> <?=$row['last'];?></div>
						</a>
					</li>
					<? endwhile; ?>
				</ul>
			</div>
		</div>
	</div>
</div>