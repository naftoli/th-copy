<?
ini_set('display_errors', 1);
ini_set('memory_limit', '128M');

$admin_auth = array('school'); 
require('header.php');

if (isset($_POST['submit'])) {
	$previous = $_POST['previous'];
	require_once 'class.medalReport.php';
	$m = new MedalReport($previous);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Medals Shipping Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script type="text/javascript">
            $( function() {
                $(".shipped").click( function() {
                    var user = $(this).parent().parent().find('span.userIDs').text();
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                            id : user, 
                            checked : checked, 
                            field : 'date_shipped',
                            table : 'medal_marks', 
                            key : 'user_id' 
                     }, function(data) {
                         if ((data == 1) && checked) {
                            var d = new Date();
                            var n = d.toDateString();
                            $(e).after('<span>' + n + '</span>');
                        } else if ((data == 1) && !checked) {
                            $(e).next('span').remove();
                        }
                     });
                });
                
                $(".received").click( function() {
                    var user = $(this).parent().parent().find('span.userIDs').text();
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                            id : user, 
                            checked : checked, 
                            field : 'date_received',
                            table : 'medal_marks', 
                            key : 'user_id' 
                     }, function(data) {
                         if ((data == 1) && checked) {
                            var d = new Date();
                            var n = d.toDateString();
                            $(e).after('<span>' + n + '</span>');
                        } else if ((data == 1) && !checked) {
                            $(e).next('span').remove();
                        }
                     });
                });
                
                $(".cshippedBtn").click( function() {
                    $(this).parent().parent().parent().parent().find('.shipped').each( function() {
                        $(this).attr('checked', !$(this).is(":checked")); //needed b/c trigger function will call ajax before checking the button
                        $(this).trigger('click');
                        $(this).attr('checked', !$(this).is(":checked")); //needed b/c trigger function will reverse checked status
                    });
                });
                
                $(".creceivedBtn").click( function() {
                    $(this).parent().parent().parent().parent().find('.received').each( function() {
                        $(this).attr('checked', !$(this).is(":checked")); //needed b/c trigger function will call ajax before checking the button
                        $(this).trigger('click');
                        $(this).attr('checked', !$(this).is(":checked")); //needed b/c trigger function will reverse checked status
                    });
                });
            });            
        </script>
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
            #main {
                font-size: 14px;
            }
            @media screen {
                .no-print {
                    display: block;
                }
                .print-only {
                    display: none;
                }
            }
            @media print {
                .no-print {
                    display: none;
                }
                .print-only {
                    display: block;
                }
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Medals Shipping Report</h1>
        
        <? 
        if (isset($m)) {
	        require_once 'class.adminSchools.php';      
	        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
	        $schools = $as->getSchools();
	        
	        require_once 'class.schoolsUsers.php';         
	        $schoolsUsers = array();
	        $totals = array();
	        
	        foreach ($schools as $id => $school) {
	            $s = new SchoolsUsers($id);
	            $schoolsUsers[$id] = $s->getUsers();
	        }
	        ?>
	        <div class='no-print'>            
	            <div align='center'>
	                <input type='button' name='print' value='Print' onclick="window.print()" />
	            </div>
	        </div>
	        <div id='main'>          
	            <?                     
	            //for super setup totals arrays
	            $grandTotal = 0;
	            $grandTotalByMedal = array();
	            foreach ( $schools as $school_id => $school_name ) {
	                echo "<h2>" . $school_name . "</h2>";
	                 
	                //set up medals array
	                $m->setSchoolId($school_id);
	                $m->setMedalDetails();
	                $details = $m->getMedalDetails();
	                $m->setMedalSummary();
	                $summary = $m->getMedalSummary();
	                $totals = $m->getMedalTotals();
	                $medalsTotal = $m->getMedalsTotal();
	                $medalsInfo = $m->getMedalsInfo();
	                $userInfo = $m->getUserInfo();
	                $medalOrds = $m->getMedalOrds();
	                $subjects = $m->getSubjects();
	                
//	                echo "<pre>";
	                //print_r($details);
	                //print_r($summary);
	                //print_r($ranks);
//	                echo "</pre>";

	                if (isset($details[$school_name])) {
						?>
						<table>
							<tr>
								<th>Teacher</th>
								<th>Grade</th>
								<th>Student</th>
								<th>Subject</th>
								<th>Medal</th>
								<th>Earned</th>
								<th>Shipped</th>
								<th>Received</th>
							</tr>
						<?php
	                    foreach ($details[$school_name] as $teacher => $class) {
	                        foreach ($class as $grade => $info) {
	                            foreach ($info as $user => $medals) {
	                                foreach ($medals as $subject => $more) {
										foreach ($more as $medal) {
											$id = $userInfo[$user];
											$subjectID = $subjects[$subject];
											$ord = $medalOrds[$medal];
											echo "<tr><td>" . $teacher . "</td><td>" . $grade . "</td><td>
												<span class='userIDs' style='display:none'>" . 
												$id . ":" . $subjectID . ":" . $ord . "</span>" . 
												$user . "</td><td>" . $subject . "</td><td>" . $medal . "</td><td>" . 
												jdtogregorian($medalsInfo[$user]['earned']) . "</td><td align='center'>";
											
											if (!is_null($medalsInfo[$user]['shipped'])) {
												echo "<input type='checkbox' class='shipped' checked='checked' />";
												echo "<span>" . $medalsInfo[$user]['shipped'] . "</span>";
											} else {
												echo "<input type='checkbox' class='shipped' />";
											}
											echo "</td><td align='center'>";
											
											
											if (!is_null($medalsInfo[$user]['received'])) {
												echo "<input type='checkbox' class='received' checked='checked' />";
												echo "<span>" . $medalsInfo[$user]['received'] . "</span>";
											} else {
												echo "<input type='checkbox' class='received' />";
											}
											echo "</td><td align='center'>";
										}
	                                }
	                            }
	                        }
	                    }
						echo "<tr><td colspan='6'></td><td><input type='button' class='shippedBtn' value='toggle' /></td>";
						echo "<td><input type='button' class='receivedBtn' value='toggle' /></td></tr>";
	                    echo "</table>"; 
	                    echo "<br /><br />"; 
	                }
	                
	                /*
	                foreach ($details as $school => $line) {
	                    if ($school != $school_name) continue;
	                    ?>
	                    <table>
	                        <tr>
	                            <th>Commander</th>
	                            <th>Platoon</th>
	                            <th>Soldier</th>
	                            <th>Medals</th>
	                        </tr>
	
	                    <? 
	                    foreach ($line as $teacher => $class) {
	                        foreach ($class as $grade => $info) {
	                            foreach ($info as $user => $medals) {
	                                echo "<tr><td>" . $teacher . "</td><td>" . $grade . "</td>";
	                                echo "<td>" . getRank($user) . " " . $user . "</td><td>";
	                                foreach ($medals as $subject => $medal) {
	                                    echo $subject . "-" . $medal . "<br />";
	                                }
	                                echo "</td></tr>";
	                            }
	                        }
	                    }
	                    echo "</table>";
	                    echo "<br /><br />";
	                }
	                echo "<br />";
	                echo "<div class='page-break'></div>";
	                
	                foreach ($summary as $school => $medals) {
	                    if ($school != $school_name) continue;   
	                    $grandTotal += $medalsTotal[$school];  
	                    echo "Total of " . $medalsTotal[$school] . " medals earned in " . $school . " from " . $heDates['start_he'] . " until " . $heDates['end_he'] . ". <br />";
	                    echo "<br />";
	                    foreach ($medals as $subject => $info) {
	                        echo "<div class='students'>" . $subject . " - " . $totals[$school][$subject] . "</div>";
	                        echo "<div class='medals'>";
	                        foreach ($info as $medal => $total) {
	                            echo $medal . "-" . $total . "<br />";
	                            if (!isset($grandTotalByMedal[$subject][$medal])) 
	                                $grandTotalByMedal[$subject][$medal] = $total;
	                            else 
	                                $grandTotalByMedal[$subject][$medal] += $total;
	                        }
	                        echo "</div>";
	                    }
	                }
	    
	                echo "<br /><br />";
	                echo "<div class='page-break'></div>";
	                foreach ($ranks as $school => $line) {
	                    if ($school != $school_name) continue;
	                    echo "Ranks earned in " . $school . " from " . $heDates['start_he'] . " until " . $heDates['end_he'] . ". <br />"; 
	                    ?>
	                    <br />
	                    Before you give out the Rank books, please tell the chayolim we are now going to honor the children who have gone up in rank:<br />
	                    <?
	                    foreach ($line as $rank => $info) {
	                        foreach ($rankNames as $rankName => $needed) {
	                            if ($rankName == $rank) {
	                                echo "<br />The " . $rank . "s who have earned " . $needed . " medals:<br />"; 
	                                foreach ($info as $teacher => $class) {
	                                    foreach ($class as $grade => $student) {
	                                        echo "<div class='students'>" . $student . "</div>";
	                                    }
	                                }
	                            }
	                        } 
	                    }
	                    echo "<br />"; 
	                }
	                echo "<div class='page-break'></div>";
	            }
	            if (count($schools) > 1) {
	                echo "<br />";
	                echo "Total medals awarded: " . $grandTotal . "<br />";
	                echo "<br />Total medals awarded by Medal:<br /><br />";
	                foreach ($grandTotalByMedal as $subject => $medals) {
	                    foreach ( $medals as $medal => $total ) { 
	                        echo $subject . " - " . $medal . " : " . $total . "<br />";
	                    }
	                }
	            } */
	        }        
	        ?>
	        </div>
	    <? 
		} else {
			?>
			<form action="medals_shipping.php" method="post">
				<input type="radio" name="previous" value="0" /> Show current report<br />
				<input type="radio" name="previous" value="1" /> Show previous report<br />
				<input type="submit" name="submit" value="submit" />
			</form>
			<?
		} 
		?>
    </body>
</html>