<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

if ($admin_user['auth'] == 'super') {
    echo "This page is only for individual schools.";
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$school_id = array_keys($schools)[0];

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf8" />
    <link href="../../../admin_styles.css" rel="stylesheet" type="text/css" />
    <style>
        button {
            padding: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . '/admin_header.php'; ?>
<h1>Chidon Confirmation</h1>
<p>
    I verify that I have reviewed all my student's marks and eligibility, and that I confirm that all students are
    on the correct eligibility track.<br /><br />
    <button id="confirm">Confirm</button>
</p>
</body>
<script>
    const school_id = <?= $school_id ?>;
    $("#confirm").click( function () {
        $.post('../ajax/schoolConfirmation.php', { school_id }, function(result) {
            let res = JSON.parse(result)
            if (res.success) {
                alert('Your school has been confirmed. Your students are now eligible for enrollment.')
            } else {
                alert("Error saving confirmation.")
            }
        })
    })
</script>
</html>
