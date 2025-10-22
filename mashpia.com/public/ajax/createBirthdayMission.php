<?php
$ids = [$_REQUEST['id']];

if (empty($id)) {
    $ids = [64876,
    78064,
    79063,
    79072,
    79075,
    79077,
    71330,
    71401,
    79127,
    80955,
    78899,
    80764,
    81002,
    57144,
    67914,
    79563,
    77812,
    77830,
    77835,
    69887,
    80567,
    80571,
    80572,
    80573,
    80575,
    80577,
    69792,
    69793,
    69794,
    81060];
}

require_once '../db.php';
require_once '../class.birthdayEn.php';
require_once '../class.birthdayYi.php';
require_once '../class.birthdayHe.php';

foreach ($ids as $id) {
    $b = new BirthdayEn($id);
    $b->setBirthday();
    $errors = $b->getErrors();

    $bi = new BirthdayYi($id);
    $bi->setBirthday();
    $errors2 = $bi->getErrors();

    $bh = new BirthdayHe($id);
    $bh->setBirthday();
    $errors3 = $bh->getErrors();

    if ($errors || $errors2 || $errors3) {
        if ($errors)
            echo json_encode($errors);
        else if ($errors2)
            echo json_encode($errors2);
        else
            echo json_encode($errors3);
    } else {
        echo json_encode($id);
    }
    echo "<br /><br />";
}