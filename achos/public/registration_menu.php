<? 
$page_name = substr($_SERVER["REQUEST_URI"], 1, strlen($_SERVER["REQUEST_URI"]) - 1);
if ( $pos = strpos($page_name, '?') ) {
    $page_name = substr($page_name, 0, $pos);
}
?>

				<ul class="list_first">
					<? if ($page_name == "registration.php") : ?>
					<li class="list_parent current"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Setup Director</a></li>
					<? else : ?>
					<li class="list_parent"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Setup Director</a></li>					
					<? endif; ?>
					
					<? if ($page_name == "registration_2.php") : ?>
					<li class="list_parent current"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Setup School</a></li>					
					<? else : ?>
					<li class="list_parent"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Setup School</a></li>
					<? endif; ?>
					
					<!--
					<? if ($page_name == "registration_3.php") : ?>
					<li class="list_parent current"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Setup Program Director</a></li>					
					<? else : ?>
					<li class="list_parent"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Setup Program Director</a></li>
					<? endif; ?>
					
					
					<? if ($page_name == "registration_4.php") : ?>
					<li class="list_parent current"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Setup Program</a></li>					
					<? else : ?>
					<li class="list_parent"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Setup Program</a></li>
					<? endif; ?>
					-->
					
					<? if ($page_name == "registration_4.php") : ?>
					<li class="list_parent current"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Benefits & Fees</a></li>					
					<? else : ?>
					<li class="list_parent"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Benefits & Fees</a></li>
					<? endif; ?>
					
					<? if ($page_name == "registration_6.php") : ?>
					<li class="list_parent current"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Setup Shipping</a></li>					
					<? else : ?>
					<li class="list_parent"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Setup Shipping</a></li>
					<? endif; ?>
					
					<? if ($page_name == "registration_7.php") : ?>
					<li class="list_parent current"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Setup Billing</a></li>										
					<? else : ?>
					<li class="list_parent"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Setup Billing</a></li>					
					<? endif; ?>

					<? if ($page_name == "registration_8.php") : ?>
					<li class="list_parent current"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Summary</a></li>					
					<? else : ?>
					<li class="list_parent"><a href="#"><img src="images/icon_door_in.png" width="28" height="28" alt="Login" />Summary</a></li>
					<? endif; ?>
					
					<li class="list_parent">
						<a href="logout.php">
							<img src="images/icon_door_in.png" width="28" height="28" alt="Logout" />
							Logout
						</a>
					</li>
				</ul>
