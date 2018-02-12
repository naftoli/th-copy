<?
if ($_FILES['photo']['name'] && $_POST['admin_id']) {
    $destination = "images/staff/" . $_FILES['photo']['name'];
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
        //save into db
        require_once 'db.php';
        $sql = "update admins set photo = '" . $_FILES['photo']['name'] . "' where admin_id = " . $_POST['admin_id'];
        if (mysql_query($sql))
            header("Location: admin.php");             
        else
            echo "could not execute query."; 
    }
} else {
    header("Location: admin.php");
}
?>