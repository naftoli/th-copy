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
            'chapter' => '5 Kapitlach'
        ],
        [
            'name' => 'Moshe Levy',
            'chapter' => '10 Kapitlach'
        ],
        [
            'name' => 'David Green',
            'chapter' => '15 Kapitlach'
        ],
        [
            'name' => 'Aharon Silver',
            'chapter' => '20 Kapitlach'
        ],
        [
            'name' => 'Shmuel Gold',
            'chapter' => '25 Kapitlach'
        ]
    ]
];

echo json_encode($response);