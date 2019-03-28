<?
$admin_auth = array('school','user'); 
require('header.php');

require_once('card_printer.php');
$lines = 5;
$cols = 2;

$cards = array();
$sql = "select * from maos_chitim_cards";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
	$cards[] = $row;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mo'os Chitim Pledge Cards</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type="text/css">
			.cards {
				width:672px;
				page-break-after:always;
				font-family:'Myriad Pro',Arial,sans-serif;
				margin:-0.1875in auto 0;
			}
			.cards .row {
				display:table;
				margin:-0.1875in auto 0;
				margin-bottom: 8px;
			}
			.cards .card {
				width:3.5in;
				height:2in;
				text-align:center;
	
				width:306px;
				height:162px;
				padding:10px 15px 10px 15px;
				display:table-cell;
			}
			.cards .card .container {
				border:2px solid #000;
				border-radius:10px;
				-moz-border-radius:10px;
				height:100%;
			}
	
			.cards .logo {
				float:left;
				margin-left:30px;
			}
			.cards .institution {
				float:right;
				margin-right:30px;
			}
			.cards .logo img, .cards .institution img {
				margin:5px 0;
				width:55px;
				height:55px;
			}
			.cards .clear {
				clear:both;
			}
	
			.cards .title img {
				width:250px;
				height:34px;
			}
	
			.cards .campaign {
				/*clear:both;*/
				font-weight:bold;
				/*position:absolute;*/
				width:302px;
			}
	
			.cards .name {
				font-size:12px;
				margin:6px 0;
				height:15px;
				/*font-weight:bold;*/
			}
	
			.cards .barcode {
				font-size:9px;
			}
	
			.cards .points {
				font-size:12px;
				width:50px;
				font-weight:bold;
				border:2px solid #000000;
				border-radius:7px;
				-moz-border-radius:7px;
				padding:3px;
				margin:-55px auto 10px;
			}
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class='no-print'>Mo'os Chitim Pledge Cards</h1>
        
        <div class="cards achievement">
			<div class="row">
        		<div class="card">
					<div class="container">
						<div class="logo"><img src="" /></div>
						<div class="institution"><img src="" /></div>
						<div class="clear"></div>
						<div class="points">5</div>
						<div class="campaign">Pledges</div>
						<div class="title"></div>
						<div class="name">
						<div class="barcode">
							<img src='barcode.php/" . $cards[$cardNum]['number'] . "'>
							<br /><?=$cards[0]['number']?>
						</div>
					</div>
				</div>
			</div>
		</div>	
	</body>
</html>