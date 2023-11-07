<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$year = GlobalSettings::getChidonYear();

$super = $admin_user['auth'] == 'super';
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Chidon Test Settings</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
      tr, th, td {
        font-size: 14px;
        padding: 10px;
      }
      fieldset {
        border-radius: 10px;
        border: 1px solid grey;
        padding: 10px;
      }
      legend {
        font-size: 16px;
        font-weight: bold;
        padding: 5px;
      }
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>Chidon Test Settings</h1>
<div id="baseSelection"></div>
<div id="platoonSelection"></div>
<div id="userSelection"></div>
<h2></h2>
<div id="settings">
  <div id="settingsTable">
    <fieldset>
      <legend>Avg Score</legend>
      <p>
          <?php
          $ct = new ChidonTests();
          $tracks = $ct->getTypes();
          foreach ($tracks as $type => $desc) {
            echo "<input type='checkbox' class='track' name='track' value='$type' /> $desc<br />";
          }
          ?>
      </p>
      <select name="avg" id="avg">
        <option value="0">Select Avg</option>
        <?php
        $i = 70;
        if ($super) $i = 50;
        for (; $i <= 100; $i += 5) {
          echo "<option value='$i'>$i</option>";
        }
        ?>
      </select>
    </fieldset>
    <br />

    <fieldset>
      <legend>Avg for Final</legend>
      <p>
          <?php
          $ct = new ChidonTests();
          $tracks = $ct->getTypes();
          foreach ($tracks as $type => $desc) {
              echo "<input type='checkbox' class='trackFinal' name='trackFinal' value='$type' /> $desc<br />";
          }
          ?>
      </p>
      <select name="avgFinal" id="avgFinal">
        <option value="0">Select Avg</option>
          <?php
          $i = 70;
          if ($super) $i = 50;
          for (; $i <= 100; $i += 5) {
              echo "<option value='$i'>$i</option>";
          }
          ?>
      </select>
    </fieldset>
    <br />

    <fieldset>
      <legend>Test Level</legend>
      <p>
          <?php
          $ct = new ChidonTests();
          $tracks = $ct->getTypes();
          foreach ($tracks as $type => $desc) {
              echo "<input type='checkbox' class='trackLevel' name='trackLevel' value='$type' /> $desc<br />";
          }
          ?>
      </p>
      <select name="level" class="level">
        <option value="0">Select Level</option>
        <option value="1">Level 1</option>
        <option value="2">Level 2</option>
      </select>
    </fieldset>
    <br />

<!--    <fieldset>-->
<!--      <legend>Reward Type</legend>-->
<!--      <select name="rewardType" id="rewardType">-->
<!--        <option value="0">Select Reward Type</option>-->
<!--        --><?php
//          $ct = new ChidonTests();
//          $types = $ct->getRewardTypes();
//          foreach ($types as $type => $desc) {
//            echo "<option value='$type'>$desc</option>";
//          }
//        ?>
<!--      </select>-->
<!--    </fieldset>-->
<!--    <br />-->

    <br />
    <button id="save">Save</button>
  </div>
</div>
</body>
<script type="text/javascript">
  let bases = <?= json_encode($schools) ?>;
  let year = <?= $year ?>;

  function setBases() {
    let html = ''
    let baseIDs = Object.keys(bases)
    let editOnly = baseIDs.length == 1
    html += `<select id="baseSelect" onchange="setPlatoons()" ${editOnly ? 'disabled' : ''}>`
    if (!editOnly) html += '<option value="">Select Base</option>';
    for (let id of baseIDs) {
      html += '<option value="' + id + '">' + bases[id] + '</option>';
    }
    html += '</select>';
    document.getElementById('baseSelection').innerHTML = html;
    if (editOnly) setPlatoons();
  }

  async function setPlatoons() {
    // get platoons based on this base
    const res = await fetch('api/getPlatoons.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ school_id: document.getElementById('baseSelect').value })
    })
    let platoons = await res.json()

    let html = '<select name="platoonSelect" id="platoonSelect" onchange="setUsers()">'
    html += '<option value="0">Select Platoon</option>'
    html += '<option value="-1">All</option>'
    platoons.map(platoon => {
      let grade = platoon.class_grade + (platoon.class_sub ? '-' + platoon.class_sub : '')
      html += `<option value="${platoon.class_id}">${grade}</option>`
    })
    document.getElementById('platoonSelection').innerHTML = html;
  }

  async function setUsers() {
    // check if all platoons are selected
    let platoonSelect = document.getElementById('platoonSelect')
    let allPlatoons = platoonSelect.value == '-1'
    if (allPlatoons) return

    // get users for specific platoon
    const res = await fetch('api/getUsers.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ class_id: platoonSelect.value })
    })
    let users = await res.json()

    let html = '<select name="userSelect" id="userSelect">'
    html += '<option value="0">Select User</option>'
    html += '<option value="-1">All</option>'
    users.map(user => {
      html += `<option value="${user.user_id}">${user.first} ${user.last}</option>`
    })
    document.getElementById('userSelection').innerHTML = html;
  }

  document.onload = setBases();
</script>
</html>
