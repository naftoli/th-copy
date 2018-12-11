<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN""http://www.w3.org/TR/html4/strict.dtd">

<HTML>
    <HEAD>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </HEAD>
    
    <BODY>
    
<?
require_once 'class.db.php';
$db = DB::getInstance();

$parents = fopen("families.txt", "r");

$contents = stream_get_contents($parents);
$arrRows = preg_split("/[\n\r]+/", $contents);

$num = 0;
foreach ($arrRows as $strLine) {
    $data = split("\t", $strLine);  

    $i = 0;
    $last = $data[$i++];
    $first = $data[$i++];
    $address = trim( $data[$i++] );
    
    $pos = strpos( $address, ' ' );
    $addressNum = substr( $address, 0, $pos );
        
    $city = $data[$i++];
    $city = str_replace( '"', '', $city );
    $temp = explode( ',', $city );
    $city = $temp[0];
    $state = $temp[1];
    $zip = $data[$i++];
    $tel = $data[$i++];
    $work = $data[$i++];
    $mobile = $data[$i++];
    $i++;
    $email = $data[$i];
    
    $user = strtolower( $last ) . $addressNum;
    $pass = 'parent';
    $sql = "insert into admins values( 
        '', '$user', '', '$pass', '', '$first', '$last', 'en', '$address', '', '$city', '$state', '$zip', 'USA', 
        '$work', '$tel', '$mobile', '$email', null, null, null, 0, 1, 1, '', ''  )";
    //echo $sql . "<br />";
    
    if ( $db->query( $sql ) ) {
        $num++;
    } else {
        print_r( $db->errorInfo() );
    }
     
}
echo "Added " . $num . " admins.";

fclose($parents);
?>
    </BODY>
</HTML>
