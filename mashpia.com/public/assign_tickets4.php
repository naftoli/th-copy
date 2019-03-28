<? 
ini_set('max_execution_time', 300);
$admin_auth = array('school','user');

require('header.php');

$users = array();
$auction_id = 70;
$school_id = $_GET['school'];

$sql = "select * from auctions where auction_id = " . $auction_id;
$result = mysql_query($sql);
$auction = mysql_fetch_assoc($result);

$prizes = array();
$sql = "select prize_id, prize_points, prize_name, prize_image_id from auction_prizes ap 
        join prizes_auction pa using (prize_id) 
        where ap.auction_id = " . $auction_id . " 
        order by prize_points, prize_name";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $prizes[] = $row;
}
    
$sql = "select user_id, first, last from users u 
        join classes c using (class_id) 
        where u.school_id = " . $school_id . "
        and u.user_registered > 0
        order by c.class_grade, c.class_sub, u.last, u.first";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $user_id = $row['user_id'];
	$userName = $row['first'] . ' ' . $row['last'];
	$auction_points = auctionPoints($user_id, $auction);
    
    $users[] = array(
        'name'      => $userName,
        'points'    => floor($auction_points["cur"])
    );
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Assign Tickets - Tzivos Hashem Management System</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
		<style>
			.outer {
				page-break-after: always;
                /*height: 59in;*/
			}
			.prize {
				font-size: 12px;
				padding: 10px;
				display: inline-block;
                width: 95px;
                height: auto;
                vertical-align: top;
			}
			.prizePoints {
				color: red;
			}
            .prizePoints span {
                float: right;
            }
			.prizeName {
				font-size: 10px;
			}
            .prizeImg {
				width: 100px;
			}
			.prizeImg img {
				width: 100px;
				max-height: 120px;
			}
			.prizeQty input {
				width: 20px;
			}
			@media print {
				.noPrint {
					display: none;
				}
			}
			@media screen {
				.noShow {
					display: none;
				}
			}
		</style>
    </head>

    <body>
            
        <? include('admin_header.php'); ?>
        
        <div class="body left marking_missions">
                        
            <H1>Assign Tickets for Raffle</H1>
            
            <div id="tickets">    
                <h2>Choose Prizes for Auction</h2>
                <p class='noPrint' align='center'><input type='submit' value='Print' onclick='window.print()' />
                <!--<input type='submit' class='save' value='Save' /></p>-->
				<?php
				foreach ($users as $id => $user) {
					$name = $user['name'];
					$points = $user['points'];
					?>
						<div class="outer">
						<p class='noShow'>Tzivos Hashem Hakhel Auction 5776</p>
						<p><?=$points?> Points available for <?=$name?></p>
						<?
						foreach ($prizes as $key => $prize) {
							echo "<div class='prize' id='" . $prize['prize_id'] . "'><div class='prizePoints'>" . $prize['prize_points'];
							echo " Point Prize <span class='prizeQty'><input type='text' /></span></div>";
							echo "<div class='prizeName'>" . $prize['prize_name'] . "</div>";
                            echo "<div class='prizeImg'><img src='file_view.php?id=" . $prize['prize_image_id'] . "' /></div></div>";
						}
						?>
						<div style='clear: both'></div><br />
						<p class='noShow'><?=$points?> Points available for <?=$name?></p>
						<p class='noShow'>Tzivos Hashem Hakhel Auction 5776</p>
						</div>
				<? } ?>
            </div>
                
		</div>        	
	</body>
</html>
