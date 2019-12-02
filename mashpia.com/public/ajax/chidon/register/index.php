<?php
require_once '../headers.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';

if ( !isset( $_REQUEST['key'] ) || $_REQUEST['key'] != 'Chidon@5780!' ) {
    echo json_encode([
        'succes'    =>  false, 
        'error'     =>  "Access Forbidden."
    ]);
    exit;
}

$data = $_POST['data'];
$children = $data['children'];
foreach ( $children as $child ) {
    $user = \Soldier::find([ $child['user_id'] ]);

    $fields = [ 'gender', 'first', 'last', 'first_he', 'last_he', 'dob' ];
    foreach ( $fields as $field ) {
        $user->{ $field } = $_POST[ $field ];
    }

    // flag to know if we need to update the birthday missions
    if ( $user->attribute_is_dirty('dob') ) $updateBirthday = true;
    else $updateBirthday = false;

    if ( !$user->is_valid() || !$user->save() ) {
        echo json_encode([
            'success'   =>  false, 
            'error'     =>  'Could not update soldier. Please check to make sure that the data is valid.'
        ]);
    } else {
        // update the birthday missions if dob was changed
        if ( $updateBirthday ) {
            $user->setupBirthdayMissions();
        }
    }


    $th_fields = [ 'sweater_size', 'book', 'recruited_by', 'purchasing_book', 'already_purchased', 'purchased_where', 'studying_with' ];
}