<?php
require_once '../headers.php';

if ( !isset( $_REQUEST['key'] ) || $_REQUEST['key'] != 'Chidon@5780!' ) {
    echo json_encode([
        'succes'    =>  false, 
        'error'     =>  "Access Forbidden."
    ]);
    exit;
}

echo "<pre>"; print_r( $_POST ); echo "</pre>";