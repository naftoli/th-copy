<?php
require $_SERVER['DOCUMENT_ROOT'] . '/db.php';
require $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
$year = GlobalSettings::getChidonRegYear();

// $info = [
//     7760539	=> [
//         'children m' =>	1
//     ],
//     7760542	=> [
//         'children m' =>	1
//     ],
//     7760543	=> [
//         'children m' =>	1
//     ],
//     7760544	=> [
//         'children s' =>	1
//     ],
//     7760545	=> [
//         'children s' =>	1
//     ],
//     7760548	=> [
//         'children m' =>	1
//     ],
//     7760549	=> [
//         'children m' =>	1
//     ],
//     7760551	=> [
//         'children m' =>	1
//     ],
//     7760552	=> [
//         'children s' =>	1
//     ],
//     7760553	=> [
//         'children s' =>	1
//     ],
//     7760647	=> [
//         'children xl' => 2
//     ],
//     7756410	=> [
//         'children xl' => 2
//     ],
//     7756411	=> [
//         'children xl' => 2
//     ],
//     7756412	=> [
//         'children m' =>	2
//     ],
//     7756414	=> [
//         'children m' => 2
//     ],
//     7756415	=> [
//         'children m' => 2
//     ],
//     7756418	=> [
//         'children xl' => 2
//     ],
//     7756419	=> [
//         'children m' =>	2
//     ],
//     7756420 => [
//         'children l' =>	2
//     ],
//     7756421	=> [
//         'children m' =>	2
//     ],
//     7748044	=> [
//         'children l' =>	2
//     ],
//     7756425	=> [
//         'children m' =>	2
//     ],
//     7756426	=> [
//         'children m' =>	2
//     ],
//     7756428 => [
//         'children m' =>	2
//     ],
//     7756429	=> [
//         'children l' =>	2
//     ],
//     7752354 => [
//         'children xl' => 3
//     ],
//     7752356	=> [
//         'children xl' => 3
//     ],
//     7752358	=> [
//         'children xl' => 3
//     ],
//     7752359	=> [
//         'children xl' => 3
//     ],
//     7748048	=> [
//         'children l' =>	3
//     ],
//     7760675	=> [
//         'children l' =>	3
//     ],
//     7752364	=> [
//         'children l' =>	3
//     ],
//     7760674	=> [
//         'children l' =>	3
//     ],
//     7760938	=> [
//         'children l' =>	3
//     ],
//     7752372	=> [
//         'children l' =>	3
//     ],
//     7745863	=> [
//         'children l' =>	3
//     ],
//     7752376	=> [
//         'children l' =>	3
//     ],
//     7749678	=> [
//         'children l' =>	4
//     ],
//     7752700	=> [
//         'children xl' => 4
//     ],
//     7749679	=> [
//         'children xl' => 4
//     ],
//     7748043	=> [
//         'children l' =>	4
//     ],
//     7749728	=> [
//         'children l' =>	4
//     ],
//     7749752	=> [
//         'adult s' => 5
//     ],
//     7749674	=> [
//         'children xl' => 5
//     ],
//     7760710	=> [
//         'children l' =>	5
//     ],
//     7742676 => [
//         'children xl' => 5
//     ],
//     7748333	=> [
//         'children xl' => 5
//     ],
//     7752707	=> [
//         'children xl' => 5
//     ]
// ];

$info = [
    7756411	=> [
        'children xl' => 2
    ],
    7756425	=> [
        'children m' =>	2
    ],
    7756428 => [
        'children m' =>	2
    ],
    7760542	=> [
        'children m' =>	1
    ]
];

$updated = 0;
$school_id = 3;
$success = true;
mysql_query('set autocommit=0');
mysql_query('begin');

foreach ( $info as $serial => $row ) {
    foreach ( $row as $size => $book ) {
        $sql = "select user_id, admin_id 
                from users u  
                join admin_auths aa on aa.id = u.user_id 
                where user_serial = " . $serial;
        $result = mysql_query( $sql );
        $row = mysql_fetch_assoc( $result );
        $user_id = $row['user_id'];
        $parent = $row['admin_id'];
        if ( $user_id ) {
            $sql = "insert into registration_charges 
                    set user_id = " . $user_id . ", 
                    school_id = " . $school_id . ", 
                    type = 'chidon', 
                    amount = 0.00, 
                    date = now(), 
                    year = " . $year;
            //echo $sql . "<br />";
            if ( !mysql_query( $sql ) ) {
                $success = false;
                break 2;
            }
            $sql = "insert into th_chidon 
                    set year = " . $year . ", 
                    school_id = " . $school_id . ", 
                    user_id = " . $user_id . ", 
                    size = '" . $size . "', 
                    reg_date = now(), 
                    book = " . $book . ", 
                    parent_id = " . $parent;
            //echo $sql . "<br /><br />";
            if ( !mysql_query( $sql ) ) {
                $success = false;
                break 2;
            }
            $updated++;
        }
    }
}

if ( $success ) {
    echo "Updated: " . $updated;
    mysql_query('commit');
} else {
    echo mysql_error();
    mysql_query('rollback');
}
mysql_query('set autocommit=1');