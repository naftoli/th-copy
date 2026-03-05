<?php
ini_set('display_errors', 1);
ini_set('error_reporting', 1);

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
// load PDO-based DB handle ($MASHPIA_DB) used in chidonWinnersSql.php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

if ($admin_user['auth'] != 'super') {
    die('Access Denied');
}

function custom_urlencode($url) {
    return implode('/', array_map('rawurlencode', explode('/', $url)));
}

// echo "<pre>"; print_r($_POST); echo "</pre>"; 
$info = [];
if (isset($_POST['team']) || isset($_POST['grade']) || isset($_POST['gender'])) {
    require_once 'chidonWinnersSql.php';
} else {
    $sql = "select * from th_chidon_winners tcw 
            join users u on u.user_serial = tcw.serial 
            join th_chidon tc on tc.user_id = u.user_id 
            where tcw.year = " . $year . " 
            group by tcw.serial 
            order by tcw.th_chidon_winner_id";
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $info[] = $row;
    }
}
// echo "<pre>"; print_r( $info ); echo "</pre>"; 
$teams = ['Mishne Torah', 'Sefer Hamitzvos', 'Blue Trophy', 'Gold Trophy', 'Silver Trophy', 'Bronze Trophy', 'KHK Gold Trophy', 'KHK Silver Trophy', 'KHK Bronze Trophy'];
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

    button, input[type="submit"] {
      font-size: 14px;
      padding: 10px;
    }

    select {
      font-size: 14px;
      padding: 5px;
      border-radius: 5px;
      border: 1px solid #ccc;
      width: 200px;
    }
  </style>
</HEAD>

<BODY>
<?php require_once $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
<h1>Chidon Winners Pictures</h1>

<h2>Selections</h2>
<form action="chidon_winners_downloads.php" method="post" id="selections">
    Team:
    <select id="team" name="team[]" multiple>
        <?php 
        foreach ($teams as $team) { 
            $selected = in_array($team, isset($_POST['team']) ? (array) $_POST['team'] : []) ? 'selected' : '';
            echo "<option value='$team' $selected>$team</option>";
        } 
        ?>
    </select><br /><br />
    Grade:
    <select id="grade" name="grade[]" multiple>
        <?php 
        for ($i = 4; $i <= 8; $i++) { 
            $selected = in_array($i, isset($_POST['grade']) ? (array) $_POST['grade'] : []) ? 'selected' : '';
            echo "<option value='$i' $selected>" . $i . "th Grade</option>";
        } 
        ?>
    </select><br /><br />
    Gender:
    <select id="gender" name="gender">
        <?php 
        $selected = isset($_POST['gender']) ? $_POST['gender'] : 'B';
        echo "<option value='B' " . ($selected == 'B' ? 'selected' : '') . ">Both</option>";
        echo "<option value='M' " . ($selected == 'M' ? 'selected' : '') . ">Boys</option>";
        echo "<option value='F' " . ($selected == 'F' ? 'selected' : '') . ">Girls</option>";
        ?>
    </select><br /><br />
    <input type="submit" value="Download"><br /><br />
    <input type="submit" name="update" id="update" value="Update Pictures">
</form>

<h2>Winners</h2>
<table class="pics">
<tr>
    <th>Serial Number</th>
    <th>Name</th>
    <th>School</th>
    <th>Grade</th>
    <th>Chidon Picture</th>
    <th></th>
</tr>
    <?php 
    foreach ($info as $child) {
        $img_fallbacks = [
            ['from_db' => false, 'field' => 'khk_photo', 'val' => $child['khk_photo'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['khk_photo'])],
            ['from_db' => false, 'field' => 'chidon_photo', 'val' => $child['chidon_photo'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_photo'])],
            ['from_db' => false, 'field' => 'mobile_pic', 'val' => $child['mobile_pic'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['mobile_pic'])],
            ['from_db' => false, 'field' => 'chidon_pic_5782', 'val' => $child['chidon_pic_5782'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_pic_5782'])],
            ['from_db' => false, 'field' => 'chidon_pic_5781', 'val' => $child['chidon_pic_5781'],    'url' => 'https://mashpia.com/mobile/reg/' . custom_urlencode($child['chidon_pic_5781'])],
            ['from_db' => true,  'field' => 'user_photo_id', 'val' => $child['user_photo_id']]
        ];
        $field = null;
        $img = null;
        // find first valid image
        foreach ($img_fallbacks as $img_fallback) {
            if (!empty($img_fallback['val'])) {
                if ($img_fallback['from_db']) {
                    $img = 'http://mashpia.com/file_view.php?id=' . $img_fallback['val'];
                } else if ($img_fallback['val'] != 'img/addphoto.png') {
                    $img = $img_fallback['url'];
                } else {
                    continue;
                }
                $field = $img_fallback['field'];
                break;
            }
        }
        echo "<tr><td>" . $child['serial'] . "</td><td>" . $child['name'] . "</td><td>";
        echo $child['school'] . "</td><td>" . $child['grade'] . "</td><td>";
        echo "<img src='" . $img . "' /></td><td>";
        if ($img) echo "<button class='delete' data-serial='" . $child['serial'] . "' data-field='" . $field . "'>Delete</button>";
        echo "</td></tr>";
        if ($img != 'http://mashpia.com/mobile/reg/img/addphoto.png') {
            $imgs[] = $img;
        }
    }
    ?>
</table>
</BODY>
<script>
    $(document).ready(function() {
        $("#update").click(function(e) {
            e.preventDefault()
            document.getElementById('selections').action = 'chidon_winners_pics.php';
            document.getElementById('selections').submit();
        });

        $('.delete').click(function(e) {
            const serial = e.target.dataset.serial;
            const field = e.target.dataset.field;
            if (serial && field) {
                $.post('ajax/deletePic.php', { serial, field }, function(result) {
                    const res = JSON.parse(result)
                    console.log(res)
                    if (res.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete picture');
                    }
                });
            } 
        });
    });
</script>
</HTML>