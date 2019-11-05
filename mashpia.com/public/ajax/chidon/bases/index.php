<?php
require_once '../headers.php'; 

if ( !isset( $_GET['key'] ) || $_GET['key'] != 'Chidon@5780!' ) {
    echo json_encode([
        'succes'    =>  false, 
        'error'     =>  "Access Forbidden."
    ]);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonYear();

$info = [];
$stmt = $MASHPIA_DB->prepare("
    SELECT 
        school_id,
        school_name,
        school_city,
        school_state,
        chidon_name,
        chidon_number,
        chidon_email,
        COUNT(*) AS finalists, 
        SUM(trophy_5779) AS trophies 
    FROM
        schools s
            JOIN
        th_chidon_schools tcs USING (school_id)
            JOIN
        th_chidon tc USING (school_id , year)
    WHERE
        tcs.year = :year AND tc.date_paid > 0
    GROUP BY school_id
    ");
$res = $stmt->execute([':year' => ($year - 1)]);
if ( $res ) {
    $rows = $stmt->fetchAll();
    foreach ( $rows as $row ) {
        $stmt2 = $MASHPIA_DB->prepare("
            SELECT 
                gender, COUNT(gender) as total
            FROM
                th_chidon tc
                    JOIN
                users u USING (user_id)
            WHERE
                tc.school_id = :id AND year = :year
            GROUP BY gender   
        ");
        $res2 = $stmt2->execute([
            ':id'   =>  $row['school_id'], 
            ':year' =>  ($year - 1)
        ]);
        if ( $res2 ) {
            $rows2 = $stmt2->fetchAll();
            $multipleGenders = false;
            if ( count( $rows2 ) > 1 ) {
                // multiple genders in same school
                $multipleGenders = true;
            } 
            foreach ( $rows2 as $row2 ) {
                $gender = $row2['gender'];
                switch ( $gender ) {
                    case 'F':
                        $type = 'Girls';
                        break;
                    case 'M':
                        $type = 'Boys';
                        break;
                }
                if ( isset( $type ) ) {
                    if ( !$multipleGenders ) {
                        $row['gender'] = $type;
                    } else {
                        // remove any existing gender in school name and then add the one we want 
                        $row['school_name'] = str_replace( 'Boys', '', $row['school_name'] );
                        $row['school_name'] = str_replace( 'Girls', '', $row['school_name'] );
                        $row['school_name'] .= " " . $type; // seperate school into 2 types, boys and girls 
                        $row['finalists'] = $row2['total'];
                        $row['gender'] = $type;
                    }
                    $info[] = $row; // assign modified row to info array that gets returned
                }
            }
        } 
    }
} 

if ( count( $info ) ) {
    echo json_encode([
        'succes'    =>  true,
        'data'      =>  $info
    ]);
} else {
    echo json_encode([
        'success'   =>  false,
        'error'     =>  "Error fetching results."
    ]);
}