<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

require_once __DIR__ . '../../../api/header/db.php';
require_once __DIR__ . '../../../class.globalSettings.php';
require __DIR__ . '../encrypt.php';

$year = GlobalSettings::getChidonYear();
$admin = mysql_real_escape_string( $_POST['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin);

function getChildren() {
    global $MASHPIA_DB, $admin_id, $year;

    $children = [];
    // track, raised, grade, trip location
    $sql = "select u.*, c.class_grade as grade, tc.th_chidon_id, tc.reward_type, IFNULL(cus.subsidy_amount, 0) as raised
            from users u 
            join th_chidon tc using (user_id)  
            join admin_auths aa on aa.id = u.user_id 
            join classes c on c.class_id = u.class_id 
            left join chidon_user_subsidies cus on u.user_id = cus.user_id and tc.year = cus.chidon_year
            where tc.year = :year 
            and aa.admin_id = :admin";
    $stmt = $MASHPIA_DB->prepare($sql);
    if (
        $stmt->execute([
        ':year'     => $year,
        ':admin'    => $admin_id
    ])) {
        $children = $stmt->fetchAll();
    }
    return $children;
}

if (! $admin_id) {
    echo json_encode([
        'success'   => false,
        'error'     => 'Missing Admin ID.'
    ]);
} else {
    $children = getChildren();
    if (! $children) {
        echo json_encode([
            'success'   => false,
            'error'     => 'You do not have any children that are eligible for the Chidon.'
        ]);
    } else {
        echo json_encode([
            'success'   => true,
            'children'  => $children
        ]);
    }
}