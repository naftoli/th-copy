<?
//error_reporting(E_ALL);
ini_set("display_errors", 1);
require_once '../db.php';
$user_id = mysql_real_escape_string( isset($_GET['id']) ? $_GET['id'] : 0 ); // get the user id
$version = mysql_real_escape_string( isset($_GET['v']) ? $_GET['v'] : 0 ); // get the version of the API

$sql = "select first, last, user_photo_id, lang_id, ut.track_id, ut.level, u.allow_parent_tasks, u.parent_marking from users u "
		."join user_tracks ut using (user_id) "
		."where user_id = " . $user_id . " "
		."ORDER BY ut.subject_id LIMIT 1"; // order by the subject id to get the first one available (preferably 1 if available)

//if($user_id == 26755 || $user_id == 22309) echo $sql;

$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$photo = $row['user_photo_id'];
$first = $row['first'];
$lang = $row['lang_id'] ? $row['lang_id'] : 1;
$ladder = $row['track_id'];
$level = $row['level'];
$allow_parent_tasks = $row['allow_parent_tasks'] == '1' && $row['parent_marking'] == "1" ? true : false;

// find out current hebrew month
$heDate = jdtojewish(unixtojd());
$heMonth = $heDate[0];
if ($heMonth == 13) $month = 1;
else $month = ++$heMonth;

$qry = "select qty, minutes from tehillim_ladders where ladder = " . $ladder . " and age = " . $level . " and month = " . $month;
$res = mysql_query($qry);
$r = mysql_fetch_assoc($res);

require_once '../class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();
$dates = GlobalSettings::getCurYearDates();
//$dates['start'] = 2458006;

//get today's day
//$jd = unixtojd();
//$today = intval(date('w'));
//$start = $jd - ($today + 2);
//$end = $start + 6;

$start = $dates['start'];
$end = $dates['end'];

require '../class.tasksCustomizationNew.php';
$tc = new TasksCustomizationNew;
$both = $tc->getCampaignsForChild( $user_id );
$campaigns = $both['campaigns'];
//$enrolled = $both['enrolled'];

$campaignLogos = array(
	1	=>	'Tehillim.svg',
	4	=>	'Tefilla.svg',
	12	=>	'Mivtzoim.svg',
	13	=>	'Niggunim.svg',
	16	=>	'hiskashrus.svg',
	21	=>	'sefer-hamitzvos.svg',
	27	=>	'tanya.svg',
	40	=>	'Yom-Dipagra.svg',
	41	=>	'Father-son.svg',
	42	=>	'Footsteps.svg',
	45	=>	'Cheshbon-Hanefesh.svg',
	90	=>	'Chitas.svg',
	100	=>	'Brias-Haguf.svg'
);

if (isset($_GET['app'])) define('HOME', 'mission_report');
else define('HOME', '../mission_report');

// find out if we are in the week prior to shabbos mevorchim
$jdNow = unixtojd();
$showTehillimQuota = 0;
require_once '../class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();
$sm = calculateSM( $year );
foreach ($sm as $val) {
	if ($jdNow >= ($val - 6) && $jdNow <= $val) {
		$showTehillimQuota = 1;
		break;
	}
}

/**************** GET THE CAMPAIGNS THE USER IS ELIGIBLE FOR ********************/
require_once(dirname(__FILE__)."/inc/functions/getCampaigns.php");
// show the button if the user has unenrolled campaigns or has no campaigns...
$has_campaigns = count(getCampaigns($user_id, false)) > 0 || count($campaigns) == 0;

?>
<header class="navbar" id="top" role="banner">
    <div class="container">
        <div class="navbar-header">
        	<h1 class="i18n" data-key="myGoals">MY GOALS</h1>
        </div>
    </div>
</header>

<div class="personalImg"></div>
<?php if ( isset($_COOKIE['lang']) && $_COOKIE['lang'] == 'he' ) : {} else: ?>
<div class="bug-report">
	<img src="/mobile/img_new/tools-color-white-svg.svg" data-user_id="<?=$user_id?>" data-category="Marking Missions" alt="bug-report" />
</div>
<?php endif; ?>

<div class="container">
    <div class="content">
		<div class="link-container">
			<a id="back-link" href="missionsNew.html?id=<?=$user_id?>">
				<img src="img_new/arrow-1-color-white-svg.svg" style="<?if ($lang == 1) {?>transform: rotateY(180deg);<?}?>"/>
				<span class="i18n" data-key="markMissions">Mark Missions </span>
            </a>
            <? if ($has_campaigns) { ?>
				<a id="campaign-link" data-toggle="modal" data-target="#enrollCampaignModal" href="#" style="text-decoration: none">
					
					<span class="i18n" data-key="EnrollCampaign">Enroll in Campaign </span>
				</a>
			<? } ?>
			<? if ($allow_parent_tasks) { // make sure that the school allows the parents to create a custom task before showing the modal ?>
				<a id="create-link" data-toggle="modal" data-target="#customTaskModal" href="#" style="text-decoration: none" class="i18n" data-key="AddCustomTask">
					Add Custom Task
				</a>
			<? } ?>
		</div>

    	<div class="info" dir="ltr" >
	<span class="i18n" data-key="Personalize2">Personalize the tasks that you would like to see for  </span>	<?=$first?><span class="i18n" data-key="Personalize3">'s missions.</span>
            <br /><span style="color: red">Tasks with a red star, are the task of that campaign that will help your child earn the medal in that campaign.</span></div>
    	<!--
    	<div class="text-left" style="margin-bottom: 20px;">
			<input type="button" id="expandAll" class="btn btn-danger btn-sm" value="Expand All" style="background-color: #1b2b51;border-color:#1b2b51;" />
		</div>
    	-->

		<?php if (isset($_GET['naftoli'])) : ?>
			<div class="panel panel-default">
        	<div id="spinner"></div>
        	<div class="panel-heading">
        		<? if ($lang == 2) : ?>
        			<i class="glyphicon glyphicon-chevron-left"></i> משנה בעל פה
        		<? else : ?>
        			<i class="glyphicon glyphicon-chevron-right"></i> משנה בעל פה
        		<? endif; ?>
        		<!--<div class="pull-right small points"><?=$points?> Points Needed</div>-->
        	</div>

        	<div class="collapse">
        		<div class='alert alert-warning' role='alert' dir="ltr">
					<div class='media'>
						<div class='media-left'>
    						<img class='media-object' width="50px;"
    							src="" alt='Camapign Logo'>
					  	</div>
					  	<div class="media-body">
					  		GOAL: To setup which mesechtos will be learned.
					  	</div>
					</div>
				</div>
                <div class="panel-body" id="mishna">
                	<? include 'getMBP.php'; ?>
                </div>
            </div>
        </div>
		<?php endif; ?>

    	<? foreach ( $campaigns as $id => $campaign ) : ?>

            <div class="panel panel-default">
            	<div id="spinner"></div>
            	<div class="panel-heading">
            		<i class="glyphicon glyphicon-chevron-left"></i> <?=$campaign?>
            		<!--<div class="pull-right small points"><?//=$points?> Points Needed</div>-->
            	</div>

            	<div class="collapse">
            		<? if ($id != 99) : ?>
            		<div class='alert alert-warning' role='alert' dir="ltr">
    					<div class='media'>
    						<div class='media-left'>
	    						<img class='media-object' width="50px;"
	    							src="<?=HOME?>/campaignLogos/<?=$campaignLogos[$id]?>" alt='Camapign Logo'>
						  	</div>
						  	<div class="media-body">
						  		<?
    							$sql = "select * from subjects where subject_id = $id";
								$result = mysql_query($sql);
								$row = mysql_fetch_assoc($result);
								echo "GOAL: " . $row['subject_description'];
								if ($id == 1) {
									?>
									<br />Click <a onclick='$("#wwtcVid").toggle()'>here</a> to watch the WWTC Tutorial.
									<div id='wwtcVid' style="display: none">
										<iframe src="https://player.vimeo.com/video/195384916" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
										<p><a href="https://vimeo.com/195384916">WWTC</a> from <a href="https://vimeo.com/tzivoshashem">Tzivos Hashem</a>
										on <a href="https://vimeo.com">Vimeo</a>.</p>
									</div>
									<?
								}
	    						?>
						  	</div>
						</div>
					</div>
					<div class="info2" dir="ltr">
						Choose the <?=$campaign?> tasks for <?=$first?>'s missions. <!--The task with a <span style='color:red'>*</span>
						is the one that needs to be done to complete a mission.-->
					</div>
					<? endif; ?>
                    <div class="panel-body" id="<?=$id?>">

						<div class="panel-spinner" style="direction: ltr;"></div>

                    	<ul class="list-unstyled tasks"></ul>

                    </div>
                </div>
            </div>

    	<? endforeach; ?>

    </div>
</div>

<? // allow parents to create custom tasks if the school allows it
if($allow_parent_tasks){
	include 'inc/modals/customTask.php';
}
// allow parents to enroll children in campaigns
include 'inc/modals/enrollChild.php';
?>

<? include 'inc/footer.php' ?>

<? include 'inc/foot.php' ?>

<script>
	//$( function() {
		//$(".form-group").hide();
		var url = location.toString();
		var pos = url.indexOf('=');
		var id = url.substring( pos+1 );

		var pos2 = id.indexOf('&');
		if (pos2 > 0) {
			id = id.substring( 0, pos2 );
		}
		/*
		$.post('reg/ajax/checkAuth.php', { user_id : id, admin_id : Cookies.get('admin') }, function( success ) {
			if (success == 0) {
				window.location = "/mobile";
			}
		});
		*/

		$("#expandAll").click( function() {
			$('.panel').trigger('click');
			//$(this).parent().parent().parent().find('.panel-heading').trigger('click');
		});

		var lang = <?=$lang == 2 ? 2 : 1?>;
		// when a panel is opened....
		$(".panel").click( function() {
			var container = this;
			var campaign = $(this).find('.panel-body').attr('id');
			// if there is no tasks preloaded....
			if ($(this).find('.tasks').html() === '' && $(this).find('.panel-spinner').html() === '') {
				var opts = {
					lines: 8, 		// The number of lines to draw
					length: 26, 	// The length of each line
					width: 12, 		// The line thickness
					radius: 26, 	// The radius of the inner circle
					scale: 0.75, 		// Scales overall size of the spinner
					corners: 1, 	// Corner roundness (0..1)
					color: '#888', 	// #rgb or #rrggbb or array of colors
					opacity: 0.25, 	// Opacity of the lines
					rotate: 0, 		// The rotation offset
					direction: 1, 	// 1: clockwise-1: counterclockwise
					speed: 1.25, 		// Rounds per second
					trail: 60, 		// Afterglow percentage
					fps: 20, 		// Frames per second when using setTimeout() as a fallback for CSS
					zIndex: 2e9, 	// The z-index (defaults to 2000000000)
					className: 'spinner', // The CSS class to assign to the spinner
					top: '50%', 	// Top position relative to parent
					left: '50%', 	// Left position relative to parent
					shadow: false, 	// Whether to render a shadow
					hwaccel: true, 	// Whether to use hardware acceleration
					position: 'relative' // Element positioning
				};
				$(this).find(".panel-spinner").css({"height": "150px"});
				var target = $(this).find(".panel-spinner")[0];
				//var target = document.getElementById('spinner');
				new Spinner(opts).spin(target);

				$(this).find('.collapse').css({"height": $(this).find('.collapse')[0].scrollHeight}); // dropdown and show the spinner...
				$(this).siblings().find('.collapse').css({"height": '0px'}); // colpase any other open dropdowns...

				$.ajax({
					type : "GET",
					url : '../ajax/getTasks.php?v=2',
					data : {subject : campaign,	user : <?=$user_id?>,	start : <?=$start?>,	end : <?=$end?>,	lang : lang,	parent: Cookies.get('admin')},
					success : function( data ) {
						data = $.parseJSON( data ); // cast tje data to JSON
						if ( data.length === 0 ) {
							$(container).find('.collapse').html('<div class="info2">No Tasks Found.<br /><br /><br /></div>');
							$("#spinner").empty(); // remove the spinner....
							return; // end the ajax call...
						}
						var str = ""; // the html to populate the dropdown with
						for ( var cat in data.tasks ) {
							for ( var enrolled in data.tasks[cat] ) {
								str += "<li><label class='checkbox-label'><input name='tasks[]' type='checkbox' class='category' value=\"" + campaign + "|" + encodeURI(cat) + "\"";
								if ( enrolled == '1' ) {
									str += ' checked ';
								}
								str += "/><span class='checkbox-display'></span></label> <b>" + cat + " ";
								// if the option is mandatory....
								if(data.mandatory[cat]){
									str += "<span style='color:red'>*</span>";
								}
								str += "</b></li><div class='task'>";
								// for each task in the tasks....
								for ( var task in data.tasks[cat][enrolled] ) {
									 str += task + "<br />"; // add the task and add a line break....
								}
								var showTehillim = <?=$showTehillimQuota?>; // should we show the tehillim quota
								if (showTehillim && campaign == 1 && (cat.indexOf('קוואטא') != -1 || cat.indexOf('Quota') != -1)) {
									str += "<span class='age' id='<?=$level?>'></span>";
									str += "<div><br /><i><b>This feature (to change ladders) is only available the week before Shabbos Mevorchim.</b></i>";
									str += "<br />My Ladder: <select name='userLevel' class='userLevel'>";
									var ladder = <?=$ladder?>;
									console.log( ladder );
									for (var m = 3; m < 8; m++) {
										if (m == ladder) {
											str += "<option value='" + m + "' selected>" + (m-2) + "</option>";
										} else {
											str += "<option value='" + m + "'>" + (m-2) + "</option>";
										}
									}
									str += "</select><br />";
									str += "My Quota: <b><span class='tQty tehillim'><?=$r['qty']?></span></b> Kapitelach<br />";
									str += "My Quota: <b><span class='tMin tehillim'><?=$r['minutes']?></span></b> Minutes</div>";
								}
								str += "</div>";
							}
						}
						if (lang == 2) str += '<br />'; // extra space on hebrew browsers....
						str += "<p><button class='btn btn-danger btn-sm save' style='background-color : #1b2b51;border-color:#1b2b51;'>Save</button></p>"; // add the save button....
		                // append the campaign to the page....
						var campaign_dom_object = $("#" + campaign);
						campaign_dom_object.find("ul").append(str);

						$(".panel-spinner").empty(); // remove the spinner....
						$(".panel-spinner").css({"height": "0"});

						// get the height of the dropdown....
						var height = campaign_dom_object.parent()[0].scrollHeight;
						//campaign_dom_object.parent().data("max-height", height); // set that to the max-data....
						campaign_dom_object.parent().css({"height": height}); // expand the box down via transition....
						campaign_dom_object.parent().parent().siblings().find('.collapse').css({"height": '0px'}); // hide the other ones if they are open....
						// if we are using hebrew remove any padding on the right.....
		                if (lang == 2) {
		                	$("#" + campaign).find("ul").css('padding-right', '0px');
		                }

		                $(".category").click( function() {
		                    //e.preventDefault();
		                    var val = decodeURI($(this).val());
		                    var checked = $(this).is(":checked");
		                    updateArray( tasks, checked, val );
		                    updateArray( tasksAdded, !checked, val );
		                });
					}
				});
			}
		});

        var tasks = [];
        var tasksAdded = [];

        //function to use for updating any of the above arrays
        function updateArray(name, checked, val) {
        	var found = false;
            for (i = 0; i < name.length; i++) {
                if (name[i] == val) {
                    found = true;
                    break;
                }
            }
            if (checked) {
				if (found)
                    name.splice( i, 1 );
            } else {
                if (!found)
                    name.push(val);
            }
        }
        // save updates to the tasks....
        $(document).on("click", ".save", function() {
            var panel = $(this).parent().parent().parent().parent().parent();

			// $(panel).removeClass('open');
			// $(panel).find('.collapse').removeClass('in');
			// $(panel).find('.collapse').css({"height": "0px"});

			var id = $(this).attr('id');
			if (id == 'save') { //mishna saving
				var arr = [];
				var mesechtos = $("#mishna").find('.mesechto');
				$(mesechtos).each( function() {
					if ($(this).is(":checked")) {
						var val = $(this).parent().find('input[name="mID"]').val();
						arr.push(val);
					}
				});
				$.post('assignMishna.php', { user : <?=$user_id?>, mesechtos : arr }, function( error ) {
					if (error != 0) {
						alert('There was an error.');
					}
				});
			} else {
                var opts = {
                    lines: 8, 		// The number of lines to draw
                    length: 26, 	// The length of each line
                    width: 12, 		// The line thickness
                    radius: 26, 	// The radius of the inner circle
                    scale: 0.75, 		// Scales overall size of the spinner
                    corners: 1, 	// Corner roundness (0..1)
                    color: '#888', 	// #rgb or #rrggbb or array of colors
                    opacity: 0.25, 	// Opacity of the lines
                    rotate: 0, 		// The rotation offset
                    direction: 1, 	// 1: clockwise-1: counterclockwise
                    speed: 1.25, 		// Rounds per second
                    trail: 60, 		// Afterglow percentage
                    fps: 20, 		// Frames per second when using setTimeout() as a fallback for CSS
                    zIndex: 2e9, 	// The z-index (defaults to 2000000000)
                    className: 'spinner', // The CSS class to assign to the spinner
                    top: '50%', 	// Top position relative to parent
                    left: '50%', 	// Left position relative to parent
                    shadow: false, 	// Whether to render a shadow
                    hwaccel: true, 	// Whether to use hardware acceleration
                    position: 'relative' // Element positioning
                };
                $(panel).find(".panel-spinner").css({"height": "150px"});
                var target = $(panel).find(".panel-spinner")[0];
                //var target = document.getElementById('spinner');
                new Spinner(opts).spin(target);

                $(panel).find('.collapse').css({"height": '350px'}); // dropdown and show the spinner...
                $(panel).siblings().find('.collapse').css({"height": '0px'}); // colpase any other open dropdowns...

                $.post('../ajax/customize.php', {
                    tasks: tasks,
                    tasksAdded: tasksAdded,
                    user: <?=$user_id?>,
                    start: <?=$start?>,
                    end: <?=$end?>,
                    lang: <?=$lang?>
                }, function (data) {
                    //alert( data );
                    console.log(data);
                    alert("Saved.");
                    //history.go(0);
                    //window.location = 'goals.php';
                    //window.location.href = "goalsNew.html";
                    $(".panel-spinner").empty(); // remove the spinner....
                    $(".panel-spinner").css({"height": "0"});
                    $(panel).removeClass('open');
                    $(panel).find('.collapse').removeClass('in');
                    $(panel).find('.collapse').css({"height": "0px"});
                });
            }
        });
        // change levels....
        $(document).on("change", ".userLevel", function() {
        	var id = <?=$user_id?>;
			var level = $(this).val();
			var age = $(".age").attr('id');
			$.post('../mobile/reg/ajax/changeLevel.php', { user: id, level : level, age : age }, function( success ) {
				if (success) {
					var data = $.parseJSON( success );
					$(".tQty").text( data.qty );
					$(".tMin").text( data.min );
				}
			});
		});
	//});
</script>
<script src="js/bug_report.js"></script>
