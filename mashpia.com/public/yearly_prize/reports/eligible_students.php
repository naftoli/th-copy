<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

/*
  * page should show all students that have registered for this year
  * as well as base commanders, teachers, principals, etc
  */

/***************** AUTHENTICATION **********************/
$admin_auth = array('school'); 
require_once $_SERVER["DOCUMENT_ROOT"].'/header.php';

/***************** EXTERNAL DEPENDENCIES **********************/
require_once $_SERVER["DOCUMENT_ROOT"].'/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.parshos.php';

/***************** GET SOME BASIC INFORMATION **********************/
$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

$year = GlobalSettings::getCurrentYear();
$parshos = Parshos::getParshos($year);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Yearly Gift Eligibility Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css">
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css">
        <link href="../css/grey_select.css" rel="stylesheet" type="text/css">
        <link href="../css/small_tables.css" rel="stylesheet" type="text/css">
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <link href="../css/fancy_checks.css" rel="stylesheet" type="text/css">
        <style>
            h3 {margin: 2%;border-bottom: 1px solid #aaa;}h4 {font-size: .95em;padding: 1%;}a.button{display: inline-block}tr {text-align: left;}
            td.wide {min-width: 125px;} div#table-marks {overflow-x: auto;overflow-y: auto;} td, th{white-space: nowrap;} .fancy-check-container {font-size: 1.5em;}
        </style>
    </head>
    <body>
        <? include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); // load the basic UI ?>
        <h1>Yearly Prize Eligibility Report</h1>
        <p class="center" style="text-align: center;">Click
            <a target="_blank" href="https://docs.google.com/document/d/1FKrfoxTUTORwW9lO3Kx0mUY9wVeCBFCn0noQzVoj4q8/edit">here</a>
            for the complete rewards manual
        </p>
        <div style="font-weight: bold">
            This gift is only possible due to the generosity of Rabbi Moshe & Ruti Weiss.
            <br /><br />
            To show your appreciation:
            <br />
            <br />
            <ul>
                <li>Please have your children write Thank You cards and send them back to HQ.</li>
                <li>Please take pictures as you give out the books and send to HQ.</li>
            </ul>
        </div>
        <br /><br />
        <div id="dropdowns">
            <? if(count($schools) == 1) {?>
                <select id="school_id" name="school_id" class="hidden"  disabled>
                    <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
                </select>
            <?} else {?>
                <i class="fa fa-university" aria-hidden="true"></i> School: 
                <select id="school_id" name="school_id">
                    <option value="">All Schools</option>
                    <option value="82">A Academy</option>
                    <? foreach($schools as $school_id => $school_name){?>
                        <option value="<?=$school_id?>"><?=$school_name?></option>
                    <?}?>
                </select>
            <?}?>
        </div>
        <div id="options">
            <label for="start_date">
                <i class="fa fa-calendar" aria-hidden="true"></i> Dates: From
            </label>
            <select id="start">
                <?foreach($parshos as $parsho){?>
                    <option value="<?= $parsho['start'] ?>"><?= $parsho['name'] . '-' . $parsho['year'] ?></option>
                <?}?>
            </select>
            <label for="end_date">To</label>
            <select id="end">
                <?php $found = false; ?>
                <?foreach($parshos as $parsho){?>
                    <option value="<?= $parsho['end'] ?>" <?php if (!$found && unixtojd() < $parsho['end']) { echo 'selected'; $found = true; }?>>
                        <?= $parsho['name'] . '-' . $parsho['year'] ?>
                    </option>
                <?}?>
            </select>
        </div>
        <div id="action-links">
            <a class="button" id="refresh">
                <i class="fa fa-spinner" aria-hidden="true"></i> Load
            </a>
            <a class="button" id="csv_export">
                <i class="fa fa-download" aria-hidden="true"></i> Generate CSV (Excel)
            </a>
        </div>

        <div id="eligible_users_report">

        </div>
        
    </body>
<script>
  $( function() {
    $("#csv_export").click(csv_export);

    $("#refresh").click( function() {
      const school_id = $("#school_id").val();
      const start = $("#start").val();
      const end = $("#end").val();

      $.ajax({
        type: "POST",
        url: "getRegInfo.php",
        data: { school_id, start, end },
        success: function(data) {
          const info = JSON.parse(data);
          // create table with following columns: School ID, School Name, Grade, First Name, Last Name, Date Registered
          const table = $("<table>");
          const header = $("<tr>");
          header.append($("<th>").text("School ID"));
          header.append($("<th>").text("School Name"));
          header.append($("<th>").text("Grade"));
          header.append($("<th>").text("First Name"));
          header.append($("<th>").text("Last Name"));
          header.append($("<th>").text("Date Registered"));
          table.append(header);
          for (let school_id in info) {
            for (let student of info[school_id]) {
              // make sure student has school and class and admin connection
              if (!student.school_name || !student.class_grade || !student.admin_id) {
                continue;
              }
              // make sure that the registration date is between the start and end dates
              const date = new Date(student.user_registered);
              const julianDate = gregorianToJulian(date);
              if (julianDate < start || julianDate > end) {
                continue;
              }

              const row = $("<tr>");
              row.append($("<td>").text(school_id));
              row.append($("<td>").text(student.school_name));
              row.append($("<td>").text(student.class_grade + (student.class_sub ? '-' + student.class_sub : '')));
              row.append($("<td>").text(student.first_name));
              row.append($("<td>").text(student.last_name));
              row.append($("<td>").text(student.user_registered));
              table.append(row);
            }
          }
          $("#eligible_users_report").empty().append(table);
        }
      });
    })
  })

  function gregorianToJulian(date) {
    // Get the year, month, and day of the date.
    const y = date.getFullYear();
    const m = date.getMonth() + 1;
    const d = date.getDate();
    return Math.floor((1461 * (y + 4800 + (m - 14) / 12)) / 4 + (367 * (m - 2 - 12 * ((m - 14) / 12))) / 12 - (3 * ((y + 4900 + (m - 14) / 12) / 100)) / 4 + d - 32075);
  }

  function csv_export() {
    var rows = []; // the rows for the csv export
    var universalBOM = "\uFEFF";
    var csvContent = "";

    $.each($("#eligible_users_report tr"), function(index, item) {
      item = $(item);
      var row = [];
      // check if this is a header row....
      $.each(item.find('th, td'), function(td_index, td) {
        td = $(td); // cast to jquery object
        if (td.find("input[type='checkbox']").length > 0) { // see if we can find a checkbox...
          row.push(td.find("input[type='checkbox']")[0].checked ? "Yes" : "No");
        } else {
          row.push('"' + td.text() + '\t"');
        }
      });

      rows.push(row); // add the row to the csv export
      row = row.join(",");
      csvContent += row + "\n";
    });

    // open the csv with a hidden element....
    var hiddenElement = document.createElement('a');
    hiddenElement.href = "data:text/csv;charset=utf-8," + encodeURIComponent(universalBOM+csvContent); // set the data
    hiddenElement.target = '_blank'; // in a new tab
    hiddenElement.download = 'yearly_prize_report.csv'; // with this file_name
    hiddenElement.click(); // and click it
  }
</script>
</html>