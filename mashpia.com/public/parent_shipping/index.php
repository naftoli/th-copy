<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
$super = $admin_user['auth'] == 'super';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

require 'class.parentShipping.php';
require 'data.php';

$cs = new ParentShipping();
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
  </style>
</head>
<body>
  <h1>Create Your Own Shipping Report</h1>
  <form id="shippingForm" action="report.php" method="post">
    <fieldset>
      <legend>Choose Items</legend>
        <?= build_items(); ?>
    </fieldset>

    <fieldset>
      <legend>Choose Item Details</legend>
      <?= build_details(); ?>
    </fieldset>

<!--    <fieldset>-->
<!--      <legend>Choose Fields</legend>-->
<!--      --><?php //= build_fields(); ?>
<!--    </fieldset>-->

    <fieldset>
      <legend style="margin-bottom: -5px;">Limit To</legend>
<!--      <h4>School</h4>-->
<!--      <select name="school">-->
<!--          --><?php //if ($super) echo '<option value="0">All Schools</option>'; ?>
<!--          --><?php //foreach ($schools as $id => $school) echo "<option value=" . $id . ">" . $school . "</option>"; ?>
<!--      </select><br />-->
      <h4>Status</h4>
      <p>
        <input type="checkbox" name="status[]" value="0" /> Not Yet Shipped<br />
        <input type="checkbox" name="status[]" value="1" /> Shipped<br />
        <input type="checkbox" name="status[]" value="2" /> Missing<br />
        <input type="checkbox" name="status[]" value="3" /> Damaged<br />
      </p>
    </fieldset>

    <fieldset>
      <legend>Type of Report</legend>
      <select name="report_type">
        <option value="all" selected>Summary and Details</option>
        <option value="summary">Summary Only</option>
        <option value="details">Details Only</option>
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
    console.log( { elem, checked })
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

  $(".check_items").click(checkAllItems)
  $("#all_items").click(checkAll)
  $("#all_details").click(checkAll)
  // $("#all_fields").click(checkAll)

  $("#create").click( function(e) {
    e.preventDefault()
    if (! $(".item:checked").length) {
      alert('You must choose at least one item!')
      return false
    }
    $("form").submit()
  })

  $("#all_details").click()
</script>
</html>