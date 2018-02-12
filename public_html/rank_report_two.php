<?php
ini_set('display_errors', 1);
$admin_auth = array('school'); 
require('header.php');

function getMedals( $user, $rank ) {
	//find out total number of medals earned
	$sql = "select count(*) as total from medal_marks where user_id = " . $user;
	$result = mysql_query($sql);
	$row = mysql_fetch_assoc($result);
	$numMedals = $row['total'];
	
	//find out number of medals earned above rank
	$info = array();
	$sql = "select rank_ord, medals_required from ranks where rank_ord in (" . $rank . ',' . ($rank+1) . ") order by rank_ord";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_assoc($result)) {
		$info[$row['rank_ord']] = $row['medals_required'];
	}
		
	return array(
		//'total'	=>	$info[$rank+1] - $info[$rank],
		//'done'	=>	$numMedals - $info[$rank]
		'needed'	=>	$info[$rank+1] - $numMedals
	);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Rank Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
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
			    background-color: red;
			}
			.image {
				width: 50px;
				height: 50px;
				border-radius: 50%;
			}
        </style> 
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>       
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1 class="no-print">Rank Report</h1>
		
        <? 
        require_once 'class.adminSchools.php';      
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();        
        $users = array();
        $grandTotals = array();
        
        //get rank names
        $rankNames = array();
        $sql = "select rank_ord, rank_name from ranks";
        $result = mysql_query( $sql );
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $rankNames[$row['rank_ord']] = $row['rank_name'];
        }
        
        //default sort method
        $orderBy = " order by s.school_name, c.class_grade, c.class_sub, u.gender, u.last, u.first, rm.rank_ord";
        
		$userIDs = array();
        foreach ( $schools as $id => $school ) {        
            $sql = "select s.school_name, u.user_id, u.last, u.first, u.gender, c.class_grade, c.class_sub, rm.rank_ord 
                    from rank_marks rm 
                    join users u using ( user_id ) 
                    join classes c on (c.class_id = u.class_id) 
                    join schools s on (s.school_id = u.school_id) 
                    where u.user_registered > 0
					and rm.rank_ord >= 8 
                    and u.school_id = $id $orderBy";
            //echo $sql;
            
            $result = mysql_query( $sql ) or die( mysql_error() );
            while ( $row = mysql_fetch_assoc( $result ) ) {
                $grade = $row['class_grade'] . ( empty( $row['class_sub'] ) ? '' : '-' . $row['class_sub'] ); 
                $userName = $row['first'] . ' ' . $row['last'];
                $users[$row['school_name']][$grade][$row['gender']][$userName] = $row['rank_ord'];
				$userIDs[$row['school_name']][$grade][$row['gender']][$userName] = $row['user_id'];
            }                   
        }
		
        //sort by rank
		$temp = array(); 
		foreach ( $users as $school => $info ) {
			foreach ( $info as $grade => $more ) {
				foreach ( $more as $gender => $user ) {
					foreach ( $user as $name => $rank ) {
						$temp[$school][$rank][$grade][$gender][$name] = 1;
					}
				}
			}
			ksort( $temp[$school] );
		}
		unset( $users );
		$users = $temp;
		//echo "<pre>"; print_r( $users ); echo "</pre>";
        
		/*
		$totals = array(); 
        foreach( $users as $school => $info ) {             
			foreach ( $info as $rank => $other ) {
				foreach ( $other as $grade => $more ) {
					foreach ( $more as $gender => $name ) {					
						if ( isset( $totals[$school][$rank][$grade][$gender] ) ) 
							$totals[$school][$rank][$grade][$gender]++;
						else 
							$totals[$school][$rank][$grade][$gender] = 1;
						/*
						if ( isset( $grandTotals[$grade][$gender][$rank] ) ) 
							$grandTotals[$grade][$gender][$rank]++;
						else 
							$grandTotals[$grade][$gender][$rank] = 1;
						*/
						/*
					}
				}
			}
		}
		*/	
		echo "<table>";
		echo "<tr><User ID</th><th>School</th><th>Student</th><th>Rank</th><th>Grade</th><th>Gender</th><th>Medals Left to General</th></tr>";
		foreach ( $users as $school => $info ) {
			foreach ( $info as $rank => $other ) {
				foreach ( $other as $grade => $more ) {
					foreach ( $more as $gender => $other ) {
						foreach ( $other as $name => $total ) {
							echo "<tr><td>" . $userIDs[$school][$grade][$gender][$name] . "</td><td>" . $school . "</td><td>" . $name . "</td><td>" . $rankNames[$rank] .
								"</td><td>" . $grade . "</td><td>" . $gender . "</td>";
							if ($rank == 8) {
								$medals = getMedals( $userIDs[$school][$grade][$gender][$name], $rank );
								echo "<td>" . $medals['needed'] . "</td>";
							} else {
								echo "<td></td>";
							}
							echo "</tr>";
						}
					}
				}
			}
		}
		echo "</table>";
            //echo "<div class='page-break'></div>";
			//echo "<pre>"; print_r( $totals ); echo "</pre>";
        /*
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
        */
        ?>
    </body>
</html>