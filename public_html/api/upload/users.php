<?php
define( "MASHPIA_AUTH_REQUIRED", true );
include_once( __DIR__ . "/../header/header.php" );

class UsersUploadRouter {
    
    function create(){
        global $pdo; global $current_user;

        if ( !isset( $_FILES['users'] ) )
            json_error("No File Sent");
        // columns to create users
        $columnNames = array( 
            "*First Name", 
            "*Last Name", 
            "*First Name Hebrew", 
            "*Last Name Hebrew",
            "*Gender",
            "*English Date of Birth", 
            "Address 1", 
            "Address 2", 
            "City", 
            "State", 
            "Zip", 
            "Country", 
            "Phone", 
            "Parents Email",
            "*Mission Type"
        );
        // load up the excel reader
        require_once( __DIR__ . '/../../PHPExcel/IOFactory.php' );
        $objPHPExcel = PHPExcel_IOFactory::load($_FILES['users']['tmp_name']);
        $objWorksheet = $objPHPExcel->getActiveSheet();

        // go through the spreadsheet
        $headers = [];
        $users = [];
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
                        [ $headers, $columnNames ]
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
                        json_error(
                            "$errorString You must supply a ". substr($headers[$cellIndex], 1) . " for every student."
                        );
                    }

                    // check name length
                    if ((
                        $headers[$cellIndex] == "*First Name"        ||
                        $headers[$cellIndex] == "*Last Name"         ||
                        $headers[$cellIndex] == "*First Name Hebrew" ||
                        $headers[$cellIndex] == "*Last Name Hebrew"
                    ) && strlen( $value ) < 3 ) {
                        json_error(
                            $errorString.substr($headers[$cellIndex], 1)." cannot be less than 3 characters in length."
                        );
                    }

                    // check character type
                    if ((
                        $headers[$cellIndex] == "*First Name Hebrew" ||
                        $headers[$cellIndex] == "*Last Name Hebrew"
                    ) && strpos( urlencode( $value ), '%' ) === false ) {
                        json_error(
                            "$errorString Hebrew name must be in hebrew characters."
                        );
                    }
                    
                    // check gender type
                    if (
                        $headers[$cellIndex] == "*Gender" && 
                        !in_array( $value, [ 'm', 'f', 'M', 'F' ] )
                    ) {
                        json_error(
                            "$errorString Gender must be 'M' or 'F'."
                        );
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
                            json_error("$errorString Date of Birth must follow the format MM/DD/YYYY.");
                        }
                        // check that it is in our year range
                        $year = date('Y');
                        $startYear = $year - 5; $endYear = $year - 15;
                        if ( $dob->format('Y') > $startYear || $dob->format('Y') < $endYear - 15 ) {
                            json_error("$errorString Date of Birth must be between $startYear and $endYear.");
                        }
                    }

                    // validate mission type
                    if ( $headers[$cellIndex] == "*Mission Type" && 
                        !in_array( $value, [ 'chabad', 'frum' ] )
                    ) {
                        json_error( "$errorString Mission type must be 'chabad' or 'frum." );
                    }
                    
                    $user[ $headers[ $cellIndex ] ] = $value;
                    $cellIndex += 1;
                } // end cell iteration
                $errorLine += 1;
                $users[] = $user;
            } // end firstRow check
        } // end row iteration
        json_response( $users );
    } // end create function
}

rest_router( new UsersUploadRouter );
