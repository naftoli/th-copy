<?php
//ini_set('display_errors', 1);
//ini_set('error_reporting', E_ALL);

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';
require __DIR__ . '/../encrypt.php';

$year = GlobalSettings::getChidonYear();
$admin = mysql_real_escape_string( $_POST['admin'] );
$admin_id = encrypt_decrypt('decrypt', $admin);

function getChildren() {
    global $MASHPIA_DB, $admin_id, $year;

    $children = [];
    // track, raised, grade, trip location
    $sql = "select u.user_id, u.school_id, u.class_id, u.mobile_pic, u.user_photo_id, u.first, u.last, u.user_serial, 
                UPPER(u.gender) as gender, 
                c.class_grade as grade, 
                tc.*, 
                conf.chidon_confirmation_id as schoolConfirmed, 
                a.admin_id, a.admin_country 
            from users u 
            join schools s using (school_id)
            join th_chidon tc using (user_id)  
            join classes c on c.class_id = u.class_id 
            left join chidon_confirmations conf on (u.school_id = conf.school_id and conf.year = :year) 
            join admin_auths aa on aa.id = u.user_id 
            join admins a using (admin_id) 
            where tc.year = :year 
            and a.admin_id = :admin";
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
//        $stmt2 = $MASHPIA_DB->prepare("SELECT * FROM th_chidon_info where user_id = :user");
        // check coupon codes for children
        $stmtCoupon = $MASHPIA_DB->prepare("
            SELECT IFNULL(value, 0) as coupon, used as coupon_used, reason as coupon_reason, 
            FROM chidon_coupons 
            WHERE user_serial = :serial 
            AND year = :year
        ");

        for ($i = 0; $i < count($children); $i++) {
            $child = $children[$i];
            $stmt->execute([
                ':year'     => $year,
                ':user'     => $child['user_id']
            ]);
            $result = $stmt->fetch();
            if ($result['raised']) $children[$i]['raised'] = $result['raised'];
            $stmtCoupon->execute([
                ':serial'   => $child['user_serial'],
                ':year'     => $year
            ]);
            $resCoupon = $stmtCoupon->fetch();
            if ($resCoupon['coupon']) {
                $children[$i]['coupon'][] = $resCoupon['coupon'];
                $children[$i]['coupon_used'][] = $resCoupon['coupon_used'];
                $children[$i]['coupon_reason'][] = $resCoupon['coupon_reason'];
            }

            // get family balance
//            $children[$i]['balance'] = TripRegistration::getFamilyBalance($child['admin_id'], $year);
//            $children[$i]['raised'] = 200;
//            $children[$i]['schoolConfirmed'] = 1;

//            $stmt2->execute([':user' => $child['user_id']]);
//            $rows = $stmt2->fetchAll();
//            foreach ($rows as $row) {
//                $children[$i]['history'][$row['year']] = ucwords($row['highest_track']);
//            }
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