<?php
$admin_auth = ['school'];
require $_SERVER['DOCUMENT_ROOT'] . '/header.php';
if ($admin_user['auth'] != 'super') {
    echo "No Permission.";
    exit;
}

require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
$stmt = $MASHPIA_DB->query("
    SELECT school_id, allow_lulav, lulav_shipping from schools where test_school = 0 and chayolei = 1
");

$rows = $stmt->fetchAll();
$stmt = $MASHPIA_DB->prepare("
    INSERT INTO lulav_settings 
    SET 
        year = :year, 
        school_id = :school, 
        allow_lulav = :allow, 
        lulav_shipping = :shipping
");
foreach ($rows as $row) {
    $stmt->execute([
        ':year'     => $year,
        ':school'   => $row['school_id'],
        ':allow'    => $row['allow_lulav'],
        ':shipping' => $row['lulav_shipping']
    ]);
}
echo "done";