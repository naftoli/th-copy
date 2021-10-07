<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $_POST['admin']);

$children = [];
$sql = "select th_chidon_id, s.school_name, user_id, first, yarmulka, gender, parent_id, size, recruited_by, khk_reg 
        from th_chidon tc 
        join users u using (user_id) 
        join schools s on s.school_id = u.school_id 
        where tc.year = " . $year . " 
        and tc.parent_id = " . $admin_id;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $children[$row['user_id']] = $row;
}

$prizes = [];
foreach ($children as $user_id => $child) {
    $sql = "select cp.prize_id, cp.price, cp.prize_name, cp.color, cp.personalization, cup.he_name 
            from chidon_user_prizes cup 
            join chidon_prizes cp using (prize_id) 
            where cup.user_id = " . $user_id . " 
            and cup.year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $prizes[$user_id][] = $row;
    }
}

$khk = [];
$newToChidon = [];
foreach ($children as $user_id => $child) {
    $sql = "select * from users where user_id = " . $user_id;
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    if ($row['khk_eligible']) $khk[] = $user_id;

    $sql = "select * from th_chidon where year != $year and user_id = " . $user_id;
    $result = mysql_query($sql);
    if (mysql_num_rows($result) == 0) $newToChidon[] = $user_id;
}

echo json_encode([
    'children'     => $children,
    'prizes'       => $prizes,
    'newToChidon'  => $newToChidon,
    'khkEligible'  => $khk
]);