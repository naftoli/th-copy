<?
$admin_auth = array('school'); 
require('header.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">
<HTML>

    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>TH Merchandise Report</title>
        <script type="text/javascript" src="scripts/jquery-1.8.3.js"></script>
        <script type="text/javascript" src="scripts/jquery.styleselect.js"></script>
        <script type="text/javascript">
            $( function() {
                $(".shipped").click( function() {
                    var id = $(this).val();
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                        id : id, 
                        checked : checked, 
                        field : 'shipped', 
                        table : 'user_add_ons', 
                        key : 'user_add_on_id'
                        }, function(data) {
                        if (data && checked) {
                            var d = new Date();
                            var n = d.toDateString();
                            $(e).after('<span><br />' + n + '</span>');
                        } else if (data && !checked){
                            $(e).next('span').remove();
                        }
                    });
                });
                $(".received").click( function() { 
                    var id = $(this).val(); 
                    var checked = $(this).is(":checked");
                    var e = this;
                    $.post('ajax/updateShipped.php', {
                        id : id, 
                        checked : checked, 
                        field : 'received', 
                        table : 'user_add_ons', 
                        key : 'user_add_on_id'
                        }, function(data) {
                        if (data && checked) {
                            var d = new Date();
                            var n = d.toDateString();
                            $(e).after('<span><br />' + n + '</span>');
                        } else if (data && !checked){
                            $(e).next('span').remove();
                        }
                    });
                });
                
                $("#allShipped").click( function() {
                    $(".shipped").each( function() {
                        if (!$(this).is(":checked")) {
                            $(this).trigger('click');
                        }
                    });
                });
                
                $("#allReceived").click( function() {
                    (".received").each( function() {
                        if (!$(this).is(":checked")) {
                            $(this).trigger('click');
                        }
                    });
                });
                
                <? if (!isset($_POST['submit'])) : ?>
                    $("#school").sSelect();
                    $("#year").sSelect();
                    $("#submit").click( function() {
                        if ($("#school").val() == 0) {
                            alert("You must choose a school!");
                            return false;
                        }
                        if (!$("#filter input").is(":checked")) {
                            alert("You must choose at least one filter.");
                            return false;
                        }
                    });
                    $("#checkAll").click( function() {
                        $("#filter input").attr('checked', true);
                    });
                    $("#uncheckAll").click( function() {
                        $("#filter input").attr('checked', false);
                    });
                <? endif; ?>
            });
        </script>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <style type='text/css'>
            table {
                font-size: 12px;
            }
            th, td {
                padding: 3px 10px;
            }
            .newPage {
                page-break-after: always;
            }
            fieldset {
                border: 1px solid white;
                padding: 10px;
                padding-top: 0px;
                -moz-border-radius: 10px;
                -webkit-border-radius: 10px;
                border-radius: 10px;
                font-size: 14px;
            }
            legend {
                margin-left: 20px;
                padding: 5px;
                color: purple;
                font-size: 16px;
            }
            @media screen {
                .instructions {
                    display: none;
                }
            }
            @media print {
                .instructions {
                    display: block;
                }
            }
        </style>
    </HEAD>

    <BODY>
        <? include('admin_header.php'); ?>
        <h1>TH Merchandise Report</h1>
        
        <? 
        require_once 'class.adminSchools.php';       
        $as = new AdminSchools( $admin_user['admin_id'], $admin_user['auth'], false );
        $schools = $as->getSchools();
        
        if (isset($_POST['submit'])) {
            //print_r($_POST); exit;
            $school = $_POST['school'];
            $year = $_POST['year'];
            
            $where = '';
            if (isset($_POST['shipped']) && !isset($_POST['notShipped'])) {
                $where .= " and shipped is not null ";
            } else if (isset($_POST['notShipped']) && !isset($_POST['shipped'])) {
                $where .= " and shipped is null ";
            }
            if (isset($_POST['received']) && !isset($_POST['notReceived'])) {
                $where .= " and received is not null ";
            } else if (isset($_POST['notReceived']) && !isset($_POST['received'])) {
                $where .= " and received is null ";
            }
            
            $add_ons = array();
            $sql = "select u.school_id, c.class_grade, c.class_sub, u.first, u.last, sa.name, 
                    ua.user_add_on_id, ua.size, ua.date, ua.shipped, ua.received, u.school_type_id     
                    from users u 
                    join classes c using (class_id)
                    join user_add_ons ua using (user_id) 
                    join school_add_ons sa using (school_add_on_id)
                    where sa.year = $year 
                    $where ";
            if ($school > 0) {
                $sql .= " and u.school_id = $school";
            }
            $sql .= " order by school_id, class_grade, class_sub, last, first, name";
            //echo $sql;
            $result = mysql_query($sql);
            $shippingInfo = array();
            while ($row = mysql_fetch_assoc($result)) {
                $grade = empty($row['class_sub']) ? $row['class_grade'] : $row['class_grade'] . '-' . $row['class_sub'];
                $name = $row['user_add_on_id'] . ":" . $row['first'] . ' ' . $row['last'];
                if ($row['name'] == 'Siddur with Biur Tefillah') {
                	$type = $row['school_type_id'];
                	$size = '';
                	if (in_array($type, array(2,12))) {
                		$size = 'Blue';
                	} else if (in_array($type, array(3,13))) {
                		$size = 'Purple';
                	}
                	$info = $row['name'] . ":" . $size . ":" . substr($row['date'], 0, strpos($row['date'], ' '));
				} else { 
					$info = $row['name'] . ":" . $row['size'] . ":" . substr($row['date'], 0, strpos($row['date'], ' '));
                }
                $add_ons[$row['school_id']][$grade][][$name][] = $info;
                $shippingInfo[$row['user_add_on_id']]['shipped'] = $row['shipped'];
                $shippingInfo[$row['user_add_on_id']]['received'] = $row['received'];
            }
            /*
            echo "<pre>";
            print_r($add_ons);
            echo "</pre>";
            exit;
            */
            
            echo "<div align='center'><input type='button' value='Print' onclick='window.print()' /></div>";
            
            $grandTotals = array();
            foreach ($add_ons as $school => $info) {
                $totals = array();
                echo "<div class='instructions'>Dear Base Commander,<br />The following items were purchased monetarily 
                        by the parents of your students. You can either give them out to your students or have 
                        them purchase their prize with miles by adding them to your store.<br />Please click on the 
                        TH Merchandise Report and check the received button once they have been received their items.</div>";                
                echo "<h2>" . $schools[$school] . "</h2>";
                echo "<table>";
                echo "<tr><th>Class</th><th>Student</th><th>Add-on</th><th>Size</th><th>Purchased</th>
                    <th>Shipped</th><th>Received</th></tr>";
                foreach ($info as $grade => $more) {
                    foreach ($more as $names) {
                        foreach ($names as $name => $info) {
                            foreach ($info as $add_on) {
                                $name_info = explode(":", $name);
                                $add_on_info = explode(':', $add_on);
                                echo "<tr><td>" . $grade . "</td><td>" . 
                                    $name_info[1] . "</td><td>" . $add_on_info[0] . "</td><td>" . 
                                        $add_on_info[1] . "</td><td>" . $add_on_info[2] . "</td><td>";
                                echo "<input type='checkbox' class='shipped' value='$name_info[0]'";
                                if (!empty($shippingInfo[$name_info[0]]['shipped'])) {
                                    echo " checked='checked' /><br />" . $shippingInfo[$name_info[0]]['shipped'];
                                } else {
                                    echo " />";
                                }
                                echo "</td><td>";
                                echo "<input type='checkbox' class='received' value='$name_info[0]'";
                                if (!empty($shippingInfo[$name_info[0]]['received'])) {
                                    echo " checked='checked' /><span><br />" . $shippingInfo[$name_info[0]]['received'] . "</span>";
                                } else {
                                    echo " />";
                                }
                                echo "</td></tr>";
                                
                                if (!isset($totals[$add_on_info[0]])) {
                                    $totals[$add_on_info[0]]['amount'] = 1;
                                } else {
                                    $totals[$add_on_info[0]]['amount']++;
                                }
                                
                                if (!isset($grandTotals[$add_on_info[0]])) {
                                    $grandTotals[$add_on_info[0]]['amount'] = 1;
                                } else {
                                    $grandTotals[$add_on_info[0]]['amount']++;
                                }
                                
                                if (!empty($add_on_info[1])) {
                                    if (!isset($totals[$add_on_info[0]]['size'][$add_on_info[1]])) {
                                        $totals[$add_on_info[0]]['size'][$add_on_info[1]] = 1;
                                    } else {
                                        $totals[$add_on_info[0]]['size'][$add_on_info[1]]++;
                                    }
                                    
                                    
                                    if (!isset($grandTotals[$add_on_info[0]]['size'][$add_on_info[1]])) {
                                        $grandTotals[$add_on_info[0]]['size'][$add_on_info[1]] = 1;
                                    } else {
                                        $grandTotals[$add_on_info[0]]['size'][$add_on_info[1]]++;
                                    }
                                }
                            }
                        }
                    }
                }
                //echo "<tr><td></td><td></td><td></td><td></td><td></td><td>
                //        <input type='button' id='allShipped' value='ship all' /></td>
                //        <td><input type='button' id='allReceived' value='receive all' /></td></tr>";
                //echo "<tr><td colspan='2'><input type='button' id='notifySchool' value='Notify School' /></td><td colspan='5'></td></tr>";
                echo "</table>";
                echo "<h2>Totals</h2>";
                echo "<table>";
                foreach ($totals as $add_on => $info) {
                    echo "<tr><td>" . $add_on . "</td><td>" . $info['amount'];
                    if (isset($info['size'])) {
                        echo " (";
                        foreach ($info['size'] as $size => $total) {
                            echo "Size " . strtoupper($size) . " - " . $total . "; ";
                        }
                        echo ")";
                    }
                    
                    echo "</td></tr>";
                }
                echo "</table>";
                echo "<div class='newPage'></div>";
            }
            
            if (count($schools) > 1) {
                echo "<h2>Grand Totals</h2>";
                echo "<table>";
                foreach ($grandTotals as $add_on => $info) {
                    echo "<tr><td>" . $add_on . "</td><td>" . $info['amount'];
                    if (isset($info['size'])) {
                        echo " (";
                        foreach ($info['size'] as $size => $total) {
                            echo "Size " . strtoupper($size) . " - " . $total . "; ";
                        }
                        echo ")";
                    }
                    
                    echo "</td></tr>";
                }
                echo "</table>";
            }
            
        } else {    
        ?>
        
        <form action="add_ons_report.php" method="post">
            <select name="school" id="school">
                <?
                if (count($schools) > 1) {
                    echo "<option value='0'>Select School</option>";
                    echo "<option value='-1'>All</option>";
                }
                foreach ($schools as $id => $school) {
                    echo "<option value=$id>$school</option>";
                }
                ?>
            </select><br /><br />
            <select name="year" id="year">
                <option value="2011">5772</option>
                <option value="2012">5773</option>
                <option value="2013">5774</option>
                <option value="2014">5775</option>
                <option value="2015" selected="selected">5776</option>
            </select><br /><br />
            
            <fieldset id="filter">
                <legend>Filter</legend>
                <input type="checkbox" name="shipped" checked="checked" />shipped<br />
                <input type="checkbox" name="notShipped" checked="checked" />not yet shipped<br />
                <input type="checkbox" name="received" checked="checked" />received<br />
                <input type="checkbox" name="notReceived" checked="checked" />not yet received<br />
                <input type="button" id="uncheckAll" value="uncheck all" />
                <input type="button" id="checkAll" value="check all" />
            </fieldset>
            
            <br />
            <input type="submit" name="submit" id="submit" value="submit" />
        </form>
        
        <? } ?>
    </body>
</html>