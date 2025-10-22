<?php
$admin_auth = array('school');
require('header.php');

require_once 'class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();

require_once 'class.globalSettings.php';
$startEnd = GlobalSettings::getCurYearDates();
// $startEnd['start'] = 2460280;

//get dates
$dates = [];
$sql = "SELECT * FROM parshos 
        WHERE start >= " . $startEnd['start'] . "
        AND end <= " . $startEnd['end'];
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $dates[] = $row;
}
$today = unixtojd();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>School Birthdays</title>
  <link href="/admin_styles.css" rel="stylesheet" type="text/css">
  <style type="text/css">
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
      background-color: #f8fafc;
      margin: 0;
      padding: 20px;
      padding-top: 0px;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      /* background: white; */
      border-radius: 12px;
      /* box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); */
      overflow: hidden;
      margin-top: -40px;
    }

    .header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 30px;
      text-align: center;
    }

    .header h1 {
      margin: 0;
      font-size: 2.5rem;
      font-weight: 300;
    }

    .content {
      padding: 40px;
    }

    .form-section {
      margin-bottom: 40px;
    }

    .form-section h2 {
      color: #374151;
      font-size: 1.5rem;
      margin-bottom: 20px;
      font-weight: 500;
    }

    .form-row {
      display: flex;
      gap: 30px;
      margin-bottom: 30px;
      flex-wrap: wrap;
    }

    .form-group {
      flex: 1;
      min-width: 300px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: #374151;
    }

    .form-control {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 16px;
      transition: border-color 0.2s, box-shadow 0.2s;
      background-color: white;
    }

    .form-control:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-control[multiple] {
      min-height: 200px;
      resize: vertical;
    }

    .date-range-container {
      display: flex;
      gap: 20px;
      align-items: flex-start;
      flex-wrap: wrap;
      margin-top: 15px;
    }

    .date-range-container .form-group {
      flex: 1;
      min-width: 200px;
      margin-bottom: 15px;
    }

    #dateRangeContainer {
      display: none;
      margin-top: 15px;
    }

    #dateRangeContainer.show {
      display: block;
    }

    #dateRangeContainer .form-control {
      width: 100%;
      box-sizing: border-box;
    }

    @media (max-width: 768px) {
      .date-range-container {
        flex-direction: column;
        gap: 15px;
      }
      
      .date-range-container .form-group {
        min-width: 100%;
      }
    }

    .radio-group {
      display: flex;
      gap: 20px;
      margin-top: 10px;
    }

    .radio-item {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .radio-item input[type="radio"] {
      width: 18px;
      height: 18px;
      accent-color: #667eea;
    }

    .radio-item label {
      margin: 0;
      cursor: pointer;
      font-weight: 400;
    }

    .button-group {
      display: flex;
      gap: 15px;
      justify-content: center;
      margin-top: 30px;
    }

    .btn {
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.2s;
      text-decoration: none;
      display: inline-block;
    }

    .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    .btn-secondary {
      background: #f3f4f6;
      color: #374151;
      border: 2px solid #e5e7eb;
    }

    .btn-secondary:hover {
      background: #e5e7eb;
    }

    .selection-info {
      background: #f0f9ff;
      border: 1px solid #bae6fd;
      border-radius: 8px;
      padding: 15px;
      margin-top: 20px;
      font-size: 14px;
      color: #0369a1;
    }

    .selection-info strong {
      color: #0c4a6e;
    }

    @media (max-width: 768px) {
      .form-row {
        flex-direction: column;
      }
      
      .date-range-container {
        flex-direction: column;
      }
      
      .button-group {
        flex-direction: column;
      }
    }
  </style>
  <script src="https://code.jquery.com/jquery-1.8.3.min.js"></script>
  <script type="text/javascript">
    $(function () {
      // Debug: Test if jQuery is working
      console.log('jQuery loaded, version:', $.fn.jquery);
      
      // Debug: Check if elements exist
      console.log('Date range container exists:', $('#dateRangeContainer').length);
      console.log('Parsha select container exists:', $('#parshaSelectContainer').length);
      console.log('Radio buttons exist:', $('input[name="parshaMethod"]').length);
      
      // Update selection info display
      function updateSelectionInfo() {
        var selectedSchools = $('#schoolSelect option:selected').length;
        var selectedGender = $('input[name="gender"]:checked').length;
        var parshaMethod = $('input[name="parshaMethod"]:checked').val();
        
        var info = '<strong>Current Selection:</strong><br>';
        info += 'Schools: ' + selectedSchools + ' selected<br>';
        
        if (parshaMethod === 'parsha') {
          var selectedParshas = $('#parshaSelect option:selected').length;
          info += 'Parshas: ' + selectedParshas + ' selected<br>';
        } else {
          var startDate = $('#startDate').val();
          var endDate = $('#endDate').val();
          if (startDate && endDate) {
            info += 'Date Range: ' + startDate + ' to ' + endDate + '<br>';
          } else {
            info += 'Date Range: Not selected<br>';
          }
        }
        
        info += 'Gender: ' + (selectedGender > 0 ? $('input[name="gender"]:checked').val() : 'Not selected');
        
        $('#selectionInfo').html(info);
      }

      // Update selection info on change
      $('#schoolSelect, #parshaSelect, input[name="gender"], input[name="parshaMethod"], #startDate, #endDate').change(updateSelectionInfo);
      
      // Initialize selection info
      updateSelectionInfo();

      // Handle parsha selection method toggle
      $('input[name="parshaMethod"]').change(function() {
        console.log('Radio button changed to:', $(this).val());
        if ($(this).val() === 'parsha') {
          console.log('Showing parsha select, hiding date range');
          $('#parshaSelectContainer').show();
          $('#dateRangeContainer').removeClass('show').hide();
        } else {
          console.log('Hiding parsha select, showing date range');
          $('#parshaSelectContainer').hide();
          $('#dateRangeContainer').addClass('show').show();
        }
      });

      // Initialize with parsha method selected
      $('input[name="parshaMethod"][value="parsha"]').attr('checked', true);
      $('#dateRangeContainer').removeClass('show').hide();

      $('#get_birthdays').click(function () {
        var schools = '';
        // check if schoolSelect exists
        if ($('#schoolSelect').length > 0) {
          $("#schoolSelect option:selected").each(function () {
            schools = schools + $(this).val() + ':';
          });
          schools = schools.substr(0, schools.length - 1);
          $('#schools').val(schools);
        } else {
          schools = $('#schools').val();
        }

        var parshas = '';
        var parshaMethod = $('input[name="parshaMethod"]:checked').val();
        
        if (parshaMethod === 'parsha') {
          $("#parshaSelect option:selected").each(function () {
            parshas = parshas + $(this).val() + ':';
          });
        } else {
          // Use date range
          var startDate = $('#startDate').val();
          var endDate = $('#endDate').val();
          if (startDate && endDate) {
            // Convert dates to the format expected by the backend
            // This would need to be adjusted based on your backend requirements
            parshas = startDate + ':' + endDate;
          }
        }
        
        parshas = parshas.substr(0, parshas.length - 1);
        $('#parshas').val(parshas);

        var gender = $('input[name="gender"]:checked').val();
        $("#gender").val(gender);

        if (schools == '' || parshas == '' || $("#gender").val() == '') {
          alert('You must pick at least one school, one parsha (or date range), and the gender.');
        } else {
          $('#get_birthday_form').submit();
        }
      });
    });
  </script>
</head>

<body>
<? require 'admin_header.php'; ?>
<h1 class="no-print">School Birthdays</h1>
<div class="container">  
  <div class="content">
    <form name="get_birthday_form" id="get_birthday_form" method="post" action="birthday_cert.php">
      <input type="hidden" name="schools" id="schools"/>
      <input type="hidden" name="parshas" id="parshas"/>
      <input type="hidden" name="gender" id="gender"/>
    </form>
  
    <?php if (count($schools) > 1) : ?>
    <div class="form-section">
      <h2>School Selection</h2>
      <div class="form-group">
        <label for="schoolSelect">Select Schools (Hold Ctrl/Cmd to select multiple)</label>
        <select id="schoolSelect" name="schools[]" class="form-control" multiple>
          <?
          foreach ($schools as $id => $school) {
              //skip certain schools
              // if (in_array($id, array(82, 65, 79, 187, 198, 241)))
              //     continue;
              echo "<option value='" . $id . "'>" . htmlspecialchars($school) . "</option>";
          }
          ?>
        </select>
      </div>
    </div>
    <?php else: ?>
      <input type="hidden" name="schools" id="schools" value="<?= key($schools) ?>"/>
    <?php endif; ?>


    <div class="form-section">
      <h2>Parsha Selection</h2>
      <div class="form-group">
        <label>Selection Method</label>
        <div class="radio-group">
          <div class="radio-item">
            <input type="radio" name="parshaMethod" value="parsha" id="parshaMethod1" checked>
            <label for="parshaMethod1">Select by Parsha</label>
          </div>
          <div class="radio-item">
            <input type="radio" name="parshaMethod" value="date" id="parshaMethod2">
            <label for="parshaMethod2">Select by Date Range</label>
          </div>
        </div>
      </div>
      <br />

      <div id="parshaSelectContainer">
        <div class="form-group">
          <label for="parshaSelect">Select Parshas (Hold Ctrl/Cmd to select multiple)</label>
          <select id="parshaSelect" name="parshas[]" class="form-control" multiple>
            <?
            foreach ($dates as $date) {
                echo "<option value='" . $date['end'] . "'>" . htmlspecialchars($date['name']) . "</option>";
            }
            ?>
          </select>
        </div>
      </div>

      <div id="dateRangeContainer">
        <div class="date-range-container">
          <div class="form-group">
            <label for="startDate">Start Date</label>
            <input type="date" id="startDate" name="startDate" class="form-control">
          </div>
          <div class="form-group">
            <label for="endDate">End Date</label>
            <input type="date" id="endDate" name="endDate" class="form-control">
          </div>
        </div>
      </div>
    </div>

    <div class="form-section">
      <h2>Gender Selection</h2>
      <div class="radio-group">
        <div class="radio-item">
          <input type="radio" name="gender" value="m" id="genderM">
          <label for="genderM">Boys</label>
        </div>
        <div class="radio-item">
          <input type="radio" name="gender" value="f" id="genderF">
          <label for="genderF">Girls</label>
        </div>
      </div>
    </div>

    <div id="selectionInfo" class="selection-info">
      <strong>Current Selection:</strong><br>
      Schools: 0 selected<br>
      Parshas: 0 selected<br>
      Gender: Not selected
    </div>

    <div class="button-group">
      <button type="button" id="get_birthdays" class="btn btn-primary">Generate Birthday Certificates</button>
    </div>
  </div>
</div>

</body>
</html>