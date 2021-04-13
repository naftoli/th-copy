<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

require 'encrypt.php';
$admin_id = encrypt_decrypt('decrypt', $_POST['admin']);

$purchases = [];
$sql = "select * from th_chidon_parent_purchases tcpp join admins using (admin_id) where authorize_id > 1 and tcpp.admin_id = " . $admin_id;
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $purchases[$row['admin_id']][] = $row;
}

$info = [];
$fields = ['celeb_box', 'celeb_box_add', 'celeb_box_add_ship', 'celeb_box_add_addr', 'sweater_mother', 'sweater_mother_ship',
    'sweater_mother_ship_addr', 'sweater_father', 'sweater_father_ship', 'sweater_father_ship_addr', 'sweater_bubby',
    'sweater_bubby_ship', 'sweater_bubby_ship_addr', 'sweater_zaidy', 'sweater_zaidy_ship', 'sweater_zaidy_ship_addr'];
foreach ($purchases as $admin => $details) {
    foreach ($details as $purchase) {
        foreach ($fields as $field) {
            if (!isset($info[$field])) $info[$field] = $purchase[$field];
            else {
                switch ($field) {
                    case 'celeb_box':
                    case 'celeb_box_add':
                    case 'celeb_box_add_ship':
                    case 'sweater_mother_ship':
                    case 'sweater_father_ship':
                    case 'sweater_bubby_ship':
                    case 'sweater_zaidy_ship':
                        if ($purchase[$field] > $info[$field]) $info[$field] = $purchase[$field];
                        break;
                    case 'sweater_mother':
                    case 'sweater_father':
                    case 'sweater_bubby':
                    case 'sweater_zaidy':
                    case 'celeb_box_add_addr':
                    case 'sweater_mother_ship_addr':
                    case 'sweater_father_ship_addr':
                    case 'sweater_bubby_ship_addr':
                    case 'sweater_zaidy_ship_addr':
                        if (!empty($purchase[$field])) $info[$field] = $purchase[$field];
                }
            }
        }
    }
}

$children = [];
$sql = "select th_chidon_id, s.school_name, user_id, first, date_paid, paid, yarmulka, gender, parent_id, size, 
            shabbaton_maven, shabbaton_pro, shabbaton_expert, shabbaton_trophy  
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
    $sql = "select cp.prize_id, cp.prize_name, cup.he_name 
            from chidon_user_prizes cup 
            join chidon_prizes cp using (prize_id) 
            where cup.user_id = " . $user_id . " 
            and cup.year = " . $year;
    $result = mysql_query($sql);
    while ($row = mysql_fetch_assoc($result)) {
        $prizes[$user_id][] = $row;
    }
}

$chidondrive = [];
foreach ($children as $user_id => $child) {
    $sql = "SELECT 
                SUM(subsidy_amount) AS total
            FROM
                mashpiadb.chidon_user_subsidies
            WHERE
                user_id = " . $user_id . " 
                    AND chidon_donation_id IN (SELECT 
                        chidon_donation_id
                    FROM
                        chidon_donations
                    WHERE
                        chidon_year = " . $year . " AND for_family_id = " . $admin_id . ")";
    $result = mysql_query($sql);
    $result = mysql_query($sql);
    $row = mysql_fetch_assoc($result);
    $chidondrive[$user_id]['raised'] = $row['total'] ?? 0;
}

echo json_encode([
    'transactions' => $purchases,
    'purchases'    => $info,
    'children'     => $children,
    'prizes'       => $prizes,
    'chidondrive'  => $chidondrive
]);