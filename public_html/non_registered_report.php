<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Not Yet Registered Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
                vertical-align: top;
            }
            .page-break {
                page-break-after: always;
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Not Yet Registered Report</h1>
        <a class="button" id='export_csv' style='display: none;'>Export To CSV</a>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';         
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $schoolsUsers = array();
        $totals = array();
        
        foreach ( $schools as $id => $school ) {
            $s = new SchoolsUsers( $id );
            $schoolsUsers[$id] = $s->getUsers(false, true);
        }
        
        /*
        echo "<pre>";
        print_r( $schoolsUsers );
        echo "</pre>";
         * 
         */
        
        foreach ( $schoolsUsers as $school => $users ) {
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table data-school_name='".$schools[$school]."' class='exported'>";
            echo "<tr><th>Grade</th><th>Student</th><th>Serial #</th><th>Start Date</th>
            	<th>DOB</th><th>Email</th><th>Phone Numbers</th></tr>";
            foreach ( $users as $user ) {
            	//get phone numbers, email from parent account
            	$qry = "select admin_email, admin_phone_home, admin_phone_work, admin_phone_mobile 
            			from admins join admin_auths aa using (admin_id) 
            			where aa.id = " . $user['user_id'] . " 
            			and aa.auth = 'user'";
				$res = mysql_query( $qry );
				if ( mysql_num_rows( $res ) > 0 ) {
					$admin = mysql_fetch_assoc( $res );
				} else {
					$admin = null;
				}
								
                $grade = $user['class_grade'] . ( empty( $user['class_sub']) ? '' : "-" . $user['class_sub'] );
                echo "<tr><td>" . $grade . "</td><td>" . $user['first'] . " " . $user['last'] . 
                    "</td><td>" . $user['user_serial'] . "</td><td>" . jdtogregorian( $user['user_start_date'] ) . 
					"</td><td>" . $user['dob'] . "</td><td>";
				if ( is_null( $admin ) ) {
                    echo "</td><td>" . $user['email'];
                } else {
                	if ( !empty( $admin['email'] ) ) {
                		echo $admin['admin_email'];
                	} else {
                		echo $user['email'];
                	}
                	echo "</td><td>";
					if ( !empty( $admin['admin_phone_home'] ) ) {
						echo $admin['admin_phone_home'] . ", <br />";
					}
					if ( !empty( $admin['admin_phone_work'] ) ) {
						echo $admin['admin_phone_work'] . ", <br />";
					}
					if ( !empty( $admin['admin_phone_mobile'] ) ) {
						echo $admin['admin_phone_mobile'];
					}
                }     
                echo "</td></tr>"; 
                if ( isset( $totals[$schools[$school]][$grade] ) ) 
                    $totals[$schools[$school]][$grade]++;
                else 
                    $totals[$schools[$school]][$grade] = 1; 
            }
            echo "</table><br /><div class='page-break'></div>";
        }
        
        echo "<div class='page-break'></div>";
        echo "<h2>Totals</h2>";
        foreach ( $totals as $school => $info ) {
        	$grandTotal = 0; 
            echo "Total number of children not yet registered in " . $school . "<br />";
            echo "<table>";
            echo "<tr><th>Grade</th><th>Total</th></tr>";
            foreach ( $info as $grade => $total ) {
                echo "<tr><td>" . $grade . "</td><td>" . $total . "</td></tr>";
                $grandTotal += $total;
            }
			echo "<tr><td><b>Grand Total</b></td><td><b>" . $grandTotal . "</b></td></tr>";
            echo "</table>";
            echo "<br /><br />";
            echo "<div class='page-break'></div>";
        }
        ?>
        <script>
            $(document).ready( function() {
                $('#export_csv').show().click( export_csv );
            })
            function export_csv() {
                alert("Please note that is feature is in BETA and may not work correctly in all browsers/readers. "
                    +"Please note that it does not work with IE or Microsoft Edge, "
                    +"and has been only been tested with Microsoft Excel 2016."
                );
                var csvContent = "Grade,Student,Serial #,Start Date,DOB,Email,Phone Numbers,Base\n"; // the baisc csv file
                var universalBOM = "\uFEFF";
                
                $.each( $('table.exported'), function( index, table ) {
                    var school_name = table.dataset.school_name;
                    table = $(table);
                    $.each(table.find("tr:not(:first-child)"), function(index, tr) {
                        var row = [];
                        $.each($(tr).find("td"), function(index, td) {
                            row.push($(td).text());
                        });
                        row.push( school_name );
                        csvContent += row.join(',') + '\n';
                    });
                });
                
                var hiddenElement = document.createElement('a');
                hiddenElement.href = "data:text/csv;charset=utf-8," + encodeURIComponent(universalBOM+csvContent); // set the data
                hiddenElement.target = '_blank'; // in a new tab
                hiddenElement.download = 'non_registered.csv'; // with this file_name
                hiddenElement.click(); // and click it
            }
        </script>
    </body>
</html>