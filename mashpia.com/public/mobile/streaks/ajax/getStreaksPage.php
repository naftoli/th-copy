<?php
ini_set("display_errors", 1);
ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/db.php';
 require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$dates = GlobalSettings::getCurYearDates();
$start = $dates['start'];
$end = $dates['end'];
$user_id = mysql_real_escape_string( isset($_GET['id']) ? $_GET['id'] : 0 ); // get the user id

$sql = "select first, last, user_photo_id, lang_id from users where user_id = " . $user_id;
$result = mysql_query($sql);
$row = mysql_fetch_assoc($result);
$photo = $row['user_photo_id'];
$first = $row['first'];
$lang = $row['lang_id'] ? $row['lang_id'] : 1;

// get campaigns the user is assigned to
$sql = "SELECT subject_id, subject_name 
        FROM user_tracks ut 
        JOIN subjects USING (subject_id) 
        WHERE user_id = $user_id 
        AND enrolled = 1 
        AND subject_id NOT IN (12, 40, 136)";
$result = mysql_query($sql);
$campaigns = [];
while ($row = mysql_fetch_assoc($result)) {
    $campaigns[$row['subject_id']] = $row['subject_name'];
}

require_once '../class.streaks.php';
$streaks = new Streaks($user_id);
$activeStreaks = $streaks->getStreaks();
if (count($activeStreaks) > 0) {
    $disabled = 'disabled';
} else {
    $disabled = '';
}
?>
<style>
    .navbar-header h1 {
        font-weight: 700;
        color: #fff;
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
</style>

<script>
    var lang = <?=$lang?>;
    $(document).ready(function() {
        $('#campaign').change(function() {
            const campaignId = $(this).val();
            if (campaignId == 0) {
                $('#task').empty();
                return;
            }
            $.ajax({
                type : "GET",
                url : '../../../ajax/getTasks.php',
                data : {
                    subject : campaignId,	
                    user : <?=$user_id?>,	
                    start : <?=$start?>,	
                    end : <?=$end?>,	
                    lang : lang,	
                    parent: Cookies.get('admin'), 
                    forStreak: true
                },
                success : function( data ) {
                    const tasks = $.parseJSON( data ); 
                    console.log(tasks);
                    let html = '<option value="0">Select a task</option>';
                    for (let gridId in tasks) {
                        html += `<option value="${gridId}">${tasks[gridId]}</option>`;
                    }
                    $('#task').html(html);
                }
            });
        });
    });

    $("#setup-streak").click(setupStreak);

    function setupStreak() {
        const campaignId = $('#campaign').val();
        const gridId = $('#task').val();
        if (campaignId == 0 || gridId == 0) {
            alert('Please select a campaign and a task');
            return;
        }
        console.log(campaignId, gridId);
        $.post('ajax/setupStreak.php', {
            gridId: gridId, userId: <?=$user_id?>
        }, function( result ) {
            console.log(result);
            const data = $.parseJSON(result);
            if (data.success) {
                alert('Streak setup successfully');
                window.location.reload();
            } else {
                const error = data.error ? data.error : 'Failed to setup streak';
                if (error[2].includes('Duplicate entry')) {
                    alert('Streak already setup');
                    return;
                }
                alert(error);
            }
        }).fail(function( jqXHR, textStatus, errorThrown ) {
            alert('Failed to setup streak: ' + errorThrown);
        });
    }
</script>

<header class="navbar" id="top" role="banner">
    <div class="container">
        <div class="navbar-header">
        	<h1 class="i18n" data-key="myStreaks">My Streaks</h1>
        </div>
    </div>
</header>
<div class="personalImg"></div>

<div class="infobox">
    You can use this page to setup a streak for a campaign.<br />
    A streak is a series of days that you have completed a task for.<br />
    You can setup a streak for a campaign by selecting a campaign and a task.<br />
    All streaks are for 90 days.<br />
    Once you have a streak setup, you cannot choose another streak until the current streak is completed (by completing the task for 90 days).
</div>

<div class="container">
    <div class="content">
        Choose a campaign to setup a streak for:
        <select name="campaign" id="campaign" <?=$disabled?>>
            <option value="0">Select a campaign</option>
            <?php foreach ($campaigns as $id => $campaign) : ?>
                <option value="<?=$id?>"><?=$campaign?></option>
            <?php endforeach; ?>
        </select><br /><br />
        Choose a task to setup a streak for:
        <select name="task" id="task" <?=$disabled?>></select><br /><br />
        <button id="setup-streak" <?=$disabled?>>Setup Streak</button>
    </div>
</div>
<br />

<hr />
<div class="container">
    <div class="content">
        <h2>Active Streaks</h2>
        <ul style="list-style-type: none; padding-left: 0;">
            <?php foreach ($activeStreaks as $gridId => $streak) : ?>
                <li><?=$streak['cat']?> - <?=$streak['name']?> (<?=count($streak['days'])?> days)</li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>