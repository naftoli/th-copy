<?
$admin_auth = array('school'); 
require_once('../header.php');
require_once 'class.auction.php';
?>
<html>
	<head>
		<meta charset='UTF-8' />
	</head>
	<style>
		table {
			font-size: 10px;
		}
		th, td {
			border: 1px solid black;
		}
	</style>
	<body>
<?

//$a = new Auction( 37 );
$a->run();
$winners = $a->getWinners();

echo "Done.";
echo "<pre>";
//print_r( $winners );
echo "</pre>";
?>
	</body>
</html>