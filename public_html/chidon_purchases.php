<?php 
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Chidon Ticket Purchases Report</title>
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
        <script type="text/javascript">
            $( function() {
                $(".shipped").click( function() {
                    var id = $(this).val();
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                        id : id, 
                        checked : checked, 
                        field : 'shipped', 
                        table : 'chidon', 
                        key : 'id'
                        }, function(data) {
                        if (data && checked) {
                            var d = new Date();
                            var n = d.toDateString();
                            $(e).after('<span><br />' + n + '</span>');
                        } else if (data && !checked){
                            $(e).next('span').remove();
                        }
                    });
                });
                $(".prepared").click( function() {
                	var checked = ($(this).is(":checked") ? 1 : 0);
                	var id = $(this).val();
                	$.post('ajax/updatePrepared.php', { id : id, checked : checked });
                });
            });
        </script>
        <!--<link href="admin_styles.css" rel="stylesheet" type="text/css">-->
        <style type='text/css'>
            table {
                font-size: 12px;
				font-family: sans-serif;
            }
            th, td {
                padding: 3px 10px;
            }
            td {
            	vertical-align: top;
            }
            .newPage {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <? //include('admin_header.php'); ?>
        <h1>Chidon Ticket Purchases Report</h1>
        
        <?
        $year = 5777;
        $purchases = array();
        //$sql = "select * from chidon where year = $year and (mqty > 0 or gqty > 0 or ggqty > 0) order by name";
        $sql = "select * from chidon where year = $year and (mqty > 0 or gqty > 0 or ggqty > 0) order by name";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$purchases[]  = $row;
		}
		
		if (empty($purchases)) {
			echo "There were no purchases made.";
		} else {
			$ticketTypes = array(
				'Men $10'	=>	'm10',
				'Men $18'	=>	'm18',
				'Men $36'	=>	'm36',
				'Men $50'	=>	'm50',
				'Men $100'	=>	'm100',
				'Women $10 (Boys Chidon)'	=>	'g10',
				'Women $18 (Boys Chidon)'	=>	'g18',
				'Women $10 (Girls Chidon)'	=>	'gg10',
				'Women $18 (Girls Chidon)'	=>	'gg18',
				'Women $36 (Girls Chidon)'	=>	'gg36',
				'Women $50 (Girls Chidon)'	=>	'gg50',
				'Women $100 (Girls Chidon)'	=>	'gg100',
			);
			$totalTypes = array();
			?>
			<table>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Phone</th>
					<th>Address</th>
					<th>City</th>
					<th>State</th>
					<th>Zip</th>
					<th>Total Men Tickets<br />(Boys Chidon)</th>
					<th>Total Women Tickets<br />(Boys Chidon)</th>
					<th>Total Women Tickets<br />(Girls Chidon)</th>
					<th>Ticket Options</th>
					<?php
					foreach ($ticketTypes as $desc => $field) {
						echo "<th>" . $desc . "</th>";
						$totalTypes[$field] = 0;
					}
					?>
					<th>Date Purchased</th>
					<th>Prepared</th>
					<th>Shipped / Picked Up</th>
				</tr>
				<?
				$totalBB = 0;
				$totalGB = 0;
				$totalGG = 0;
				foreach ($purchases as $purchase) {					
					//add to totals
					$totalBB += $purchase['mqty'];
					$totalGB += $purchase['gqty'];
					$totalGG += $purchase['ggqty'];
										
					echo "<tr><td>" . $purchase['name'] . "</td><td>" .  
						$purchase['email'] . "</td><td>" . 
						$purchase['phone'] . "</td><td>";
					if (empty($purchase['address'])) {
						echo "&nbsp;</td><td></td><td></td><td>";
					} else {
						echo $purchase['address'] . "</td><td>" . $purchase['city'] . "</td><td>" . 
						$purchase['state'] . "</td><td>" . $purchase['zip'];
					}
					echo "</td><td>" . $purchase['mqty'] . "</td><td>" . $purchase['gqty'] . "</td><td>" . 
						$purchase['ggqty'] . "</td><td>" . $purchase['method'] . "</td><td>";
					foreach ($ticketTypes as $desc => $field) {
						echo $purchase[$field] . "</td><td>";
						$totalTypes[$field] += $purchase[$field];
					}
					echo $purchase['date_purchased'] . "</td><td>";
					echo "<input type='checkbox' class='prepared' value=" . $purchase['id'];
					if ($purchase['prepared']) {
                        echo " checked='checked'";
                    }
					echo " /></td><td>";
					echo "<input type='checkbox' class='shipped' value=" . $purchase['id'];
                    if (!empty($purchase['shipped']) && $purchase['shipped'] > 0) {
                        echo " checked='checked' /><span><br />" . $purchase['shipped'] . "</span>";
                    } else {
                        echo " />";
                    }
					echo "</td></tr>";
				}
				echo "<tr><th colspan='7' style='text-align: right'>Totals:</th>";
				echo "<th style='text-align: left'>" . $totalBB . "</th>";
				echo "<th style='text-align: left'>" . $totalGB . "</th>";
				echo "<th style='text-align: left'>" . $totalGG . "</th>";
				echo "<th></th>";
				foreach ($totalTypes as $field => $total) {
					echo "<th>" . $total . "</th>";
				}
				echo "<th colspan='3'></th></tr>";
				?>
			</table>
			<?
		}
        ?>
        
    </body>
</html>