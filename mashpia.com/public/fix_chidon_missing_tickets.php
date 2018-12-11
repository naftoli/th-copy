<?php
require 'db.php';

$fields = array('m10','m18','m36','m50','m100','g10','g18','gg10','gg18','gg36','gg50','gg100');

$row = 0;
if (($handle = fopen("ChidonTickets2.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($row++) {
            $j = 0;
            $name = $data[$j++];
            $email = $data[$j++];
            $mqty = empty($data[$j++]) ? 0 : $data[$j-1];
            $gqty = empty($data[$j++]) ? 0 : $data[$j-1];
            $ggqty = empty($data[$j++]) ? 0 : $data[$j-1];
            $method = $data[$j++];
            $date = $data[$j++];
            $trans_id = $data[$j++];
            $amount = $data[$j++];
            $cc = $data[$j++];
            $address = $data[$j++];
            $city = $data[$j++];
            $state = $data[$j++];
            $zip = $data[$j++];
            foreach ($fields as $field) {
                $$field = $data[$j++];
            }
            
            $sql = "insert into chidon
                    set name = '" . $name . "',
                    email = '" . $email . "',
                    mqty = " . $mqty . ",
                    gqty = " . $gqty . ",
                    ggqty = " . $ggqty . ",
                    paid = " . $amount . ",
                    approval = '" . $trans_id . ":" . $cc . ":" . $amount . ":" . $date . "',
                    date_purchased = now(), 
                    method = '" . $method . "',
                    address = \"" . $address . "\",
                    city = '" . $city . "',
                    state = '" . $state . "',
                    zip = '" . $zip . "',
                    year = 5777";
            foreach ($fields as $field) {
                if (!empty($$field)) {
                    $sql .= ", " . $field . "=" . $$field;
                }
            }
            mysql_query($sql) or die(mysql_error());
        }
    }
    fclose($handle);
}
echo "Done.";