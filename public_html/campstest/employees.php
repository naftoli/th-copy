<?php
$admin_auth = array('camp');
require('../header.php'); 

$camp_id = gri('camp_id', -1);
$sql = "SELECT a.first, a.last FROM admin_auths AS aa JOIN admins AS a USING (admin_id) WHERE aa.auth='camp' AND aa.id=" . $camp_id;
$query = mq($sql);
?>
<div class="slider">

	<div class="col_title">
		<span><?=T_('Staff');?></span>
		<a class="slider_back">Dashboard</a>
	</div>
	
	<div class="col_content">
					
		<div id="lists-bunks" class="lists">
						
			<div class="content">

					<table class="module">
						<thead>
							<tr>
								<th>Staff</th>
							</tr>
						</thead>
						
						<tr>
							<td><li>George Calder</li></td>
						<tr>
						
						
					<? //while ($row = mysql_fetch_assoc($query)) :?>
					<!--<li>
						<a href="divisions.php?img_name=<?//=$group_type_name;?>&group_id=<?//=$row['group_id'];?>&group_name=<?//=$row['group_name'];?>">
							<div class="image">
								<img height="32" width="32" alt="Bunks" src="images/<?//=$group_type_name;?>.png">
							</div>
							<div class="name"><?//=$row['first'];?> <?//=$row['last'];?></div>
						</a>
					</li>-->
					<? //endwhile; ?>
					
					</table>
				
			</div>
		</div>
	</div>
</div>