<?php
include_once( __DIR__ . "/../header/header.php" );

class LabelsRouter {

    public function index() {

        $labels = Label::all([
            'conditions' => 'active = 1',
            'order' => 'label_name',
            'include' => 'frequency'
        ]);

        json_response( $labels, true, true );
    }
}

rest_router( new LabelsRouter );
