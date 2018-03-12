<?php
ini_set('display_errors',1);

$admin_auth = array('school');
require ( $_SERVER['DOCUMENT_ROOT'].'/header.php' );

require_once ( $_SERVER['DOCUMENT_ROOT'].'/class.globalSettings.php' );
$year = GlobalSettings::getChidonYear();

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Chidon CSV -> DBS uploader</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <style>
            label {
                width: 150px;
                font-weight: bold;
                display: inline-block;
            }
            hr {
                display: block;
            }
        </style>
    </head>
    
    <body>
        <?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); // $school_id is reset at this point if set above.?>
        <h1>CSV Chidon Uploader</h1>
        
        <p>
            Use this form to sync the Chidon Spreadsheets with the system to generate ID cards.
        </p>
        
        <form action="id_cards.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload"/>
            <label for="id_cards">ID Cards: </label>
            <input type="file" name="id_cards" id="id_cards" accept=".csv" />
            <input type="submit" value="Sync with CSV file" />
        </form>
        <br/>
        
        <form action="id_cards.php" method="post" accept-charset="UTF-8">
            <input type="hidden" name="action" value="generate"/>
            <strong>Download ID Cards Template (with data): </strong>
            <select name="gender">
                <option value="M">Boys Chidon</option>
                <option value="G">Girls Chidon</option>
            </select>
            <input type="submit" value="Download CSV File" />
        </form>
        
        <hr/>
        
        <form action="bunks.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload"/>
            <label for="bunks">Bunks: </label>
            <input type="file" name="bunks" id="bunks" accept=".csv" />
            <input type="submit" value="Sync with CSV file" />
        </form>
        <br/>
        
        <form action="bunks.php" method="post" accept-charset="UTF-8">
            <input type="hidden" name="action" value="generate"/>
            <strong>Download ID Cards Template (with data): </strong>
            <select name="gender">
                <option value="boys">Boys Chidon</option>
                <option value="girls">Girls Chidon</option>
            </select>
            <input type="submit" value="Download CSV File" />
        </form>
        
        <hr/>
        
        <form action="chaperones.php" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload"/>
            <label for="chaperones">Chaperones: </label>
            <input type="file" name="chaperones" id="chaperones" accept=".csv" />
            <input type="submit" value="Sync with CSV file" />
        </form>
        <br/>
        
        <form action="chaperones.php" method="post" accept-charset="UTF-8">
            <input type="hidden" name="action" value="generate"/>
            <strong>Download ID Cards Template (with data): </strong>
            <select name="gender">
                <option value="boys">Boys Chidon</option>
                <option value="girls">Girls Chidon</option>
            </select>
            <input type="submit" value="Download CSV File" />
        </form>
        
        <hr/>
        
        <a href="../IDcards/" class="button">Generate ID Cards Here</a>
    </body>
</html>