<?php
$admin_auth = array('camp');
require('../header.php'); 

?>

<div class="slider">

	<div class="col_title">
		<span><?=$group_type_name;?></span>
		<a class="slider_back">Dashboard</a>
	</div>
	
	<div class="col_content">
					
		<div id="lists-bunks" class="lists">
						
			<div class="content">
				<ul>
					
					<? while ($row = mysql_fetch_assoc($query)) :?>
					<li>
						<a href="divisions.php?img_name=<?=$group_type_name;?>&group_id=<?=$row['group_id'];?>&group_name=<?=$row['group_name'];?>">
							<div class="image">
								<img height="32" width="32" alt="Bunks" src="images/<?=$group_type_name;?>.png">
							</div>
							<div class="name"><?=$row['group_name'];?></div>
						</a>
					</li>
					<? endwhile; ?>
				</ul>
			</div>
		</div>
	</div>
</div>