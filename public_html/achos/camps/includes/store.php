<?php 
include ($_SERVER['DOCUMENT_ROOT'] . "/lang.php");

include ("get_camp_id.php");
$camp_id = get_camp_id();

$sql = "SELECT * FROM prizes_camp WHERE camp_id=" . $camp_id;
$query = mysql_query($sql);
$no_of_prizes = mysql_num_rows($query);

$sql = "SELECT COUNT(IF(sp.voucher_id > 0,1,0)) AS withdrawn, ";
$sql = $sql . "COUNT(IF(sp.scan_date > 0,1,0)) AS scanned, ";
$sql = $sql . "COUNT(*) AS total_purchases ";
$sql = $sql . "FROM store_purchases AS sp ";
$sql = $sql . "JOIN users AS u USING (user_id) ";
$sql = $sql . "WHERE u.camp_id=" . $camp_id;
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$withdrawn = $row['withdrawn'];
$scanned = $row['scanned'];
$total_purchases = $row['total_purchases'];
?>

	<div class="slider">
	
		<div class="col_title">
			<span><?=T_("Manage Store");?></span>
		</div>
		
		<div class="col_content">
		
			<div id="module-info" class="module list_prizes">
																		
				<div class="module_content">
				
					<h1><?=T_("Store Stats");?></h1>
					<ul class="stats">
						<li><?=T_("Activated Prizes");?><span><?=$no_of_prizes;?></span></li>
						<li><?=T_("Prizes - Registered");?><span>41</span></li>
						<li><?=T_("Campers - Non-Registered");?><span>12</span></li>
					</ul>
					<ul class="stats">
						<li><?=T_("Vouchers - Printed");?><span><?=$withdrawn;?></span></li>
						<li><?=T_("Vouchers - Registered");?><span><?=$scanned;?></span></li>
						<li><?=T_("Vouchers - Non-Registered");?><span><?=($total_purchases - $scanned);?></span></li>
					</ul>
					<div class="clear"></div>
					
				</div>					
					
			</div>
																							
					
			<div id="lists" class="module lists forms">
			
				<div class="module_content">
				
					<ul>
					
						<li>
							<a class="link" href="content.php?output=storescan">
								<div class="icon"></div>
								<div class="title"><?=T_("Scan Voucher");?></div>
							</a>
						</li>
						
						<li>
							<a class="link" href="content.php?output=storeprint">
								<div class="icon"></div>
								<div class="title"><?=T_("Print and Cash");?></div>
							</a>
						</li>
						
						<li>
							<a class="link" href="content.php?output=manage_prizes">
								<div class="icon"></div>
								<div class="title"><?=T_("Manage Prizes");?></div>
							</a>
						</li>
						
						<li>
							<a class="link" href="content.php?output=gettingstarted6">
								<div class="icon"></div>
								<div class="title"><?=T_("Install Prizes");?></div>
							</a>
						</li>
						
						
					</ul>
					
				</div>
		
			</div> <!-- <div class="col_content"> -->

		</div> <!-- <div class="slider"> -->