<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>CD Purchases Report</title>
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
                        table : 'cd_purchases', 
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
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
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
        <? include('admin_header.php'); ?>
        <h1>CD Purchases Report</h1>
        
        <?
        $purchases = array();
        $sql = "select * from cd_purchases";
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
					<th>Number of CD's</th>
					<th>Date Purchased</th>
					<th>Shipped / Picked Up</th>
				</tr>
				<?
				foreach ($purchases as $purchase) {
					echo "<tr><td>" . $purchase['name'] . "</td><td>" . $purchase['email'] . "</td><td>" . 
						$purchase['phone'] . "</td><td>";
					if ($purchase['method'] == 'pickup') {
						echo "Pickup from Museum";
					} else {
						if ($purchase['address'] == '') {
							echo "&nbsp;";
						} else {
							echo $purchase['address'] . "<br />" . $purchase['city'] . ", " . 
							$purchase['state'] . "<br />" . $purchase['zip'];
						}
					}
					echo "</td><td>" . $purchase['qty'] . "</td><td>" . $purchase['date_purchased'] . "</td><td>";
					echo "<input type='checkbox' class='shipped' value=" . $purchase['id'];
                    if (!empty($purchase['shipped']) && $purchase['shipped'] > 0) {
                        echo " checked='checked' /><span><br />" . $purchase['shipped'] . "</span>";
                    } else {
                        echo " />";
                    }
					 echo "</td></tr>";
				}
				?>
			</table>
			<?
		}
        ?>
        
    </body>
</html>