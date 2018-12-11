<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Shool Orders</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
	</head>
	
	<body>
		<? include('admin_header.php'); ?>
        <h1>School Orders</h1>
        
        <?
        $sql = "select o.*, s.school_name, a.first, a.last  
        		from 5775_orders o 
        		join schools s using (school_id) 
        		join admins a using (admin_id)";
		$result = mysql_query($sql);
		$orders = array();
		while ($row = mysql_fetch_assoc($result)) {
			$orders[] = $row;
		}
		if (!empty($orders)) {
	        ?>
	        <table>
	        	<tr>
	        		<th>School</th>
	        		<th>Admin</th>
	        		<th>Posters</th>
	        		<th>Brochures</th>
	        		<th>Shipping Method</th>
	        		<th>Date Ordered</th>
	        	</tr>
	        	<?
	        	foreach ($orders as $order) {
	        		echo "<tr><td>" . $order['school_name'] . "</td><td>" . $order['first'] . ' ' . $order['last'] . 
	        		"</td><td>" . $order['posters'] . "</td><td>" . $order['brochures'] . "</td><td>" . $order['shipping_method'] . 
	        		"</td><td>" . $order['order_date'] . "</td></tr>";
	        	}
	        	?>
	        </table>
	    <?
		} else {
			echo "No orders found.";
		}
		?>
	</body>
</html>
       