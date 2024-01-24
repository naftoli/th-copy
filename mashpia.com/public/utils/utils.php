<?php
$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';

function checkAuth() {
    global $admin_user;
    if ($admin_user['auth'] != 'super') {
        die('Access denied');
    }
}

function getAuth() {
    global $admin_user;
    return $admin_user['auth'];
}

function getDbHandle() {
    global $MASHPIA_DB;
    return $MASHPIA_DB;
}

function getChidonYear() {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
    return GlobalSettings::getChidonYear();
}

function getChayoleiYear() {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class.globalSettings.php';
    return GlobalSettings::getCurrentYear();
}

function getSchools() {
    global $admin_user;
    require_once $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php';
    $as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
    return $as->getSchools();
}