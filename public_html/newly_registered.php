<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Newly Registered Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script type="text/javascript">
            $( function() {
                $(".cshipped").click( function() {
                    var user = $(this).parent().find('span.userIDs').text();
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                            id : user, 
                            checked : checked, 
                            field : 'cards_shipped',
                            table : 'newly_registered', 
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
                    var user = $(this).parent().find('span.userIDs').text();
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                            id : user, 
                            checked : checked, 
                            field : 'cards_received',
                            table : 'newly_registered', 
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
                
                $(".sshipped").click( function() {
                    var user = $(this).parent().find('span.userIDs').text();
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                            id : user, 
                            checked : checked, 
                            field : 'stickers_shipped',
                            table : 'newly_registered', 
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
                
                $(".sreceived").click( function() {
                    var user = $(this).parent().find('span.userIDs').text();
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                            id : user, 
                            checked : checked, 
                            field : 'stickers_received',
                            table : 'newly_registered', 
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
                
                $(".sshippedBtn").click( function() {
                    $(this).parent().parent().parent().parent().find('.sshipped').each( function() {
                        $(this).attr('checked', !$(this).is(":checked")); //needed b/c trigger function will call ajax before checking the button
                        $(this).trigger('click');
                        $(this).attr('checked', !$(this).is(":checked")); //needed b/c trigger function will reverse checked status
                    });
                });
                
                $(".sreceivedBtn").click( function() {
                    $(this).parent().parent().parent().parent().find('.sreceived').each( function() {
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
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Newly Registered Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';         
       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'] );
        $schools = $as->getSchools();
        $schoolsUsers = array();
        $totals = array();
        
        foreach ( $schools as $id => $school ) {
            $s = new SchoolsUsers( $id );
            $schoolsUsers[$id] = $s->getUsers();
        }
        
        require_once 'class.userInfo.php';
        $u = new UserInfo(5775);
        $usersInfo = $u->getNewlyRegistered();
        
        /*
        echo "<pre>";
        print_r( $schoolsUsers );
        echo "</pre>";
         * 
         */
         
        foreach ($schoolsUsers as $school => $users) {
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table>";
            echo "<tr><th>Grade</th><th>Student</th><th>Cards Shipped</th><th>Cards Received</th><th>Sticker Books Shipped</th><th>Sticker Books Received</th></tr>";
            foreach ( $users as $user ) {
                if (isset($usersInfo[$user['user_id']])) {
                    $userID = $user['user_id'];
                    $cshipped = $usersInfo[$userID]['cards_shipped'];
                    $creceived = $usersInfo[$userID]['cards_received'];
                    $sshipped = $usersInfo[$userID]['stickers_shipped'];
                    $sreceived = $usersInfo[$userID]['stickers_received'];
                    $grade = $user['class_grade'] . (empty( $user['class_sub']) ? '' : "-" . $user['class_sub']);
                    echo "<tr><td>" . $grade . "</td><td>" . $user['first'] . " " . $user['last'] . "<td align='center'>";
                    
                    if (!is_null($cshipped)) {
                        echo "<input type='checkbox' class='cshipped' checked='checked' />";
                        echo "<span>" . $cshipped . "</span>";
                    }
                    else {
                        echo "<input type='checkbox' class='cshipped' />";
                    }
                    echo "<span class='userIDs' style='display:none'>" . $userID . "</span></td><td align='center'>";
                    
                    if (!is_null($creceived)) {
                        echo "<input type='checkbox' class='creceived' checked='checked' />";
                        echo "<span>" . $creceived . "</span>";
                    }
                    else {
                    	echo "<input type='checkbox' class='creceived' />";
                    }
                    echo "<span class='userIDs' style='display:none'>" . $userID . "</span></td><td align='center'>";
                    
                    if (!is_null($sshipped)) {
                        echo "<input type='checkbox' class='sshipped' checked='checked' />";
                        echo "<span>" . $sshipped . "</span>";
                    }
                    else {
                        echo "<input type='checkbox' class='sshipped' />";
                    }
                    echo "<span class='userIDs' style='display:none'>" . $userID . "</span></td><td align='center'>";
                    if (!is_null($sreceived)) {
                        echo "<input type='checkbox' class='sreceived' checked='checked' />";
                        echo "<span>" . $sreceived . "</span>";
                    }
                    else {
                        echo "<input type='checkbox' class='sreceived' />";
                    }
                    echo "<span class='userIDs' style='display:none'>" . $userID . "</span></td></tr>"; 
                    
                    if ( isset( $totals[$schools[$school]][$grade] ) ) 
                        $totals[$schools[$school]][$grade]++;
                    else 
                        $totals[$schools[$school]][$grade] = 1; 
                }
            }
            echo "<tr><td></td><td></td><td align='center'><input type='button' class='cshippedBtn' value='toggle all' /></td>";
            echo "<td align='center'><input type='button' class='creceivedBtn' value='toggle all' /></td>";
            echo "<td align='center'><input type='button' class='sshippedBtn' value='toggle all' /></td>";
            echo "<td align='center'><input type='button' class='sreceivedBtn' value='toggle all' /></td></tr>";
            echo "</table><br /><div class='page-break'></div>";
        } 
        
        echo "<div class='page-break'></div>";
        echo "<h2>Totals</h2>";
        foreach ( $totals as $school => $info ) {
            $grandTotal = 0; 
            echo "Total number of children registered in " . $school . "<br />";
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
    </body>
</html>