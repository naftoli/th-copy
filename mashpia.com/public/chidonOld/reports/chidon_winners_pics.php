<?php
ini_set('display_errors', 1);
ini_set('error_reporting', 1);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth'], true, true); // add chidon schools
$schools = $as->getSchools();

require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$limit = isset($_GET['limit']) ? $_GET['limit'] : 0;

$info = [];
$sql = "select * from th_chidon_winners tcw 
        join users u on u.user_serial = tcw.serial 
        join th_chidon tc on tc.user_id = u.user_id
        where tcw.year = " . $year . " 
        order by tcw.th_chidon_winner_id";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $info[] = $row;
}
// echo "<pre>"; print_r( $info ); echo "</pre>"; 
?>
<!DOCTYPE html>
<HTML>
<HEAD>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Review Enrollment</title>
  <link href="../../admin_styles.css" rel="stylesheet" type="text/css">
  <style type='text/css'>
    tr, th, td {
      font-size: 14px;
      padding: 5px;
    }

    .pics img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      border-color: #aaa;
      margin-left: auto;
      margin-right: auto;
      display: block;
    }

    button {
      font-size: 14px;
      padding: 10px;
    }
  </style>
</HEAD>

<BODY>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
<h1>Chidon Winners Pictures</h1>
<table class="pics">
<tr>
    <th>Serial Number</th>
    <th>Name</th>
    <th>School</th>
    <th>Grade</th>
    <th>Chidon Picture</th>
</tr>
    <?php 
    foreach ($info as $child) {
        $img_fallbacks = [
            ['from_db' => false, 'val' => $child['khk_photo'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['khk_photo'])],
            ['from_db' => false, 'val' => $child['chidon_photo'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_photo'])],
            ['from_db' => false, 'val' => $child['mobile_pic'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['mobile_pic'])],
            ['from_db' => false, 'val' => $child['chidon_pic_5782'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_pic_5782'])],
            ['from_db' => false, 'val' => $child['chidon_pic_5781'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_pic_5781'])],
            ['from_db' => true,  'val' => $child['user_photo_id']]
        ];
        $img = null;
        // find first valid image
        foreach ($img_fallbacks as $img_fallback) {
            if (!empty($img_fallback['val']) && $img_fallback['val'] !== 'img/addphoto.png') {
                $img = $img_fallback['url'];
                break;
            }
        }
        echo "<tr><td>" . $child['serial'] . "</td><td>" . $child['name'] . "</td><td>";
        echo $child['school'] . "</td><td>" . $child['grade'] . "</td><td>";
        echo "<img src='" . $img . "' /></td></tr>";
        if ($img != 'http://mashpia.com/mobile/reg/img/addphoto.png') {
            $imgs[] = $img;
        }
    }
    ?>
</table>
</BODY>
</HTML>