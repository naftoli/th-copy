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
    const a = Number(jd) + 32044;
    const b = Math.floor(((4*a) + 3)/146097);
    const c = a - Math.floor((146097*b)/4);
    const d = Math.floor(((4*c) + 3)/1461);
    const e = c - Math.floor((1461 * d)/4);
    const f = Math.floor(((5*e) + 2)/153);

    const D = e + 1 - Math.floor(((153*f) + 2)/5);
    const M = f + 3 - 12 - Math.round(f/10);
    const Y = (100*b) + d - 4800 + Math.floor(f/10);

    return new Date(Y,M,D);
  }

  // once document loaded get info for table as json
  document.addEventListener('DOMContentLoaded', async function () {
    const year = document.getElementById('year').value
    const res = await fetch('/registration/getRegistration.php?year=' + year)
    const info = await res.json()
    const data = info.data
    // console.log(data)
    const schools = info.schools
    const totals = {}
    let html = ''
    for (let school in data) {
      if (school != 0) {
        html += "<h2>" + schools[school] + "</h2>"
      }
      html += `
        <table><tr>${school > 0 ? '' : "<th>School</th>"}${[61, 269].includes(school) ? "<th>Family ID</th>" : '' }<th>Grade</th><th>Student</th><th>Serial #</th><th>TH Started Date</th>
        <th>${year} Registration Date</th></tr>
      `
      for (let user of data[school]) {
        if (!totals[user.school_id]) totals[user.school_id] = {}
        const grade = user.class_grade + (user.class_sub ? '-' + user.class_sub : '')
        if (!totals[user.school_id][grade]) totals[user.school_id][grade] = 0
        totals[user.school_id][grade]++
        html += `
          <tr>${school > 0 ? '' : "<td>" + schools[user.school_id] + "</td>"}${[61, 269].includes(school) ? "<td>" + user.admin_id + "</td>" : '' }<td>${grade}</td><td>${user.first + ' ' + user.last}</td>
          <td>${user.user_serial}</td><td>${jdToGreg(user.user_start_date).toDateString()}</td><td>${user.reg_date}</td></tr>
        `
      }
      html += "</table><div class='page-break'></div>"
    }
    let grandTotal = 0
    html += "<h2>Totals</h2>"
    html += "<table><tr><th>School<th>Grade</th><th>Total</th></tr>"
    for (let school in totals) {
      for (let grade in totals[school]) {
        grandTotal += totals[school][grade]
        html += "<tr><td>" + schools[school] + "</td><td>" + grade + "</td><td>" + totals[school][grade] + "</td></tr>"
      }
    }
    html += "<tr><td><b>Grand Total</b></td><td></td><td><b>" + grandTotal + "</b></td></tr></table>"
    document.getElementById('reg').innerHTML = html
  })
</script>
</html>