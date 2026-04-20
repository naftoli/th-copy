<?php
// ini_set('display_errors', 1);
// ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
$super = $admin_user['auth'] == 'super';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getRegistrationYear();
$cur_year = GlobalSettings::getCurrentYear();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

require_once 'class.chayoleiShipping.php';
require_once 'data.php';

$cs = new ChayoleiShipping();
$cs->setYear($cur_year);
$max_hachayol_shipment_option = $cs->getMaxHachayolShipmentNum((int) $cur_year);
$categories = $cs->getCategories();
$items = $cs->getItems();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Shipping Reports</title>
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.css"/>
  <style>
    body {
      font-family: sans-serif;
      font-size: 14px;
      padding-left: 3%;
      padding-right: 3%;
    }
    fieldset {
      float: left;
      width: 40%;
      padding-right: 20px;
      padding-left: 20px;
      padding-bottom: 20px;
    }
    fieldset h4 {
      margin-top: 20px;
      margin-bottom: 5px;
    }
    /* School multi-select dropdown */
    .school-multiselect {
      position: relative;
      width: 100%;
      max-width: 280px;
    }
    .school-multiselect .dropdown-trigger {
      min-height: 34px;
      padding: 6px 12px;
      border: 1px solid #ccc;
      border-radius: 4px;
      background: #fff;
      cursor: pointer;
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      gap: 4px;
    }
    .school-multiselect .dropdown-trigger:hover {
      border-color: #66afe9;
    }
    .school-multiselect .dropdown-trigger .placeholder {
      color: #999;
    }
    .school-multiselect .dropdown-trigger .badge {
      font-size: 12px;
      padding: 2px 6px;
      background: #337ab7;
      color: #fff;
      border-radius: 3px;
    }
    .school-multiselect .dropdown-trigger .badge .remove {
      cursor: pointer;
      margin-left: 4px;
      font-weight: bold;
    }
    .school-multiselect .dropdown-trigger .chevron {
      margin-left: auto;
      font-size: 12px;
    }
    .school-multiselect .dropdown-panel {
      display: none;
      position: absolute;
      z-index: 9999;
      top: 100%;
      left: 0;
      right: 0;
      margin-top: 2px;
      background: #fff;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-shadow: 0 6px 12px rgba(0,0,0,0.175);
      max-height: 300px;
      overflow: hidden;
    }
    .school-multiselect.open .dropdown-panel {
      display: block;
    }
    .school-multiselect .search-box {
      padding: 8px;
      border-bottom: 1px solid #eee;
    }
    .school-multiselect .search-box input {
      width: 100%;
      padding: 6px 12px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .school-multiselect .check-all-row {
      padding: 8px 12px;
      border-bottom: 1px solid #eee;
      font-weight: bold;
      cursor: pointer;
    }
    .school-multiselect .check-all-row:hover {
      background: #f5f5f5;
    }
    .school-multiselect .option-row {
      padding: 6px 12px;
      cursor: pointer;
    }
    .school-multiselect .option-row:hover {
      background: #f5f5f5;
    }
    .school-multiselect .options-list {
      max-height: 220px;
      overflow-y: auto;
    }
  </style>
</head>
<body>
  <h1>Create Your Own Shipping Report</h1>
  <?php if ($super) { ?>
    <p style="margin-bottom:1rem;"><a href="shipments.php">Manage Hachayol shipments (issue ranges)</a></p>
  <?php } ?>
  <form id="shippingForm" action="report.php" method="post">
    <fieldset>
      <legend>Choose Items</legend>
        <?= build_items(); ?>
    </fieldset>

    <fieldset>
      <legend>Choose Item Details</legend>
      <?= build_details(); ?>
    </fieldset>

    <fieldset>
      <legend>Choose Fields</legend>
      <?= build_fields(); ?>
    </fieldset>

    <fieldset>
      <legend style="margin-bottom: -5px;">Limit To</legend>
      <h4>Year</h4>
      <select name="year">
        <?php
        for ($y = 5782; $y <= $year; $y++) {
            echo "<option value='" . $y . "'";
            if ($y == $cur_year) echo " selected ";
            echo ">" . $y . "</option>";
        }
        ?>
      </select>
      <br />
      <h4>Gender</h4>
      <select name="gender">
        <option value="0">All</option>
        <option value="m">Boys</option>
        <option value="f">Girls</option>
      </select><br />
      <h4>School(s)</h4>
      <?php
      $schools_filtered = array_filter($schools, function ($s) { return strlen($s) >= 3; });
      if ($super) {
          $schools_data = [];
          foreach ($schools_filtered as $id => $name) {
              $schools_data[] = ['id' => (int)$id, 'name' => $name];
          }
          $schools_json = json_encode($schools_data);
          ?>
      <div id="school-multiselect-container" class="school-multiselect">
        <div class="dropdown-trigger" id="school-dropdown-trigger">
          <span class="placeholder">Select schools...</span>
          <span class="chevron">▼</span>
        </div>
        <div class="dropdown-panel" id="school-dropdown-panel">
          <div class="search-box">
            <input type="text" id="school-search" placeholder="Search schools..." />
          </div>
          <div class="check-all-row">
            <input type="checkbox" id="school-check-all" /> <label for="school-check-all">Select All Schools</label>
          </div>
          <div class="options-list" id="school-options-list"></div>
        </div>
      </div>
      <div id="school-hidden-inputs"></div>
      <script>window.CHAYOLEI_SCHOOLS_DATA = <?= $schools_json ?>;</script>
      <?php
      } else {
          echo '<select name="school[]" id="school">';
          foreach ($schools_filtered as $id => $school) {
              echo "<option value='" . (int)$id . "'>" . htmlspecialchars($school) . "</option>";
          }
          echo '</select>';
      }
      ?>
      <br />
      <h4>Status</h4>
      <p>
        <input type="checkbox" name="status[]" value="0" /> Not Yet Shipped<br />
        <input type="checkbox" name="status[]" value="1" /> Shipped<br />
        <input type="checkbox" name="status[]" value="2" /> Received<br />
        <input type="checkbox" name="status[]" value="3" /> Missing<br />
        <input type="checkbox" name="status[]" value="4" /> Damaged<br />
        <input type="checkbox" name="status[]" value="5" /> Replaced<br />
      </p>
    </fieldset>

    <fieldset>
      <legend>Shipment Number</legend>
      <select name="shipment_number" id="shipment_number">
        <option value="0">Any/All</option>
        <?php
        for ($sn = 1; $sn <= $max_hachayol_shipment_option; $sn++) {
            echo '<option value="' . $sn . '">' . $sn . "</option>\n";
        }
        ?>
      </select>
    </fieldset>

    <?php if ($super) : ?>
      <fieldset>
        <legend>Shipping to (for MS/AK only):</legend>
        <select name="ship_to">
          <option value="all" selected>All</option>
          <option value="usa">USA Only</option>
          <option value="intl">Outside USA Only</option>
        </select>
      </fieldset>
    <?php endif; ?>

    <fieldset>
      <legend>Type of Report</legend>
      <select name="report_type">
        <option value="all">Summary and Details</option>
        <option value="summary">Summary Only</option>
        <option value="details">Details Only</option>
        <?php if ($super) echo "<option value='file'>CSV File for MS/AK</option>"; ?>
<!--        --><?php //if ($super) echo "<option value='fileGear'>CSV File for Gear</option>"; ?>
      </select><br />
      <?php if ($super) echo '<input type="checkbox" name="grand_summary" value="1" /> Include Grand Summary at the End of the Report'; ?>
      <br />
      <br />
      <button id="create">Create Report</button>
    </fieldset>
  </form>
  <p></p>
</body>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script>
  function checkAll() {
    let id = $(this).attr('id')
    let info = id.split('_')
    let elem = '.' + info[1].substring(0, info[1].length - 1)
    let checked = $(this).is(":checked")
    console.log({ elem, checked })
    $(elem).each( function () {
      this.checked = checked
    })
  }

  function checkAllItems() {
    let checked = $(this).is(":checked")
    let elem = $(this).parent().find('.item')
    $(elem).each( function () {
      this.checked = checked
    })
  }

  function getSelectedSchoolIds() {
    if (window.schoolMultiselectSelected) return window.schoolMultiselectSelected;
    const val = $("#school").val();
    return val ? (Array.isArray(val) ? val : [val]) : [];
  }

  $(".check_items").click(checkAllItems)
  $("#all_items").click(checkAll)
  $("#all_details").click(checkAll)
  $("#all_fields").click(checkAll)

  $("#create").click( function(e) {
    e.preventDefault()
    if (! $(".item:checked").length) {
      alert('You must choose at least one item!')
      return false
    }
    if (! $(".field:checked").length) {
      alert('You must choose at least one field!')
      return false
    }
    if (!getSelectedSchoolIds().length) {
      alert('You must choose at least one school!')
      return false
    }
    $("form").submit()
  })

  $(function() {
    $("#all_details").click()

    // School multi-select (super users only)
    if (window.CHAYOLEI_SCHOOLS_DATA && $("#school-multiselect-container").length) {
      var schools = window.CHAYOLEI_SCHOOLS_DATA;
      window.schoolMultiselectSelected = [];
      try {
        var stored = sessionStorage.getItem('chayolei_shipping_schools');
        if (stored) {
          var arr = JSON.parse(stored);
          if (Array.isArray(arr)) window.schoolMultiselectSelected = arr.map(String);
        }
      } catch (e) {}

      function renderOptions(filter) {
        var q = (filter || "").toLowerCase();
        var html = "";
        schools.forEach(function(s) {
          if (q && s.name.toLowerCase().indexOf(q) < 0) return;
          var checked = window.schoolMultiselectSelected.indexOf(String(s.id)) >= 0;
          html += '<div class="option-row" data-id="' + s.id + '">' +
            '<input type="checkbox" class="school-opt-cb" id="school-opt-' + s.id + '" value="' + s.id + '"' + (checked ? ' checked' : '') + '> ' +
            '<label for="school-opt-' + s.id + '">' + $("<div>").text(s.name).html() + '</label></div>';
        });
        $("#school-options-list").html(html);
      }

      function syncHiddenInputs() {
        var container = $("#school-hidden-inputs").empty();
        window.schoolMultiselectSelected.forEach(function(id) {
          container.append('<input type="hidden" name="school[]" value="' + id + '">');
        });
      }

      function updateTrigger() {
        var trigger = $("#school-dropdown-trigger");
        trigger.find(".badge, .placeholder").remove();
        if (window.schoolMultiselectSelected.length === 0) {
          trigger.prepend('<span class="placeholder">Select schools...</span>');
        } else {
          var names = {};
          schools.forEach(function(s) { names[s.id] = s.name; });
          window.schoolMultiselectSelected.forEach(function(id) {
            var name = names[id] || id;
            trigger.append('<span class="badge" data-id="' + id + '">' + $("<span>").text(name).html() + ' <span class="remove">&times;</span></span>');
          });
        }
        $("#school-check-all").prop("checked", window.schoolMultiselectSelected.length === schools.length);
        renderOptions($("#school-search").val());
        syncHiddenInputs();
        try { sessionStorage.setItem('chayolei_shipping_schools', JSON.stringify(window.schoolMultiselectSelected)); } catch (e) {}
      }

      $("#school-dropdown-trigger").on("click", function(e) {
        e.stopPropagation();
        $("#school-multiselect-container").toggleClass("open");
        if ($("#school-multiselect-container").hasClass("open")) $("#school-search").focus();
      });

      $(document).on("click", function() {
        $("#school-multiselect-container").removeClass("open");
      });
      $("#school-multiselect-container").on("click", function(e) { e.stopPropagation(); });

      $("#school-search").on("input", function() {
        renderOptions($(this).val());
      });

      $("#school-check-all").on("change", function() {
        if ($(this).is(":checked")) {
          schools.forEach(function(s) {
            if (window.schoolMultiselectSelected.indexOf(String(s.id)) < 0) {
              window.schoolMultiselectSelected.push(String(s.id));
            }
          });
        } else {
          window.schoolMultiselectSelected = [];
        }
        updateTrigger();
      });

      $("#school-options-list").on("change", ".school-opt-cb", function() {
        var id = String($(this).val());
        if ($(this).is(":checked")) {
          if (window.schoolMultiselectSelected.indexOf(id) < 0) window.schoolMultiselectSelected.push(id);
        } else {
          window.schoolMultiselectSelected = window.schoolMultiselectSelected.filter(function(x) { return x !== id; });
        }
        updateTrigger();
      });

      $("#school-options-list").on("click", ".option-row", function(e) {
        if (!$(e.target).is("input")) {
          var cb = $(this).find(".school-opt-cb");
          cb.prop("checked", !cb.prop("checked")).trigger("change");
        }
      });

      $("#school-dropdown-trigger").on("click", ".badge .remove", function(e) {
        e.stopPropagation();
        e.stopImmediatePropagation();
        var id = $(this).closest(".badge").data("id");
        window.schoolMultiselectSelected = window.schoolMultiselectSelected.filter(function(x) { return x !== String(id); });
        updateTrigger();
      });

      updateTrigger();
    }
  })
</script>
</html>