<?php
ini_set('display_errors',1);
require_once '../../db.php';
$admins = array();
$children = array();
$sql = "select a.*, tc.*, u.first as ufirst, u.last as ulast, s.school_name     
        from th_chidon tc
        join users u using (user_id)
        join schools s on tc.school_id = s.school_id  
        join admin_auths aa on (aa.id = tc.user_id) 
        join admins a using (admin_id)
        where tc.year = 5778
        and tc.date_paid > 0";
$result = mysql_query( $sql );
while ($row = mysql_fetch_assoc( $result )) {
    $admins[$row['admin_id']] = $row;
    $children[$row['admin_id']][] = $row;
}
//echo "<pre>"; print_r($info); echo "</pre>";
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Donor List</title>
		<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.css"/>
		<style>
			body {
				font-family: sans-serif;
				font-size: 12px;
				padding-left: 3%;
				padding-right: 3%;
			}
			fieldset {
				float: left;
				width: 40%;
				padding-right: 20px;
				padding-left: 20px;
				padding-bottom: 20px;
			}
		</style>
	</head>
	
	<body>
        <h2>Donor List</h2>
        <p>Report is based on children that were elligible for coming to the shabbaton last year.</p>
        <form method="get" action="">
            <p>
                Only show list assigned to: 
                <select name="filter">
                    <option value="0">All</option>
                    <option value="1" <?php if (isset($_GET['filter']) && $_GET['filter'] == 1) echo "selected"?>>Yerachmiel Benjaminson</option>
                    <option value="2">Shimmy Weinbaum</option>
                    <option value="3">Sholom Ber Baumgarten</option>
                    <option value="4">Rochi Benjaminson</option>
                </select>
                <input type="submit" name="submit" value="filter" />
            </p>
        </form>
        <table id="table" class="table table-striped">
            <thead>
                <tr>
                    <th>Parent Name</th>
                    <th>Parent Address</th>
                    <th>Parent Email</th>
                    <th>Parent Number</th>
                    <th style="width: 160px;">Children / Marks</th>
                    <th>Assigned To</th>
                    <th>Pledged</th>
                    <th>Donated</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($admins as $admin_id => $admin) {
                    $info = array();
                    $sql = "select * from th_donor_list where admin_id = " . $admin_id;
                    if (isset($_GET['filter'])) {
                        switch ($_GET['filter']) {
                            case 1:
                                $sql .= " and assigned = 'Yerachmiel Benjaminson'";
                                break;
                            case 2:
                                $sql .= " and assigned = 'Shimmy Weinbaum'";
                                break;
                            case 3:
                                $sql .= " and assigned = 'Sholom Ber Baumgarten'";
                                break;
                            case 4:
                                $sql .= " and assigned = 'Rochi Benjaminson'";
                                break;
                        }
                    }
                    $result = mysql_query( $sql );
                    if (mysql_num_rows( $result )) {
                        $info = mysql_fetch_assoc( $result );
                    } else {
                        if (isset($_GET['filter']) && $_GET['filter'] > 0) continue;
                    }
                    $address = $admin['admin_address1'] . "<br />" . $admin['admin_city'] . ", " . $admin['admin_state'] . "<br />" .
                        $admin['admin_postal'] . "<br />" . $admin['admin_country'];
                    echo "<tr id=" . $admin_id . "><td>" . $admin['first'] . ' ' . $admin['last'] . "</td><td>" . $address . "</td><td>" .
                        $admin['admin_email'] . "</td><td>";
                    echo "Cell 1: " . $admin['admin_phone_mobile'] . "<br />";
                    echo "Cell 2: " . $admin['admin_phone_mobile2'] . "<br />";
                    echo "Work: " . $admin['admin_phone_work'] . "<br />";
                    echo "Home: " . $admin['admin_phone_home'] . "</td><td>";
                    $i = 1;
                    foreach ($children[$admin_id] as $child) {
                        echo "Child: " . $child['ufirst'] . "<br />";
                        echo "School: " . $child['school_name'] . "<br />";
                        echo "Avg Part 1: " . number_format(($child['test1a'] + $child['test2a'] + $child['test3a']) / 3, 2) . "<br />";
                        echo "Avg Part 2: " . number_format(($child['test1b'] + $child['test2b'] + $child['test3b']) / 3, 2);
                        if (count($children[$admin_id]) > $i++) echo  "<br /><br />";
                    }
                    echo "</td><td>";
                    if ($info['assigned']) echo $info['assigned'];
                    else {
                        echo "<select name='assign' class='assign'><option value='0'>Choose One</option>";
                        echo "<option value='Yerachmiel Benjaminson'>Yerachmiel Benjaminson</option>";
                        echo "<option value='Shimmy Weinbaum'>Shimmy Weinbaum</option>";
                        echo "<option value='Sholom Ber Baumgarten'>Sholom Ber Baumgarten</option>";
                        echo "<option value='Rochi Benjaminson'>Rochi Benjaminson</select>";
                    }
                    echo "</td><td>";
                    echo "<input type='text' name='pledged' class='pledged' ";
                    if ($info['pledged']) echo "value='" . $info['pledged'] . "' ";
                    if (empty($info)) echo "disabled ";
                    echo "/></td><td><input type='text' name='donated' class='donated' ";
                    if ($info['donated']) echo "value='" . $info['donated'] . "' ";
                    if (empty($info)) echo "disabled ";
                    echo "/></td><td><textarea cols='20' rows='5' name='notes' class='notes'";
                    if (empty($info)) echo " disabled ";
                    echo ">";
                    if ($info['notes']) echo $info['notes'];
                    echo "</textarea></td></tr>";
                }
                ?>
            </tbody>
        </table>
    </body>
	<script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.7/jqc-1.12.4/dt-1.10.13/cr-1.3.2/fc-3.2.2/fh-3.1.2/r-2.1.1/sc-1.4.2/se-1.2.0/datatables.min.js"></script>
	<script>
		$('#table').DataTable({
			paging : false
		});
        $(".assign").change( function() {
            var id = $(this).parent().parent().attr('id');
            var val = encodeURI($(this).val());
            if (val) {
                $.post('ajax/updateDonorList.php', { admin_id : id, field : 'assigned', value : val }, function( error ) {
                    if (parseInt(error) == 0) {
                        var tr = '#' + id;
                        $(tr).find('input.pledged').attr('disabled',false);
                        $(tr).find('input.donated').attr('disabled',false);
                        $(tr).find('textarea').attr('disabled',false);
                    }
                });
            }
        });
        
        $(".pledged").blur( function() {
            var id = $(this).parent().parent().attr('id');
            var val = $(this).val();
            $.post('ajax/updateDonorList.php', { admin_id : id, field : 'pledged' , value: val }, function( error ) {
                if (parseInt(error) != 0) {
                    alert('Error updating.');
                }
            });
        });
        
        $(".donated").blur( function() {
            var id = $(this).parent().parent().attr('id');
            var val = $(this).val();
            $.post('ajax/updateDonorList.php', { admin_id : id, field : 'donated', value: val }, function( error ) {
                if (parseInt(error) != 0) {
                    alert('Error updating.');
                }
            });
        });
        
        $(".notes").blur( function() {
            var id = $(this).parent().parent().attr('id');
            var val = encodeURI($(this).text());
            $.post('ajax/updateDonorList.php', { admin_id : id, field : 'notes', value: val }, function( error ) {
                if (parseInt(error) != 0) {
                    alert('Error updating.');
                }
            });
        });
	</script>