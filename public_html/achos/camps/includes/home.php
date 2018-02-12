<?php
include ("get_camp_id.php");
$camp_id = get_camp_id();

$sql = "SELECT count(*) AS unassigned_staff FROM admins WHERE camp_id=" . $camp_id . " AND staff_type_id IS NULL";
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$unassigned_staff = $row['unassigned_staff'];

$sql = "SELECT count(*) ";
$sql = $sql . "FROM users AS u ";
$sql = $sql . "LEFT JOIN member_groups AS mg ON (mg.user_id=u.user_id AND mg.end_date=0) ";
$sql = $sql . "WHERE u.camp_id=" . $camp_id . " AND camp_registered IS NOT NULL AND mg.member_group_id IS NULL";
$query = mysql_query($sql);
$total_unassigned = mysql_num_rows($query);
?>				
				<div class="slider">
				
                    <div class="col_title">
						<span>Welcome to the Hachayol Admin Dashboard</span>
					</div>
					
                    <div class="col_content">
					
                        <p>Please select an Menu option to begin.</p>
						
                        <div class="module" id="module-alerts">
						
                            <h1>Alerts</h1>
							
                            <div class="module_content">
                                <ul>
                                    <li>
                                        <a class="dismiss" href="#" title="Dismiss Alert">x</a>
                                        <span class="date">May 25</span>
                                        PLEASE NOTE: This page is best viewed in Firefox or Chrome.
                                    </li>
                                    <li>
                                        <a class="dismiss" href="#" title="Dismiss Alert">x</a>
                                        <span class="date">May 25</span>
                                        <a href="#">
											<?=$unassigned_staff;?> Staff Members
										</a> have not been assigned a group
                                    </li>
                                    <li>
                                        <a class="dismiss" href="#" title="Dismiss Alert">x</a>
                                        <span class="date">May 25</span>
                                        <a href="#">
											<?=$total_unassigned;?> Campers
										</a> have not been placed in a group.
                                    </li>
                                </ul>
                            </div>
							
                        </div>
						
                    </div>
					
                </div>