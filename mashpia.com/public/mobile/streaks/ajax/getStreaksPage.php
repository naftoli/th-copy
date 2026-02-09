<?php
// ini_set("display_errors", 1);
// ini_set('error_reporting', E_ALL);
ini_set('memory_limit', '256M');
ini_set('max_execution_time', 300);

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
        AND subject_id NOT IN (12, 15, 40, 136)";
$result = mysql_query($sql);
$campaigns = [];
while ($row = mysql_fetch_assoc($result)) {
    $campaigns[$row['subject_id']] = $row['subject_name'];
}

$disabled = '';
require_once '../classes/class.streaks.php';
$streaks = new Streaks($user_id, $start, $end);
$activeStreaks = $streaks->getStreaks();
if (count($activeStreaks) > 0) {
    foreach ($activeStreaks as $streak) {
        if ($streak['days_done'] < $streak['days_needed']) {
            $disabled = 'disabled';
            break;
        }
    }
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
        margin: 10px;
        padding: 15px;
        font-size: 14px;
        background-color: #f0f0f0;
        border: 1px solid #ccc;
        border-radius: 10px;
        color: chocolate;
    }
    progress {
        height: 50px;
        margin-left: 10px;
        vertical-align: middle;
    }
    #tasks-accomplished {
        margin: auto;
        margin-bottom: 20px;
    }
    .campaign-container, .task-container, .button-container {
        line-height: 1.5;
        margin: auto 15px;

    }
    select, button {
        margin: 10px auto;
    }
</style>
<script src="/jquery.js"></script>
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
                    for (let streakId in tasks) {
                        html += `<option value="${streakId}">${tasks[streakId]}</option>`;
                    }
                    $('#task').html(html);
                }
            });
        });
    });

    $("#setup-streak").click(setupStreak);

    function setupStreak() {
        const campaignId = $('#campaign').val();
        const streakId = $('#task').val();
        if (campaignId == 0 || streakId == 0 || streakId == '0') {
            alert('Please select a campaign and a task');
            return;
        }
        console.log(campaignId, streakId);
        $.post('ajax/setupStreak.php', {
            streakId: streakId, userId: <?=$user_id?>
        }, function( result ) {
            console.log(result);
            const data = $.parseJSON(result);
            if (data.success) {
                alert('Streak setup successfully');
                window.location.reload();
            } else {
                const error = data.error ? data.error : 'Failed to setup streak';
                if (error.includes('Duplicate entry')) {
                    alert('Streak already setup');
                    return;
                }
                alert(error);
            }
        })
    }
</script>

<header class="navbar" id="top" role="banner">
    <div class="container">
        <div class="navbar-header">
        	<h1 class="i18n" data-key="myStreaks">Streak Hachlota</h1>
        </div>
    </div>
</header>
<div class="personalImg"></div>

<div class="container">
    <div class="content">
        <button id="back-to-missions">Back to Missions</button>
        <script>
            $("#back-to-missions").click(function() {
                window.location.href = '/mobile/missionsNew.html?id=' + <?=$user_id?>;
            });
        </script>
        <div class="infobox">
        Pick a campaign and task.<br /><br />
        Do it 90 days in a row to give the Rebbe nachas, and unlock your next Streak Hachlota!
        </div>
        <!-- <button id="tasks-accomplished" class="btn btn-default" onclick="javascript: location.href='tasksAccomplished.php?id=<?=$user_id?>'">View Tasks Accomplished</button> -->
        <div class='campaign-container'>
            Choose a campaign or all campaigns:
            <select name="campaign" id="campaign" <?=$disabled?>>
                <option value="0">Select a campaign</option>
                <option value="-1">All campaigns</option>
                <?php foreach ($campaigns as $id => $campaign) : ?>
                    <option value="<?=$id?>"><?=$campaign?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class='task-container'>
            Choose a task to setup a streak for:
            <select name="task" id="task" <?=$disabled?>></select>
        </div>
        <div class='button-container'>
            <button id="setup-streak" <?=$disabled?>>Setup Streak</button>
        </div>
    </div>
</div>
<hr />

<?php if (count($activeStreaks) > 0) : ?>
<div class="container">
    <div class="content">
        <h2>Active Streak Hachloto(s)</h2>
        <ul style="list-style-type: none; padding-left: 0;">
            <?php foreach ($activeStreaks as $streakId => $streak) : ?>
                <li>
                    <b><?=$streak['cat']?></b> - <?=$streak['name']?> (<?=$streak['days_done']?> days) 
                    <progress value="<?=$streak['days_done']?>" max="<?=$streak['days_needed']?>"></progress>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>