<?php
//ini_set('display_errors',1);
$admin_auth = array('school');
require_once( __DIR__ . '/../header.php' );
require_once( __DIR__ . '/../class.adminSchools.php' );
require_once( __DIR__ . '/../class.schoolClasses.php' );

$as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
$schools = $as->getSchools();

// key corresponds to db table and field name and value corresponds to output text on form
// when we are not pulling from a specific table I use the word 'calc' instead
$userInfo = [
    'users|first'           => 'First Name', 
    'users|last'            => 'Last Name', 
    'users|first_he'        => 'Hebrew First', 
    'users|last_he'         => 'Hebrew Last', 
    'users|dob'             => 'English DOB', 
    'users|dob_he'          => 'Hebrew DOB', 
    'users|user_registered' => 'Registered Date', 
    'classes|class_grade'   => 'Class Grade', 
    'classes|class_sub'     => 'Class Sub',
    'schools|school_name'   => 'School', 
    'ranks|rank_name'       => 'Current Rank',
    'calc|store_points'     => 'Store Miles', 
    'calc|total_points'     => 'Total Miles', 
    'calc|total_this_yr'    => 'Total Miles from Beginning of Year' 
];

$adminInfo = [
    'admins|first'             => 'First Name', 
    'admins|last'              => 'Last Name', 
    'admins|admin_address1'    => 'Address Line 1', 
    'admins|admin_address2'    => 'Address Line 2', 
    'admins|admin_city'        => 'City', 
    'admins|admin_state'       => 'State', 
    'admins|admin_postal'      => 'Zip', 
    'admins|admin_country'     => 'Country', 
    'admins|admin_email'       => 'Email', 
    'admins|admin_phone_work'  => 'Work Phone', 
    'admins|admin_phone_home'  => 'Home Phone', 
    'admins|admin_phone_mobile'=> 'Cell Phone'
];

function buildSelect( $info ) {
    $html = '';
    foreach ( $info as $key => $val ) {
        $html .= "<input type='checkbox' name='" . $key . "' /> " . $val . "<br />";
    }
    return $html;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Reports</title>
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.css"/>
        <style>
            body {
                font-family: sans-serif;
                font-size: 12px;
                padding-left: 3%;
                padding-right: 3%;
            }
            fieldset {
                float: left;
                width: 40%;
                padding-right: 20px;
                padding-left: 20px;
                padding-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <h1>Create Your Own Report</h1>
        <p>
            Use following form to select the information you would like to see on your report.<br />
            <i>Please Note: If you choose to see points, the time it takes to generate the report will vary greatly so please be patient.</i>
        </p>
        <form action="createReport.php" method="POST" name="reportGenerator" id="reportGenerator">
            <fieldset id="userInfo">
                <legend>
                    Student Info
                </legend>
                <?= buildSelect( $userInfo ); ?>
            </fieldset>

            <fieldset id="parentInfo">
                <legend>
                    Parent Info
                </legend>
                <?= buildSelect( $adminInfo ); ?>
            </fieldset>
            
            <div style="clear: both"></div>
            <fieldset id="options">
                <legend>Limit To</legend>
                <?php 
                if ( count( $schools ) > 1 ) {
                    echo "<select name='school' id='school'>";
                    foreach ( $schools as $id => $school ) {
                        echo "<option value='" . $id . "'>" . $school . "</option>";
                    }
                    echo "</select><br /><br />";
                } else {
                    // get school id
                    $school_id = key( $schools );
                    echo "<input type='hidden' name='school' value='" . $school_id . "' />";
                }

                // get school id
                reset( $schools );
                $school_id = key( $schools );
                // get classes
                $sc = new SchoolClasses( $school_id );
                $grades = $sc->getClasses();
                echo "<select name='grade' id='grade'>";
                echo "<option value='-1'>All Classes</option>";
                foreach ( $grades as $grade ) {
                    echo "<option value='" . $grade['class_id'] . "'>" . ($grade['class_grade'] . (empty($grade['class_sub']) ? '' : '-' . $grade['class_sub'])) . "</option>";
                }
                echo "</select>";
                ?>
            </fieldset>
            
            <div style="clear: both">
                <input type="submit" name="submit" value="generate" />
            </div>
        </form>
    </body> 
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script>
        $(function() {
            $("form").submit( function() {
                if ( !$("#userInfo input:checked").length && !$("#parentInfo input:checked").length ) {
                    alert('You have not chosen any fields!');
                    return false;
                }
            });

            $("#school").change( function() {
                var id = $(this).val();
                $.post( 'ajax/getGrades.php', { school: id }, function( result ) {
                    var grades = JSON.parse( result );
                    var html = "<option value='-1'>All Classes</options>";
                    for ( var g in grades ) {
                        html += "<option value='" + g + "'>" + grades[g] + "</option>";
                    }
                    $("#grade").empty();
                    $("#grade").append( html );
                });
            });
        });
    </script>       
</html>