<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getCurrentYear();

$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true);
$schools = $as->getSchools();

$stmt = $MASHPIA_DB->prepare("SELECT auction_id FROM auctions WHERE year = :year");
$stmt->execute(['year' => $year]);
$auction_id = $stmt->fetchColumn();

$stmt = $MASHPIA_DB->prepare("SELECT * FROM aauction_prizes WHERE auction_id = :auction_id");
$stmt->execute(['auction_id' => $auction_id]);
$prizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$prize_types = [];
$stmt = $MASHPIA_DB->query("SELECT * FROM prizes_auction_types");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $prize_types[$row['prize_id']][] = $row['school_type_id'];
}

$school_prizes = [];
$stmt = $MASHPIA_DB->query("SELECT * FROM prizes_auction_schools");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $school_prizes[$row['school_id']][] = $row['prize_id'];
}

if (isset($_POST['submit'])) $school_id = $_POST['school_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auction Schools</title>
    <link href="../admin_styles.css" rel="stylesheet" type="text/css">
</head>
<body>
    <?php include('../admin_header.php'); ?>
    <h1>Auction Schools</h1>
    <?php if (!isset($_POST['submit'])) : ?>
    <form action="admin_auction_schools.php" method="post">
        Choose a school:
        <select name="school_id" id="school_id">
            <?php foreach ($schools as $school_id => $school_name) { ?>
                <option value="<?php echo $school_id; ?>"><?php echo $school_name; ?></option>
            <?php } ?>
        </select><br /><br />
        <input type="submit" name="submit" value="Submit">
    </form>
    <?php else : ?>
    
    <?php endif; ?>
</body>
</html>