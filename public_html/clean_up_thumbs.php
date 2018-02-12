<?php
require 'db.php';

$info = array();
$sql = "select t.thumb FROM mashpiadb.thumbs t 
        left join files f using (file_id) 
        where f.file_id is null";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result)) {
    $thumb = $row['thumb'];
    if (file_exists('thumbs/' . $thumb)) unlink('thumbs/' . $thumb);
}
echo "done";