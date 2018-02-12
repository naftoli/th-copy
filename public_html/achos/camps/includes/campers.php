<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();
?>

			<div class="slider">
			
				<div class="col_title">
					<span>Manage Campers</span>
				</div>
				
				<div class="col_content">
				
					<? include ("campers_header.php"); ?>
					
					<div class="module lists" id="lists-campers">
						<div class="module_content">
							<ul>
								<li>
									<a class="link" href="content.php?output=campers_list">
										<div class="icon"></div>
										<div class="name">All Campers</div>
									</a>
								</li>
								<li>
									<a class="link" href="content.php?output=grouptypes">
										<div class="icon"></div>
										<div class="name">Camper Groups</div>
									</a>
								</li>
								<li>
									<a class="link" href="content.php?output=campers_list&view=unassigned">
										<div class="icon"></div>
										<div class="name">Unassigned Campers</div>
									</a>
								</li>
							</ul>
						</div>
					</div>
					<div class="module lists" id="lists-campers">
						<div class="module_content">
							<ul>
								<li>
									<a class="link" href="content.php?output=camperadd">
										<div class="icon"></div>
										<div class="name">Add a Camper</div>
									</a>
								</li>
								<li>
									<a class="link" href="content.php?output=camperbulk">
										<div class="icon"></div>
										<div class="name">Upload Camper List</div>
									</a>
								</li>
								<li>
									<a class="link" href="content.php?output=campers_register">
										<div class="icon"></div>
										<div class="name">Register Campers</div>
									</a>
								</li>
							</ul>
						</div>
					</div>
  				</div>
			</div>
