<h1>Chayolei Tzivos Hashem</h1>

<style type="text/css">
	.button {
		float: left;
		margin: 24px;
		text-align: center;
	} 
	.button img {
		height: 100px;
	}
	.button a, .kiosk a {
		font-size: 12px;
	}
	.kiosk {
		float: left;
		margin: 15px;
	} 
	.kiosk img {
		height: 160px;
	}
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
	.updates div {
		clear: left;
		padding-top: 10px;
		padding-bottom: 10px;
	}
	.updates div:nth-child(even) {
		background-color: #F2F2F2;
	}
	.updates div .inner {
		clear: none;
		overflow: hidden;
		padding: 0;
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
	.balPeh td {
		font-size: 10px;
		text-align: center;
		padding: 10px;
		border-right: 1px solid grey;
		border-bottom: 1px solid grey;
		width: 150px;
		vertical-align: bottom;
	}
	.balPeh td:nth-child(3) {
		border-right: none;
	}
	.balPeh tr:last-child > td {
		border-bottom: none;
	}
	.balPeh td > table td {
		border: none;
	}
	.noPadding td {
		padding: 0;
	}
	.name {
		font-size: 13px;
		font-weight: bold;
	}
	.schoolLogo {
		margin: auto;
	}
	.schoolLogo img {
		height: 120px;
	}
	.inner {
		font-size: 14px;
	}
</style>

<script>
	/*
	var tanyaCampaign = 9;
	var mishnaCampaign = 10;
	
	$(function() {
		var tanyaP = $(".tanyaP");
		var mishnaP = $(".mishnaP");
		
		$.each(tanyaP, function() {
			var ids = $(this).parent().parent().parent().parent().parent().attr('id');
			var info = ids.split(':');
			var user = info[0];
			var o = this;
			$.post('ajax/getLines.php', {
	    		id : tanyaCampaign, 
	    		user: user, 
	    		type : 'pledged', 
	    	}, function(data) {
	    		if (data > 0) {
	    			$(o).val(data);
	    		}
	    	});
		});
		
    	$.each(mishnaP, function() {
			var ids = $(this).parent().parent().parent().parent().parent().attr('id');
    		var info = ids.split(':');
			var user = info[0];
    		var o = this;
    		$.post('ajax/getLines.php', {
	    		id : mishnaCampaign, 
	    		user : user, 
	    		type : 'pledged'
	    	}, function(data) {
	    		if (data > 0) {
	    			$(o).val(data);
	    		}
	    	});
    	});
    	
    	var tanya = $(".tanya");
		var mishna = $(".mishna");
		
		$.each(tanya, function() {
			var ids = $(this).parent().parent().parent().parent().parent().attr('id');
			var info = ids.split(':');
			var user = info[0];
			var o = this;
			$.post('ajax/getLines.php', {
	    		id : tanyaCampaign, 
	    		user: user, 
	    		type : 'learned', 
	    	}, function(data) {
	    		if (data > 0) {
	    			$(o).val(data);
	    		}
	    	});
		});
		
    	$.each(mishna, function() {
			var ids = $(this).parent().parent().parent().parent().parent().attr('id');
    		var info = ids.split(':');
			var user = info[0];
    		var o = this;
    		$.post('ajax/getLines.php', {
	    		id : mishnaCampaign, 
	    		user : user, 
	    		type : 'learned'
	    	}, function(data) {
	    		if (data > 0) {
	    			$(o).val(data);
	    		}
	    	});
    	});
    	
    	$(".tanyaP").blur(function() {
			var ids = $(this).parent().parent().parent().parent().parent().attr('id');
    		var info = ids.split(':');
			var user = info[0];
			var grade = info[1];
			var school = info[2];
    		var val = $(this).val();
    		if (user > 0 && !isNaN(val)) {
	    		$.post('ajax/updateBalPehCampaign.php', {
		    		id : tanyaCampaign, 
	        		val : val, 
	        		school : school,
	        		grade : grade,  
	        		user : user, 
	        		table : 'lines_pledged'
		    	});
		    }
    	});
    	
    	$(".mishnaP").blur(function() {
			var ids = $(this).parent().parent().parent().parent().parent().attr('id');
    		var info = ids.split(':');
			var user = info[0];
			var grade = info[1];
			var school = info[2];
    		var val = $(this).val();
    		if (user > 0 && !isNaN(val)) {
	    		$.post('ajax/updateBalPehCampaign.php', {
		    		id : mishnaCampaign, 
	        		val : val, 
	        		school : school,
	        		grade : grade,  
	        		user : user, 
	        		table : 'lines_pledged'
		    	});
		    }
    	});
    	
    	$(".tanya").blur(function() {
			var ids = $(this).parent().parent().parent().parent().parent().attr('id');
    		var info = ids.split(':');
			var user = info[0];
			var grade = info[1];
			var school = info[2];
    		var val = $(this).val();
    		if (user > 0 && !isNaN(val)) {
	    		$.post('ajax/updateBalPehCampaign.php', {
		    		id : tanyaCampaign, 
	        		val : val, 
	        		school : school,
	        		grade : grade,  
	        		user : user, 
	        		table : 'lines_learned'
		    	});
		    }
    	});
    	
    	$(".mishna").blur(function() {
			var ids = $(this).parent().parent().parent().parent().parent().attr('id');
    		var info = ids.split(':');
			var user = info[0];
			var grade = info[1];
			var school = info[2];
    		var val = $(this).val();
    		if (user > 0 && !isNaN(val)) {
	    		$.post('ajax/updateBalPehCampaign.php', {
		    		id : mishnaCampaign, 
	        		val : val, 
	        		school : school,
	        		grade : grade,  
	        		user : user, 
	        		table : 'lines_learned'
		    	});
		    }
    	});
	});
	*/
</script>

<?php
$cur_admin_id = $admin_user['admin_id'];
$user_auths = mq("SELECT school_id, school_name, first, last, user_id, admin_auths.role_id, role_name, user_code FROM admin_auths LEFT JOIN users ON (admin_auths.id = users.user_id) LEFT JOIN roles ON (admin_auths.role_id = roles.role_id AND admin_auths.auth = roles.role_auth) LEFT JOIN schools USING (school_id) WHERE auth = 'user' AND admin_id = $cur_admin_id" . ($admin_user['auth'] == 'super' ? '' : "") . ' ORDER BY last, first'); //  AND school_id IN ($school_ids)
$myshliach = false;
while($row = mysql_fetch_assoc($user_auths)) {
	if (in_array($row['school_id'], array(61,269))) {
		$myshliach = true;
		break;
	} 
}
if ($myshliach) {
	$s = "select logo from schools where school_id = 61";
	$r = mysql_query($s);
	$rr = mysql_fetch_assoc($r);
	$logo = $rr['logo'];
	?>
	<div class='schoolLogo' align="center">
		<img src="images/parentIcons/Girl with Flag.gif" />
		<img src="images/parentIcons/Mother.gif" />
		<img src="schoolLogos/<?=$logo?>" />
		<img src="images/parentIcons/Father.gif" />
		<img src="images/parentIcons/Boy with Flag.gif" />
	</div>
	<div style='clear: both'></div>
	<?
}

function getRank($user, $type = 'name') {
	$sql = "select r.rank_name, r.rank_image_id from ranks r 
			join rank_marks rm using (rank_ord) 
			where rm.user_id = $user 
			order by rm.rank_ord desc 
			limit 1";
	//echo $sql;
	$result = mysql_query($sql);
	if (mysql_num_rows($result) > 0) {
		$row = mysql_fetch_assoc($result);
		$rankName = $row['rank_name'];
		$rankImage = $row['rank_image_id'];
	} else {
		$sql = "select rank_name, rank_image_id from ranks where rank_ord = 1";
		//echo $sql;
		$result = mysql_query($sql);
		$row = mysql_fetch_assoc($result);
		$rankName = $row['rank_name'];
		$rankImage = $row['rank_image_id'];
	}
	if ($type == 'name') {
		return $rankName;
	} else if ($type == 'image') {
		return $rankImage;
	}
}
$admin->get_markable_children();
?>
<H2><?=T_('Welcome Commander')?>, <?=$admin->first?> <?=$admin->last?></H2> 
<!--
<div align="center">
	Click <a href="admin_parent_user_track.php">here</a> to adjust your children's tehillim quotas.
</div>
-->
<div class="buttons"> 
	<div class="button">
		<a href="mission_report/newParentPrint.php">
			<img src="images/parentIcons/Printer.gif" />
			<br />Print Missions
		</a>
	</div>
	
	<div class="button">
		<a href="mission_report/newParentMark.php">
			<img src="images/parentIcons/Checkbox.gif" />
			<br />Mark Missions
		</a>
	</div>
	
	<div class="button">
		<a href="children_stickers.php">
			<img src="images/stickers/Sticker-3968888978.png" />
			<br />Sticker Report
		</a>
	</div>
	
	<div class="button">
		<a href="parentPersonalizedReport.php">
			<img src="images/parentIcons/scoreboard.gif" />
			<br />Scoreboard
		</a>
	</div>	
</div>
<div style='clear: both'></div>
<h2></h2>

<? if (isset($_COOKIE['naftoli'])) : ?>
<? //if ($myshliach) : ?>
<div class="updates">
	<h2>Tzivos Hashem Updates</h2>
	<div>
		<img src="homeIcons/Present.gif" />
		<div class="inner">
			<!--
			<li>$36,000 for Mo'os Chitim</li>
			<li>112,000 Lines of Mishanyos Baal Peh</li>
			<li>224,000 Lines of Tanya Baal Peh</li>
			<li>Click <a href="admin_student_pledges.php">here</a> for the Yud Alef Nissan Report</li>
			<li>Click <a href="maos_chitim_cards.php">here</a> for the mo'os chitim pledge cards</li>
			<li>Click <a href="downloads/Sponsor Sheet 5774 D3.pdf">here</a> for the mo'os chitim pledge form</li>
			-->
			<h3>In honor of Yud Alef Nissan:</h3>
			<table class='balPeh'>
				<tr>
					<?
					$i = 1; 
					foreach ($admin->children as $child) {
						if (empty($child->class_id) || empty($child->school_id)) continue;
						echo "<td id='" . $child->user_id . ':' . $child->class_id . ':' . $child->school_id . "'>
							<span class='name'>" . $child->first . ' ' . $child->last . "</span><br />
							Lines Pledged<br /><table><tr class='noPadding'>
							<td><input type='text' class='tanyaP' size='5' />
							<br />Tanya</td><td><input type='text' class='mishnaP' size='5' /><br />
							Mishna</td></tr></table>
							Lines Learned<br /><table><tr class='noPadding'>
							<td><input type='text' class='tanya' size='5' />
							<br />Tanya</td><td><input type='text' class='mishna' size='5' /><br />
							Mishna</td></tr></table></td>";
						if ($i % 3 == 0) {
							echo "</tr><tr>";
						}
						$i++;	
					}
					?>
				</tr>
			</table>
		</div>
		<div style="clear: both"></div>
	</div>
</div>
<h2></h2>
<? endif; ?>

<!--
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
			Click <a href="hakhel.php?end=<?=$i?>">here</a> for daily videos.
		</p>
		
		
		
		<? //for ($i = 1; $i < 20; $i++) : ?>
		<!--
			<h4>Day <?=$i?></h4>
			<p>
				Click <a href="hakhel/Part <?=$i?> HD.mp4">here</a> for video.
				<br />Click <a href="hakhel/Phone Translation <?=$i?>.png">here</a> for text/translation of sicha.
			</p>
			
			<p>Click <a href="hakhel.php?end=<?=$i?>">here</a> for previous days.</p>
		-->	
		<? //endfor; ?>
<!--	</div>
</div>
<h2></h2>
<!--
<div align="center">
	<video width="480" height="320" controls>
	  <source src="downloads/Auction3.mp4" type="video/mp4">
	Your browser does not support the video tag. 
	Click <a href="downloads/Auction3.mp4">here</a> to view end of year auction video clip.
	</video>
</div>

<div align="center">
	<video width="480" height="320" controls>
	  <source src="downloads/chidon2.mp4" type="video/mp4">
	Your browser does not support the video tag. 
	Click <a href="downloads/chidon2.mp4">here</a> to view end of year auction video clip.
	</video>
</div>

<div align="center">
	<video width="480" height="320" controls>
	  <source src="downloads/Auction 5775 HD.mp4" type="video/mp4">
	Your browser does not support the video tag. 
	Click <a href="downloads/Auction 5775 HD.mp4">here</a> to view end of year auction video clip.
	</video>
</div>
<br />

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

<div align="center">
	<h3>Links to enter your children into the End of Year Auction</h3>
</div>
-->
<div class="buttons">
	<? 
	foreach ($admin->children as $child) {
		if (empty($child->class_id) || empty($child->school_id)) continue;
		echo "<div class='kiosk'><a class='kiosk_link' value='" . $child->user_id . "' href='#'>" . $child->first . "'s Kiosk<br />
			<img src='file_view.php?id=" . $child->user_photo_id . "' alt='" . $child->first . ' ' . $child->last . "' />";
		echo "<img src='file_view.php?id=" . getRank($child->user_id, 'image') . "'  
			style='position: relative; height: 60px; left: -40px; top: 20px;' /></a><br />";
		echo "<form method='post' target='_blank' id='form" . $child->user_id . "' action='statement.php'>
			<input type='hidden' name='new_login' /><input type='hidden' name='user_code' value='3";
		echo $child->user_code;
		echo "' /></form>";
		echo "</div>";
	}
	?>
	<script>
		$(".kiosk_link").click( function() {
			var id = $(this).attr('value');
			$("#form" + id).submit();
		});
	</script>
</div>
<div style='clear: both'></div>
<h2></h2>

<? if ($myshliach) : ?>
<br />
<div>
	<a class="twitter-timeline" href="https://twitter.com/MyShliach" data-widget-id="500027762225057794">Tweets by MyShliach</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
</div>
<h2></h2>
<br />
<div class="updates">
	<h2>Tzivos Hashem Resources</h2>
	
	<div class="resources">
		<span>
			<img src="homeIcons/Hachayol Header.gif" /><br />
			<a href="https://www.dropbox.com/sh/hi9cbiye4ubryuy/AAA19HR5kSdliyqFpZKKDpmKa?dl=0">
				5777
			</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;/&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a href="https://www.dropbox.com/sh/c2g76cp76it1bf6/AABw7AHHEKWfahv-yIFXV8Qsa?dl=0">
				5778
			</a>
		</span>
		
		<span>
			<a href="https://www.dropbox.com/s/id0bfk1wpuebkum/CTH%20-%20BC%20Calendar%205777%20posters%20HR.pdf?dl=0">
				<img src="homeIcons/Calendar.gif" /><br />
				Commander's Calendar
			</a>			
		</span>
		<span>
			<a href="https://www.dropbox.com/sh/41u2regs73kfp9h/AACJV58J9KD6elXXZisYz74Ia?dl=0">
				<img src="homeIcons/Tanya CD.gif" /><br />
				Tanya Resources
			</a>
		</span>
		
		<span>
			<a href="https://www.dropbox.com/sh/i9hjub6ug1ii6q7/AABgHDk2nH-tNE5JKDJericXa?dl=0">
				<img src="homeIcons/Chidon.gif" /><br />
				Yahadus Resources
			</a>
		</span>
	</div>
	<br /><br /><br /><br /><br />
	<span style='clear: both'></span>
</div>
<div style='clear: both'></div>
<? endif; ?>

<h2></h2>
