<?php
define( 'MASHPIA_AUTH_REQUIRED', true );
require_once( __DIR__ . '/../header/header.php' );

if ( $current_user )
    json_response( 
        $current_user->to_array([
            'only' => [
                'admin_id', 'username', 'title', 'first', 'last', 'lang', 
                'father', 'mother', 'father_pic', 'mother_pic',
                'home_phone', 'cell_phone', 'admin_email'
            ],
            'methods' => [ 'authCode', 'logins' ]
        ])
    );
else
    json_error( 'Invalid Credentials' );
