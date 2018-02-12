<?
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
            });
        </script>
        <!--<link href="admin_styles.css" rel="stylesheet" type="text/css">-->
        <style type='text/css'>
            table {
                font-size: 12px;
                font-family: "sans-serif";
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
        $year = 5776;
        $purchases = array();
        $sql = "select * from chidon where year = $year and ggqty > 0";
		$result = mysql_query($sql);
		while ($row = mysql_fetch_assoc($result)) {
			$purchases[]  = $row;
		}
		
		if (empty($purchases)) {
			echo "There were no purchases made.";
		} else {
			?>
			<table>
				<tr>
					<th>Name</th>
					<th>Email</th>
					<th>Phone</th>
					<th>Address</th>
					<th>Women Tickets<br />(Girls Chidon)</th>
					<th>VIP Seats</th>
					<th>Front Row</th>
					<th>Honoring<br /></th>
					<th>Honoring<br /></th>
					<th>Ticket Options</th>
					<th>Date Purchased</th>
					<th>Shipped / Picked Up</th>
				</tr>
				<?
				$totalBB = 0;
				$totalGB = 0;
				$totalGG = 0;
				foreach ($purchases as $purchase) {
					//get contestant names
					$id = $purchase['chidon_reg_id'];
					if (!empty($id)) {
						$sql = "select name, last_name from chidon_reg where chidon_reg_id = " . $id;
						$result = mysql_query($sql);
						$row = mysql_fetch_assoc($result);
						$c1 = $row['name'] . ' ' . $row['last_name'];
					} else {
						$c1 = '';
					}
					
					$id2 = $purchase['chidon_reg_id2'];
					if (!empty($id2)) {
						$sql = "select name, last_name from chidon_reg where chidon_reg_id = " . $id2;
						$result = mysql_query($sql);
						$row = mysql_fetch_assoc($result);
						$c2 = $row['name'] . ' ' . $row['last_name'];
					} else {
						$c2 = '';
					}
					
					//add to totals
					$totalGG += $purchase['ggqty'];
					echo "<tr><td>" . $purchase['name'] . "</td><td>" .  
						$purchase['email'] . "</td><td>" . 
						$purchase['phone'] . "</td><td>";
					if (empty($purchase['address'])) {
						echo "&nbsp;";
					} else {
						echo $purchase['address'] . "<br />" . $purchase['city'] . ", " . 
						$purchase['state'] . "<br />" . $purchase['zip'];
					}
					echo "</td><td>" . $purchase['ggqty'] . "</td><td>" . 
						($purchase['vip_seats'] ? 'yes' : 'yes') . "</td><td>" . 
						($purchase['fr'] ? 'yes' : 'no') . "</td><td>" . 
						$c1 . "</td><td>" . $c2 . "</td><td>" . 
						$purchase['method'] . "</td><td>" . $purchase['date_purchased'] . "</td><td>";
					echo "<input type='checkbox' class='shipped' value=" . $purchase['id'];
                    if (!empty($purchase['shipped']) && $purchase['shipped'] > 0) {
                        echo " checked='checked' /><span><br />" . $purchase['shipped'] . "</span>";
                    } else {
                        echo " />";
                    }
					echo "</td></tr>";
				}
				echo "<tr><th colspan='4' style='text-align: right'>Totals:</th>";
				echo "<th style='text-align: left'>" . $totalGG . "</th>";
				echo "<th colspan='6'></th></tr>";
				?>
			</table>
			<?
		}
        ?>
        
    </body>
</html>