<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

$super = $admin_user['auth'] == 'super';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$year = GlobalSettings::getChidonYear();

$ct = new ChidonTests();
$tracks = $ct->getTypes();

if (count($schools) == 1) {
    $editOnly = true;
    $disabled = ' disabled';
    $selected = ' selected';
} else {
    $editOnly = false;
    $disabled = '';
    $selected = '';
}
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
        width: 45%;
      }
      legend {
        font-size: 16px;
        font-weight: bold;
        padding: 5px;
      }
      <?php if (!$super) : ?>
        #settings {
          display: none;
        }
      <?php endif; ?>
    </style>
</head>
<body>
<?php include($_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
<h1>Chidon Test Settings</h1>
<div class="infobox">
  Chidon HQ has set the standard that an 80% is needed to pass each track and that all are taking the test on level 2 (Harder test).
  <br /><br />
  You have the option to change the passing mark up/down as well as change the test to level 1 (Easier test).
  You can either do this for your full school, individual platoon (class) or per child. Remember to press save!
  <br /><br />
  Please note that the lowest you can choose is 70%. If there is a particular child who needs a lower average.
  Please contact HQ and they will adjust it for you.
  <br /><br />
  PLEASE NOTE: The settings for School/Grade/Child are all different, so changing one will NOT CHANGE the others.
</div>
<br />
<div id="baseSelection">
  Choose Base:
  <select name="baseSelect" id="baseSelect" onchange="setPlatoons()" <?= $selected . $disabled ?>>
      <?php if (! $editOnly) { ?>
        <option value="0">Select Base</option>
      <?php
      }
      foreach ($schools as $id => $name) {
          echo "<option value='$id'>$name</option>";
      }
      ?>
  </select>
</div>
<div id="platoonSelection"></div>
<div id="userSelection"></div>
<br />
<button id="showSettings" onclick="getSettings(); return false;">Show / Refresh Settings</button>
<div id="settings">
  <h2></h2>
  <div id="settingsTable">
    <fieldset style="float: left;">
      <legend>Avg Score</legend>
      <form id="avgScore">
        <p>
          <?php
          foreach ($tracks as $type => $desc) {
            echo "<input type='checkbox' class='tracks' name='tracks[]' value='$type' checked /> $desc ";
            echo "<span id='chidon_passing_avgs_$type'></span>";
            echo "<br />";
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
        <br />
        <button class="save" onclick="save('avgScore'); return false;">Save</button>
      </form>
    </fieldset>

    <fieldset style="float: right;">
      <legend>Avg for Final</legend>
      <form id="avgScoreFinal">
        <p>
            <?php
            foreach ($tracks as $type => $desc) {
                echo "<input type='checkbox' class='tracks' name='tracks[]' value='$type' checked /> $desc ";
                echo "<span id='chidon_final_passing_avgs_$type'></span>";
                echo "<br />";
            }
            ?>
        </p>
        <select name="avgFinal" id="avg_final">
          <option value="0">Select Avg</option>
            <?php
            $i = 70;
            if ($super) $i = 50;
            for (; $i <= 100; $i += 5) {
                echo "<option value='$i'>$i</option>";
            }
            ?>
        </select>
        <br />
        <button class="save" onclick="save('avgScoreFinal'); return false;">Save</button>
      </form>
    </fieldset>

    <div style="clear: both;"></div>
    <br />
    <fieldset>
      <legend>Test Level</legend>
      <form id="levels">
        <p>
          <input type="checkbox" name="tests" id="tests" value="1" checked /> Tests
          <span id='chidon_test_levels_tests'></span><br />
          <input type="checkbox" name="finals" id="finals" value="1" checked /> Finals
          <span id='chidon_test_levels_finals'></span><br />
        </p>
        <select name="level" class="level">
          <option value="0">Select Level</option>
          <option value="1">Level 1</option>
          <option value="2">Level 2</option>
        </select>
        <br />
        <button class="save" onclick="save('levels'); return false;">Save</button>
      </form>
    </fieldset>
    <br />
  </div>
</div>
</body>
<script type="text/javascript">
  $( function() {
    // find out if coming from entering marks page
    let url = new URL(window.location.href)
    let fromMarks = url.searchParams.get('fromMarks')
    if (fromMarks) alert('You can only enter the marks once you have set the avgs per track and level for your school.')
    if (document.getElementById('baseSelect').value != 0) setPlatoons()
  })

  async function getSettings() {
    let school_id = document.getElementById('baseSelect').value
    let class_id = document.getElementById('platoonSelect') ? document.getElementById('platoonSelect').value : 0
    let user_id = document.getElementById('userSelect') ? document.getElementById('userSelect').value : 0
    const res = await fetch('api/getSettings.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ school_id, class_id, user_id })
    })
    const settings = await res.json()
    console.log(settings)

    // set avgs
    let id = user_id > 0 ? user_id : class_id > 0 ? class_id : school_id > 0 ? school_id : 0
    for (let table of ['chidon_passing_avgs', 'chidon_final_passing_avgs']) {
      for (let track of ['maven', 'pro', 'expert', 'genius']) {
        let elem = '#' + table + '_' + track
        let avg = settings[id] && settings[id][table] && settings[id][table][track] ? settings[id][table][track] : 80 // always default to 80
        $(elem).text('(' + avg + ')')
      }
    }
    // set levels
    let testLevel = settings[id] && settings[id]['chidon_test_levels'] && settings[id]['chidon_test_levels']['tests']
      && settings[id]['chidon_test_levels']['tests'] == 1 ? 1 : 2
    let finalLevel = settings[id] && settings[id]['chidon_test_levels'] && settings[id]['chidon_test_levels']['finals']
      && settings[id]['chidon_test_levels']['finals'] == 1 ? 1 : 2
    $('#chidon_test_levels_tests').text('(' + testLevel + ')')
    $('#chidon_test_levels_finals').text('(' + finalLevel + ')')

    // show settings
    document.getElementById('settings').style.display = 'block'
  }

  async function setPlatoons() {
    // create platoon selection
    let html = ''
    let info = await getInfo('baseSelect', 'classes')
    if (! info.length) document.getElementById('userSelection').innerHTML = html
    else {
      html += 'Choose Platoon: '
      html += '<select name="platoonSelect" id="platoonSelect" onchange="setUsers()">'
      html += '<option value="0">All Platoons</option>'
      info.map(platoon => {
        let grade = platoon.class_grade + (platoon.class_sub ? '-' + platoon.class_sub : '')
        html += `<option value="${platoon.class_id}">${grade}</option>`
      })
    }
    document.getElementById('platoonSelection').innerHTML = html;
  }

  async function setUsers() {
    // create user selection
    let html = '';
    let info = await getInfo('platoonSelect', 'users')
    if (info.length) {
      html += 'Choose Chayol: '
      html += '<select name="userSelect" id="userSelect">'
      html += '<option value="0">All Chayolim</option>'
      info.map(user => {
        html += `<option value="${user.user_id}">${user.first} ${user.last}</option>`
      })
    }
    document.getElementById('userSelection').innerHTML = html;
  }

  async function getInfo(elem, table) {
    let info = []
    let id = document.getElementById(elem).value
    if (id > 0) {
      const res = await fetch('api/getInfo.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id, table })
      })
      info = await res.json()
    }
    return info
  }

  async function save(id) {
    let info = new FormData(document.forms[id])
    info.set('elem', id)
    let school_id = document.getElementById('baseSelect') ? document.getElementById('baseSelect').value : 0
    let class_id = document.getElementById('platoonSelect') ? document.getElementById('platoonSelect').value : 0
    let user_id = document.getElementById('userSelect') ? document.getElementById('userSelect').value : 0
    info.set('school_id', school_id)
    info.set('class_id', class_id)
    info.set('user_id', user_id)

    if (validateForm(id, info)) {
      let conf = true
      if (user_id == 0) {
        conf = confirm('Please note, if you have already changed settings in the past, those children will stay ' +
          'with the same settings and will not be updated with what you are doing now, you will need to update those children manually.' +
          '\n\nAre you sure you want to save?')
      }
      if (conf == false) return false
      const res = await fetch('api/saveSettings.php', {
        method: 'POST',
        body: info
      })
      const data = await res.json()
      if (data.error) alert(data.error)
      else {
        alert('Saved!')
        getSettings()
      }
    }
  }

  function validateForm(id, info) {
    switch (id) {
      case 'avgScore':
        if (!info.get('avg') || info.get('avg') == 0) {
          alert('Please select an average score')
          return false
        }
        if (!info.get('tracks[]')) {
          alert('Please select at least one track')
          return false
        }
        break
      case 'avgScoreFinal':
        if (!info.get('avgFinal') || info.get('avgFinal') == 0) {
          alert('Please select an average score')
          return false
        }
        if (!info.get('tracks[]')) {
          alert('Please select at least one track')
          return false
        }
        break
      case 'levels':
        if (!info.get('level') || info.get('level') == 0) {
          alert('Please select a level')
          return false
        }
        if (!info.get('tests') && !info.get('finals')) {
          alert('Please select what you are applying the level to')
          return false
        }
        break
    }
    return true
  }
</script>
</html>
