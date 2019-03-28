<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>

    <body>
<?
require_once 'db.php';
require_once 'class.birthday.php';
$b = new Birthday();
$b->setBirthday();

echo "<pre>";
if ( $errors = $b->getErrors() )
    print_r( $errors );
else 
    echo "No errors.";
echo "</pre>";
?>
    </body>
</html>