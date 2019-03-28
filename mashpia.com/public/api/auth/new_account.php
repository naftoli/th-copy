<?php
require_once( __DIR__ . '/../header/header.php' );
require_once( __DIR__ . '/../../chabad_org/classes/ChabadShliach.php' );

use mashpia\api\auth\Auth as Auth;

class NewAdminRouter {

    // functions as update
    public function create() {

        if ( !isset( $_POST['password'] ) || !isset( $_POST['confirm'] ) || $_POST['password'] !== $_POST['confirm'] )
            return json_error( 'Could not create TH Account', [ 'Passwords do not match' ] );

        $admin = Admin::build( $_POST );
        $admin->username = $admin->admin_email;
        $admin->beta = 1;

        if ( !$admin->is_valid() || !$admin->save() )
            return json_error( 'Could not create TH Account', $admin->errors->full_messages() );

        $mosdos = [];
        $year = GlobalSettings::getCurrentYear();

        if ( isset( $_POST['mosdos'] ) && is_array( $_POST['mosdos'] ) ) {
            // create a base for each mosad and connect it to our new account.
            foreach( $_POST['mosdos'] as $mosad ) {
                // if the mosad was unselected. Skip it
                if ( !$mosad['selected'] )
                    continue;

                $school = School::find_by_mosad_id( $mosad['mosad_id'] );

                if ( !$school ) {
                    $school = School::build( $mosad );
                    $school->copyAddressToShipping(); // set the shipping address
                    $school->generateInitials();
                    $school->notes = $mosad['url'];
                    $school->inst_id = 10; // set as a C-kids base
                    $school->school_era = $year;
                    $school->save();
                }

                $mosdos[] = $school;
            }
        }
        // connect the admin to each mosad
        foreach( $mosdos as $school ) {
            if ( $school->school_id ) {
                AdminAuth::create([
                    'id' => $school->school_id,
                    'auth' => 'school',
                    'admin_id' => $admin->admin_id
                ]);
            }
        }

        json_response([
            'account' => $admin,
            'tokens' => Auth::generateKeys( $admin )
        ], true, true);
    }
}

rest_router( new NewAdminRouter );
