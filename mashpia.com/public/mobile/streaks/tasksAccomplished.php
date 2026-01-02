<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);

function getUserTasks($user_id, $campaign, $start_date, $end_date) {
    global $MASHPIA_DB, $lang;

    // get tasks information for the selected campaign
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/user_track.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/user.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/school_class.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class.taskExceptions.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/date_tasks_mission.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/daily_task.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/weekly_task.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/shabbos_task.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/no_label_task.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/task.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/date_tasks_mark.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/pesukim_task.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/pesukim/class.pesukim.php';

    $lang = 1; // default language is english
    $stmt = $MASHPIA_DB->prepare("SELECT * FROM users WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $lang = $user['lang_id']; // update the language
    $user = new user($user); // create a new user
    $user->get_rank(); // get his rank
    $user->get_school_class(); // and get his class
    $user->get_user_tracks($campaign, $start_date, $end_date, [], $lang); // get the users tracks
    return $user;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();
$dates = GlobalSettings::getCurYearDates();
$start = $dates['start'];
$lang = 1;
$user_id = $_GET['id'];

// get campaigns the user is assigned to
$campaigns = [];
$sql = "SELECT subject_id, subject_name 
        FROM user_tracks ut 
        JOIN subjects USING (subject_id) 
        WHERE user_id = $user_id 
        AND enrolled = 1 
        AND subject_id NOT IN (12, 40, 136)";
$stmt = $MASHPIA_DB->prepare($sql);
$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $campaigns[$row['subject_id']] = $row['subject_name'];
}

if (isset($_POST['submit'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $date_range = $_POST['date_range'];
    $campaign_chosen = $_POST['campaign'];
    $order_by = $_POST['order_by'] ?? 'campaign';
    if ($start_date == '' && $end_date == '') {
        if ($date_range == '-1') {
            $start_date = $start; // beginning of the year
        } else {
			$start_date = unixtojd() - (int)$date_range; // subtract days, not weeks
        }
        $end_date = unixtojd(); // today
    } else {
        $start_date = unixtojd(strtotime($start_date));
        $end_date = unixtojd(strtotime($end_date));
    }
    $user_tracks = getUserTasks($user_id, $campaign_chosen, $start_date, $end_date)->user_tracks;
    
    $tasks = [];
    $task_types = ['daily_tasks', 'weekly_tasks', 'shabbos_tasks'];
    $grid_ids = [];
    $gridIdToShort = [];
    $gridIdToMeta = [];
    // Prepare label_id lookup by grid_id (in case task objects don't expose label_id)
    $labelStmt = $MASHPIA_DB->prepare("SELECT label_id FROM date_tasks WHERE grid_id = :grid_id LIMIT 1");
    foreach ($user_tracks as $user_track) {
        foreach ($task_types as $task_type) {
            foreach ($user_track->{$task_type} as $task) {
                // show only unique tasks... (tasks with the same grid_id are the same task)
                if (! in_array($task->grid_id, $grid_ids)) { 
                    $grid_ids[] = $task->grid_id;
                    $tasks[$task->label_name][] = [
                        'short_name' => $task->short_name,
                        'name' => $task->task_name,
                        'grid_id' => $task->grid_id
                    ];
                    $gridIdToShort[$task->grid_id] = $task->short_name;
                    // Determine label_id: prefer property on task, else lookup via grid_id
                    $labelId = null;
                    if (isset($task->label_id)) {
                        $labelId = (int)$task->label_id;
                    } else {
                        $labelStmt->execute(['grid_id' => $task->grid_id]);
                        $rowLbl = $labelStmt->fetch(PDO::FETCH_ASSOC);
                        if ($rowLbl && isset($rowLbl['label_id'])) {
                            $labelId = (int)$rowLbl['label_id'];
                        }
                    }
                    // normalize type
                    $type = $task_type === 'daily_tasks' ? 'daily' : ($task_type === 'weekly_tasks' ? 'weekly' : 'shabbos');
                    $gridIdToMeta[$task->grid_id] = [
                        'short_name' => $task->short_name,
                        'label' => $task->label_name,
                        'label_id' => $labelId,
                        'frequency_id' => isset($task->frequency_id) ? (int)$task->frequency_id : null,
						'type' => $type,
						'label_ord' => isset($task->label_ord) ? (int)$task->label_ord : null
                    ];
                }
            }
        }
    }
    
    require_once 'classes/class.accomplished.php';
    $accomplished = new Accomplished($user_id, $grid_ids, $start_date, $end_date);
    $accomplished->setAccomplished();
    $accomplished_tasks = $accomplished->getAccomplished();
    // echo "<pre>"; print_r($accomplished_tasks); echo "</pre>"; exit;
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
	<meta charset="utf-8">
	<title class="i18n" data-key="Title">Tzivos Hashem | Tasks Accomplished</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">
	<link rel="stylesheet" href="/mobile/css/lib/animate.css">
	<style type="text/css">
		@charset "UTF-8";

		html {
			font-family: sans-serif;
			-webkit-text-size-adjust: 100%;
			-ms-text-size-adjust: 100%
		}

		body {
			margin: 0
		}

		* {
			-webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box
		}

		:after,
		:before {
			-webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box
		}

		html {
			font-size: 10px
		}

		body {
			font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-size: 14px;
			line-height: 1.42857143;
			color: #333;
			background-color: #fff
		}

		@-ms-viewport {
			width: device-width
		}

		body,
		html {
			overflow-x: hidden;
			height: 100%
		}

		body {
			background: #fff;
			font-family: 'Open Sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
			font-weight: 300;
			padding-bottom: 100px;
			background-size: cover;
			background-attachment: fixed
		}
	</style>
	<link rel="preload" href="/mobile/css/all.css" as="style" onload="this.rel='stylesheet'">
	<noscript>
		<link rel="stylesheet" href="/mobile/css/all.css" as="style" onload="this.rel='stylesheet'">
	</noscript>
	<link href="//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="preload" type="text/css"
		as="style" onload="this.rel='stylesheet'">
	<noscript>
		<link href="//fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800" rel="stylesheet" type="text/css">
	</noscript>
	<script>
		! function (e) {
			"use strict";
			var t = function (t, n, r) {
				function o(e) {
					if (i.body) return e();
					setTimeout(function () {
						o(e)
					})
				}

				function a() {
					d.addEventListener && d.removeEventListener("load", a), d.media = r || "all"
				}
				var l, i = e.document,
					d = i.createElement("link");
				if (n) l = n;
				else {
					var s = (i.body || i.getElementsByTagName("head")[0]).childNodes;
					l = s[s.length - 1]
				}
				var u = i.styleSheets;
				d.rel = "stylesheet", d.href = t, d.media = "only x", o(function () {
					l.parentNode.insertBefore(d, n ? l : l.nextSibling)
				});
				var f = function (e) {
					for (var t = d.href, n = u.length; n--;)
						if (u[n].href === t) return e();
					setTimeout(function () {
						f(e)
					})
				};
				return d.addEventListener && d.addEventListener("load", a), d.onloadcssdefined = f, f(a), d
			};
			"undefined" != typeof exports ? exports.loadCSS = t : e.loadCSS = t
		}("undefined" != typeof global ? global : this),
			function (e) {
				if (e.loadCSS) {
					var t = loadCSS.relpreload = {};
					if (t.support = function () {
						try {
							return e.document.createElement("link").relList.supports("preload")
						} catch (e) {
							return !1
						}
					}, t.poly = function () {
						for (var t = e.document.getElementsByTagName("link"), n = 0; n < t.length; n++) {
							var r = t[n];
							"preload" === r.rel && "style" === r.getAttribute("as") && (e.loadCSS(r.href, r, r.getAttribute(
								"media")), r.rel = null)
						}
					}, !t.support()) {
						t.poly();
						var n = e.setInterval(t.poly, 300);
						e.addEventListener && e.addEventListener("load", function () {
							t.poly(), e.clearInterval(n)
						}), e.attachEvent && e.attachEvent("onload", function () {
							e.clearInterval(n)
						})
					}
				}
			}(this);
	</script>
	<script src="/mobile/js/all.js" defer></script>
	<link rel="apple-touch-icon" href="apple-touch-icon.png">
	<!-- Place favicon.ico in the root directory -->
	<link rel="stylesheet" href="/mobile/css/bug_report.css" />
	<style>
		html,
		body,
		span,
		p,
		h1,
		h2,
		h3,
		h4,
		h5,
		h6,
		div,
		button,
		i,
		input {
			color: #1b2b51;
		}

		a > span {
			color: #fff;
		}

		.container-fluid>.navbar-collapse,
		.container-fluid>.navbar-header,
		.container>.navbar-collapse,
		.container>.navbar-header {
			margin: 0px;
		}

		.container {
			transition: .25s;
		}

		.link-container {
			text-align: center;
		}

		/*			misc page styles...*/
		.page-streaks .task {
			font-size: 13px;
			margin-bottom: 8px;
		}

		.page-streaks li label {
			margin-bottom: 0px;
			margin-right: 5px;
		}

		.page-streaks .he li label {
			margin-bottom: 0px;
			margin-right: 0px;
			margin-left: 5px;
			float: right;
			margin-top: 10px;
		}

		/*        	Other more genearal stylss...*/
		.alert {
			margin-bottom: 0;
		}

		.info {
			padding: 2%;
			margin-top: 5px;
			margin-bottom: 7px;
			font-weight: bold;
			line-height: 1.1;
			clear: both;
		}

		.info2 {
			padding: 2%;
			font-weight: bold;
			line-height: 1.1;
			margin-bottom: -10px;
		}

		.panel-heading {
			cursor: pointer;
		}

		.collapse {
			display: block;
			visibility: visible;
			height: 0px;
			overflow: hidden;
		}

		/*			Updated checkboxes...*/
		label.checkbox-label input {
			display: none
		}

		label.checkbox-label .checkbox-display {
			width: 20px;
			height: 20px;
			background: url("//mashpia.com/mobile/img_new/square-color-purple-svg.svg");
			display: inline-block;
		}

		label.checkbox-label input:checked+.checkbox-display {
			width: 20px;
			height: 20px;
			background: url("//mashpia.com/mobile/img_new/square-check-color-green-svg.svg");
			display: inline-block;
		}

		/*			square the edges between the streaks and the dropdowns...*/
		.alert-warning {
			border-top-left-radius: 0px;
			border-top-right-radius: 0px;
		}

		.panel.panel-default .panel-heading {
			margin-bottom: 0px;
		}

		.panel.panel-default.open .panel-heading {
			border-bottom-left-radius: 0px;
			border-bottom-right-radius: 0px;
		}

		/*			Change the arrow icons to use images instead of glyphicons...*/
		i.glyphicon.glyphicon-chevron-right,
		i.glyphicon.glyphicon-chevron-left {
			background: url("https://mashpia.com/mobile/img_new/arrow-1-color-white-svg.svg");
			width: 10px;
			height: 15px;
			background-repeat: no-repeat;
		}

		i.glyphicon.glyphicon-chevron-right:before,
		i.glyphicon.glyphicon-chevron-left:before {
			content: "";
		}

		/*			And flip them to the other side on for hebrew...*/
		.he .panel.panel-default .panel-heading i {
			margin-left: 8px;
			transform: rotate(180deg);
		}

		.he .panel.panel-default.open>.panel-heading i {
			transform: rotate(90deg);
		}

		/*			Change the color of the dropdowns and make the text bolder...*/
		.panel.panel-default .panel-heading {
			background: #1b2b51;
			font-weight: 400;
		}

		/*			Some modal Styles*/
		.modal-header {
			border-top-left-radius: 4px;
			border-top-right-radius: 4px;
			background: #1b2b51;
			color: #fff;
		}

		.modal-header span,
		.modal-header {
			color: #fff;
		}

		.modal-header .close {
			opacity: 1;
			font-size: 2em;
			width: 20px;
			font-weight: 100;
		}

		/*			Links on top of page...*/
		a#back-link,
		a#create-link,
		a#campaign-link,
		button.enroll {
			color: #fff;
			padding: 8px;
			border-radius: 5px;
			display: inline-block;
			background: #1b2b51;
			transition: .1s;
		}

		a#back-link:hover,
		a#create-link:hover {
			text-decoration: none
		}

		a#create-link,
		.he a#back-link {
			float: right;
		}

		.he a#create-link,
		a#back-link {
			float: left;
		}

		a#back-link img {
			height: 20px;
			width: 20px;
			padding: 2px;
		}

		.navbar-header {
			min-height: 48px;
		}

		.page-streaks header.navbar h1 {
			height: auto;
			font-size: 40px;
			padding: 16.5px 0px;
			margin: 0px;
		}

		@media (max-width: 556px) {

			a#back-link img,
			.he a#back-link img {
				display: none;
			}

			.page-streaks header.navbar h1 {
				font-size: 30px;
				padding-top: 8px;
				padding-bottom: 5px;
			}
		}

		@media (max-width: 415px) {

			a#back-link,
			a#create-link,
			a#campaign-link {
				font-size: .9em;
			}

			a#back-link img {
				height: 15px;
				width: 15px;
				padding: 1px;
			}
		}

		@media (max-width: 370px) {

			a#back-link,
			.he a#back-link,
			a#create-link,
			.he a#create-link,
			a#campaign-link,
			.he a#campaign-link {
				float: none;
				margin-left: 0px;
				margin: 5px;
			}

			a#back-link,
			.he a#back-link {
				display: inline-block;
				margin-top: -10px;
				margin-right: 0px;
			}
		}

		.hebrew {
			font-family: Arial;
		}

        select, button {
            padding: 6px;
        }
	</style>

    <style>
        .navbar-header h1 {
            font-weight: 700;
            color: #fff;
            padding-bottom: 10px;
        }
        .navbar {
            min-height: auto !important;
        }
        .infobox {
            margin: 0 auto;
            padding: 10px;
            font-size: 1.2em;
            line-height: 1.5;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 10px;
            margin: 0 8% 20px 8%;
            color: brown;
        }
        progress {
            height: 50px;
            margin-left: 10px;
            vertical-align: middle;
        }
        #chart-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        #chart-legend {
            display: flex;
            align-items: center;
            gap: 18px;
            justify-content: flex-start;
            max-width: 1200px;
            margin: 8px auto 0 auto;
            padding: 0 20px;
            font-size: 13px;
            color: #555;
        }
        #chart-legend .item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        #chart-legend .swatch {
            width: 24px;
            height: 10px;
            border-radius: 2px;
            display: inline-block;
            box-sizing: border-box;
        }
        #chart-legend .swatch.accomplished {
            background-color: #2ecc71;
            border: 1px solid #2ecc71;
        }
        #chart-legend .swatch.missing {
            background-color: #e0e0e0;
            border: 1px solid #cfcfcf;
        }
    </style>

</head>

<body id="main">
    <header class="navbar" id="top" role="banner">
        <div class="container">
            <div class="navbar-header">
                <h1 class="i18n" data-key="tasksAccomplished">Tasks Accomplished</h1>
            </div>
        </div>
    </header>
    <div class="personalImg"></div>

    <div class="container">
        <div class="content">
            <button id="back-button" class="btn btn-default" onclick="javascript: location.href='index.html?id=<?=$user_id?>'">Back</button>
            <br /><br />
            <form action="tasksAccomplished.php?id=<?=$user_id?>" method="post" onsubmit="return validateForm()">
                Choose date range:
                <input type="date" name="start_date" placeholder="Start Date" />
                <input type="date" name="end_date" placeholder="End Date" /><br /><br />
                OR choose from the following options:
                <select name="date_range">
                    <option value="-1">From beginning of year</option>
                    <option value="90">Last 90 days</option>
                    <option value="60">Last 60 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="7">Last 7 days</option>
                </select>
                <br /><br />
                Choose a campaign to view tasks accomplished for:
                <select name="campaign" id="campaign">
                    <option value="0">Select a campaign</option>
                    <option value="-1">All campaigns</option>
                    <?php foreach ($campaigns as $id => $campaign) : ?>
                        <option value="<?=$id?>"><?=$campaign?></option>
                    <?php endforeach; ?>
                </select><br /><br />
                Order by:
                <select name="order_by">
                    <option value="campaign">Mission sheet order</option>
                    <option value="completed-asc">Completed (ascending)</option>
                    <option value="completed-desc">Completed (descending)</option>
                </select><br /><br />
                <button type="submit" name="submit" class="btn btn-default">Submit</button>
            </form>
        </div>
    </div>
    <hr />
    <div id="chart-container"></div>
    <div id="chart-controls" style="max-width:1200px;margin:8px auto 0 auto;padding:0 20px;">
        Sort chart:
        <select id="chartSortSelect">
            <option value="campaign">Mission sheet order</option>
            <option value="completed-asc">Completed (ascending)</option>
            <option value="completed-desc">Completed (descending)</option>
            <option value="alpha">Task name (A–Z)</option>
        </select>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<?php if (!empty($accomplished_tasks)) : ?>
<script>
  // Payload from PHP
  const chartPayload = <?php
    // Build a compact payload: { startJd, endJd, tasks: [ { name, jds: [..] } ] }
    $payload = [
        'startJd' => (int)$start_date,
        'endJd' => (int)$end_date,
        'tasks' => [],
        'orderBy' => isset($order_by) ? $order_by : 'campaign'
    ];
    foreach ($accomplished_tasks as $gid => $rows) {
        if (!isset($gridIdToShort[$gid])) continue;
        $jds = [];
        foreach ($rows as $r) {
            $jds[] = (int)$r['mark_date'];
        }
        sort($jds, SORT_NUMERIC);
        $label = isset($gridIdToMeta[$gid]['label']) ? $gridIdToMeta[$gid]['label'] : '';
        $labelId = isset($gridIdToMeta[$gid]['label_id']) ? $gridIdToMeta[$gid]['label_id'] : null;
        $frequencyId = isset($gridIdToMeta[$gid]['frequency_id']) ? $gridIdToMeta[$gid]['frequency_id'] : null;
        $type = isset($gridIdToMeta[$gid]['type']) ? $gridIdToMeta[$gid]['type'] : null;
		$labelOrd = isset($gridIdToMeta[$gid]['label_ord']) ? $gridIdToMeta[$gid]['label_ord'] : null;
        $payload['tasks'][] = [
            'name' => $gridIdToShort[$gid],
            'jds' => $jds,
            'label' => $label,
            'labelId' => $labelId,
			'frequencyId' => $frequencyId,
			'type' => $type,
			'labelOrd' => $labelOrd
        ];
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  ?>;

  // Convert Julian Day Number to a UTC Date (start of day)
  function jdToUtcDate(jd) {
    // Algorithm to convert JDN to Gregorian date in UTC
    let l = jd + 68569;
    const n = Math.floor(4 * l / 146097);
    l = l - Math.floor((146097 * n + 3) / 4);
    const i = Math.floor(4000 * (l + 1) / 1461001);
    l = l - Math.floor(1461 * i / 4) + 31;
    const j = Math.floor(80 * l / 2447);
    const day = l - Math.floor(2447 * j / 80);
    l = Math.floor(j / 11);
    const month = j + 2 - 12 * l;
    const year = 100 * (n - 49) + i + l;
    // Return midnight UTC for that Gregorian date
    return new Date(Date.UTC(year, month - 1, day));
  }

  // Group consecutive JDs into [start,end) ranges in ms
  function groupJdRanges(jds) {
    if (!jds || jds.length === 0) return [];
    const ranges = [];
    let start = jds[0];
    let prev = jds[0];
    for (let idx = 1; idx < jds.length; idx++) {
      const jd = jds[idx];
      if (jd === prev + 1) {
        prev = jd;
      } else {
        // push [start, prev]
        const startMs = jdToUtcDate(start).getTime();
        const endMs = jdToUtcDate(prev + 1).getTime(); // exclusive end (next midnight)
        ranges.push([startMs, endMs]);
        start = jd;
        prev = jd;
      }
    }
    // final range
    ranges.push([jdToUtcDate(start).getTime(), jdToUtcDate(prev + 1).getTime()]);
    return ranges;
  }
  // Same grouping but keep values in JD for set inversion
  function groupJdRangesRaw(jds) {
    if (!jds || jds.length === 0) return [];
    const raw = [];
    let start = jds[0];
    let prev = jds[0];
    for (let idx = 1; idx < jds.length; idx++) {
      const jd = jds[idx];
      if (jd === prev + 1) {
        prev = jd;
      } else {
        raw.push([start, prev + 1]); // end is exclusive
        start = jd;
        prev = jd;
      }
    }
    raw.push([start, prev + 1]);
    return raw;
  }
  // Invert ranges within [startJd, endJdExclusive) to get "missing" ranges
  function invertRanges(startJd, endJdExclusive, rangesRaw) {
    const missing = [];
    let cursor = startJd;
    rangesRaw.forEach(r => {
      const s = r[0], e = r[1];
      if (cursor < s) missing.push([cursor, s]);
      cursor = Math.max(cursor, e);
    });
    if (cursor < endJdExclusive) missing.push([cursor, endJdExclusive]);
    return missing;
  }

  // Dynamic sorting helpers
  const baseTasks = chartPayload.tasks.slice();
  function sortTasks(orderBy) {
    const tasks = baseTasks.slice();
    if (orderBy === 'completed-asc') {
      tasks.sort((a, b) => a.jds.length - b.jds.length);
    } else if (orderBy === 'completed-desc') {
      tasks.sort((a, b) => b.jds.length - a.jds.length);
    } else if (orderBy === 'alpha') {
      tasks.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
    } else {
      // Mission page order:
      // 1) type order; 2) frequency (daily: 15 first); 3) label_ord within same label; 4) label; 5) task name
      const typeRank = { daily: 0, weekly: 1, shabbos: 2 };
      tasks.sort((a, b) => {
        const ta = (a.type && a.type in typeRank) ? typeRank[a.type] : 99;
        const tb = (b.type && b.type in typeRank) ? typeRank[b.type] : 99;
        if (ta !== tb) return ta - tb;
        const afRaw = (a.frequencyId ?? 9999);
        const bfRaw = (b.frequencyId ?? 9999);
        const af = (a.type === 'daily' && afRaw === 15) ? -1 : afRaw;
        const bf = (b.type === 'daily' && bfRaw === 15) ? -1 : bfRaw;
        if (af !== bf) return af - bf;
        if ((a.labelId ?? null) !== null && a.labelId === (b.labelId ?? null)) {
          const lao = (a.labelOrd ?? 9999);
          const lbo = (b.labelOrd ?? 9999);
          if (lao !== lbo) return lao - lbo;
        }
        const lab = (a.label || '').localeCompare(b.label || '');
        if (lab !== 0) return lab;
        return (a.name || '').localeCompare(b.name || '');
      });
    }
    return tasks;
  }

  // Build series from tasks
  function buildSeriesFrom(sortedTasks) {
    const dataPoints = [];
    const missingPoints = [];
    const countsByNameLocal = {};
    const totalDaysInWindow = (chartPayload.endJd - chartPayload.startJd + 1);
    sortedTasks.forEach(t => {
      const ranges = groupJdRanges(t.jds);
      const raw = groupJdRangesRaw(t.jds);
      const inv = invertRanges(chartPayload.startJd, chartPayload.endJd + 1, raw);
      if (!countsByNameLocal[t.name]) countsByNameLocal[t.name] = { done: 0, total: totalDaysInWindow };
      countsByNameLocal[t.name].done += t.jds.length;
      ranges.forEach(r => {
        dataPoints.push({ x: t.name, y: r });
      });
      inv.forEach(r => {
        const startMs = jdToUtcDate(r[0]).getTime();
        const endMs = jdToUtcDate(r[1]).getTime();
        missingPoints.push({ x: t.name, y: [startMs, endMs] });
      });
    });
    return { dataPoints, missingPoints, countsByNameLocal };
  }

  // X axis min/max from selected date range
  const xMin = jdToUtcDate(chartPayload.startJd).getTime();
  // end is exclusive: add one day
  const xMax = jdToUtcDate(chartPayload.endJd + 1).getTime();

  let currentOrder = chartPayload.orderBy || 'campaign';
  const initialTasks = sortTasks(currentOrder);
  const builtInitial = buildSeriesFrom(initialTasks);
  let chartCountsByName = builtInitial.countsByNameLocal;
  const dataPoints = builtInitial.dataPoints;
  const missingPoints = builtInitial.missingPoints;
  const uniqueTasks = Array.from(new Set(initialTasks.map(t => t.name)));
  // Make each bar ~30px tall by sizing the total chart height and slot/bar ratio
  const ROW_HEIGHT_PX = 30;    // desired bar thickness
  const ROW_GAP_PX = 4;        // gap between rows
  const SLOT_PX = ROW_HEIGHT_PX + ROW_GAP_PX;
  const BAR_HEIGHT_PERCENT = Math.max(1, Math.min(100, Math.round((ROW_HEIGHT_PX / SLOT_PX) * 100)));
  // Add some extra space (x-axis + padding)
  const chartHeight = Math.max(200, uniqueTasks.length * SLOT_PX + 60);

  const options = {
    series: [
      { name: 'Missing', data: missingPoints },
      { name: 'Accomplished', data: dataPoints }
    ],
    chart: { type: 'rangeBar', height: chartHeight, toolbar: { show: false } },
    plotOptions: { bar: { horizontal: true, barHeight: BAR_HEIGHT_PERCENT + '%', rangeBarGroupRows: true } },
    xaxis: {
      type: 'datetime',
      min: xMin,
      max: xMax,
      labels: { format: 'MMM d' }
    },
    yaxis: {
      labels: {
        style: { fontSize: '12px' },
        formatter: function (val) {
          const m = chartCountsByName[val];
          if (!m) return val;
          return val + ' (' + m.done + ' / ' + m.total + ')';
        }
      }
    },
    colors: ['#e0e0e0', '#2ecc71'],
    dataLabels: { enabled: false },
    grid: { xaxis: { lines: { show: true } }, padding: { right: 0 } },
    tooltip: { x: { format: 'MMM d' } }
  };

  const chart = new ApexCharts(document.querySelector('#chart-container'), options);
  chart.render();

  // Dynamic sort control
  const sortSelect = document.getElementById('chartSortSelect');
  if (sortSelect) {
    sortSelect.value = currentOrder;
    sortSelect.addEventListener('change', function () {
      currentOrder = this.value;
      const sorted = sortTasks(currentOrder);
      const rebuilt = buildSeriesFrom(sorted);
      chartCountsByName = rebuilt.countsByNameLocal;
      chart.updateSeries([
        { name: 'Missing', data: rebuilt.missingPoints },
        { name: 'Accomplished', data: rebuilt.dataPoints }
      ], true);
      chart.updateOptions({
        yaxis: { labels: { formatter: function (val) {
          const m = chartCountsByName[val];
          if (!m) return val;
          return val + ' (' + m.done + ' / ' + m.total + ')';
        }}}
      }, false, false);
    });
  }
</script>
<?php endif; ?>
<script>
    var lang = <?=$lang?>;

    function updateFormValues() {
        const start_date = "<?=isset($start_date) ? date('Y-m-d', jdtounix($start_date)) : ''?>";
        const end_date = "<?=isset($end_date) ? date('Y-m-d', jdtounix($end_date)) : ''?>";
        const date_range = "<?=$date_range ?? ''?>";
        const campaign = "<?=$campaign_chosen ?? ''?>";
        const order_by = "<?=isset($order_by) ? $order_by : (isset($_POST['order_by']) ? $_POST['order_by'] : '')?>";
        if (start_date) $('input[name="start_date"]').val(start_date);
        if (end_date) $('input[name="end_date"]').val(end_date);
        if (date_range) $('select[name="date_range"]').val(date_range);
        if (campaign) $('select[name="campaign"]').val(campaign);
        if (order_by) $('select[name="order_by"]').val(order_by);
    }

    function validateForm() {
        const start_date = $('input[name="start_date"]').val();
        const end_date = $('input[name="end_date"]').val();
        const date_range = $('select[name="date_range"]').val();
        const campaign = $('select[name="campaign"]').val();
        if (campaign == 0) {
            alert('Please select a campaign');
            return false;
        }
        return true;   
    }
    document.addEventListener("DOMContentLoaded", function (event) {
        updateFormValues();
        $(function () {
            if (navigator.userAgent.includes("Firefox")) {
                $(".content").css("margin-bottom", "120px;");
            }
            var url = location.toString();
            var pos = url.indexOf('id=');
            var id = url.substring(pos + 3);
            var str = id.split("&")[0];
            var version = id.split("&")[1] ? "&" + id.split("&")[1] : "";
            // get the date from the url...
            var d = id.indexOf('&');
            if (d > 0) {
                id = id.substring(0, d);
            }
            // make sure that the user is signed in....
            $.post('/mobile/reg/ajax/checkAuth.php', {
                user_id: id,
                admin_id: Cookies.get('admin')
            }, function (success) {
                if (success === 0) {
                    window.location = "/mobile";
                }
            });


            $("#main").addClass("animated fadeIn");
            // if the language is set to 2 (yiddish) in the html
            if (lang == 2) {
                $(".container").eq(1).addClass('he'); // add a he class to the page
                $(".container.he").attr('dir',
                    'rtl'); // set the text direction to the other direction...
                $(".personalImg").css({
                    "right": "2%"
                }); // move the profile photo over a bit...
            } else {
                $(".personalImg").css({
                    "left": "2%"
                }); // move the profile image a but from the edge...
            }
            // setup the links on the bottom of the page
            $("#missionsLink").attr('href', 'missionsNew.html?id=' + id);
            $("#rankLink").attr('href', 'reg/rank.html?id=' + id);
            $("#storeLink").attr('href', 'store/index.html?id=' + id);
            $("#goalsLink").parent().addClass('active');
            // when a panel is opened...
            $(".panel-heading").click(function () {
                var c = $(this).parent().attr('class');
                var height = $(this).parent().find('.collapse')[0].scrollHeight;

                if (c.indexOf('open') > 0) {
                    $(this).parent().removeClass('open');
                    $(this).parent().find('.collapse').removeClass('in');
                    $(this).parent().find('.collapse').css({
                        "height": '0px'
                    });
                } else {
                    $(this).parent().addClass('open');
                    $(this).parent().find('.collapse').addClass('in');
                    $(this).parent().siblings().removeClass('open');
                    $(this).parent().siblings().find('.collapse').removeClass(
                        'in');

                    $(this).parent().find('.collapse').css({
                        "height": height + "px"
                    });
                    $(this).parent().siblings().find('.collapse').css({
                        "height": '0px'
                    });
                }
            });

            // loadup the users image...
            $.post('/mobile/reg/ajax/getPhoto.php', {
                user_id: id
            }, function (success) {
                var info = $.parseJSON(success);
                var html = '<a href="/mobile/reg/medals/index.html?id=' + id +
                    '">';
                if (info.mobile_pic) html +=
                    '<img id="userImg" src="https://mashpia.com/mobile/reg/' + info
                        .mobile_pic + '">';
                else if (info.thumb) html +=
                    '<img id="userImg" src="https://mashpia.com/thumbs/' + info
                        .thumb + '">';
                else if (info.photo) html +=
                    '<img id="userImg" src="https://mashpia.com/file_view.php?id=' +
                    info.photo + '">';
                html += '</a>';
                $(".personalImg").append(html);
            });
        });
    });
</script>
<script>
    (function (i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function () {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', '//www.google-analytics.com/analytics.js', 'ga');
    ga('create', 'UA-71974937-1', 'auto');
    ga('send', 'pageview');
</script>
<script class="jsbin" src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://unpkg.com/i18next@19.6.3/dist/umd/i18next.min.js"></script>
