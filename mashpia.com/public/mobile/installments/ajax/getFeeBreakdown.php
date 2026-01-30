<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

// Start output buffering to catch any errors
ob_start();

header('Content-Type: application/json');

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/chidon_drive/site/enrollment/class.tripRegistration.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/mobile/reg/ajax/encrypt.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonOld/coupons/class.couponCode.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/chidonTests/class.chidonTests.php';

    if (!isset($_POST['admin'])) {
        throw new Exception('Admin parameter missing');
    }

    $admin = $_POST['admin'];
    $admin_id = encrypt_decrypt('decrypt', $admin);

    if (!$admin_id) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'error' => 'Invalid admin ID'
        ]);
        exit;
    }

    $year = GlobalSettings::getChidonYear();

    // Track fee structure (smallest to largest)
    $trackInfo = [
        'Yesod' => 50,
        'Yediah' => 105,
        'Havonah' => 205,
        'Iyun' => 205,
        'Ultimate' => 350,
    ];

    // Get children using the same logic as getChildren.php
    $sql = "select u.user_id, u.school_id, u.class_id, u.mobile_pic, u.user_photo_id, u.first, u.last, u.user_serial,  
                UPPER(u.gender) as gender, 
                c.class_grade, 
                tc.*, 
                conf.chidon_confirmation_id as schoolConfirmed, 
                cor.open_reg as openRegForSchool,
                a.admin_id, a.admin_country, a.admin_address1, a.admin_address2, a.admin_city, a.admin_state, a.admin_postal 
            from users u 
            join schools s using (school_id)
            join th_chidon tc using (user_id)  
            join classes c on c.class_id = u.class_id 
            left join chidon_confirmations conf on (u.school_id = conf.school_id and conf.year = :year) 
            left join chidon_open_reg cor on (cor.school_id = u.school_id and cor.year = :year) 
            join admin_auths aa on aa.id = u.user_id 
            join admins a using (admin_id) 
            where tc.year = :year 
            and a.admin_id = :admin";
    $stmt = $MASHPIA_DB->prepare($sql);
    $stmt->execute([
        ':year' => $year,
        ':admin' => $admin_id
    ]);
    $children = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$children) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'error' => 'No eligible children found'
        ]);
        exit;
    }

    // Get family balance (only once)
    $r = new TripRegistration($admin_id, $year);
    $familyBalance = $r->getFamilyBalance();

    // Get chidon drive amounts and personal credits
    $stmt = $MASHPIA_DB->prepare("
        SELECT IFNULL(SUM(subsidy_amount), 0) as raised 
        FROM chidon_user_subsidies 
        WHERE chidon_year = :year AND user_id = :user
    ");

    $stmtPersonalCredit = $MASHPIA_DB->prepare("
        SELECT IFNULL(SUM(amount), 0) as personal_credit 
        FROM registration_charges 
        WHERE year = :year AND user_id = :user 
            AND type in ('RRYSD', 'RRYDA', 'RRHVN')
    ");

    $c = new CouponCode($MASHPIA_DB, $year);

    foreach ($children as &$child) {
        $child['familyBalance'] = $familyBalance;
        
        $stmt->execute([
            ':year' => $year,
            ':user' => $child['user_id']
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $child['raised'] = $result['raised'];
        
        $stmtPersonalCredit->execute([
            ':year' => $year,
            ':user' => $child['user_id']
        ]);
        $res = $stmtPersonalCredit->fetch(PDO::FETCH_ASSOC);
        $child['personal_credit'] = $res['personal_credit'];
        
        $child['coupon'] = $c->checkForUserCode($child['user_serial']);
    }
    unset($child); // break reference so later foreach doesn't overwrite last element

    // Get tracks
    $ct = new ChidonTests();
    $types = $ct->getTypes();
    $tracks = [];

    foreach ($children as $child) {
        $track = '';
        $cumulative = '';
        // only need to calculate track if there's no reward type set for this child
        if (empty($child['reward_type']) || $child['reward_type'] === 'highest track passed') {
            $ct->setStudents($child['school_id'], $child['class_id'], $child['user_id']);
            $ct->setScores();
            $ct->calculateMarks();
            $scores = $ct->getScores();
            if (isset($scores[$child['th_chidon_id']])) {
                $cumulative = $ct->calculateCumulative($child, $scores[$child['th_chidon_id']]);
            };
            if ($cumulative == 'iyun') $track = 'Iyun';
            else {
                $marks = $ct->getMarks();
                if (isset($marks[$child['th_chidon_id']])) {
                    $track = $ct->getHighestTrack($marks[$child['th_chidon_id']], $child['user_id']);
                }
            }
        } else {
            $track = $child['reward_type'];
        }
        if (array_key_exists($track, $types)) {
            $track = $types[$track];
        }
        $tracks[$child['user_id']] = $track;
    }

    // Get KHK eligibility (KHK class is in ChidonTests.php which is already included)
    $ids = [];
    foreach ($children as $child) {
        if ($child['class_grade'] == '8') {
            $ids[] = $child['user_id'];
        }
    }
    $khk = [];
    if (count($ids)) {
        $khk_result = KHK::getUltimateTripEligibility($ids);
        if ($khk_result && is_array($khk_result) && count($khk_result) > 0) {
            $khk = $khk_result[0];
        }
    }

    // Process each child
    $breakdown = [];
    $totalOwing = 0;

    foreach ($children as $child) {
        // Skip if already paid
        if ($child['date_paid']) {
            continue;
        }
        
        // Get track
        $track = isset($tracks[$child['user_id']]) ? $tracks[$child['user_id']] : null;
        if (!$track) {
            continue;
        }
        
        // Check KHK eligibility
        $khk_eligible = ($child['class_grade'] == '8' && isset($khk[$child['user_id']]) && $khk[$child['user_id']]);
        // if ($khk_eligible && in_array($track, ['Yesod', 'Yediah'])) {
        //     $track = 'Havonah';
        // }
        if ($khk_eligible) {
            $track = 'Ultimate';
        }
        
        // Get fee amounts for this track
        $amount = $trackInfo[$track] ?? 0;
        
        // Calculate credits
        $personalCredit = intval($child['personal_credit'] ?? 0);
        $raised = floatval($child['raised'] ?? 0);
        $coupon = floatval($child['coupon'] ?? 0);
        
        // Calculate final fee after credits
        $finalFee = $amount;
        
        // Apply personal credit (only if track is not Yesod)
        if ($personalCredit > 0 && $track != 'Yesod') {
            $finalFee -= $personalCredit;
        } 
        
        // Apply chidon drive credit
        if ($raised > 0 && $finalFee > 0) {
            $finalFee -= $raised;
        } 
        
        // Apply coupon credit
        if ($coupon > 0 && $finalFee > 0) {
            $finalFee -= $coupon;
        } 
        
        if ($finalFee < 0) {
            $finalFee = 0;
        }
        
        $breakdown[] = [
            'user_id' => $child['user_id'],
            'first' => $child['first'],
            'last' => $child['last'],
            'track' => $track,
            'fee' => $amount,
            'personal_credit' => $personalCredit,
            'chidon_drive_credit' => $raised,
            'coupon_credit' => $coupon,
            'final_fee' => round($finalFee, 2)
        ];
        
        $totalOwing += $finalFee;
    }

    // Apply family balance credit to total (subtract it)
    $familyBalance = floatval($familyBalance ?? 0);
    if ($familyBalance > 0) {
        $totalOwing -= $familyBalance;
        // Don't let total go below zero
        if ($totalOwing < 0) {
            $totalOwing = 0;
        }
    }

    $response = [
        'success' => true,
        'breakdown' => $breakdown,
        'total_owing' => round($totalOwing, 2),
        'family_balance' => $familyBalance
    ];

    // Clear any output that might have been generated
    ob_clean();
    echo json_encode($response);
    exit;
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    error_log('getFeeBreakdown Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    exit;
} catch (Error $e) {
    ob_clean();
    http_response_code(500);
    error_log('getFeeBreakdown Fatal Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode([
        'success' => false,
        'error' => 'Fatal error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    exit;
} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    error_log('getFeeBreakdown Throwable: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode([
        'success' => false,
        'error' => 'Error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
}
