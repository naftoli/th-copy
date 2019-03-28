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
        <LINK href="card_printer.css" rel="stylesheet" type="text/css">
        <STYLE type="text/css">
			.backs {
			  margin:-.05in .125in;
			  page-break-after: always;
			}

			.backs td {
			  -webkit-box-sizing: border-box;
			  -moz-box-sizing: border-box;
			  vertical-align: middle;
			  height: 2in;
			  width: 3.5in;
			}

			.backs td td {
			  width: auto;
			  height: auto;
			  border: none;
			}
			
			.title {
				font-family: ribeye-marrow, serif;
				font-size: 18px;
				padding-top: .1in !important;
				padding-bottom: .05in;
			}
			
			.leftIcon {
				float: left;
			}
			
			.rightIcon {
				float: right;
			}

			@media print {
			  .no-print {
			  	display: none;
			  }
			}
		</STYLE>
		<script src="//use.edgefonts.net/ribeye-marrow.js"></script>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class='no-print'>Mo'os Chitim Pledge Cards</h1>
        
        <table class="backs">
        	<?
        	$cardNum = 0;
        	for ($i = 0; $i < $lines; $i++) {
        		echo "<tr class='row'>";
				for ($j = 0; $j < $cols; $j++) {
					echo "<td>";
					echo "<div class='card_back'>
						<div class='border'>
							<table>
								<tr>
									<td class='title'>Sponsor Pledge Card</td>
								</tr>
							</table>
							<div class='barcode'>
								<img src='barcode.php/" . $cards[$cardNum]['number'] . "'>
								<br />" . $cards[$cardNum]['number'] . " 
							</div>
							<div class='points'>
								<table>
									<tr>
										<td><div class='border'>$" . $cards[$cardNum]['value'] . "</div></td>
									</tr>
								</table>							 
							</div>
						</div>
					</div>";
					echo "</td>";
					$cardNum++;
				}
				echo "</tr>";
        	}
        	?>
        </table>
	</body>
</html>