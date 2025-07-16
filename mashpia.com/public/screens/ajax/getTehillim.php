<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);

$admin_auth = ['school'];
require_once __DIR__ . '/../../header.php';
require_once __DIR__ . '/../../class.shabbosMevorchim.php';
$sm = new ShabbosMevorchim();

require_once __DIR__ . '/../../class.adminSchools.php';
$adminSchools = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $adminSchools->getSchools();

$response = [
    'success' => true,
    'data' => [
        [
            'name' => 'Yosef Cohen',
            'chapter' => 'Chapter 1'
        ],
        [
            'name' => 'Moshe Levy',
            'chapter' => 'Chapter 2'
        ],
        [
            'name' => 'David Green',
            'chapter' => 'Chapter 3'
        ],
        [
            'name' => 'Aharon Silver',
            'chapter' => 'Chapter 4'
        ],
        [
            'name' => 'Shmuel Gold',
            'chapter' => 'Chapter 5'
        ]
    ]
];

echo json_encode($response);