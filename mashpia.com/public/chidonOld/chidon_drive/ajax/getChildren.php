<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

require_once __DIR__ . '/../../../api/header/db.php';
require_once __DIR__ . '/../../../class.globalSettings.php';
require __DIR__ . '/../encrypt.php';

$year = GlobalSettings::getChidonYear();
$admin = mysql_real_escape_string( $_POST['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin);

function getChildren() {
    global $MASHPIA_DB, $admin_id, $year;

    $children = [];
    // track, raised, grade, trip location
    $sql = "select u.user_id, u.school_id, u.class_id, u.mobile_pic, u.user_photo_id, u.first, u.last, u.user_serial, UPPER(u.gender) as gender,
                c.class_grade as grade, 
                tc.th_chidon_id, tc.test_type, tc.reward_type, tc.payment_request as subsidy, tc.date_paid, tc.year, 
                IFNULL(cc.value, 0) as coupon, cc.used as coupon_used, cc.reason as coupon_reason,  
                conf.chidon_confirmation_id as schoolConfirmed 
            from users u 
            join schools s using (school_id)
            join th_chidon tc using (user_id)  
            join classes c on c.class_id = u.class_id 
            left join coupon_codes cc on (u.user_serial = cc.serial_num and cc.year = :year) 
            left join chidon_confirmations conf on (u.school_id = conf.school_id and conf.year = :year) 
            where tc.year = :year 
            and tc.parent_id = :admin";
    $stmt = $MASHPIA_DB->prepare($sql);
    if (
        $stmt->execute([
        ':year'     => $year,
        ':admin'    => $admin_id
    ])) {
        $children = $stmt->fetchAll();

        // find sum of chidon drive raised for child
        $stmt = $MASHPIA_DB->prepare("
            SELECT IFNULL(SUM(subsidy_amount), 0) as raised 
            FROM chidon_user_subsidies 
            WHERE chidon_year = :year AND user_id = :user
        ");
        // check track history
        $stmt2 = $MASHPIA_DB->prepare("SELECT * FROM th_chidon_info where user_id = :user");

        for ($i = 0; $i < count($children); $i++) {
            $child = $children[$i];
            $stmt->execute([
                ':year'     => $year,
                ':user'     => $child['user_id']
            ]);
            $result = $stmt->fetch();
            if ($result['raised']) $children[$i]['raised'] = $result['raised'];
//            $children[$i]['raised'] = 200;
//            $children[$i]['schoolConfirmed'] = 1;

            $stmt2->execute([':user' => $child['user_id']]);
            $rows = $stmt2->fetchAll();
            foreach ($rows as $row) {
                $children[$i]['history'][$row['year']] = ucwords($row['highest_track']);
            }
        }
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