<?php
if ( !isset( $_REQUEST['id'] ) || !isset( $_REQUEST['school'] ) || $_REQUEST['id'] == 0 || $_REQUEST['school'] == 0 ) {
    header("Location: index.php");
    exit;
}

$admin_auth = ['school'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/header/db.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mivtzoim/classes/mivtzoim.php';

$m = new Mivtzoim( $_REQUEST['id'] );
$r = new MivtzoimReport( $m );
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Mivtzoim Report</title>
        <link href="../mivtzoim.css" rel="stylesheet" type="text/css">
        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <!-- dataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/dataTables.bootstrap4.min.css" />
        <!-- Fontawesome 5 -->
        <script defer src="https://use.fontawesome.com/releases/v5.0.8/js/all.js" integrity="sha384-SlE991lGASHoBfWbelyBPLsUlwY1GwNDJo3jSJO04KZ33K2bwfV9YBauFfnzvynJ" crossorigin="anonymous"></script>
        <!-- Source Sans Pro font -->
        <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro" rel="stylesheet">
    </head>
    <body>
        <?//= require $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'; ?>
        <h1>Mivtzoim Leaderboard</h1>

        <?php 
        $r->createIndividualBoard( $_REQUEST['school'] );
        $num_users = $r->getNumUsers();
        ?>
    </body>

    <!-- Bootstrap Javascript -->
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <!-- Table Sorting JS -->
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(document).ready(function () {
            var num_users = <?= $num_users ?>;
            $('#leaderboard').DataTable({
                'pageLength': num_users
            });
        });
    </script>
</html>