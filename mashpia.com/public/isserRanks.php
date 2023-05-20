<?php
$admin_auth = array('school'); 
require('header.php'); 

if (isset($_GET['prev']) && $_GET['prev'] == 1) {
	$prev = true;
} else {
	$prev = false;
}

require_once 'class.rankReport.php';
$rr = new RankReport($prev);
$rr->setRankNames();
$rankNames = $rr->getRankNames();
$heDatesRanks = $rr->getHeReportDates();

$rankOrds = [];
$sql = "select * from ranks order by rank_ord";
$result = mysql_query( $sql ) or die( mysql_error() );
while ( $row = mysql_fetch_assoc( $result ) ) {
    $rankOrds[$row['rank_name']] = $row['rank_ord'];
}

function getRank($user) {
	$name = explode(" ", $user);  
	$sql = "select rank_name 
			from ranks r 
			join rank_marks rm 
			using (rank_ord) 
			join users u 
			using (user_id) 
			where u.last = \"$name[1]\"   
			and u.first = \"$name[0]\"  
			order by rm.rank_ord desc 
			limit 0,1";
	$result = mysql_query( $sql ) or die( mysql_error() );
	$row = mysql_fetch_assoc( $result );
	return $row['rank_name'];
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

    <HEAD>
        <TITLE>Medals Ranks Ceremony</TITLE>
        <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style type="text/css">
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
            .medals { 
                margin-left: 30px;
            }
        </style>     
    </HEAD>   
    
    <BODY>
        <?php include('admin_header.php'); ?>   
        <? 
        $super = false;
        $schools = array();
        //if it's a super user, loop through all schools
        //otherwise show school associated with account
        if ( $admin->auth == 'super' ) {
            $super = true;
        }
        require_once 'class.adminSchools.php';      
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], false );
        $schools = $as->getSchools();
        ?>
        <div class='no-print'>
            <h1>Isser's Ranks Summary Sheet</h1>
            
            <p>
            	<? if ($prev) : ?>
            	<a href="isserRanks.php">Show next shipment</a>
            	<? else : ?>
            	<a href="isserRanks.php?prev=1">Show previous shipment</a>
            	<? endif; ?>
            </p>

            <div align='center'>
                <input type='button' name='print' value='Print' onclick="window.print()" />
            </div>
        </div>
        <div id='main'>          
            <?
            $ranktotals = array();
            foreach ( $schools as $school_id => $school_name ) {
                if (in_array($school_id, [180, 585, 588, 612, 709])) continue;
                $rr->setSchoolId( $school_id );                
                $rr->setRanks('byRank');
                $ranks = $rr->getRanks();
				$userInfo = $rr->getUserInfo();
				$heNames = $rr->getUserHeNames();
				//echo "<pre>"; print_r($ranks); echo "</pre>";
    
                foreach ( $ranks as $school => $line ) {
                    if ( $school != $school_name ) continue;
					echo "<h2>" . $school_name . "</h2>";
                    echo "Ranks earned in " . $school . " from " . $heDatesRanks['start_he'] . " until " . $heDatesRanks['end_he'] . ". <br />";
                    echo "<div id='$school_id'>
                            <button class='cardBtnAll'>Toggle All Cards</button>
                            <button class='bookBtnAll''>Toggle All Books</button>
                        </div>";
					$totals = [];

                    foreach ( $line as $rank => $info ) {
                        foreach ( $rankNames as $rankName => $needed ) {
                        	//echo $rankName . "<br />";
                            if ( $rankName == $rank ) {
                            	echo "<h2>" . $rank . "</h2><table>";
                                echo "<tr><th>Card Sent</th><th>Book Sent</th><th>Serial #</th><th>Name</th></tr>";
                                foreach ( $info as $teacher => $class ) {
                                    foreach ( $class as $grade => $info ) {
										$add = count($info);
                                    	if (isset($ranktotals[$rank]))
											$ranktotals[$rank] += $add;
										else 
											$ranktotals[$rank] = $add;
										if (isset($totals[$rank]))
											$totals[$rank] += $add;
										else
											$totals[$rank] = $add;
											
                                    	foreach ($info as $student) {
                                            $user_id = $student['user_id'];
                                    		$sql = "select user_serial from users where user_id = " . $user_id;
											$result = mysql_query($sql);
											$row = mysql_fetch_assoc($result);
                                            $id = $user_id . '|' . $rankOrds[$rank];
                                            $cardChecked = $student['date_card_shipped'];
                                            $bookChecked = $student['date_book_shipped'];
											echo "<tr><td><input type='checkbox' class='rank_card' id='$id' ";
                                            if ($cardChecked) echo "checked";
                                            echo "></td><td><input type='checkbox' class='rank_book' id='$id' ";
                                            if ($bookChecked) echo "checked";
                                            echo "></td><td>" . $row['user_serial'] . "</td><td>";
											if (!empty($heNames[$user_id]))
												echo $heNames[$user_id] . ' - ';
                                            echo $userInfo[$user_id];
                                            echo " (" . $grade . ")";
											echo "</td></tr>";
										}
                                    }	
                                }
                                echo "<tr><td><button class='cardBtn'>Toggle</button></td><td><button class='bookBtn'>Toggle</button></td></tr>";
								echo "</table><br />";
                            }
                        } 
                    }
					if ($super) {
						?>
						<h2><?=$school?> Totals</h2>
						<table>
							<tr>
								<th>Rank</th>
								<th>Total</th>
							</tr>
							<?
							$gtotal = 0;
							foreach ($totals as $rank => $total) {
								$gtotal += $total;
								echo "<tr><td>" . $rank . "</td><td>" . $total . "</td></tr>";
							}
							echo "<tr><th></th><th>" . $gtotal . "</th></tr>";
							?>
						</table>
						<?
					}
                    echo "<br /><br />";
					echo "<div class='page-break'></div>"; 
                }
            }
            ?>
			<h2><?=$super ? 'Grand ' : ''?>Totals</h2>
            <table>
            	<tr>
            		<th>Rank</th>
            		<th>Total</th>
            	</tr>
            	<?
				$gtotal = 0;
            	foreach ($ranktotals as $rank => $total) {
					$gtotal += $total;
            		echo "<tr><td>" . $rank . "</td><td>" . $total . "</td></tr>";
            	}
				echo "<tr><th></th><th>" . $gtotal . "</th></tr>";
            	?>
            </table>
        </div>    
    </BODY>
    <script>
        function update(elem, type) {
          let id = $(elem).attr('id');
          let info = id.split('|');
          let user_id = info[0];
          let rank = info[1];
          let checked = $(elem).is(":checked") ? 1 : 0;
          let url = 'edit_functions.php?function_name=update_ranks&parameters=' + user_id + '_' + rank + '_' + checked + '_' + type
          $.getJSON(url, function(success) {
            if (success = 0) {
              alert('Error updating rank ' + type)
            }
          })
        }
        $( function () {
          $(".rank_card").click( function () {
            update(this, 'card')
          })
          $(".rank_book").click( function () {
            update(this, 'book')
          })

          $(".cardBtnAll").click( function() {
            $(this).next('table').find(".rank_card").each( function() {
              $(this).trigger('click')
            })
          })

          $(".bookBtnAll").click( function() {
            $(this).next('table').find(".rank_book").each( function() {
              $(this).trigger('click')
            })
          })

          $(".cardBtn").click( function() {
            $(this).closest('table').find('.rank_card').each( function() {
              $(this).trigger('click')
            })
          })

          $(".bookBtn").click( function() {
            $(this).closest('table').find('.rank_book').each( function() {
              $(this).trigger('click')
            })
          })
        })
    </script>
</HTML>
 