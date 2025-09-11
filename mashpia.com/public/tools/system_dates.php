<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require('../header.php');

if ($admin_user['auth'] != 'super') {
    echo "You are not authorized to access this page.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>System Dates</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
    <style>
        tr, th, td {
        padding: 10px;
        font-size: 14px;
        border-bottom: 1px solid #f0f0f0;
        }
    </style>
</head>
<body>
    <?php include('../admin_header.php'); ?>
    <h1>System Dates</h1>
    <p class="infobox">
        Please note, you need to enter the end dates. For the first date enter the day before the first date.
    </p>
    <select name="year" id="year">
    <?php for ($i = 0; $i <= 5; $i++) { ?>
        <option value="<?php echo $year - $i; ?>" <?php if ($year - $i == $year) echo 'selected'; ?>><?php echo $year - $i; ?></option>
    <?php } ?>
    </select>
    <button id="changeYear">Change Year</button>
    <br/><br/>
    <!-- add date input -->
    <input type="date" id="newDate" />
    <br/><br/>
    <!-- add button to add date -->
    <button id="addDate">Add Date</button>
    <br/><br/>
    <div id="dates"></div>
    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script>
        function getDates(year) {
            fetch('ajax/dates.php?action=getDates&year=' + year)
                .then(response => response.json())
                .then(data => { 
                    if (data.success) {
                        createTable(data.dates, year);
                    } else {
                        alert('Error loading dates: ' + (data.error || 'Unknown error'));
                    }
                });
        }
        
        function createTable(data, year) {
            let html = '';
            if (!data || data.length == 0) {
                html += '<p>No dates found for ' + year + '</p>';
            } else {
                html += '<table>';
                html += '<caption style="font-weight: bold; font-size: 16px; padding: 10px; text-align: left;">System Dates for ' + year + '</caption>';
                html += '<tr><th><th>Date</th><th>Action</th></tr>';
                data.forEach((date, i) => {
                    // change date format for input to yyyy-mm-dd
                    let date_info = date.split('/')
                    // add 0 padding to month and day
                    date_info[0] = date_info[0].padStart(2, '0');
                    date_info[1] = date_info[1].padStart(2, '0');
                    const date_input = date_info[2] + '-' + date_info[0] + '-' + date_info[1];
                    html += `<tr><td>${i+1}</td><td><input class="dateInput" type="date" value="${date_input}" data-old-date="${date_input}" /></td><td>
                        <button class="deleteDate" data-date="${date_input}">Delete</button>
                        </td></tr>`;
                    });
                    html += '</table>';
            }
            document.getElementById('dates').innerHTML = html;
        }

        getDates(document.getElementById('year').value);

        document.getElementById('changeYear').addEventListener('click', function () {
            getDates(document.getElementById('year').value);
        });

        document.getElementById('addDate').addEventListener('click', function () {
            const date = document.getElementById('newDate').value;
            fetch('ajax/dates.php?action=addDate&year=' + document.getElementById('year').value + '&date=' + date)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        createTable(data.dates, document.getElementById('year').value);
                    } else {
                        alert(data.error);
                    }
                });
        });

        $(document).on('click', '.deleteDate', function () {
            const date = $(this).data('date');
            fetch('ajax/dates.php?action=deleteDate&year=' + document.getElementById('year').value + '&date=' + date)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        createTable(data.dates, document.getElementById('year').value);
                    } else {
                        alert(data.error);
                    }
                });
        });

        $(document).on('change', '.dateInput', function () {
            const date = $(this).val();
            const old_date = $(this).data('old-date');
            fetch('ajax/dates.php?action=updateDate&year=' + document.getElementById('year').value + '&date=' + date + '&old_date=' + old_date)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        createTable(data.dates, document.getElementById('year').value);
                    } else {
                        alert(data.error);
                    }
                });
        });
    </script>
</body>
</html>