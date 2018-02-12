<? 
require('header.php'); 
require_once('calendar.php');
require_once('file_save.php');

$range = gri('range', 0);

include("camps/includes/classes/user.php");
$sql = "SELECT * FROM users WHERE user_id=" . $user['user_id'];
$query = mysql_query($sql);
$row = mysql_fetch_assoc($query);
$user = new user($row);
$user->get_school();
$user->get_rank();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">
	<HEAD>
		<TITLE><?=T_('Scan your Achievement or Prize card'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
		<LINK href="styles_reset.css" rel="stylesheet" type="text/css">
		<LINK href="styles_kiosk.css" rel="stylesheet" type="text/css">
		<SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
		<SCRIPT type="text/javascript" src="jquery-ui.js"></SCRIPT>
		<STYLE type="text/css">
		.transactions {
		  font-size: 14px;
		  margin: auto;
		  background-color: #ffffce;
		  -webkit-border-radius: .06in;
		  -moz-border-radius: .06in;
		  border-radius: .06in;
		  color: black;
		  font-weight: normal;
		  max-width: 90%;
		}

		.transactions th {
		  padding: 2px .5em;
		  text-align: <?=$align_start?>;
		  background-color: #e8efbd;
		  font-size: 150%;
		}

		.transactions td {
		  padding: 4px .5em;
		}

		.transactions tr.even td {
		  background-color: #fff6aa;
		}

		.transactions tr:first-child th:first-child, .transactions tr:first-child th:last-child, .transactions tr:first-child td:first-child, .transactions tr:first-child td:last-child, .transactions tr:last-child th:first-child, .transactions tr:last-child th:last-child, .transactions tr:last-child td:first-child, .transactions tr:last-child td:last-child {
		  -webkit-border-radius: .06in;
		  -moz-border-radius: .06in;
		  border-radius: .06in;
		}

		a {
		  text-decoration: none;
		  font-weight: bold;
		  line-height: 40px;
		  padding: 7px 0px;
		}

		a:link, a:visited {
		  color: #8ead3e;
		}

		a:hover, a:active {
		  color: #ffcc00;
		}

		.links {
		  padding-top: 1em;
		  text-align: center;
		}
		</STYLE>
	</HEAD>
	
	<body class="blue">
	
		<div id="wrapper">
		
			<div id="header">
			
				<div class="org">
			
					<? include ("nav.php"); ?>
			
					<div class="org_photo">					
						<!--<?//=!is_null($user_row['school_logo_kiosk_id']) ? linkImgFile($user_row['school_logo_kiosk_id'], null, 100) : (!is_null($user_row['school_logo_id']) ? linkImgFile($user_row['school_logo_id'], null, 100) : '')?>-->
						<?=!is_null($user->school->school_logo_kiosk_id) ? linkImgFile($user->school->school_logo_kiosk_id, null, 100) : (!is_null($user->school->school_logo_id) ? linkImgFile($user->school->school_logo_id, null, 100) : '')?>
					</div>
					
					<?=T_('Base')?>: #<?=$user->school->school_number;?><BR>
					<?=es($user->school->school_name);?><BR>
					<?=es($user->rank_name);?> <?=es($user->first);?> <?=es($user->last);?>
				
				</div>
				
				<noscript>
					<p class="js_alert">Notice: You have javascript disabled.<br>Some parts of the site will not function without javascript.</p>
				</noscript>
				
			</div>
			

			<div id="main">
				<div id="page_title">
					<?=T_('Transactions')?>
				</div>
				
				<div class="padding_top">
					<?
					$user_miles = mysql_result(mq(totalMarks("WHERE user_id=" . $user->user_id)), 0);
					$running_balance = $user_miles;
					$result = userStatement($user->user_id, rangeToDate($range));
					?>
					
					<TABLE class="transactions">
					<? if (mysql_num_rows($result)) : ?>
						<TR>
							<TH><?=T_('Posting Date')?></TH>
							<TH><?=T_('Subject')?></TH>
							<TH><?=T_('Description')?></TH>
							<TH><?=T_('Miles Earned')?></TH>
							<TH><?=T_('Balance')?></TH>
						</TR>
						
						<? $toggle = true; ?>
						<? while($row = mysql_fetch_assoc($result)): ?>
						<TR class="<?=($toggle = !$toggle) ? 'even' : 'odd'?>">
							<TD><?=dateToHebrew($row['mark_date'])?></TD>
							<TD><?=es($row['subject_name'])?><BR><?=es($row['name'])?></TD>
							<TD><?=es($row['description'])?></TD>
							<TD style="text-align: right;"><?=floatval($row['points']) ? number_format($row['points'], 2) : '-'?></TD>
							<TD style="text-align: right;"><?=number_format($running_balance, 2)?></TD>
						</TR>
						
							<? $running_balance -= $row['points']; ?>
						<? endwhile; ?>
					<? else: ?>
						<TR>
							<TD><?=T_('No transactions for the time period selected.')?></TD>
						</TR>
					<? endif; ?>
					</TABLE>

					<P class="links">
						<?=T_('Show')?>:
						<A HREF="transactions.php?range=0"><?=T_('Today')?></A> &bull;
						<A HREF="transactions.php?range=1"><?=T_('This week')?></A> &bull;
						<A HREF="transactions.php?range=2"><?=T_('Two weeks')?></A> &bull;
						<A HREF="transactions.php?range=4"><?=T_('Four weeks')?></A> &bull;
						<A HREF="transactions.php?range=50"><?=T_('Fifty weeks')?></A>
					</P>
					
				</div>
				
			</div>
			
		</div>
		
		<div id="footer">
			<div class="footer_logout"></div>
		</div>
		
	</body>
	
</html>
