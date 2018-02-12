<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Newly Joined Report</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
        <script type="text/javascript">
            $( function() {
                $(".shipped").click( function() {
                    var user = $(this).parent().find('span.userIDs').text();
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                            id : user, 
                            checked : checked, 
                            field : 'shipped',
                            table : 'newly_joined', 
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
                    var user = $(this).parent().find('span.userIDs').text();
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                            id : user, 
                            checked : checked, 
                            field : 'received',
                            table : 'newly_joined', 
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
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>Newly Joined Report</h1>
        <? 
        require_once 'class.adminSchools.php';
        require_once 'class.schoolsUsers.php';         
       
        $as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
        $schools = $as->getSchools();
        $schoolsUsers = array();
        $totals = array();
        
        foreach ( $schools as $id => $school ) {
            $s = new SchoolsUsers( $id );
            $schoolsUsers[$id] = $s->getUsers();
        }
        
        require_once 'class.userInfo.php';
        $u = new UserInfo;
        $usersInfo = $u->getNewlyJoined(2456522);
        
        /*
        echo "<pre>";
        print_r( $schoolsUsers );
        echo "</pre>";
         * 
         */
         
        foreach ($schoolsUsers as $school => $users) {
            echo "<h2>" . $schools[$school] . "</h2>";
            echo "<table>";
            echo "<tr><th>Grade</th><th>Student</th><th>Binder & Boards Shipped</th><th>Binder & Boards Received</th></tr>";
            foreach ($users as $user) {
                if (isset($usersInfo[$user['user_id']])) {
                    $userID = $user['user_id'];
                    $shipped = $usersInfo[$userID]['shipped'];
                    $received = $usersInfo[$userID]['received'];
                    $grade = $user['class_grade'] . (empty( $user['class_sub']) ? '' : "-" . $user['class_sub']);
                    echo "<tr><td>" . $grade . "</td><td>" . $user['first'] . " " . $user['last'] . "<td align='center'>";
                    
                    if (!is_null($shipped)) {
                        echo "<input type='checkbox' class='shipped' checked='checked' />";
                        echo "<span>" . $shipped . "</span>";
                    }
                    else {
                        echo "<input type='checkbox' class='shipped' />";
                    }
                    echo "<span class='userIDs' style='display:none'>" . $userID . "</span></td><td align='center'>";
                    
                    if (!is_null($received)) {
                        echo "<input type='checkbox' class='received' checked='checked' />";
                        echo "<span>" . $received . "</span>";
                    }
                    else {
                        echo "<input type='checkbox' class='received' />";
                    }
                    echo "<span class='userIDs' style='display:none'>" . $userID . "</span></td></tr>";
                                        
                    if ( isset( $totals[$schools[$school]][$grade] ) ) 
                        $totals[$schools[$school]][$grade]++;
                    else 
                        $totals[$schools[$school]][$grade] = 1; 
                }
            }
            echo "<tr><td></td><td></td><td align='center'><input type='button' class='shippedBtn' value='toggle all' /></td> 
                    <td align='center'><input type='button' class='receivedBtn' value='toggle all' /></td></tr>";
            echo "</table><br /><div class='page-break'></div>";
        } 
        
        echo "<div class='page-break'></div>";
        echo "<h2>Totals</h2>";
        foreach ( $totals as $school => $info ) {
            $grandTotal = 0; 
            echo "Total number of new children joined in " . $school . "<br />";
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