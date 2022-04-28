<?php
$admin_auth = array('school'); 
require('header.php'); 

$previous = false;
if ( isset($_GET['go']) && $_GET['go'] == 'back' ) {
    $previous = true; 
    //$myshliach->setPreviousDates();
}

require 'class.myShliachShipLabels.php';
$myshliach = new MyShliachShipLabels($previous, 269);

//$myshliach->overrideDates( 2456960, 2457037 );
$parents = $myshliach->getParents();
$medals = $myshliach->getMedals();
$ranks = $myshliach->getRanks();
$admins = $myshliach->getAdmins();
$userInfo = $myshliach->getUserInfo();
$heDates = $myshliach->getHeReportDates();
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type="text/css">
            .label {
                width: 2.15in;
                height: 1in;
                font-size: 12px;
                padding: 5px;
                float: left;
            }
            .space {
                width: .35in;
                height: 1in;
                float: left;
                padding: 5px 20px;
            }
            .page-break {
                clear: both;
                page-break-after: always;
            }
            .medal {
                width: 1in;
                float: left;
                font-size: 9px;
            }
            .name {
                width: 2.15in;
                font-size: 14px;
            }
            .topSpace {
                height: 0.5in;
                width: 7in;
            }
            .instructions {
                width: 50%;
            }
            @media screen {
                #report_div {
                    display: none;
                }
                .no-print {
                    display: block;
                }
            }
            @media print {
                #report_div {
                    display: block;
                }
                .no-print {
                    display: none;
                }
            }
        </style>
        <script type="text/javascript">
            function check() {
                if ( confirm( "Have you made sure to set your printer margins properly?\nIf not, please click 'cancel', set your margins, and then click 'print' again." ) ) 
                    window.print();
            }
        </script>
	</head>

	<body>
	<? include('admin_header.php'); ?>
	<div class="no-print">
        <h1>Medals Labels Report</h1>        
            <div>
                Current Report is calculated from <?=$heDates['start_he']?> up to <?=$heDates['end_he']?>.<br />
                <? if ( $previous ) { ?> 
                Click <a href='anashShipLabels.php'>here</a> to show next report dates.<br /><br />
                <? } else { ?> 
                Click <a href='anashShipLabels.php?go=back'>here</a> to show previous report dates.<br /><br />
                <? } ?>
            </div>
            <div class='instructions'>
                <b>Printing Instructions</b><br />
                Please set your printer margins to the following:<br />
                0.5 Top<br />
                0.3 Left<br />
                0.0 Right and Bottom<br /><br />   
                <div align='center'>
                    <input type='button' name='print' value='Print' onclick="check()" />    
                </div>
            </div>
        </div>
        
        <div id="report_div" name="report_div">
            <div class='topSpace'></div>
            <?
            $cols = 1; //counter for columns
            $rows = 1; //counter for rows
            
			foreach ($parents as $admin => $children) {
					
				$parent = $admins[$admin];
				$name = $parent['first'] . ' ' . $parent['last'];
				$address = $parent['admin_address1'] . "<br />" . (empty($admin['admin_address2']) ? '' : 
						$admin['admin_address2'] . "<br />") . $parent['admin_city'] . ', ' . $parent['admin_state'] . 
					" " . $parent['admin_postal'] . "<br />" . $parent['admin_country'];
				
				echo "<div class='label'>";
				echo "<span class='name'>";
				echo "<b>" . $name . " (AK)</b><br />" . $address . "</span></div>";
				checkForBreak();
				
				foreach ($children as $child) {
					if (isset($medals[$child]) || isset($ranks[$child])) {
						
						if (isset($medals[$child])) {
							$numMedals = 1;
							echo "<div class='label'>";
							echo "<span class='name'>";
							echo $userInfo[$child] . "</span><br />";
							foreach ($medals[$child] as $medal) {
								if ($numMedals > 8) {
									echo "</div>";
									checkForBreak();
									echo "<div class='label'>";
									echo "<span class='name'>";
									echo $userInfo[$child] . "</span><br />";
									$numMedals = 1;
								}
								if ($medal['subject_name'] == 'שבת מברכים תהילים') $medal['subject_name'] = 'תהילים';
								echo "<span class='medal'>" . $medal['subject_name'] . "-" . $medal['medal_name'] . "</span>";
								$numMedals++;
							}
							echo "</div>";	
							checkForBreak();
						}
						
						if (isset($ranks[$child])) {
							foreach ($ranks[$child] as $rank) {
								echo "<div class='label'>";
								echo "<span class='name'>";
								echo "Name : " . $userInfo[$child] . "<br />";
								echo "Rank : " . $rank['rank_name'] . "<br />" . 
									"Serial #: " . $rank['user_serial'];
								echo "</span></div>";
								checkForBreak();
							}
						}
					}
				}	
			}

			function checkForBreak() {
				global $cols, $rows;
				if (($cols % 3) != 0) {
                	echo "<div class='space'></div>";
                } else {
                    $cols = 0; //reset cols so that it will show new row
                    $rows++; //add row
                    if ( ($rows % 11) == 0 ) {
                        $rows = 1; //reset rows counter and add space to top of new page
                        echo "<div class='page-break'></div><div class='topSpace'></div>"; 
                    }
                }
                $cols++;
			}
			?>
        </div>
	</body>
</html>