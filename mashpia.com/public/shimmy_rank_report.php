<?php
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Rank Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
			body {
				-webkit-print-color-adjust: exact;
			}
            p {
                font-size: 12px;
            }
            table {
                font-size: 11px;
            }
            th, td {
                padding: 3px 10px;
            }
            .missionSelection {
                width: 30%;
                float: left;
                line-height: 1.5;
                margin-top: 10px;
            }
            .classSelection {
                width: 25%;
                float: left;
                line-height: 1.5;
                margin-top: 10px;
            }
            fieldset {
                border: 1px solid white;
                padding: 10px;
                padding-top: 0px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
            }
            legend {
                margin-left: 20px;
                padding: 5px;
                color: purple;
            }
            .page-break {
                page-break-after: always;
            }
            @media print {
                .no-print {
                    display: none;
                }
            }
            .totals {
                border-top: 1px dashed purple;
                border-bottom: 1px dashed purple;
            }
            .classes {
                margin: auto;
            }
            .sort {
                font-size: 14px;
            }
            .sort a {
                text-decoration: underline;
            }
            .sort .sortBy {
                color: purple;
                font-weight: bold;
            }
			.circle {
				border-radius: 50%;
                display: inline-block;
                width: 20px;
			    height: 20px;
			    border: 1px solid grey;
            }
            .fill {
			    /*background-color: red !important;*/
				box-shadow: inset 0 0 0 1000px red;
			}
			.image {
				width: 50px;
				height: 50px;
				border-radius: 50%;
			}
        </style> 
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
		<script>
			function print_report() {
				//alert("Please note that Microsoft Edge does NOT support printing medals at the moment. Please use Chrome or Firefox if you can.\n\n"+
				//	  "If you are using Internet Explorer please go to 'Page Setup' and enable 'Print Background Colors and Images'");
                window.print();
            }
		</script>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="no-print">Rank Report</h1>
        
        <div align='center' class='no-print'>
            <input type='button' value='Print' onclick='print_report()' />
        </div>
        <? 
        require_once 'class.adminSchools.php';      
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();        
        $users = array();
		$medals = array();
		$thumbs = array();
		$images = array();
        $grandTotals = array();
        $sum = 0;
        
        //get rank names
        $rankNames = array();
        $sql = "select rank_ord, rank_name from ranks";
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $rankNames[$row['rank_ord']] = $row['rank_name'];
        }
        
        //default sort method
        $orderBy = " order by s.school_name, c.class_grade, c.class_sub, u.last, u.first, rm.rank_ord";
        foreach ( $schools as $id => $school ) {        
            $sql = "select s.school_name, u.user_id, u.last, u.first, c.class_grade, c.class_sub, rm.rank_ord  
                    from rank_marks rm 
                    join users u using ( user_id ) 
                    join classes c on (c.class_id = u.class_id) 
                    join schools s on (s.school_id = u.school_id) 
                    where u.user_registered > 0 
                    and u.school_id = $id $orderBy";
            //echo $sql;
            
            $result = mysql_query( $sql );
            while ( $row = mysql_fetch_assoc( $result ) ) {
                $grade = $row['class_grade'] . ( empty( $row['class_sub'] ) ? '' : '-' . $row['class_sub'] ); 
                $userName = $row['first'] . ' ' . $row['last'];
                $users[$row['school_name']][$grade][$userName] = $row['rank_ord'];
            }                   
        }
         
        //display info
        foreach( $users as $school => $info ) {
            $totals = array();              
            foreach ( $info as $grade => $user ) {
                echo "<h2>" . $school . ' - ' . $grade . "</h2>";
                echo "<table>";
                echo "<tr><th>Student</th><th>Rank</th></tr>";
                foreach ( $user as $name => $rank ) {
                    echo "<tr><td>" . $name . "</td><td>" . $rankNames[$rank] . "</td></tr>";
                    if ( isset( $totals[$rank] ) ) 
                        $totals[$rank]++;
                    else 
                        $totals[$rank] = 1;
                    if ( isset( $grandTotals[$rank] ) ) 
                        $grandTotals[$rank]++;
                    else 
                        $grandTotals[$rank] = 1;
                    $sum++;
                }
                echo "</table>";
                echo "<div class='page-break'></div>";
            }
            
            ksort( $totals );
            echo "<h2>" . $school . " Totals</h2>";
            echo "<table>";
            echo "<tr><th>Rank</th><th>Total</th></tr>";
            foreach ( $totals as $rank => $total ) {
                echo "<tr><td>" . $rankNames[$rank] . "</td><td>" . $total . "</td></tr>";
            }
            echo "</table>";
            echo "<div class='page-break'></div>";
        }
        
        if ( $admin->auth == 'super' ) {
            ksort( $grandTotals ); 
            echo "<h2>Grand Totals</h2>";
            echo "<table>";
            echo "<tr><th>Rank</th><th>Total</th><tr>"; 
            foreach ( $grandTotals as $rank => $total ) {
                echo "<tr><td>" . $rankNames[$rank] . "</td><td>" . $total . "</td></tr>";
            }
            echo "</table>";
        }
        //echo "Sum: " . $sum;
        ?>
    </body>
</html>