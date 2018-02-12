<?php
$admin_auth = array('camp');
require('../header.php'); 

$img_name = $_GET['img_name'];
$group_id = $_GET['group_id'];
$group_name = $_GET['group_name'];

$sql = "SELECT * FROM divisions WHERE group_id=" . $group_id;
$query = mq($sql);
?>
<div class="slider">

	<div class="col_title">
		<span>
			<?=$group_name;?>
		</span>
		<a class="slider_back">
			<?=$group_name;?>
		</a>
	</div>
	
	<div class="col_content">
					
		<div id="lists-bunks" class="lists">
						
			<div class="content">
				<ul>
					
					<? while ($row = mysql_fetch_assoc($query)) :?>
					<li>
						<a href="division_members.php?img_name=<?=$img_name;?>&division_id=<?=$row['division_id'];?>&division_name=<?=$row['division_name'];?>">
							<div class="image">
								<img height="32" width="32" alt="Bunks" src="images/<?=$img_name;?>.png">
							</div>
							<div class="name"><?=$row['division_name'];?></div>
						</a>
					</li>
					<? endwhile; ?>
				</ul>
			</div>
		</div>
	</div>
</div>