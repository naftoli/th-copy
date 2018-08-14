<?php
//echo "<pre>"; print_r( $_POST ); echo "</pre>";
require '../db.php';
require 'classes/class.reportingEngine.php';

// build array to pass into engine
$info = array();
foreach ( $_POST as $k => $v ) {
    if ( $k != 'submit' ) {
        // find table and column info
        $pos = strpos($k, '|');
        if ( $pos !== false ) {
            $table = substr($k, 0, $pos++);
            $field = substr($k, $pos);
            if ( $table != 'calc' ) {
                $info[$table][] = $field;
            } else { // it's not info from a table but it's a calculated value
                switch ( $field ) {
                    case 'store_points':
                    case 'total_points':
                    case 'total_this_yr':
                        $info['calc'][] = $field;
                        break;
                }
            }
        }
    }
}

$engine = new ReportingEngine( $info );
$engine->setSchool( mysql_real_escape_string( $_POST['school'] ) );
$engine->setClass( mysql_real_escape_string( $_POST['grade'] ) );

$engine->createQry();
if ( $engine->runQry() ) {
    $result = $engine->getResult();
    //echo "<pre>"; print_r( $result ); echo "</pre>";
} else {
    echo $engine->getError();
    exit;
}

// create mapping between fields in resultset and table header textual representation
$fields = [
    'u_first'           => 'Student First Name', 
    'u_last'            => 'Student Last Name', 
    'u_first_he'        => 'Hebrew First Name', 
    'u_last_he'         => 'Hebrew Last Name', 
    'u_dob'             => 'DOB',
    'u_dob_he'          => 'Hebrew DOB',
    'u_user_registered' => 'Registration Date', 
    'c_class_grade'     => 'Class Grade', 
    'c_class_sub'       => 'Class Sub',
    's_school_name'     => 'School',
    'r_rank_name'       => 'Rank',
    'a_first'           => 'Parent First',
    'a_last'            => 'Parent Last',
    'a_admin_address1'  => 'Parent Address',
    'a_admin_address2'  => 'Parent Address 2',
    'a_admin_city'      => 'Parent City',
    'a_admin_state'     => 'Parent State',
    'a_admin_postal'    => 'Parent Zip',
    'a_admin_country'   => 'Parent Country',
    'a_admin_email'     => 'Parent Email',
    'a_admin_phone_work'=> 'Parent Work Phone',
    'a_admin_phone_home'=> 'Parent Home Phone',
    'a_admin_phone_mobile' => 'Parent Cell',
    'store_points'      => 'Store Miles',
    'total_points'      => 'Total Miles Earned', 
    'total_this_yr'     => 'Total Miles Earned this Year', 
    'user_id'           => 'Student ID'
];
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Reports</title>
		<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.css"/> -->
        <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" />
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
        <h1>Personalized Report</h1>
        <table id="table" class="table table-striped table-condensed">
            <thead>
                <tr>
                    <?php
                    for ( $i = 0; $i < 1; $i++ ) { // all rows have same keys / fields so only need to get it from the first one
                        echo "<tr>";	
                        foreach ( $result[$i] as $field => $value ) {
                            echo "<th>" . $fields[$field] . "</th>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach ( $result as $info ) {
                        echo "<tr>";	
                        foreach ( $info as $value ) {
                            echo "<td>" . $value . "</td>";
                        }
                        echo "</tr>";
                    }
                ?>
            </tbody>
        </table>
    </body>
    
    <!-- <script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.js"></script> -->
    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script>
		$('#table').DataTable();
    </script>
</html>