<?
$admin_auth = array('school'); 
require('header.php');

require_once 'class.rankReport.php';
$r = new RankReport;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Ranks Shipping Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script type="text/javascript">
            $( function() {
                $(".cshipped").click( function() {
                    var user = $(this).parent().parent().find('td span.userIDs').text();
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                            id : user, 
                            checked : checked, 
                            field : 'date_card_shipped',
                            table : 'rank_marks', 
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
                
                $(".creceived").click( function() {
                    var user = $(this).parent().parent().find('td span.userIDs').text();
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                            id : user, 
                            checked : checked, 
                            field : 'date_card_received',
                            table : 'rank_marks', 
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
                
                $(".bshipped").click( function() {
                    var user = $(this).parent().parent().find('td span.userIDs').text();
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                            id : user, 
                            checked : checked, 
                            field : 'date_book_shipped',
                            table : 'rank_marks', 
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
                
                $(".breceived").click( function() {
                    var user = $(this).parent().parent().find('td span.userIDs').text();
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                            id : user, 
                            checked : checked, 
                            field : 'date_book_received',
                            table : 'rank_marks', 
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
                    $(this).parent().parent().parent().parent().find('.cshipped').each( function() {
                        $(this).attr('checked', !$(this).is(":checked")); //needed b/c trigger function will call ajax before checking the button
                        $(this).trigger('click');
                        $(this).attr('checked', !$(this).is(":checked")); //needed b/c trigger function will reverse checked status
                    });
                });
                
                $(".creceivedBtn").click( function() {
                    $(this).parent().parent().parent().parent().find('.creceived').each( function() {
                        $(this).attr('checked', !$(this).is(":checked")); //needed b/c trigger function will call ajax before checking the button
                        $(this).trigger('click');
                        $(this).attr('checked', !$(this).is(":checked")); //needed b/c trigger function will reverse checked status
                    });
                });
                
                $(".bshippedBtn").click( function() {
                    $(this).parent().parent().parent().parent().find('.bshipped').each( function() {
                        $(this).attr('checked', !$(this).is(":checked")); //needed b/c trigger function will call ajax before checking the button
                        $(this).trigger('click');
                        $(this).attr('checked', !$(this).is(":checked")); //needed b/c trigger function will reverse checked status
                    });
                });
                
                $(".breceivedBtn").click( function() {
                    $(this).parent().parent().parent().parent().find('.breceived').each( function() {
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
        <h1>Ranks Shipping Report</h1>
        <? 
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
            foreach ( $schools as $school_id => $school_name ) {
                echo "<h2>" . $school_name . "</h2>";
                 
                //set up ranks array
                $r->setSchoolId($school_id);
                $r->setRanks();
                $ranks = $r->getRanks();
                $r->setRankNames();
                $rankInfo = $r->getRankInfo();
                $userInfo = $r->getUserInfo();
                $rankOrds = $r->getRankOrds();
                
                echo "<pre>";
                //print_r($ranks);
                //print_r($rankOrds);
                echo "</pre>";

                if (isset($ranks[$school_name])) {
                    ?>
                    <table>
                        <tr>
                            <th>Teacher</th>
                            <th>Grade</th>
                            <th>Student</th>
                            <th>Rank</th>
                            <th>Card Printed</th>
                            <th>Card Shipped</th>
                            <th>Card Received</th>
                            <th>Book Shipped</th>
                            <th>Book Received</th>
                        </tr>
                    <?php
                    foreach ($ranks[$school_name] as $teacher => $class) {
                        foreach ($class as $grade => $info) {
                        	foreach ($info as $users) {
	                            foreach ($users as $user => $rank) {
	                                $id = $userInfo[$user];
	                                $ord = $rankOrds[$rank];
	                                echo "<tr><td>" . $teacher . "</td><td>" . $grade . "</td><td><span class='userIDs' style='display: none'>$id:$ord</span>" . $user . 
	                                        "</td><td>" . $rank . "</td><td>" . $rankInfo[$user]['card_printed'] . 
	                                        "</td><td align='center'>";
	                                        
	                                        if (!is_null($rankInfo[$user]['card_shipped'])) {
	                                            echo "<input type='checkbox' class='cshipped' checked='checked' />";
	                                            echo "<span>" . $rankInfo[$user]['card_shipped'] . "</span>";
	                                        } else {
	                                            echo "<input type='checkbox' class='cshipped' />"; 
	                                        }
	                                        echo "</td><td align='center'>";
	                                        
	                                        if (!is_null($rankInfo[$user]['card_received'])) {
	                                            echo "<input type='checkbox' class='creceived' checked='checked' />";
	                                            echo "<span>" . $rankInfo[$user]['card_received'] . "</span>";
	                                        } else {
	                                            echo "<input type='checkbox' class='creceived' />";
	                                        }
	                                        echo "</td><td align='center'>";
	                                        
	                                        if (!is_null($rankInfo[$user]['book_shipped'])) {
	                                            echo "<input type='checkbox' class='bshipped' checked='checked' />";
	                                            echo "<span>" . $rankInfo[$user]['book_shipped'] . "</span>";
	                                        } else {
	                                            echo "<input type='checkbox' class='bshipped' />"; 
	                                        }
	                                        echo "</td><td align='center'>";
	                                        
	                                        if (!is_null($rankInfo[$user]['book_received'])) {
	                                            echo "<input type='checkbox' class='breceived' checked='checked' />";
	                                            echo "<span>" . $rankInfo[$user]['book_received'] . "</span>";
	                                        } else {
	                                            echo "<input type='checkbox' class='breceived' />";
	                                        }
	                                        echo "</td></tr>";
	                            }
	                        }
                        }
                    }
                    echo "<tr><td></td><td></td><td></td><td></td><td></td><td>";
                    echo "<input type='button' class='cshippedBtn' value='toggle all' /></td><td>";
                    echo "<input type='button' class='creceivedBtn' value='toggle all' /></td><td>";
                    echo "<input type='button' class='bshippedBtn' value='toggle all' /></td><td>";
                    echo "<input type='button' class='breceivedBtn' value='toggle all' /></td><td>";
                    echo "</tr></table>"; 
                    echo "<br /><br />";
                }
            }        
            ?>
        </div>
    </body>
</html>