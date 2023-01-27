<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true);
$schools = $as->getSchools();

require 'class.chidonShipping.php';
require 'data.php';

$cs = new ChidonShipping();
$cs->setSchools(array_keys($schools));
$children = $cs->getChildren();



$cats = [];
$info = [];
// figure out which functions to run based on which categories were chosen and save it in info array
// with the category name as the index
foreach ($_POST['cats'] as $cat => $val) {
    $cats[] = $cat; // keep track of chosen categories
    $func = 'get' . str_replace(' ', '', ucwords($cat));
    $info[$cat] = $cs->$func();
}
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
    th, th, td {
      font-size: 14px;
      padding: 5px;
      border-bottom: 1px solid grey;
    }
  </style>
</head>
<body>
  <h1>Create Your Own Shipping Report</h1>

</body>
</html>