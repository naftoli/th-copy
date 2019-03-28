<?php
require 'db.php';

$fields = array('m10','m18','m36','m50','m100','g10','g18','gg10','gg18','gg36','gg50','gg100');

$row = 0;
if (($handle = fopen("TicketBreakdown.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($row++) {
            $j = 0;
            $id = $data[$j++];
            foreach ($fields as $field) {
                $$field = $data[$j++];
            }
            
            $sql = "update chidon set ";
            foreach ($fields as $field) {
                if (!empty($$field)) {
                    $sql .= $field . "=" . $$field . ", ";
                }
            }
            $sql = substr($sql, 0, strlen($sql) - 2);
            $sql .= " where id = " . $id;
            mysql_query($sql);
        }
    }
    fclose($handle);
}
echo "Done.";