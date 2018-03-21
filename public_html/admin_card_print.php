<?
ini_set('display_errors', 1);
$dual_auth = true; 
$admin_auth = array('school'); 

// imports
require( $_SERVER['DOCUMENT_ROOT'] . '/header.php' ); 
require_once( $_SERVER['DOCUMENT_ROOT'] . '/calendar.php' );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/file_save.php' );

// if the admin is a superuser
if ($admin_user['auth'] == 'super') {
	// check for users with no rank
	$blank_ranks_query = mysql_query(
		"SELECT u.user_id FROM users AS u
		LEFT JOIN rank_marks AS rm ON ( u.user_id = rm.user_id ) 
		WHERE rm.rank_ord IS NULL"
	);
	
	while ($no_rank = mysql_fetch_row($blank_ranks_query)) {
		$create_private_sql = "INSERT into rank_marks (rank_ord, user_id, date_promoted) values(1, $rowP[0], " . unixtojd() . ")";
		//echo $create_private_sql . "<br />";
		mysql_query($create_private_sql);
	}
}

$action = "";
if ( isset( $_GET["action"] ) ) {
	$action = $_GET["action"];
}
// mark cards as printed
if ( !empty( $admin_user ) && $admin_user['auth'] == 'super' && ( $rank_marks = gra( 'rank_marks' ) ) ) {
	//print_r($rank_marks);
	foreach($rank_marks as $user_id => $rank_ord) {
		if (!isset($_POST['skip'][$user_id])) continue;
		$user_id = intval($user_id);
		$rank_ord = intval($rank_ord);
		//echo "updating $user_id<br />";
		if ( !mysql_query(
			"UPDATE rank_marks SET date_printed = NOW() 
			WHERE user_id = $user_id AND rank_ord = $rank_ord 
			AND date_printed IS NULL"
		)) // end if we can update the cards as printed
			 die(mysql_error());
	}
}

if ( !empty($admin_user) ) {
	assure_id_school('school_id');
	$school_id 	= gri( 'school_id', -2 );
	$class_id 	= gri( 'class_id', 	-1 );
	$user_id 		= gri( 'user_id', 	-1 );
	// set the card type based on the type of user
	if (isset($_REQUEST['type_of_card'])) 
		$card_type = $_REQUEST['type_of_card'];
	else {
		if ( $admin_user['auth'] == 'super' ) $card_type = 'permanent';
		else $card_type = 'temporary';
	}
	// get the rank we want to print
	$rank_ord  = gri( 'rank_ord', -1 );
	$rank_type = gr( 'rank_type', 'current' );
	// get the date printed
	$hide_printed = $admin_user['auth'] == 'super' ? gri( 'hide_printed', 0 ) : 0;
	$withoutPic 	= $admin_user['auth'] == 'super' ? gri( 'withoutPic', 1 ) : 1;

	if ( isset( $_GET['action'] ) && !isset( $_GET['withoutPic'] ) )
		$withoutPic = 0;
} else {
	$school_id 	= $user['school_id'];
	$class_id 	= $user['class_id'];
	$user_id 		= $user['user_id'];
	$card_type 	= 'temporary';
	$rank_ord 	= -1;
	$rank_type 	= 'current';
	$hide_printed = 0;
	$withoutPic = 0;
}

// total lines and columns
$lines=5; $cols=2;
// permenent cards only have one line and column
if ($card_type == 'permanent') {
	$lines = 1;
	$cols = 1;
}
// change the SQL based on the rank type
switch($rank_type) {
	case 'past':
		//$rank_sql = 'LEFT JOIN rank_marks USING (user_id) LEFT JOIN ranks USING (rank_ord)';
		$rank_sql = 'LEFT JOIN rank_marks USING (user_id) LEFT JOIN ranks USING (rank_ord)';
		$rank_where = "and date_promoted < " . $_GET['end_date'];
    break;
	case 'all':
		$rank_sql = 'JOIN ranks';
    break;
	// current is the default
	case 'current':
	default:
		//$rank_sql = 'LEFT JOIN (SELECT MAX(rank_ord) rank_ord, user_id FROM rank_marks GROUP BY user_id) rank USING (user_id)' . ($hide_printed ? ' LEFT JOIN rank_marks USING (rank_ord, user_id)' : '') . ' LEFT JOIN ranks USING (rank_ord)';
		$rank_sql = 'LEFT JOIN (SELECT MAX(rank_ord) rank_ord, user_id FROM rank_marks where date_promoted < ' . unixtojd() . ' GROUP BY user_id) rank USING (user_id)' . ($hide_printed ? ' LEFT JOIN rank_marks USING (rank_ord, user_id)' : '') . ' LEFT JOIN ranks USING (rank_ord)';
    break;
}

if (isset($_GET["school_id"]))
	$school_id = $_GET["school_id"];

// following was added to skip update dates 'AND date_promoted not in (2455817,2455772)'
$sql1 = "SELECT DISTINCT user_id, first, last, first_he, last_he, username, user_code, 
		user_serial, school_name, school_city, school_state, school_number, school_logo_id, 
		class_grade, class_sub, user_photo_id, mobile_pic, dob, dob_he_offset, user_start_date, 
		rank_ord, rank_name, rank_image_id, rank_color, rank_background_image_id 
		FROM users $rank_sql" . ' 
		JOIN schools USING (school_id) 
		LEFT JOIN classes USING (school_id, class_id) 
		WHERE user_registered > 0' .
		($school_id != -1 ? " AND school_id = $school_id" : '') . 
		($class_id != -1 ? " AND class_id = $class_id" : '') . 
		($user_id != -1 ? " AND user_id = $user_id" : '') . 
		($rank_ord != -1 ? " AND rank_ord = $rank_ord" : '') . 
		($hide_printed && $rank_type != 'all' ? ' AND date_printed IS NULL ' : '') . 
		(!empty($_GET['serial']) ? ' and user_serial = ' . $_GET['serial'] . ' ' : '') . 
		(!empty($_GET['last']) ? ' and last = \'' . $_GET['last'] . '\' ' : '') .
		(isset($rank_where) ? ' ' . $rank_where : '') . 
		' ORDER BY school_name, class_grade, class_sub, last, first, username, rank_ord';
		//echo $sql1; exit;
				
echo "<input type='hidden' name='sql' value='" . $sql1 . "'>";
$query1 = mysql_query($sql1);
//echo mysql_num_rows($query1);exit;
// $result = !gr('display') ? false : mq(
// 	"SELECT user_id, first, last, first_he, last_he, username, user_code, 
// 	user_serial, school_name, school_city, school_state, school_number, school_logo_id, 
// 	class_grade, class_sub, user_photo_id, dob, dob_he_offset, user_start_date, rank_ord, 
// 	rank_name, rank_image_id, rank_color, rank_background_image_id 
// 	FROM users $rank_sql" . 
// 	' LEFT JOIN schools USING (school_id) 
// 	LEFT JOIN classes USING (school_id, class_id) 
// 	WHERE user_start_date IS NOT NULL' 
// 	. ($school_id != -1 ? " AND school_id = $school_id" : '') 
// 	. ($class_id != -1 ? " AND class_id = $class_id" : '') 
// 	. ($user_id != -1 ? " AND user_id = $user_id" : '') 
// 	. ($rank_ord != -1 ? " AND rank_ord = $rank_ord" : '') 
// 	. ($hide_printed && $rank_type != 'all' ? ' AND date_printed IS NULL' : '') 
// 	. ' ORDER BY school_name, class_grade, class_sub, last, first, username, rank_ord'
// ); //MAX(rank_ord) sub-query is not optimizied, consider joining to user and duplicating filter. However, might not be necessary since the sub-query is so fast (index read).
?>

<!DOCTYPE html>

<html DIR="<?=$dir?>">
	<head>
		<title><?=T_("Soldier's Rank Cards"), ' - ', T_('Tzivos Hashem Management System')?></title>
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
		<script type="text/javascript" src="icalendar.js"></script>
		<style type="text/css">
			.card_sheet { margin: auto; page-break-after: always; margin-bottom:15px; }
			.card { 
				width: 3.5in; height: 2in; font-size: 2mm; position: relative; 
				padding: 0px; vertical-align: top; margin: auto;
			}
			.top { 
				text-align: center; font-weight: bold; color: white; height: .2in; 
				line-height: .2in; font-size: 1.85mm;
			}
			.sig { 
				border-top: .03in solid #808080; /* default color */ height: .3in; 
				background-color: white; text-align: right; padding-<?=$align_end?>: .04in;
			}
			.card .sig h1 { 
				text-transform: uppercase; padding: 0mm; margin: 0mm; font-weight: bold; 
				font-size: .1in; line-height: .125in; text-align:right;
			}
			.bg { 
				display: block; position: absolute; width: 3.375in; z-index: -1;
				height: 1.625in; /* 2.125 - .2 - .3 + .03 + .03 */
			}
			.card table { width: 100%; }
			.card table td { vertical-align: top; }
			.card p { margin-top: 0px; padding-top: 0px; }
			.card em { text-transform: uppercase; font-style: normal; }

			.card .first h2 { 
				text-transform: uppercase; padding: 0px; margin: 0px; font-weight: bold; 
				font-size: .1in; line-height: .125in; padding-bottom: .04in;
			}
			.card .logo { vertical-align: bottom; }
			.card .base { padding: .02in 0px; vertical-align: bottom; }
			.card .base p { margin: 0px; padding: 0px; padding-top: .04in; }
			.card .base b { text-transform: uppercase; font-size: 2.5mm; }
			.card .code p { margin-bottom: 0px; padding-bottom: 0px; }
			.card .code img { width: 2.43in; height: .25in; }
			.selection { margin-left: -20px; }

			/* these styles use PHP */
			.card .first { padding-<?=$align_start?>: .04in; padding-top: .02in; }
			.card .logo img { height: .75in; padding-<?=$align_start?>: .04in; }
			.card .photo { padding-top: .02in; padding-<?=$align_end?>: .04in; }
			.card .photo img { 
				height: 1in; border: .03in solid #808080; display: block; float: <?=$align_end;?>; 
			}
			.card .code { 
				text-align: center; font-size: .14in; line-height: .14in; margin-bottom: 0px; 
				width: 2.47in; padding-<?=$align_start?>: .04in; vertical-align: middle; padding-top: .02in;
			}
			.card .stats { padding-top: .02in; padding-<?=$align_end?>: .04in;}
			.card .stats p { 
				font-size: .08in; line-height: .09in; text-align: <?=$align_start?>; 
				font-weight: bold; text-align: center; margin: 0px; padding: 0px; padding-bottom: .04in;
			}

			@media print {
				.selection, .skipCard { visibility: hidden; }
				body { background-color: transparent; }
				.card .bg { display: none; }
				.top { background-color: black; }
			}
			/** PHP generated print styles */
			@media print {
			<? if ($card_type == 'permanent') { ?>
				.top { visibility: hidden; }
				.sig { border-color: transparent !important; background-color: transparent !important; }
				.card_sheet { margin-bottom:0;}
			<? } else { ?>
				.sig { border-color: black; padding-right:.125in; }
				.panel { padding-left:.125in; }
				.card h1, .card em { color: black; }
				.card_sheet { margin:0 .16in; margin-bottom:-.1in; }
			<? } ?>
			}
		</style>
	</head>
	
	<body>
		<?php include('admin_header.php'); ?>
		
		<script>
			$( document ).ready( function() {
				$( '.type_of_card' ).click( function() {
					$( '#card_type' ).val( $( this ).val() );					
				});					
			});
		</script>
		
		<div class="body">
			<div class="noprint">			
				<h1><?=T_("Soldier's Rank Cards")?></h1>
				<?php 
				if (!empty($message)) {
					echo "<h2>" . $message . "</h2>";
				}
				// if we have a user
				if (!empty($admin_user)) {
					// and he is a superuser
				    if ($admin_user['auth'] == 'super') { ?>
							<h2>Printing Instructions</h2>
							
							<p style="font-size: 150%;">
								File → Page Set up <br/> <br/>
								Portrait<br/>
								Scale 90<br/><br/>
								Margins: Top, Left, Right, Bottom (all): 0.0<br/><br/>
								All headers and footers: Blank<br/>
							</p>
						<?php } // end showing the printing instructions for  ?>
					<hr>
					<?php // render the school dropdown if it is a superuser or we have more then one school on the account.
					if ($admin_user['auth'] == 'super' || count($admin_user['auths']['school']) != 1) {
						// get the schools that the logged in user has access to...
						$school_result = mq(
							'SELECT school_id, school_name FROM schools WHERE test_school=0' 
							. ($admin_user['auth'] != 'super' ? ' AND school_id IN (' . implode(',', $admin_user['auths']['school'] ) . ')' : '') 
							. ' ORDER BY school_name'
						);?>

						<form action="admin_card_print.php" method="get" accept-charset="UTF-8">
							<p>
								<label><?=T_('Select Institution')?>: 
									<select name="school_id">								
									<?php // show an all option for super users
									if ( $admin_user['auth'] == 'super' ) { ?>
										<option value="-1">&lt;<?=T_('All')?>&gt;</option>
									<?php } // end if superuser
									while ( $school_row = mysql_fetch_assoc( $school_result ) ) { ?>
										<option value="<?= $school_row['school_id'] ?>" <?= $school_row['school_id'] == $school_id ? 'selected' : ''?>>
											<?=es($school_row['school_name'])?>
										</option>
									<?php } // end while loop for each school ?>
									</select>
								</label>
								
								<input class="submit" type="submit" value="<?=T_('Go')?>">
							</P>
						</form>
						
					<hr>
					<?php
					} // end if there is more then one school
				} // end if user is not blank?>
				
			</div>
			
			<?php // validate that we have a school selected
			if( $school_id == -2 ) { ?>
				<?= T_('Please select an Institution.') ?>
			<?php 
			} // end if we do not have a school
			// make sure that $admin_user is not empty (someone is logged in)
			if( !empty( $admin_user ) ) { ?>
				<div class="noprint">
			<?php
				// get all the classes
				$class_result = mq(
					"SELECT class_id, class_grade, class_sub FROM classes 
					WHERE school_id = $school_id ORDER BY class_grade, class_sub"
				);
				// get all the students
				$user_result = mq(
					"SELECT class_grade, class_sub, user_id, username, first, last 
					FROM users LEFT JOIN classes USING (school_id, class_id) 
					WHERE school_id = $school_id" .  ($class_id != -1 ? " AND class_id = $class_id" : '') . 
					" AND user_start_date IS NOT NULL ORDER BY class_grade, class_sub, last, first, username"
				);
				// get all the ranks
				$rank_result = mq(
					'SELECT rank_ord, rank_name FROM ranks ORDER BY rank_ord'
				);
			?>
			
			<form action="admin_card_print.php" method="get" accept-charset="UTF-8">
				<input type="hidden" name="action" id="action" value="print">
				<input type="hidden" name="school_id" value="<?=$school_id?>">
				
				<p>								
					<?php if( mysql_num_rows( $class_result ) ) { ?>
						<?=T_('Show Platoon')?>: 
						<select name="class_id">
							<option value="-1">&lt;<?=T_('All')?>&gt;
							<?php
							while( $class_row = mysql_fetch_assoc( $class_result ) ) {?>
								<option value="<?=$class_row['class_id']?>" <?=$class_row['class_id'] == $class_id ? 'selected' : ''?>>
									<?=es($class_row['class_grade'])?>-<?=es($class_row['class_sub'])?>
								</option>
							<?php } // end while loop for each class ?>
						</select>
						<br/>
					<?php } // end if we have classes
					
					if( mysql_num_rows( $user_result ) ) { ?>
						<?=T_('Show Soldier')?>
						<SELECT name="user_id">
							<OPTION value="-1">&lt;<?=T_('All')?>&gt;
							<? while($user_row = mysql_fetch_assoc($user_result)): ?>
							<OPTION value="<?=$user_row['user_id']?>" <?=$user_row['user_id'] == $user_id ? 'SELECTED' : ''?>><?=$class_id == -1 && $user_row['class_grade'] != '' ? es($user_row['class_grade'] . '-' . $user_row['class_sub']) . ': ' : ''?><?=es($user_row['last'])?>, <?=es($user_row['first'])?> (<?=es($user_row['username'])?>)</OPTION>
							<?endwhile;?>
						</SELECT> 
						(<?=T_('Note: Only registered students')?>)<BR>
					<?php } // end if we have users to pick from ?>
					
					<?= T_('Limit to Rank') ?>:
					<select name="rank_ord">
						<option value="-1">&lt;<?=T_('All')?>&gt;
						<?php while( $row = mysql_fetch_assoc( $rank_result ) ) { ?>
							<option value="<?=$row['rank_ord']?>" <?=$row['rank_ord'] == $rank_ord ? 'selected' : ''?>>
								<?=es($row['rank_name'])?>
							</option>
						<?php } // end while loop for rank options ?>
					</select>
					
					<br/>
									
					<?php // superuser options only
					if ( $admin_user['auth'] == 'super' ) { ?>
						<?=T_('Card type')?>:
						<label>
							<input type="radio" <?= $card_type == 'temporary' ? 'checked' : "" ;?> 
								class="type_of_card" name="type_of_card" value="temporary" />
							<?=T_('Temporary')?>
						</label>

						<label>
							<input type="radio"  <?= $card_type == 'permanent' ? 'checked' : "";?> 
								class="type_of_card" name="type_of_card" value="permanent" />
								<?=T_('Permanent')?>
						</label>
					
						<br/>
						
					<?php // if the user is not a superuser
					} else { // only allow them to print temporary cards via the UI ?>
						<input type="hidden" id="card_type" value="temporary">
					<?php } // end non-superuser options ?>
				
					<br/>
					<?=T_('Show Rank')?>:
					<label>
						<input type="radio" name="rank_type" value="current" <?=$rank_type == 'current' ? 'checked' : ''?>>
						<?=T_('Current Only')?>
					</label>
					<label>
						<input type="radio" name="rank_type" value="past" <?=$rank_type == 'past' ? 'checked' : ''?>>
						<?=T_('Current and Previous')?>
					</label>
					<label>
						<input type="radio" name="rank_type" value="all" <?=$rank_type == 'all' ? 'checked' : ''?>>
						<?=T_('All possible ranks')?>
					</label>
					(<?=T_('For use with Limit to Rank, and printing cards in advance')?>)
					<br/>
					
					<?php // superuser only
					if($admin_user['auth'] == 'super') { ?>
						<label>
							<?=T_('Hide cards marked as printed')?>: 
							<input type="checkbox" name="hide_printed" value="1" <?=$hide_printed ? ' checked' : ''?>>
						</label>
						(<?=T_('Not effective with choice: "All possible ranks"')?>)
						<br/>
					<?php } // end superuser only?>
					
					<input type="checkbox" name="withoutPic" value="1" <?=$withoutPic ? 'checked' : ''?> >
					Include soldier(s) without pictures
					<br /> <br />
					With the following last name: <input type="text" name="last" /><br />
					With the following serial number: <input type="text" name="serial" /><br />
					<br />
					
					<span class='dates'>
						Show ranks earned before: 
						<input type="hidden" name="end_date" value="<?=unixtojd()?>">
							<input type="text" name="end_date_disp" readonly 
								value="<?=es(dateToHebrew(unixtojd()))?>"
								onClick="getDate(this.form, 'end_date', true);"
							/>
					</span>
					<br />
					
					<input class="submit" type="submit" name="display" value="<?=T_('Go')?>">
				</p>
			</form>
			
			<hr>
			</div>
			<?php
			} // end if from line 313 ( make sure a user is set.. )
			if ( true ) { // are we printing anything? ?>
				<div class="noprint">
				<?php
				if( !empty($admin_user) ) { // if we have a user
					if($admin_user['auth'] == 'super') { // and he has super powers ?>
						<p>
							<a href="admin_school.php"> <?=T_('Back to Institution list')?> </a>
						</p>
					<?php } // end superuser only link ?>
					<p>
						<a href="admin_class.php?school_id=<?=$school_id?>"><?=T_('Back to Platoon list')?></a>
					</p>
					<p>
						<a href="admin_user.php?school_id=<?=$school_id?>&amp;class_id=<?=$class_id?>">
							<?=T_('Back to Soldier list')?>
						</a>
					</p>
				</div>
				<?php } // end if from line 453 ?>
				<p class="noprint" style="text-align: center;">
					<input type="button" value="<?=T_('Print')?>" onClick="print();">
				</p>
			
				<form action="admin_card_print.php" method="post" accept-charset="UTF-8">
					<?php // make sure we have a superuser on our hands
					if (!empty($admin_user) && $admin_user['auth'] == 'super') { ?>
						<p class="noprint" style="text-align: center;">
							<input type="hidden" name="school_id" 	value="<?=$school_id?>"	/>
							<input type="hidden" name="class_id" 		value="<?=$class_id?>"	/>
							<input type="hidden" name="user_id" 		value="<?=$user_id?>"		/>
							<input type="hidden" name="rank_ord" 		value="<?=$rank_ord?>"	/>
							<input type="hidden" name="type" 				value="<?=$card_type?>"	/>
							<input type="submit" value="<?=T_('Mark Cards as Printed')?>"		/>
						</P>
					<?php } // end superuser only form
					// default to line 0, column 0
					$line=0; $col=0;
					
					if ($action != "") {
						while ( $row = mysql_fetch_assoc( $query1 ) ) {
							if ( !isset($_GET['withoutPic'] ) && ( !($row['user_photo_id'] > 0 ) && !$row[ 'mobile_pic' ] ) ) 
								continue;
							// end the row for the column
							if ( $col >= $cols ) {
								echo "</tr>";
								$col = 0;
							}
							// if we are on the first column
							if ( $col == 0 ) {
								if( $line >= $lines ) { // if we have hit the last line
									echo "</table><hr class='noprint'>"; // end the table
									$line = 0; $col = 0; // and restart the counter
								}
								// if we are at the beginning of the card start the next card table
								if ($line == 0) 
									echo "<table class='card_sheet'>";
								// we have moved on to the next line
								$line++;
								echo "<tr>";
							}
							// mark that we are on the next column
							$col++;
							// if we do not have a rank color....
							if ( is_null($row['rank_color'] ) ) 
								$row['rank_color'] = '#808080'; // default to dark grey
							?>
							<td class="card">
								<?php 
								if( !is_null( $row['rank_ord'] ) ) { ?>
									<input type="hidden" name="rank_marks[<?=$row['user_id']?>]" value="<?=$row['rank_ord']?>" />
								<?php } // end if the rank order is null or not ?>
							
								<input type="checkbox" name="skip[<?=$row['user_id']?>]" value="1" class="selection" style="float: left" checked />
								<div class="top" style="background-color: <?=es($row['rank_color'])?>;">
									<?=sprintf(
										T_( 'This card entitles the cardholder to TH privileges with the %s rank' ), 
										es( $row['rank_name'] )
									)?>
								</div>
							
								<div class="sig" style="border-color: <?=es($row['rank_color'])?>;">
									<?= T_('Authorized Signature') ?>
									<h1>
										<em style="color: <?=es($row['rank_color'])?>;"><?=es($row['rank_name'])?></em> 
										<?= 
											es( !empty( $row['first_he'] ) ? $row[ 'first_he' ] : $row[ 'first' ] )
											.' ' 
											.es( !empty( $row['last_he'] ) ? $row[ 'last_he' ] : $row[ 'last' ] )
										?>
									</h1>
									<?=T_('SERIAL') . ' # ' . $row['user_serial']?>
								</div>
							
								<?= // render the background image for the rank
									!is_null( $row['rank_background_image_id'] ) ? 
										linkImgFile( $row['rank_background_image_id'], NULL, NULL, 'class="bg"' ) : 
										''
								?>
								<div class="panel">
									<table cellspacing="0" cellpadding="0">
										<tr>
											<td class="logo" width="1">
												<?=!is_null( $row['school_logo_id'] ) ? linkImgFile( $row['school_logo_id'] ) : ''?>
											</td>
											<td class="base">
												<p>
													<?= T_('BASE') ?> #<?= $row['school_number'] ?> <br/>
													<strong><?= es( $row['school_name'] ) ?></strong><br/>
													<?= es( $row['school_city'] ) . ', ' . es( $row['school_state'] )?>
													&nbsp;&nbsp;&nbsp;&nbsp;
													Platoon: <?=
														es( $row['class_grade'] ) . 
														( empty( $row['class_sub'] ) ? '' : '-' . es( $row[ 'class_sub' ] ) )
													?>
												</p>
											</td>

											<!-- ***** STUDENTS PHOTO ***** -->
											<td class="photo" width="1">
												<?php
												if ( $row[ 'mobile_pic' ]) {
													echo '<img src="mobile/reg/' . $row['mobile_pic'] . '" style="border-color: ';
													echo es( $row['rank_color'] ) . '" />'; 
												} else if ($row['user_photo_id'] > 0) {
													echo linkImgFile( $row['user_photo_id'], NULL, NULL, 'style="border-color: ' . es( $row['rank_color'] ) . ';"');
												} ?>
											</td>
											<!-- ***** END STUDENTS PHOTO ***** -->
										</tr>
									</table>

									<table>
										<tr>
											<td class="code">
												<p>
													<img src="barcode.php/3<?=$row['user_code']?>" alt=""><br/>
													3<?=$row['user_code']?>
												</p>
											</td>
											
											<td class="stats">
												<p>
													<?=T_('Member since')?><br/>
													<span dir="rtl">
														<?=!empty( $row['user_start_date'] ) ? 
															dateToHebrewShortYear($row['user_start_date']) : 
															''
														?>
													</span>
												</p>
												<p>
												<?php
												if( $bdate = dateToJD( $row['dob'] ) + $row['dob_he_offset'] ) {
													$cal = cal_from_jd( $bdate, CAL_JEWISH );
													$valid = T_('Valid until') . '<br><span dir="rtl">' . dateToHebrewShortYear(
														cal_to_jd( CAL_JEWISH, 13, cal_days_in_month( CAL_JEWISH, 13, $cal['year'] + 13 ), $cal['year'] + 13 ) 
													) . '</span>';
												} else {
													$valid = '';
												}
												echo $valid;
												?>
												</p>
											</td>
										</tr>
									</table>
								</div>
							</td>
						<?php 
						} // end while we pull results from $query1
					} // end if action is not blank
					// close the last card table
					echo "</tr></table>" ?>
				</form>
			<?php
			} // end if there is a result ( line 450 ) ?>
		</div>
		
		<div class="noprint">
			<?php include('admin_footer.php'); ?>
		</div>
	</body>
	
	<script>
		$(".selection").click( function() {
			if (!$(this).is(":checked")) {
				$(this).parent().addClass('skipCard');
				$(this).val('0');
			} else {
				$(this).parent().removeClass('skipCard');
				$(this).val('1');
			}
		});
	</script>
</html>
