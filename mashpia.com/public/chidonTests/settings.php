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
<div id="baseSelection">
  <select name="baseSelect" id="baseSelect" onchange="setPlatoons()" <?= $selected . $disabled ?>>
      <?php if (! $editOnly) { ?>
        <option value="0">Select Base</option>
        <option value="-1">All</option>
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
<div id="settings">
  <h2></h2>
  <div id="settingsTable">
    <fieldset style="float: left;">
      <legend>Avg Score</legend>
      <form id="avgScore">
        <p>
          <?php
          foreach ($tracks as $type => $desc) {
            echo "<input type='checkbox' class='tracks' name='tracks[]' value='$type' /> $desc<br />";
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
                echo "<input type='checkbox' class='tracks' name='tracks[]' value='$type' /> $desc<br />";
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
          <input type="checkbox" name="tests" id="tests" value="1" /> Tests<br />
          <input type="checkbox" name="finals" id="finals" value="1" /> Finals<br />
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
    if (document.getElementById('baseSelect').value != 0) setPlatoons()
  })

  async function setPlatoons() {
    // show settings
    document.getElementById('settings').style.display = 'block'
    // create platoon selection
    let html = ''
    let info = await getInfo('baseSelect', 'classes')
    if (! info.length) document.getElementById('userSelection').innerHTML = html
    else {
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
      html += '<select name="userSelect" id="userSelect">'
      html += '<option value="0">All Users</option>'
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
      const res = await fetch('api/saveSettings.php', {
        method: 'POST',
        body: info
      })
      const data = await res.json()
      if (data.error) alert(data.error)
      else alert('Saved!')
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
