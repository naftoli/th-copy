<? 
//header("Location: under_construction2.php");
$admin_auth = array('school','user'); 
require('header.php'); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link href="admin_styles.css" rel="stylesheet" type="text/css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Miles Report</title>
<style type='text/css'>
@media print {
	table {
        page-break-after: always;
    }
    .no-print {
        display: none;
    }
}
@media all {
    tr, th, td {
        border: 1px dashed black;
        padding: 10px;
        font-size: 12px;
    }
}
</style>
</head>

<body>
<? include('admin_header.php');?>
<h1 class="no-print">Miles Report</h1>
<? 
if ($admin_auth[0] == 'school') { 

    $numSchools = count( $admin_user['auths']['school'] );
    if ( $numSchools > 1 && !isset( $_POST['submit'] ) ) {
    		
    	$where = '';
        for ( $i = 0; $i < $numSchools; $i++ ) {
            if ( $i < ($numSchools - 1) ) {
            	$where .= $admin_user['auths']['school'][$i] . ',';
            } else {
            	$where .= $admin_user['auths']['school'][$i];
            }
        }
    	
    	$schools = array();
    	$sql = "select school_id, school_name from schools where school_id in ($where) order by school_name";
    	$result = mysql_query( $sql );
    	while ( $row = mysql_fetch_assoc( $result ) ) {
    		$schools[] = $row;
    	}
    	echo "<form action='auctionMiles.php' method='post'>";
    	echo "<select name='school'>";
        foreach ( $schools as $school ) {
        	echo "<option value='$school[school_id]'>$school[school_name]</option>";
        }
    	echo "</select>";
        echo "&nbsp;&nbsp;";
    	echo "<input type='submit' value='go' name='submit' />";
    	echo "</form>";
    
    } else {
        
        if ( count( $admin_user['auths']['school'] ) == 0 ) {
            echo "You have no schools associated with your account.";
            exit;
        }
    
        $id = isset( $_POST['school'] ) ? $_POST['school'] : $admin_user['auths']['school'][0];
        
        if ( ($numSchools == 1 && !isset( $_POST['submit'] )) || (isset( $_POST['school'] ) && !isset( $_POST['class'] ) ) ) {
            $classes = array();
            $sql = "select class_id, class_grade, class_sub 
                    from classes 
                    where class_era = 0 
                    and school_id = " . $id;
            //echo $sql;
            $result = mysql_query( $sql );
            $classes[] = array( 'class_id' => 0, 
                                'class_grade'   =>  "All Classes", 
                                'class_sub'     =>  '' );
            while ( $row = mysql_fetch_assoc( $result ) ) {
                $classes[] = $row;
            }
            echo "Please choose a class:";
            echo "<form action='auctionMiles.php' method='post'>";
            echo "<select name='class'>";
            foreach ( $classes as $class ) {
                echo "<option value='" . $class['class_id'] . "'>" . $class['class_grade'] . 
                    ( empty( $class['class_sub'] ) ? '' : "-" . $class['class_sub'] ) . "</option>";
            }
            echo "</select>";
            echo "&nbsp;&nbsp;";
            if ( isset( $_POST['school'] ) ) {
                echo "<input type='hidden' name='school' value=" . $_POST['school'] . " />";
            }
            echo "<input type='submit' value='go' name='submit' />";
            echo "</form>";
       
        } else {
            require 'class.points.php';
            ?>
            <div class='no-print'>
                <div align='center'>
                    <input type='button' value='Print' onclick='window.print()'>
                </div>
            </div>
            <br />
            <?
            $class_id = isset( $_POST['class' ] ) ? $_POST['class'] : 0;
            $users = array();
            $codes = array();
            $sql = "select * from users u 
            		join classes c using (class_id) 
            		where u.school_id = $id 
            		and u.user_registered > 0 ";
            if ( $class_id > 0 ) {
                $sql .= "and class_id = " . $class_id . " order by u.last, u.first";
            } else {
                $sql .= "order by c.class_grade, c.class_sub, u.last, u.first";
            }
            $res = mysql_query( $sql );
            while ( $row = mysql_fetch_assoc( $res ) ) {
            	$users[] = $row;
            	//$codes['user_code'][] = $row['user_code'];
            }
            //$points = header_icorpa_points_multi_testing( $codes );
            
            /*
            echo "<pre>";
            print_r( $points );
            echo "</pre>";
             * 
             */
            
            $user_classes = array();
            foreach ( $users as $user ) {
            	$grade = $user['class_grade'] . "-" . $user['class_sub'];	
            	$user_classes[$grade][] = $user;
            }
            
            $sql5 = "select * from auctions where auction_id = 78";
            $result5 = mysql_query($sql5);
            $auction = mysql_fetch_assoc($result5);
            
            foreach ( $user_classes as $grade => $arr ) {
            	?>
            	<h3>Grade: <?=$grade?></h3>
            	<br />
            	<table>
            		<tr>
            			<th>Grade</th>		
            			<th>Last Name</th>
            			<th>First Name</th>
            			<th>Miles</th>
            		</tr>
            	<?
            	foreach ( $arr as $user ) {
            		$user_id = $user['user_id'];
            		//$code = '3' . $user['user_code'];
            	?>
            	
            		<?	
                    //$cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = $user_id AND mark_date >= " . dateThisYear(13, 18))), 0));
            		//$cur_points = floatval(mysql_result(mq(totalMarks("WHERE user_id = $user_id")), 0));
					//echo "<input type='hidden' name='" . $user_id . "M' value='" . $cur_points . "' />";
					//echo "<input type='hidden' name='" . $user_id . "V2' value='" . $points[$code]['all_points'] . "' />";
            		//$cur_points += isset( $points[$code] ) ? $points[$code]['all_points'] : 0 ;
            		//$points = auctionPoints($user_id, $auction);
					$p = new Points( $user_id );
					$points = $p->getAuctionPoints( $auction['auction_points_start_date'] );
                    echo "<tr><td>" . $grade . "</td><td>" . $user['last'] . "</td><td>" . $user['first'] . "</td><td>" .
                        $points . "</td></tr>";
            	} 
                echo "</table><div class='no-print'><br /></div>";
        	}
        }
    }
} else { ?>
no permission to view this page
<? } ?>
</body>
</html>