<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = array('school');
require('header.php');

require_once 'class.adminSchools.php';
require_once 'class.globalSettings.php';

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$year = GlobalSettings::getRegistrationYear();
?>
<!DOCTYPE html>
<HTML>
<HEAD>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Registered Report</title>
  <link href="admin_styles.css" rel="stylesheet" type="text/css">
  <style>
    tr, th, td {
      padding: 10px;
      font-size: 14px;
      border-bottom: 1px solid #f0f0f0;
    }

    .page-break {
      page-break-after: always;
    }
  </style>
</HEAD>

<BODY>
<? include('admin_header.php'); ?>
<h1>Registered Report</h1>
<select name="year" id="year">
    <?php
    for (; $year > ($year - 5); $year--) {
        echo "<option value='$year'>$year</option>";
    }
    ?>
</select>
<br /><br />
<div id="main"></div>
</body>
<script>
    document.getElementById('year').addEventListener('change', function () {
        window.location.href = 'registered_report.php?year=' + this.value;
    });

    // once document loaded get info for table as json
    document.onload( async () => {
      const year = document.getElementById('year')
      const res = fetch('/registration/getRegistration.php', {
        method: 'GET',
        body: JSON.stringify(year),
        headers: {
          'Content-Type': 'application/json'
        }
      })
      const data = await res.json()
      console.log(data)
      let html = ''
      for (let school in data) {
        if (school != 0) {
          html +=
        }
      }
    })
</script>
</html>