<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = array('school');
require('header.php');

require_once 'class.globalSettings.php';
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
    $selected = isset($_GET['year']) ? $_GET['year'] : 0;
    for ($i = 0; $i <= 5; $i++) {
        $yr = $year - $i;
        echo "<option value='$yr'";
        if ($yr == $selected) echo " selected";
        echo ">$yr</option>";
    }
    ?>
</select>
<br/><br/>
<div id="reg"></div>
</body>
<script>
  document.getElementById('year').addEventListener('change', function () {
    window.location.href = 'registered_report.php?year=' + this.value;
  });

  const jdToGreg = jd => {
    // convert a Julian number to a Gregorian Date.
    //    S.Boisseau / BubblingApp.com / 2014
    var a = Number(jd) + 32044;
    var b = Math.floor(((4*a) + 3)/146097);
    var c = a - Math.floor((146097*b)/4);
    var d = Math.floor(((4*c) + 3)/1461);
    var e = c - Math.floor((1461 * d)/4);
    var f = Math.floor(((5*e) + 2)/153);

    var D = e + 1 - Math.floor(((153*f) + 2)/5);
    var M = f + 3 - 12 - Math.round(f/10);
    var Y = (100*b) + d - 4800 + Math.floor(f/10);

    return new Date(Y,M,D);
  }

  // once document loaded get info for table as json
  document.addEventListener('DOMContentLoaded', async function () {
    const year = document.getElementById('year').value
    const res = await fetch('/registration/getRegistration.php?year=' + year)
    const info = await res.json()
    const data = info.data
    console.log(data)
    const schools = info.schools
    let html = ''
    for (let school in data) {
      if (school != 0) {
        html += "<h2>" + schools[school] + "</h2>"
      }
      html += `
        <table><tr>${school > 0 ? '' : "<th>School</th>"}<th>Grade</th><th>Student</th><th>Serial #</th><th>TH Started Date</th>
        <th>${year} Registration Date</th></tr>
      `
      for (let user of data[school]) {
        const grade = user.class_grade + (user.class_sub ? '-' + user.class_sub : '')
        html += `
          <tr>${school > 0 ? '' : "<td>" + schools[user.school_id] + "</td>"}<td>${grade}</td><td>${user.first + ' ' + user.last}</td>
          <td>${user.user_serial}</td><td>${jdToGreg(user.user_start_date).toDateString()}</td><td>${user.reg_date}</td></tr>
        `
      }
      html += "</table><div class='page-break'></div>"
    }
    document.getElementById('reg').innerHTML = html
  })
</script>
</html>