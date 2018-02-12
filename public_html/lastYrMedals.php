<?php
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML DIR="<?=$dir?>">

    <HEAD>
        <TITLE>Medals Ranks Ceremony</TITLE>
        <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
        <SCRIPT type="text/javascript" src="icalendar.js"></SCRIPT>
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
        <h1>Total Medals Earned May 28, 2013 - Oct. 23, 2014</h1>
        <div id='main'>          
            <?                     
            $subjects = array();
			$sql = "select * from subjects";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
			    $subject = $row['subject_name'];
			    if ($row['subject_name'] == 'שבת מברכים תהילים') 
			        $subject = "WWTC";
			    $subjects[$row['subject_id']] = $subject;
			}
			
			$medals = array();
			$sql = "select * from medals";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
			    $medals[$row['medal_ord']] = $row['medal_name'];
			}
			
			$medalsEarned = array();
			$sql = "select subject_id, medal_ord, count(*) as total 
					from medal_marks 
					where date_awarded > 2456440
					and date_awarded < 2456954 
					and date_received is null 
			 		group by subject_id, medal_ord";
			$result = mysql_query($sql);
			while ($row = mysql_fetch_assoc($result)) {
				$medalsEarned[$row['subject_id']][$row['medal_ord']] = $row['total'];
			}
			?>
			<table>
				<tr>
					<th>Subject</th>
					<th>Medal</th>
					<th>Total</th>
				</tr>
				<?
				foreach ($medalsEarned as $subject => $info) {
	            	foreach ($info as $medal => $total) {
	            		echo "<tr><td>" . $subjects[$subject] . "</td><td>" . $medals[$medal] . "</td><td>" . 
	            			$total . "</td></tr>";
	            	}
	            }
				?>
			</table>
        </div>    
    </BODY>
</HTML>
 