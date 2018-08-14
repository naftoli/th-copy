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
$engine->createQry();
if ( $engine->runQry() ) {
    $result = $engine->getResult();
    //echo "<pre>"; print_r( $result ); echo "</pre>";
} else {
    echo $engine->getError();
    exit;
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
        <table id="table" class="table table-striped table-condensed">
            <thead>
                <tr>
                    <?php
                    for ( $i = 0; $i < 1; $i++ ) { // all rows have same keys / fields so only need to get it from the first one
                        echo "<tr>";	
                        foreach ( $result[$i] as $field => $value ) {
                            echo "<th>" . $field . "</th>";
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
    
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.js"></script>
	<script>
		$('#table').DataTable({
			paging : false
		});
    </script>
</html>