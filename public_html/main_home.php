<?
require_once 'class.globalSettings.php';
require_once 'calendar.php';
$chidonYear = GlobalSettings::getChidonYear();

// get campaigns for current year
$tanyaCampaign = 0;
$mishnaCampaign = 0;
$campaign_query = mysql_query( "SELECT * FROM line_campaigns WHERE year = " . $chidonYear );
while ($campaign = mysql_fetch_assoc( $campaign_query )) {
	if (strtolower($campaign['type']) == 'tanya') $tanyaCampaign = $campaign['id'];
	else if (strtolower($campaign['type']) == 'mishna') $mishnaCampaign = $campaign['id'];
}
?>
<script>
	function number(nStr) {
	    nStr += '';
	    x = nStr.split('.');
	    x1 = x[0];
	    x2 = x.length > 1 ? '.' + x[1] : '';
	    var rgx = /(\d+)(\d{3})/;
	    while (rgx.test(x1)) {
	        x1 = x1.replace(rgx, '$1' + ',' + '$2');
	    }
	    return x1 + x2;
	}
	
	$(function() {
        $(".photo a").click( function() {
            $(".upload").html( "<form action='upload_photo.php' method='post' enctype='multipart/form-data'>" +  
                    "<br />Upload Picture:<br /><input type='file' name='photo' /><br />" + 
                    "<input type='hidden' name='admin_id' value='<?=$admin_user['admin_id']?>' />" + 
                    "<input type='submit' name='submit' value='submit' />" + 
                "</form>" );
        });
        
        var del = <?=isset($_GET['deletedParents']) ? $_GET['deletedParents'] : 0?>;
        if (del) {
        	alert("Parent Accounts with no children association have been deleted!");
        }
		
		var tanyaCampaign = <?=$tanyaCampaign?>;
		var mishnaCampaign = <?=$mishnaCampaign?>;
        
        $.post('ajax/getLines.php', {
        	id : tanyaCampaign, 
        	type : 'pledged', 
        	school : <?=$school_id?>        	
        }, function( data ) {
        	$("#lines").val(data);
        });
        
        $.post('ajax/getLines.php', {
        	id : mishnaCampaign, 
        	type : 'pledged', 
        	school : <?=$school_id?>        	
        }, function( data ) {
        	$("#mLines").val(data);
        });
        
        $.post('ajax/getLines.php', {
        	id : tanyaCampaign, 
        	type : 'learned', 
        	school : <?=$school_id?>        	
        }, function( data ) {
        	$("#learned").val(data);
        });
        
        $.post('ajax/getLines.php', {
        	id : mishnaCampaign, 
        	type : 'learned', 
        	school : <?=$school_id?>        	
        }, function( data ) {
        	$("#mLearned").val(data);
        });
        
        //get last year's lines learned
        $.post('ajax/getLines.php', {
        	id : tanyaCampaign - 2, 
        	type : 'learned', 
        	school : <?=$school_id?>        	
        }, function( data ) {
			if (data == '') data = 0;
        	$("#linesPrevious").text(number(data));
        });
        
        $.post('ajax/getLines.php', {
        	id : mishnaCampaign - 2, 
        	type : 'learned', 
        	school : <?=$school_id?>
        }, function( data ) {
			if (data == '') data = 0;
        	$("#mLinesPrevious").text(number(data));
        });
        
        $("#lines").keyup( function(event) {
			if (event.keyCode == 9) {return false;} // do not run if the key is a TAB
        	var num = $(this).val().trim();
			if (num == '') num = 0;
        	if (isNaN(num)) {
        		alert("You must enter a number.");
        		return;
        	}
        	$.post('ajax/updateBalPehCampaign.php', {
        		id : tanyaCampaign, 
        		val : num, 
        		school : <?=$school_id?>, 
        		table : 'lines_pledged'
        	}, function( data ) {
        		if (data == 1) {
        			//alert("Updated.");
        		} else {
        			alert("Error updating.");
        		}
        	});
        });
        
        $("#mLines").keyup( function(event) {
			if (event.keyCode == 9) {return false;} // do not run if the key is a TAB
        	var num = $(this).val().trim();
			if (num == '') num = 0;
        	if (isNaN(num)) {
        		alert("You must enter a number.");
        		return;
        	}
        	$.post('ajax/updateBalPehCampaign.php', {
        		id : mishnaCampaign, 
        		val : num, 
        		school : <?=$school_id?>, 
        		table : 'lines_pledged'
        	}, function( data ) {
        		if (data == 1) {
        			//alert("Updated.");
        		} else {
        			alert("Error updating.");
        		}
        	});
        });
	});
</script>

<style>
	.updates {
		line-height: 1.6;	
	}
	.updates h2 {
		font-size: 20px;
		background-color: #3d7ccf;
		text-shadow:0 1px 0 #000;
		text-align: center;
		color: white;
		padding: 1px;
		margin-top: 0;
	}
	.updates h3 {
		font-size: 20px;
	}
	.updates h4 {
		font-size: 16px;
	}
	.updates li {
		font-size: 13px;
	}
	.updates p {
		font-size: 13px;
	}
	.updates img {
		float: left;
		height: 100px;
		padding-right: 20px;
		padding-bottom: 20px;
		border: 0;
	}
	.updates img.ad {
		height: 200px;
	}
	.updates div {
		clear: left;
		padding-top: 10px;
		padding-bottom: 10px;
	}
	.updates div:nth-child(odd) {
		background-color: #F2F2F2;
	}
	.updates div .inner {
		clear: none;
		overflow: hidden;
		padding: 10px;
	}
	.updates div.resources {
		background-color: #D5D8DE;
	}
	.updates .resources span:first-child {
		margin-left: 5%;
	}
	.updates .resources span {
		font-size: 10px;
		text-align: center;
		float: left;
		margin: auto;
		padding-right: 14px;
	}
	.updates .resources img {
		height: 100px;
		padding: 0;
	}
	.baseInfo > img {
		float: right;
		border: 5px solid black;
	}
	.middle .line {
		margin-top: 30px;
		height: 6px;
		background-color: #3d7ccf;
	}
	.button {
		float: left;
		margin: 26px;
		text-align: center;
	} 
	.button img {
		height: 100px;
	}
	.button a {
		font-size: 12px;
	}
	.schoolLogo {
		float: left;
		margin-right: 30px;
	}
	.schoolLogo img {
		height: 120px;
	}
	#thermometer {
		float: right;
		margin-right: -150px;
		margin-left: -100px;
	}
	#thermometer p {
		font-size: 14px;
		font-weight: bold;
		width: 200px;
		text-align: right;
		margin-left: 40px;
		color: red;
		line-height: 1.4;
	}

	#rank-promotions {
		height: 300px;
		padding: 15px;
		text-align: center;
		overflow: auto;
		background: #F2F2F2;
		margin-bottom: 25px;
	}
	#rank-promotions table { width: 100%; }
	#rank-promotions table tbody tr { border-top: 1px solid #333; }
	#rank-promotions th, #rank-promotions td { font-size: 1.2em; padding: 8px 4px; }
	#rank-promotions td:first-child { width: 25%; }
	#rank-promotions td { width: 50%; }
	#rank-promotions td:last-child { width: 25%; }
</style>

<? if (!in_array($schl_id, $tanyaOnlySchools)) : ?>

<div class="baseInfo">
	<? 
    $p = "select photo from admins where admin_id = " . $admin_user['admin_id'];
    $res = mysql_query($p);
    $pRow = mysql_fetch_assoc($res);
    $photo = $pRow['photo'];
    if (!empty($photo)) { 
        $size = getimagesize("images/staff/$photo"); 
        $width = $size[0];
        $height = $size[1];
        if ($width > 100) {
            if ($width > 200) {
                if ($width > 400) {
                    $width = 0.25 * $width;
                    $height = 0.25 * $height;
                } else {
                    $width = 0.5 * $width;
                    $height = 0.5 * $height;
                }
            } else {
                $width = 0.75 * $width;
                $height = 0.75 * $height;
            } 
        }
        echo "<img src='images/staff/$photo' width='$width' height='$height' />";
	}
	?>

	<div class='schoolLogo'>
		<img src="schoolLogos/1.gif" />
		<?php
		if (!empty($row['school_logo_id']) && empty($row['logo'])) {
			echo "<img src='file_view.php?id=" . $row['school_logo_id'] . "' />";
		} else {
			echo "<img src='schoolLogos/" . (empty($row['logo']) ? 'TH-Blank Logo.gif' : $row['logo']) . "' />";
		}
		?>
		<img src="schoolLogos/2.gif" />
	</div>
	
	<p>
		<b>Base:</b> <?=$row['school_name']?><br />
		<? if ($admin->first != "") { ?>
			<b>Commanding Officer:</b> <?=$admin->first . ' ' . $admin->last?><br />
		<? } ?>
		<? if ($row['school_address1'] != "") { ?>
			<?=es($row['school_address1'])?><BR>
			<?=es($row['school_address2'])?><?=$row['school_address2'] ? '<BR>' : ''?>
			<?=es($row['school_city'])?> <?=es($row['school_state'])?>, <?=es($row['school_postal'])?><BR>
			<?=es($row['school_country'])?><?=$row['school_country'] ? '<BR>' : ''?>
			<?=es($row['school_phone'])?><?=$row['school_phone'] ? '<BR>' : ''?>  
		<? } ?>
	</p>
	<div style='clear: both'></div>
</div>
<div class="middle">
	<div class="line"></div>
	<div class="buttons"> 
		<div class="button">
			<a href="print_missions2.php">
				<img src="images/parentIcons/Printer.gif" />
				<br />Print Missions
			</a>
		</div>
		
		<div class="button">
			<a href="mark_missions2.php">
				<img src="images/parentIcons/Checkbox.gif" />
				<br />Mark Missions
			</a>
		</div>
	</div>
	<div style='clear: both'></div>
</div>

<div class="updates">
	<h2>Tzivos Hashem Updates</h2>
	
	<?
    $regYear = GlobalSettings::getRegistrationYear();

    $qry = "SELECT COUNT(user_id) AS total FROM user_registration "
        ."WHERE school_id = $school_id AND year = $regYear;";
	$resultQ = mysql_query($qry);
	$rowQ = mysql_fetch_assoc($resultQ);
	$registered = $rowQ['total'];
	
    $qry2 = "SELECT COUNT(user_id) AS total FROM users u "
        ."LEFT JOIN user_registration USING (user_id) "
        ."WHERE u.school_id = $school_id AND ( year = $regYear OR year is null );";
	$resultQ2 = mysql_query($qry2);
	$rowQ2 = mysql_fetch_assoc($resultQ2);
    $notRegistered = $rowQ2['total'];
    
    $school_registered_query = mysql_query(
        "SELECT COUNT(*) as total, date_paid FROM school_registrations WHERE school_id = $school_id AND year = $regYear;"
    );
    $school_registered_query = mysql_fetch_assoc( $school_registered_query );
    $school_registered = $school_registered_query['date_paid'];
    if ( $school_registered_query['total'] > 0 ) { ?>
        <div>
            <div class="inner">
                <h3>Registration <?=$regYear?></h3>
                <? if ( $school_registered ) { ?>
                    <p>
                        You have <?=$registered?> chayolim registered in the program for <?=$regYear?>.<br />
                        <? if ($notRegistered > 0) : ?>
                        <span style="color: red; font-weight: bold;">
                            You still have <?=$notRegistered?> chayolim that are not yet registered for <?=$regYear?>!<br />
                            <!-- Click <a href="admin_users_register.php?school_id=<?=$school_id?>&registered=1">here</a> to register them!</span> -->
                        </span>
                        <? endif; ?>
                    </p>
                <? } else { ?>
                    <h4>Pre-register your base for <?=$regYear?> <a href='registration.php'>here</a>.</h4>
                <? } ?>
                <!--Click <a href="child_list.php">here</a> for the list of parent accounts with linked children.-->
            </div>
        </div>
    <?php } ?>
	<!--
	<div>
		<div class="inner">
			<h3>End of Year Hakhel Auction</h3>
			<p>
				Click <a href="assign_tickets3.php?school=<?=$schl_id?>">here</a> to print auction tickets forms for entire school.<br />
				Click <a href="assign_tickets4.php?school=<?=$schl_id?>">here</a> to print auction tickets forms with pictures for entire school.<br />
				Click <a href="assign_tickets2.php">here</a> to print and enter auction tickets per child (condensed).<br />
				Click <a href="assign_tickets.php">here</a> to print and enter auction tickets per child (expanded with images).<br />
			</p>
		</div>
	</div>
	
	<div>
		<div class="inner">
			<h3>Tzivos Hashem Mobile Site</h3>
			Click <a href="th_accounts_children.php">here</a> for the letter to parents
			<br /><br />
		</div>
	</div>
		
	<?
	$qry = "select count(user_id) as total from users where school_id = $school_id and user_registered > 0";
	$resultQ = mysql_query($qry);
	$rowQ = mysql_fetch_assoc($resultQ);
	$registered = $rowQ['total'];
	
	$qry2 = "select count(user_id) as total from users where school_id = $school_id and user_registered is null";
	$resultQ2 = mysql_query($qry2);
	$rowQ2 = mysql_fetch_assoc($resultQ2);
	$notRegistered = $rowQ2['total'];
	?>
	
	<div>
		<div class="inner">
			<h3>Registered Chayolim</h3>
			<p>Click <a href="missing_account_report.php">here</a> to see a list of which students are not associated with a parent account.</p>
			<p>
				You have <?=$registered?> chayolim registered in the program.<br />
				<? if ($notRegistered > 0) : ?>
				<span style="color: red; font-weight: bold;">
					You still have <?=$notRegistered?> chayolim that are not yet registered!<br />
					Click <a href="admin_users_register.php?registered=1">here</a> to register them!</span>
				</span>
				<? endif; ?>
				<br />
				Click <a href="students5775.php">here</a> to see your list of students from last year.
			</p>
		</div>
	</div>
	
	<div>
		<div class="inner">
			<h3>Daily Chitas</h3>
			<p>
				Click <a href="http://Kidschitas.org/today">here</a> for the daily chitas summary for your chayolim.
			</p>
		</div>
	</div>
	-->
<? else : ?>
<div class='schoolLogo'>
	<img src="schoolLogos/1.gif" />
	<img src="schoolLogos/<?=empty($row['logo']) ? 'TH-Blank Logo.gif' : $row['logo']?>" />
	<img src="schoolLogos/2.gif" />
</div>

<p>
	<b>Base:</b> <?=$row['school_name']?><br />
	<b>Commanding Officer:</b> <?=$admin->first . ' ' . $admin->last?><br />
    <?=es($row['school_address1'])?><BR>
    <?=es($row['school_address2'])?><?=$row['school_address2'] ? '<BR>' : ''?>
    <?=es($row['school_city'])?> <?=es($row['school_state'])?>, <?=es($row['school_postal'])?><BR>
    <?=es($row['school_country'])?><?=$row['school_country'] ? '<BR>' : ''?>
    <?=es($row['school_phone'])?><?=$row['school_phone'] ? '<BR>' : ''?>        
</p>
<div style='clear: both'></div>
<? endif; ?>

	<? //if (false) : ?>
	<? //if (isset($_COOKIE['naftoli'])) : ?>
	<!--
	<div>
		<img src="homeIcons/Present.gif" />
		<div class="inner">
			<h3>Our Birthday Gift to the Rebbe</h3>
			<h4>Last year you learned <span id="linesPrevious"></span> lines of Tanya and <span id="mLinesPrevious"></span> lines of Mishna.</h4>
			<h4>Please enter your base Tanya and Mishna commitments for this year.</h4>
			<p>In honor of the Rebbe's Birthday our base is <b>committing to learn</b>:<br />
				<input type="text" name="lines" id="lines" size="6" /> Lines of Tanya Baal Peh<br />
				<input type="text" name="lines" id="mLines" size="6" /> Lines of Mishna Baal Peh<br />
				<br />Our base has <b>actually learned</b>:<br />
				<input type="text" name="learned" id="learned" size="6" disabled /> Lines of Tanya Baal Peh<br />
				<input type="text" name="mLearned" id="mLearned" size="6" disabled /> Lines of Mishna Baal Peh<br />
				<br />
				<? if (!in_array($schl_id, $tanyaOnlySchools)) : ?>
				Click <a href="editSoldierLines2.php">here</a> to change lines learned for individuals.<br />				
				<? endif; ?>
				<!--Click <a href="editPlatoonLines2.php">here</a> to change lines learned for classes.<br />-->
				<!--
				Click <a href="yud_alef_nissan_choose.php">here</a> for reports.<br />
				<br />Our base is pledging $<input type="text" name="maos" id="maos" size="4" /> for Mo'os Chitim<br />
				<li>Click <a href="yud_alef_nissan_choose.php">here</a> for the army / base / platoon reports</li>
				<li>Click <a href="maos_chitim_cards.php">here</a> for the mo'os chitim pledge cards</li>
				<li>Click <a href="maos_chitim_form.php">here</a> for the mo'os chitim form</li>
				<li>Click <a href="https://www.dropbox.com/sh/dy2spa1ucklqhf1/jltNQKOkAm">here</a> for the mo'os chitim resources</li>
				-->
			</p>
			<!--
			<p>
				Click <a href="order_form.php">here</a> to order posters.
			</p>
			-->
			<!--
		</div>
	</div>
	-->
	<? //endif; ?>
	
	<!--
	<div>
    	<img src="homeIcons/Global Rally.jpg" />
    	<div class="inner"> 
			<h3>Chanuka Rally</h3>
			<li><a href="https://vimeo.com/148420501">Rally - with countdown - Vimeo</a></li>
		</div>
	</div>
	-->
<? if (!in_array($schl_id, $tanyaOnlySchools)) : ?>

	<?
	$today = unixtojd();
	require 'class.report.php';
	$r = new Report();
	$dates = $r->dates;
	$num = count($dates);
	while (--$num >= 0) {
		if ($today >= $dates[$num] && $today < ($dates[$num] + 20)) {
			?>
			<!--
			<div>
		    	<img src="homeIcons/Global Rally.jpg" />
		    	<div class="inner"> 
					<h3>Yud Shvat Rally</h3>
					<p>
						Click <a href="promotion_report.php">here</a> for promotion picture report.
					</p>
					
					<h4>Rally Links:</h4>
					<p>Password for links is "play".</p>
					<li><a href="https://vimeo.com/156353649">Rally - with countdown - Vimeo</a></li>
					<li><a href="http://we.tl/PVmpoaohDj">Download Rally</a></li>
					<!--
					<li><a href="http://we.tl/uBIMfMmpLz">Cheshvon Rally - with countdown - Download</a></li>
					<br />
					
					<h3>Prepare for Rally</h3>
					<li>Schedule a time in your school for the rally.</li>
					<li>Download Rally Poster <a href="https://www.dropbox.com/s/dy7gmzbniye12gc/Rally%20Poster%20Chof%20Cheshvan%205776.pdf?dl=0">here</a> and plaster around school.</li>
					<li>Download Sicha <a href="https://www.dropbox.com/sh/qycese7wxny1rhx/AACFHpw2SJy2MttNco2uRauPa?dl=0">here</a> and make sure the teachers teach it to the students.</li>
					<li>Prepare coins of 10&cent; for Tzedakah.</li>
					<li>Print out achievement cards:</li>
					<p style="margin-left: 20px;">
						1. On homepage go to mileage program.<br />
						2. Create campaign (can be called rally) and save.<br />
						3. Click tasks and create a task (can be called "Rocking the Rally").<br />
						4. Go to point cards (back on the left) and click that campaign and task.<br />
						5. Decide how many points (we recommend 5 points), and how many pages 
						(note: you can not make copies of these because they each need a diff. bar card), 
						and click print!
					</p>
					<li>Download Rally Niggun Sheet & Audio here.</li>
					<li>Print out <a href="https://www.dropbox.com/s/ddlx97umrt5c01e/letter%20from%20principal%20and%20prep%20sheet%20for%20rally.docx?dl=0">letter</a>
						 to give teachers before rally.</li>					
					<!--
					<li>Click <a href="downloads/gt/Gimmel Tammuz Sicha - Subtitles.mp4">here</a> for the rally Sicha</li>
					<li>Click <a href="downloads/gt/Tzoma Lecha Nafshi.wmv">here</a> for the Tzomo Licha Nafshi Niggun</li>
					<li>Click <a href="downloads/gt/With a Tehillim.mp4">here</a> for the With a Tehillim Niggum</li>
					<li>Click <a href="downloads/gt/We Want Moshiach Now.mp4">here</a> for the We Want Mashiach Now Niggun</li>
					<li>Click <a href="downloads/gt/Gimmul Tammuz Sicha with English.doc">here</a> for the Sicha Word Doc</li>
					<li>Click <a href="downloads/Grand Auction Winners.mp4">here</a> for the Grand Auction Winners</li>
					<p><b>If clicking on the link does not download, please right click link and choose "save link as..."</b></p>
					
				</div>
			</div>
			-->
			<?
			break;
		}
	} 
	?>
	<!--
	<div>
		<div class="inner">
			<h3>Hebrew Names for Pushka</h3>
			<p>
				Click <a href="pushka_grade_report.php">here</a> to print the soldier's names by grade and give them out to your 
				teachers to check over.<br />
				Click <a href="pushka_soldier_report.php">here</a> to print the soldier's names individually to give out to the parents.<br />
				Click <a href="namesForPushka.php">here</a> to update your chayol's and staff's names for the pushka.<br />
				Click <a href="downloads/CTH - Registration Poster 5776.pdf">here</a> for brochure.
			</p>
		</div>
	</div>
	<!--
	<div>
		<img src="homeIcons/Rally.gif" /> 
		<div class="inner">
			<h3>Chof Vov Elul Rally</h3>
			
			<p>
				Rally links:
				<li><a href="http://we.tl/a5tIw2xHX4">WeTransfer</a></li>
				<li><a href="https://vimeo.com/138821610">Vimeo</a> - with countdown</li>
				<li><a href="https://vimeo.com/138872960">Vimeo</a> - without countdown</li>
			</p>
			<!--
			<p>
				Pictures must be uploaded by Sunday Chof Beis Elul.<br />
				Click <a href="promotion_report.php">here</a> for the report. <br />
				Upload promotion pictures <a href="https://www.dropbox.com/home/1.%20Chof%20Ches%20Elul/Promotion%20Photos">here</a>.<br />
				Username: rallypromotionpictures@gmail.com<br />
				Password: cthrallypromotions<br />
			</p>
			-->
			<!--
			<li>Click <a href="https://www.dropbox.com/s/4ao5q0qwrd6tmvq/Purim%20Poster.pdf?dl=0">here</a> for rally poster</li>
			<li>Click <a href="https://www.dropbox.com/s/94sbkoj6fz1z2ox/Purim%20Rally%20Sicha.pdf?dl=0">here</a> to download the rally sicha</li>
			<li>Click <a href="https://www.dropbox.com/sh/dsly8pgzp3jajxs/AAAAbab2rhCcWHbyTsVsaKloa?dl=0">here</a> for the resources on the Posuk of Yismach</li>
			<li>Click <a href="https://vimeo.com/86615168">here</a> for the medal and rank ceremony video.</li>
			<li>Click <a href="medal_rank_ceremony.php">here</a> for the medal and rank ceremony report.</li>
			
			<li>Click <a href="http://we.tl/bKJG3PUiDl">here</a> to download rally video.</li>
			<li>Click <a href="https://vimeo.com/121108451">here</a> to watch rally video on vimeo (with countdown).</li>
			<li>Click <a href="https://vimeo.com/121109578">here</a> to watch rally video on vimeo (without countdown).</li>
			
		</div>
	</div> 
	
	
	<div>
		<div class="inner">
			<h3>Hakhel Daily Video</h3>
			
			<?
			$first = 2457274;
			$current = unixtojd();
			$i = $current - $first;
			?>
			
			<p>
				Click <a href="hakhel/AUD-20150902-WA0002.m4a">here</a> for intro to video.<br />
				Click <a href="hakhel.php?end=<?=$i?>">here</a> for link to videos.
			</p>			
			
			<? //for ($i = 1; $i < 20; $i++) : ?>
				<!--
				<h4>Day <?=$i?></h4>
				<p>
					Click <a href="hakhel/Part <?=$i?> HD.mp4">here</a> for video.
					<br />Click <a href="hakhel/Phone Translation <?=$i?>.png">here</a> for text/translation of sicha.
				</p>
							
			<? //endfor; ?>
			
		</div>
	</div>
	
	<div>
	    <div class="inner">
	    	<h3>Setup Guide</h3>
			<li>Click <a href="admin_setup_guide.php">here</a> for Setup Guide</li>
			<br />
		</div>
	</div>
	-->
	<!-- <div>
    	<img src="homeIcons/Commander Meeting.gif" />
	    <div class="inner">
	    	<h3>Iyar Meeting</h3>
			<li>Click <a href="https://www.anymeeting.com/WebConference/RecordingDefault.aspx?c_psrid=E957DD8787493C">here</a> for Iyar Recording</li>
			<li>Click <a href="https://docs.google.com/document/d/16qs4MYpHFzoVhHnY-vYR1Eg-iU1cu4uRzLdfbIGuwTE/edit?usp=sharing">here</a> for Iyar Notes</li>
			<li>Click <a href="https://drive.google.com/drive/folders/0B0VZvvLwWxVhQ1pIMVJhc2t4Nkk?usp=sharing">here</a> for the Base Commander Manuals</li>
		</div>
	</div> -->
	<!--
	<div>
		<div class="inner">
			<h3>School Setup</h3>
			<p>
				Click <a href="admin_setup_guide.php">here</a> for setup guide!
			</p>
		</div>
	</div>
	
	<div>
		<div class="inner">
			<h3>Registration 5776</h3>
			<p>
				Click <a href="admin_users_register.php">here</a> to register your chayolim!
			</p>
			<p>
				Click <a href="https://www.dropbox.com/sh/7sy6vd4ocywp6kb/AACPLjmtZGckS7nTSdGh1iSRa/CTH%20-%20Registration%20Brochure%205776%20single%20pages%20LR.pdf?dl=0">here</a> 
				for Registration Brochure.<br />
				Click <a href="https://www.dropbox.com/sh/7sy6vd4ocywp6kb/AAAjshP-GYps4KxfDEllUtzia/CTH%20-%20Registration%20Form%205776%20HR%20cropped.pdf?dl=0">here</a> 
				for Registration Form.<br />
				Click <a href="https://www.dropbox.com/sh/7sy6vd4ocywp6kb/AAAfNcIeLjaEgwdgXQfvCWxsa/CTH%20-%20Registration%20Poster%205776%2011x17%20Low%20res.pdf?dl=0">here</a> 
				for Registration Poster.
			</p>
		</div>
	</div>
	
	<div>
		<img src="webAds\Auction.jpg" class="ad" />
		<div class="inner">
			<h3>End-of-year auction</h3>
			<p>
			   View the winners <a href="auction/winners.php">here</a>!
			</p>
			<p>
				View list of student prize tickets <a href="user_prizes.php">here</a>!
			</p>
			<br />
			<br />
			<br />
		</div>
	</div>

	<div>
		<img src="webAds\Rebbe-Story-Ad.jpg" class="ad" />
		<div class="inner">
			<h3>Rebbe Stories Contest</h3>
			<p>
				One week left to enter!<br />
				Click <a href="https://docs.google.com/forms/d/1RCZaIZSYc-KYtr8703ltH4WavoPZ5zY9kvBZofoQkpU/viewform?usp=send_form">here</a> to submit your stories.
			</p>
			<br />
			<br />
			<br />
			<br />
		</div>
	</div>
	
	<div>
		<img src="homeIcons/Rally.gif" /> 
		<div class="inner">
			<h3>Shavuos Rally 5775</h3>
			<p>To View on Vimeo click <a href="https://vimeo.com/128438851">here</a></p>
			<p>
				Click on the link to download:<br />
				<a href="http://we.tl/e9DHMFOPvl">Sivan 5775 Rally • (with countdown) 57:43 930MB</a><br />
				<a href="http://we.tl/UQvc0C4cJ9">Sivan 5775 Rally • without Rally Highlights • (with countdown) 48:47<br />
			</p>
		</div>
	</div>
	
	<div>
		<h3>Bonus video!</h3>
		<h4>My Special Moment with the Rebbe</h4>
		<li>Click <a href="https://youtu.be/cRiSvN0Om60">here</a> for the link</li>
	</div>
	<!--
	<div>
		<img src="homeIcons/Rally.gif" /> 
		<div class="inner">
			<h3>Nissan Rally</h3>
			<p>Click <a href="https://www.dropbox.com/sh/z6n251v3vmq2rmm/AAD63bJS_KHhw4bpkC6Z_3RSa?dl=0">here</a> for rally resources<br />
			Click <a href="medal_rank_ceremony.php">here</a> for Medal and Rank Ceremony</p>
			<p>
				Rally Segments:<br />
				<a href="downloads/yanRally/1. Countdown.mp4">Countdown</a><br />
				<a href="downloads/yanRally/2. Tzomo Lcho Nafshi.mp4">Tzoma Lecha Nafshi Niggun</a><br />
				<a href="downloads/yanRally/3. Promotions.mp4">Promotions</a><br />
				<a href="downloads/yanRally/4. We Want Moshiach Now.mp4">We Want Moshiach Now</a><br />
				<a href="downloads/yanRally/5. Sicha.mp4">Sicha</a><br />
				<a href="downloads/yanRally/6. Gift to the Rebbe.mp4">Gift to the Rebbe</a><br /> 
				<a href="downloads/yanRally/7. Winners.mp4">Winners</a><br />
				<a href="downloads/yanRally/9. Credits.mp4">Credits</a>				
			</p>
			<!--
			<p>Begin taking promotion pictures Friday, Chof Bais Adar/March 13th. <br />
			Click <a href="promotion_report.php">here</a> for the report. <br />
			Upload promotion pictures <a href="https://www.dropbox.com/sh/w5hywn59wfyycr4/AADCcoYtbOMmpQyy9OMDa7gDa?dl=0">here</a>.<br />
			Username: rallypromotionpictures@gmail.com<br />
			Password: cthrallypromotions</p>
			<li>Click <a href="https://www.dropbox.com/s/4ao5q0qwrd6tmvq/Purim%20Poster.pdf?dl=0">here</a> for rally poster</li>
			<li>Click <a href="https://www.dropbox.com/s/94sbkoj6fz1z2ox/Purim%20Rally%20Sicha.pdf?dl=0">here</a> to download the rally sicha</li>
			<li>Click <a href="https://www.dropbox.com/sh/dsly8pgzp3jajxs/AAAAbab2rhCcWHbyTsVsaKloa?dl=0">here</a> for the resources on the Posuk of Yismach</li>
			<li>Click <a href="https://vimeo.com/86615168">here</a> for the medal and rank ceremony video.</li>
			<li>Click <a href="medal_rank_ceremony.php">here</a> for the medal and rank ceremony report.</li>
			
			<li>Click <a href="http://we.tl/bKJG3PUiDl">here</a> to download rally video.</li>
			<li>Click <a href="https://vimeo.com/121108451">here</a> to watch rally video on vimeo (with countdown).</li>
			<li>Click <a href="https://vimeo.com/121109578">here</a> to watch rally video on vimeo (without countdown).</li>
			
		</div>
	</div> 
	<!--
	<div id="thermometer">
		<p>Help us reach our goal of 5,000 registered chayolim for the month of Shevat!</p>
	    <canvas id="demo" height="450" width="400"></canvas>
	</div>
	-->
	<div>
		<img src="homeIcons/WWTC Logo.gif" />
		<div class="inner">
			<h3>Shabbos Mevorchim Competition</h3>
			<h4><a href="http://mashpia.com/shabbos_mevorchim_hq.php">Click Here</a> for the Army-wide Competition Report</h4>
			<div id="liveStats" style="display: none"></div>
		</div>
	</div>
	
	<script>
		var school = <?=$schl_id?>;
		$.post('ajax/getSchoolStats.php', { school : school }, function( data ) {
			if (data) {
				$("#liveStats").html(data);
				$("#liveStats").show();
			}
		});
	</script>
	<!--
	<div> 
    	<img src="" />
	    <div class="inner">
			<h3>Mivtza Tzivos Hashem</h3>
			<h4>You have <?//=$totalSchoolReg?> registered chayolim in your school.</h4>
			<h4>You can register another <?//=$totalSchool - $totalSchoolReg?> chayolim.</h4>
			<li>Click <a href="non_registered_report.php">here</a> for non registered report</li>
			<li>Click <a href="mission_sheets_checklist.php">here</a> for Teacher Mission Report</li>
			<li>Click <a href="downloads/entry-form 5775 D2.pdf">here</a> for the submission form</li>
			<li>Click <a href="downloads/Letter for BC.pdf">here</a> for an explanation of this campaign</li>
		</div>
	</div>
	
	<? //if (isset($_COOKIE['naftoli'])) : ?>
	<div>
		<img src="homeIcons/Present.gif" />
		<div class="inner">
			<h3>Our Birthday Gift to the Rebbe</h3>
			<h4>Please enter your base Tanya and Mishna commitments</h4>
			<p>In honor of the Rebbe's Birthday our base is <b>committing to learn</b>:<br />
				<input type="text" name="lines" id="lines" size="6" /> Lines of Tanya Baal Peh<br />
				<input type="text" name="lines" id="mLines" size="6" /> Lines of Mishna Baal Peh<br />
				<br />Our base has <b>actually learned</b>:<br />
				<input type="text" name="learned" id="learned" size="6" /> Lines of Tanya Baal Peh<br />
				<input type="text" name="mLearned" id="mLearned" size="6" /> Lines of Mishna Baal Peh<br />
				Click <a href="editSoldierLines.php">here</a> to change lines learned for individuals.<br />
				<p>
					Click <a href="yud_alef_nissan_choose.php">here</a> for reports.<br />
					<!--Click <a href="editPlatoonLines.php">here</a> to change platoon quotas.<br />
				</p>
				<!--
				<br />Our base is pledging $<input type="text" name="maos" id="maos" size="4" /> for Mo'os Chitim<br />
				<li>Click <a href="yud_alef_nissan_choose.php">here</a> for the army / base / platoon reports</li>
				<li>Click <a href="maos_chitim_cards.php">here</a> for the mo'os chitim pledge cards</li>
				<li>Click <a href="maos_chitim_form.php">here</a> for the mo'os chitim form</li>
				<li>Click <a href="https://www.dropbox.com/sh/dy2spa1ucklqhf1/jltNQKOkAm">here</a> for the mo'os chitim resources</li>
				
			</p>
			<p>
				Click <a href="order_form.php">here</a> to order posters.
			</p>
		</div>
	</div>
	
	<div>
		<img src="images/parentIcons/Printer.gif" />
		<div class="inner">
			<h3>Pesach Missions</h3>
			<p>Click <a href="print_missionsYT.php">here</a> to print Pesach Missions.</p>
			<br />
		</div>
	</div>
	-->
	
	<!--
	<div>
		<img src="" />
		<h3>Hiskashrus Essay Contest</h3>
		<li>Click <a href="https://www.dropbox.com/s/ovxq7ruhtytbyuh/CTH%20-%20Hiskashrus%20Essay%20Contest%20Teves%20Poster%205775%20HR.pdf?dl=0">here</a> for the contest poster</li>
		<li>Click <a href="https://www.dropbox.com/s/x0fjp0gxyh70xzq/BC%20Letter.pdf?dl=0">here</a> for an explanation of the contest</li>
		<br />
	</div>
	<!--
	<div>
		<img src="" />
		<div class="inner">
			<h3>Resources</h3>
			<h4>Take advantage of all our resources for Kislev Yomei Depagra!</h4>
			<li>View them all <a href="https://www.dropbox.com/sh/7sdgrc3gjwcmyiy/AAB9Qh2dh-GrujIhKdVNTJ5ua?dl=0">here</a></li>
			<br />
		</div>
	</div>
	
	<div>
    	<img src="" />
	    	<div class="inner">
			<h3>Shabbos Mevorchim Teves</h3>
			<h4>Remember to hang up the <a href="choose_sm_report.php">Shabbos Mevorchim Reports</a> on Tuesday!</h4>
			<br /><br />
		</div>
	</div>
	
	<div>
    	<img src="homeIcons/Rally.gif" />
    	<div class="inner">
    		<h3>Chanuka Rally</h3>
    		<li>Click <a href="https://www.dropbox.com/s/esx3e389tlprjkm/Rally%20Sicha-%20Chanuka.pdf?dl=0">here</a> for the rally sicha</li>
			<li>Click <a href="https://www.dropbox.com/s/31cwsv77owjhv5u/Rally%20Poster-%20Chanuka.pdf?dl=0">here</a> for the rally poster</li>
			<li>Click <a href="https://www.dropbox.com/sh/p97pstmmk4macjy/AABOjG6Eu_XXW7ANHC6sAcOZa?dl=0">here</a> for all the rally resources</li>
    		<li>Click <a href="http://we.tl/BYJuoodZ62">here</a> to download rally</li>
    		<li>Click <a href="https://vimeo.com/114925269">here</a> to view rally on Vimeo</li>
		</div>
	</div>
	
	<div>
    	<img src="" />
	    <div class="inner">
			<h3>Setup Your Base</h3>
			<li>Click <a href="admin_setup_guide.php">here</a> to have your base up and running!</li>
		</div>
	</div>
	
	<div>
    	<img src="homeIcons/Present.gif" />
	    <div class="inner">
			<h3>Rebbe's Sicha</h3>
			<li>Click <a href="downloads/Tzivos Hashem sichos week 1.pdf">here</a> to  download a Sicha from the Rebbe on Tzivos Hashem.</li>
			<br />
			<br />
		</div>
	</div>
	
	<div>
		<img src="homeIcons/Parent Account.gif" />
		<div class="inner">
			<h3>Parent Tutorial</h3>
			<li>Click <a href="downloads/How the Army works.avi">here</a> to download a tutorial to send out to parents on Tzivos Hashem.</li>
			<br />
			<br />
		</div>
	</div>
	<!--
	<div>
		<img src="" />
		<div class="inner">
			<h3>Summer Missions</h3>
			<p>Make sure to mark all summer missions before your students are un-registered on Friday, Yud Zayin Elul.</p>
			<li>Click <a href="date_tasks_report_new.php">here</a> to mark summer missions</li>
		</div>
	</div>
	
	<div>
    	<img src="" />
	    <div class="inner">
			<h3>Order more Registration Brochures and Posters</h3>
			<li>Click <a href="order_form.php">here</a> to order more registration brochures and posters</li>
		</div>
	</div>
	
	<div>
		<img src="homeIcons/Chidon.gif" />
		<div class="inner">
			<h3>Chidon</h3>
			<h4>Remember to register your school for the chidon!</h4>
			<li>Click <a href="http://chidon613.com">here</a> to register your school</li>
			<p>&nbsp;</p>
		</div>
	</div>
	
	<div>
		<img src="homeIcons/Parent Account.gif" />
		<div class="inner">
			<h3>Parent Accounts</h3>
			<h4>Make sure all Parents in your school have parent accounts</h4>
			<li>Click <a href="parent_children_barcodes.php">here</a> for Parent Invitations</li>
			<li>Click <a href="parent_list.php">here</a> to view parent accounts</li>
		</div>
	</div>
	-->

	<div style='clear: both; background-color: #D5D8DE'></div>
	
	<h2>Tzivos Hashem Resources</h2>
	
	<div class="resources">
		<span>
			<a href="https://www.dropbox.com/sh/c2g76cp76it1bf6/AABw7AHHEKWfahv-yIFXV8Qsa?dl=0" target='_blank'>
			<img src="homeIcons/Hachayol Header.gif" /><br />
				5779
			</a>
		</span>
		
		<span>
			<a href="https://www.dropbox.com/s/9h5k3bqrvm1qrjr/CTH%20-%20BC%20Calendar%205779%20with%20bleed%20HR.pdf?dl=0" target='_blank'>
				<img src="homeIcons/Calendar.gif" /><br />
				Commander's Calendar
			</a>			
		</span>
		<span>
			<a href="https://www.dropbox.com/sh/41u2regs73kfp9h/AACJV58J9KD6elXXZisYz74Ia?dl=0" target='_blank'>
				<img src="homeIcons/Tanya CD.gif" /><br />
				Tanya Resources
			</a>
		</span>
		
		<span>
			<a href="https://www.dropbox.com/sh/ztiltfbvpo4te9p/AABZjQmM71L5YESXllu1xjrIa?dl=0" target='_blank'>
				<img src="homeIcons/Chidon.gif" /><br />
				Yahadus Resources
			</a>
		</span>
	</div>
	<br /><br /><br /><br /><br />

	<div style='clear: both; background-color: #D5D8DE'></div>
	<h2>Latest 50 Rank Promotions</h2>
	<div id='rank-promotions'>
		<table>
			<thead>
				<tr>
					<th>Rank</th><th>Name</th><th>Date</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$promotions_query = mysql_query(
					"SELECT date_promoted, rank_name, rank_ord, first, first_he, last, last_he "
					."FROM rank_marks JOIN ranks USING (rank_ord) JOIN users USING (user_id) "
					."WHERE school_id = $school_id AND rank_ord > 1 ORDER BY date_promoted DESC LIMIT 50"
				);
				while ( $row = mysql_fetch_assoc( $promotions_query ) ) {?>
					<tr>
						<td><?=$row['rank_name']?></td>
						<td>
							<?=$row['first_he'] ? $row['first_he'] : $row['first']?>
							<?=$row['last_he'] ? $row['last_he'] : $row['last']?>
						</td>
						<td><?=dateToHebrew($row['date_promoted'])?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
		
	</div>
	<span style='clear: both'></span>
</div>

<? //else : ?>
<!--
<div class="baseInfo">
	<p>
		<b>Base:</b> <?=$row['school_name']?><br />
		<b>Commanding Officer:</b> <?=$admin->first . ' ' . $admin->last?><br />
	    <?=es($row['school_address1'])?><BR>
	    <?=es($row['school_address2'])?><?=$row['school_address2'] ? '<BR>' : ''?>
	    <?=es($row['school_city'])?> <?=es($row['school_state'])?>, <?=es($row['school_postal'])?><BR>
	    <?=es($row['school_country'])?><?=$row['school_country'] ? '<BR>' : ''?>
	    <?=es($row['school_phone'])?><?=$row['school_phone'] ? '<BR>' : ''?>        
	</p>
	<div style='clear: both'></div>
</div>

<div class="updates">
	<h2>Tzivos Hashem Updates</h2>
	
	<div>
    	<img src="homeIcons/Rally.gif" />
    	<div class="inner"> 
			<h3>Gimmel Tamuz Rally</h3>
			<li>Click <a href="downloads/gt/Gimmel Tammuz Sicha - Subtitles.mp4">here</a> for the rally Sicha</li>
			<li>Click <a href="downloads/gt/Tzoma Lecha Nafshi.wmv">here</a> for the Tzomo Licha Nafshi Niggun</li>
			<li>Click <a href="downloads/gt/With a Tehillim.mp4">here</a> for the With a Tehillim Niggum</li>
			<li>Click <a href="downloads/gt/We Want Moshiach Now.mp4">here</a> for the We Want Mashiach Now Niggun</li>
			<!--
			<li>Click <a href="http://fm2.chabad.org:8080/11Nissan5774_800k_0.f4v">here</a> to download rally</li>
			<li>Click <a href="http://we.tl/TvcuDxZVxC">here</a> to download all rally files</li>
			<li>Click <a href="yan/1. Countdown- Nissan.mp4">here</a> to see the Rally</li>
			<li>Click <a href="yan/2. Tzoma Lecha Nafshi.avi">here</a> for the rally Tzomo Licha Nafshi Niggun</li>
			<li>Click <a href="yan/3. Rebbe's 113th kapitel.jpg">here</a> for the new Kapitul</li>
			<li>Click <a href="yan/4. We Want Moshiach Now.mp4">here</a> for the rally We Want Mashiach Now Niggun</li>
			<li>Click <a href="yan/5. Sicha- Nissan.mp4">here</a> for the rally Sicha</li>
			<li>Click <a href="yan/6. Winners- Nissan.mp4">here</a> for the rally Winners</li>
			<li>Click <a href="yan/7. Promotions- Nissan.mp4">here</a> for the rally Promotions</li>
			<li>Click <a href="yan/8. Credits- Nissan.mp4">here</a> for the rally Credits</li>
			<li>Click <a href="promotion_report.php">here</a> for promotion picture report</li>
			<li>Click <a href="https://www.dropbox.com/sh/wlgjh7i4oaikos8/t3YkYTRHk5">here</a> for the rally Sicha and poster</li>
			<li>Click <a href="https://rcpt.hightail.com/2482248412/d3782275734b86f5fa7c5434223bf313?cid=tx-02002208350200000000&s=19105">here</a> to download this month’s awesome Purim Rally (MP4)</li>
			<li>Click <a href="http://vimeo.com/88874638">here</a> to download this month’s awesome Purim Rally (Vimeo)</li>
			
		</div>
	</div>
	<!--
	<div>
		<img src="homeIcons/Present.gif" />
		<div class="inner">
			<h3>Our Birthday Gift to the Rebbe</h3>
			<h4>Please enter your base Tanya and Mishna commitments</h4>
			<p>In honor of the Rebbe's Birthday our base is committing to learn:<br />
				<input type="text" name="lines" id="lines" size="6" /> Lines of Tanya Baal Peh<br />
				<input type="text" name="mLines" id="mLines" size="6" /> Lines of Mishna Baal Peh<br />
				<br />Our base has actually learned:<br />
				<input type="text" name="learned" id="learned" size="6" /> Lines of Tanya Baal Peh<br />
				<input type="text" name="mLearned" id="mLearned" size="6" /> Lines of Mishna Baal Peh<br />
				<li>Click <a href="yud_alef_nissan_choose.php">here</a> for the army / base / platoon reports</li>
			</p>
		</div>
	</div>
	
</div>
-->
<? endif; ?>