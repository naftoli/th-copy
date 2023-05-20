<?php
ini_set('display_errors', 1);
ini_set('error_reporting', E_ALL);
ini_set('memory_limit', '256M');

$admin_auth = array('school'); 
require('header.php');

require_once 'class.medalReport.php';
$m = new MedalReport;
$m->setDateToAll();
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
                
                $(".shippedBtn").click( function() {
                    $(this).parent().parent().parent().parent().find('.shipped').each( function() {
                        $(this).attr('checked', !$(this).is(":checked")); //needed b/c trigger function will call ajax before checking the button
                        $(this).trigger('click');
                        $(this).attr('checked', !$(this).is(":checked")); //needed b/c trigger function will reverse checked status
                    });
                });
                
                $(".receivedBtn").click( function() {
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
        <?php include('admin_header.php'); ?>
        <h1>Medals Shipping Report</h1>
        
        <?php
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
		<?php
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
				echo "<tr><td colspan='6'></td><td><input type='button' class='shippedBtn' value='toggle all' /></td>";
				echo "<td><input type='button' class='receivedBtn' value='toggle all' /></td></tr>";
				echo "</table>";
				echo "<br /><br />";
			}
		}
		?>
		</div>
    </body>
</html>