<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class UsersUploadRouter {

    function authenticate(){
        global $current_user;

        return $current_user->login['code'] == 'BC' || isset( $_POST['school_id'] );
    }
    
    function create(){
        global $pdo; global $current_user;

        if ( !isset( $_FILES['users'] ) )
            json_error("No File Sent", 'UPLOAD-USERS-17');
        // columns to create users
        $columnNames = [
            "*First Name",  "*Last Name", 
            "*First Name Hebrew",  "*Last Name Hebrew",
            "*Gender", "*English Date of Birth", 
            "Address 1", "Address 2", "City", "State", "Zip", "Country", 
            "Phone", "Parents Email", "*Mission Type"
        ];
        $dbColumnNames = [
            'first', 'last', 'first_he', 'last_he',
            'gender', 'dob', 'user_address1', 'user_address2',
            'user_city', 'user_state', 'user_postal', 'user_country',
            'user_phone', 'email', 'school_type_id'
        ];
        // load up the excel reader
        require_once( __DIR__ . '/../../PHPExcel/IOFactory.php' );
        $objPHPExcel = PHPExcel_IOFactory::load($_FILES['users']['tmp_name']);
        $objWorksheet = $objPHPExcel->getActiveSheet();

        // go through the spreadsheet
        $headers = [];
        $users = []; $errors = [];
        $firstRow = true;
        $errorLine = 1;

        foreach( $objWorksheet->getRowIterator() as $row ) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            // header row
            if ( $firstRow ) {
                // iterate through the cells
                foreach ( $cellIterator as $cell ) {
                    $headers[] = $cell->getValue();
                }
                // make sure that the headers are valid
                if ( array_diff( $headers, $columnNames ) )
                    json_error(
                         "You have an incorrect excel sheet.\n"
                        ."Please download the sample file again and do not modify the header information.",
                        'UPLOAD-USERS-57'
                    );

                $firstRow = false;
            } else {
                $cellIndex = 0;
                $user = [];
                foreach ( $cellIterator as $cell ) {
                    $value = trim($cell->getValue());
                    $errorString = "Error on line $errorLine:  ";
                    // validate required rows
                    if ((
                        $headers[$cellIndex] == "*First Name"              || 
                        $headers[$cellIndex] == "*Last Name"               ||
                        $headers[$cellIndex] == "*First Name Hebrew"       || 
                        $headers[$cellIndex] == "*Last Name Hebrew"        ||
                        $headers[$cellIndex] == "*Gender"                  || 
                        $headers[$cellIndex] == "*English Date of Birth"   || 
                        $headers[$cellIndex] == "*Mission Type"
                    ) && $value == "" ) {
                        $errors[] = "$errorString You must supply a "
                            . substr($headers[$cellIndex], 1) . " for every student.";
                    }

                    // check name length
                    if ((
                        $headers[$cellIndex] == "*First Name"        ||
                        $headers[$cellIndex] == "*Last Name"         ||
                        $headers[$cellIndex] == "*First Name Hebrew" ||
                        $headers[$cellIndex] == "*Last Name Hebrew"
                    ) && strlen( $value ) < 3 ) {
                        $errors[] = $errorString.substr($headers[$cellIndex], 1)
                            ." cannot be less than 3 characters in length.";
                    }

                    // check character type
                    if ((
                        $headers[$cellIndex] == "*First Name Hebrew" ||
                        $headers[$cellIndex] == "*Last Name Hebrew"
                    ) && strpos( urlencode( $value ), '%' ) === false ) {
                        $errors[] = "$errorString Hebrew name must be in hebrew characters.";
                    }
                    
                    // cast gender to lower case
                    if ( $headers[$cellIndex] == "*Gender" ) {
                        $value = strtoupper( $value );
                    }

                    // check gender type
                    if (
                        $headers[$cellIndex] == "*Gender" && 
                        !in_array( $value, [ 'M', 'F' ] )
                    ) {
                        $errors[] = "$errorString Gender must be 'M' or 'F'.";
                    } 

                    // validate dob
                    if (
                        $headers[$cellIndex] == "*English Date of Birth"
                    ) {
                        // parse the date
                        if ( is_numeric( $value ) ) {
                            $date = PHPExcel_Shared_Date::ExcelToPHPObject( $value );
                            $jd = unixtojd( $date->getTimestamp() ); 
                            // for some reason the result is off by one day so need to add 1
                            $dob = jdtogregorian( ++$jd );
                        } else {
                            $dob = $value;
                        }
                        // make sure it is a valid date
                        try {
                            $dob = new \DateTime( $dob );
                        } catch ( Exception $e ) {
                            $errors[] ="$errorString Date of Birth must follow the format MM/DD/YYYY.";
                        }
                        // check that it is in our year range
                        $year = date('Y');
                        $startYear = $year - 5; $endYear = $year - 15;
                        if ( $dob->format('Y') > $startYear || $dob->format('Y') < $endYear - 15 ) {
                            $errors[] ="$errorString Date of Birth must be between $startYear and $endYear.";
                        }
                    }

                    // validate mission type
                    if ( $headers[$cellIndex] == "*Mission Type" && 
                        !in_array( $value, [ 'chabad', 'frum', 'c-kids' ] )
                    ) {
                        $errors[] = "$errorString Mission type must be 'chabad', 'frum' or 'c-kids'.";
                    }
                    
                    $user[ $dbColumnNames[ $cellIndex ] ] = $value;
                    $cellIndex += 1;
                } // end cell iteration
                $errorLine += 1;
                $users[] = $user;
            } // end firstRow check
        } // end row iteration

        // return an error if there where no users provided
        if ( count( $users ) == 0 ) {
            return json_error( "No soldiers found on spreadsheet. Please check your file before uploading.", 'UPLOAD-USERS-157' );
        }

        // return the list of errors
        if ( count( $errors ) >= 1 ){
            return json_error( 
                count( $errors )." errors where found in your spreadsheet. "
                ."Please correct your file before uploading again.",
                $errors
            );
        }

        $school = false;
        if ( isset( $_POST['school_id'] ) ) {
            $school = School::find( $_POST['school_id'] );
        } else if ( $current_user->login['code'] == 'BC' ) {
            $school = School::find( $current_user->login['id'] );
        }

        // create all the users...
        foreach( $users as $index => $user ){
            if ( $user['school_type_id'] == 'chabad' ) {
                $user['school_type_id'] = $user['gender'] == 'M' ? 2 : 3; 
            } else if ( $user['school_type_id'] == 'frum' ) {
                $user['school_type_id'] = $user['gender'] == 'M' ? 12 : 13;
            } else {
                $user['school_type_id'] = $user['gender'] == 'M' ? 22 : 23; 
            }

            $user = new User( $user );
            // copy over defaults from the school on creation...
            $user->school_id = $school->school_id;
            $user->chayolei = $school->chayolei;
            $user->chidon   = $school->chidon;
            $user->yan    = $school->tanya;
            
            $user->save();
            $users[$index] = $user;
        }

        json_response( false );
    } // end create function
}

rest_router( new UsersUploadRouter );
