<?php
include('file_save.php');
include ("get_camp_id.php");
$camp_id = get_camp_id();

$user_id = $_GET["user_id"];
$rank_ord = $_GET["rank_ord"];

$row = mysql_fetch_assoc(mysql_query("SELECT camp_name, camp_number, camp_city, camp_state, camp_logo_id FROM camps WHERE camp_id=" . $camp_id));
$camp_name = $row['camp_name'];
$camp_number = $row['camp_number'];
$camp_city = $row['camp_city'];
$camp_state = $row['camp_state'];
$camp_logo_id = $row['camp_logo_id'];

$campers = get_camp_members($camp_id, $user_id, $rank_ord);

function get_camp_members($camp_id, $user_id, $rank_ord) {
	$campers = array();
	
	$sql = "SELECT u.user_id, u.first, u.last, u.user_photo_id ";
	$sql = $sql . "FROM users AS u ";
	if ($rank_ord > 0)
		$sql = $sql . " JOIN rank_marks AS rm ON (u.user_id=rm.user_id AND rm.rank_ord=" . $rank_ord . ") ";
	$sql = $sql . "WHERE camp_id=" . $camp_id . " AND camp_registered IS NOT NULL ";
	if ($user_id > 0)
		$sql = $sql . " AND u.user_id=" . $user_id . " ";
	$sql = $sql . " ORDER BY u.first, u.last ";
	
	$query = mysql_query($sql);
	while ($row = mysql_fetch_assoc($query)) {
		$user_id = $row['user_id'];
		$first = $row['first'];
		$last = $row['last'];
		$user_photo_id = $row['user_photo_id'];
		
		$element = compact('user_id', 'first', 'last', 'user_photo_id');
		array_push($campers, $element);	
	}
	
	return ($campers);
}

function get_camper_details($user_id){
	$camper = array();
	
	$sql = "SELECT u.*, r.rank_name, r.rank_color ";
	$sql = $sql . "FROM users AS u ";
	$sql = $sql . "LEFT JOIN rank_marks AS rm USING (user_id) ";
	$sql = $sql . "LEFT JOIN ranks AS r USING (rank_ord) ";
	$sql = $sql . "WHERE user_id=" . $user_id;
	
	$query = mq($sql);
	
	while ($row = mysql_fetch_assoc($query)) {
		$user_id = $row['user_id'];
		$user_code = $row['user_code'];
		$user_start_date = $row['user_start_date'];
		$first = $row['first'];
		$last = $row['last'];
		$email = $row['email'];
		$first_he = $row['first_he'];
		$last_he = $row['last_he'];
		$gender = $row['gender'];
		$lang = $row['lang'];
		$user_address1 = $row['user_address1'];
		$user_address2 = $row['user_address2'];
		$user_city = $row['user_city'];
		$user_state = $row['user_state'];
		$user_postal = $row['user_postal'];
		$user_phone = $row['user_phone'];
		$user_country = $row['user_country'];
		$user_photo_id = $row['user_photo_id'];
		$user_code = "3" . $row['user_code'];
		$user_photo_id = $row['user_photo_id'];
		$rank_name = $row['rank_name'];
		$rank_color = $row['rank_color'];

		$array_element = compact('user_id', 'user_code', 'user_start_date', 'first','last','email','first_he','last_he','gender','lang','user_address1','user_address2','user_city','user_state','user_postal','user_phone','user_country', 'user_photo_id','user_code', 'user_photo_id', 'rank_name', 'rank_color');
		array_push($camper, $array_element);
	}
	
	return ($camper);
}
?>
<HTML>

	<HEAD>		
		<!--<LINK href="admin_styles.css" rel="stylesheet" type="text/css">-->
		
		<STYLE type="text/css">
			body {
				font-family:Tahoma,Verdana,Arial,Helvetica,sans-serif;
				font-size:10px;
			}

			.card_sheet {
			  margin: auto;
			  page-break-after: always;
			}

			.card {
			  width: 3.375in;
			  height: 2.125in;
			  font-size: 2mm;
			  position: relative;
			  padding: 0px;
			  vertical-align: top;
			  margin: auto;
			}

			.top {
			  text-align: center;
			  font-weight: bold;
			  color: white;
			  height: .2in;
			  line-height: .2in;
			  font-size: 1.85mm;
			}

			.sig {
			  border-top: .03in solid #808080; /* default color */
			  height: .3in;
			  background-color: white;
			  text-align: right;
			  padding: .04in;
			}

			.card .sig h1 {
			  text-transform: uppercase;
			  padding: 0mm;
			  margin: 0mm;
			  font-weight: bold;
			  font-size: .1in;
			  line-height: .125in;
			}

			.bg {
			  display: block;
			  position: absolute;
			  width: 3.375in;
			  height: 1.625in; /* 2.125 - .2 - .3 + .03 + .03 */
			  z-index: -1;
			}

			.card table {
			  width: 100%;
			}

			.card table td {
			  vertical-align: top;
			}

			.card p {
			  margin-top: 0px;
			  padding-top: 0px;
			}

			.card em {
			  text-transform: uppercase;
			  font-style: normal;
			}

			.card .first {
			  padding: .04in;
			  padding-top: .02in;
			}

			.card .first h2 {
			  text-transform: uppercase;
			  padding: 0px;
			  margin: 0px;
			  font-weight: bold;
			  font-size: .1in;
			  line-height: .125in;
			  padding-bottom: .04in;
			}

			.card .logo {
			  vertical-align: bottom;
			}

			.card .logo img {
			  height: .75in;
			  padding: .04in;
			}

			.card .base {
			  padding: .02in 0px;
			  vertical-align: bottom;
			}

			.card .base p {
			  margin: 0px;
			  padding: 0px;
			  padding-top: .04in;
			}

			.card .base b {
			  text-transform: uppercase;
			  font-size: 2.5mm;
			}

			.card .photo {
			  padding-top: .02in;
			  padding: .04in;
			}

			.card .photo img {
			  height: 1in;
			  border: .03in solid #808080;
			  display: block;
			}

			.card .code {
			  text-align: center;
			  font-size: .14in;
			  line-height: .14in;
			  margin-bottom: 0px;
			  width: 2.47in;
			  padding: .04in;
			  vertical-align: middle;
			  padding-top: .02in;
			}

			.card .code p {
			  margin-bottom: 0px;
			  padding-bottom: 0px;
			}

			.card .code img {
			  width: 2.43in;
			  height: .25in;
			}

			.card .stats {
			  padding-top: .02in;
			  padding: .04in;
			}

			.card .stats p {
			  font-size: .08in;
			  line-height: .09in;
			  font-weight: bold;
			  text-align: center;
			  margin: 0px;
			  padding: 0px;
			  padding-bottom: .04in;
			}

			@media print {
			  body {
				background-color: transparent;
			  }

			  .card .bg {
				display: none;
			  }

			  .top {
				background-color: black;
			  }

			  .top {
				visibility: hidden;
			  }
			  .sig {
				border-color: transparent !important;
				background-color: transparent !important;
			  }

			  .card h1, .card em {
				color: black;
			  }
			}
		</STYLE>
		
	</HEAD>

	<BODY style="background-color:white;">
		<form>
			<input type="button" value="Print This Page" onClick="window.print()" />
			<input type="button" value="CLOSE" onClick="window.close()" />
		</form>

		<div id="printarea">
		<table class='card_sheet'>
	
	<?							
		$counter = 0;
		foreach ($campers as $camper) :
			$user_id = $camper['user_id']; 
			$camper_details = get_camper_details($user_id); 
			$counter++; 
			$remainder = $counter % 2; 
	?>
	
			<? if ($remainder == 1) : ?>
			<tr>
			<? endif; ?>	
	
				<td class='card'>

					<div class='top' style='background-color:<?=$camper_details[0]['rank_color'];?>'>
						This card entitles the cardholder to CGI Tzivos Hashem privileges
					</div>
					
					<div class='sig' style='border-color:<?=$camper_details[0]['rank_color'];?>'>
						<label style="color:grey;">Authorized Signature</label>
						<h1>
							<em style='color:<?=$camper_details[0]['rank_color'];?>'><//?=$camper_details[0]['rank_name']; ?></em> <?=$camper_details[0]['first'] . ' ' . $camper_details[0]['last'];?>
						</h1>
					</div>
					
					<div class='panel'>
						<table cellpadding='0' cellspacing='0'>
							<tbody>
								<tr>
									<td class='logo' width='1'>
										<?=linkImgFile($camp_logo_id);?>
										<!--<img src='/file_view.php?id=<?//=$camp_logo_id;?>' alt='Picture1.png'>-->
									</td>
									<td class='base'>
										<p style='text-transform: uppercase; font-size: 2.5mm; font-family:Tahoma,Verdana,Arial,Helvetica,sans-serif;'>
											<label style="color:grey;">BASE #<?=$camp_number;?></label><br>
											<b><?=$camp_name;?></b><br>
											<label style="color:grey;"><?=$camp_city;?>, <?=$camp_state;?></label>
										</p>
									</td>
									
									
									<td class='photo' width='1'>
										<? if ($camper_details[0]['user_photo_id'] > 0) : ?>
											<?=linkImgFile($camper_details[0]['user_photo_id'], NULL, NULL, "style='border-color:" . $camper_details[0]['rank_color'] . ";'");?>
										<? endif; ?>
									</td>
									
									
								</tr>
							</tbody>
						</table>
						
						<table>
							<tbody>
								<tr>
									<td class='code'>
										<p>
											<img src='barcode.php/<?=$camper_details[0]['user_code'];?>' alt=''><br>
											<?=$camper_details[0]['user_code'];?>
										</p>
									</td>
									<td class='stats'>
										<p>
											Member Since
											<br><span dir='rtl'><?=jdtogregorian($camper_details[0]['user_start_date']);?></span>
										</p>
										<p>
										</p>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</td>
			
			<? if ($remainder == 0) : ?>
			</tr>
			<? endif; ?>		

		<? endforeach; ?>

			</table>
			</div>
			
	</BODY>
	
</HTML>
