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
        <title>Generate Chidon ID Cards</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <style>
            .half {
                width: 49.6%;
                display: inline-block;
            }
            .option {
                font-size: 1.5em;
                text-align: center;
                margin-bottom: 30px;
            }
            select#grade, select#gender, input[type='submit'] {
                font-size: .8em;
            }
        </style>
    </head>
    
    <body>
        <?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); // $school_id is reset at this point if set above.?>
        <h1>Generate ID Cards</h1>
        
        <h2>Student ID Cards</h2>
        
        <form action="generate.php" method="GET">
            <input type="hidden" name="type" value="student"/>
            <div class="option half">
                <i class="fa fa-graduation-cap" aria-hidden="true"></i> Grade:
                <select id="grade" name="grade">
                    <option value="" >All Grades</option>
                    <option value="4">4th Grade </option>
                    <option value="5">5th Grade </option>
                    <option value="6">6th Grade </option>
                    <option value="7">7th Grade </option>
                    <option value="8">8th Grade </option>
                </select>
            </div>
            
            <div class="option half">
                <i class="fa fa-venus-mars" aria-hidden="true"></i> Gender:
                <select id="gender" name="gender">
                    <option value="" >All Genders</option>
                    <option value="M">Boys</option>
                    <option value="F">Girls</option>
                </select>
            </div>
            <div class="option">
                <input type="submit" value="Generate"/>
            </div>
        </form>
        
        <h2>Chaperone ID Cards</h2>
        
        <form action="generate.php" method="GET">
            <input type="hidden" name="type" value="chaperone"/>
            <div class="option">
                <i class="fa fa-users" aria-hidden="true"></i> Shabbaton:
                <select id="gender" name="gender">
                    <option value="" >Both</option>
                    <option value="M">Boys</option>
                    <option value="F">Girls</option>
                </select>
            </div>
            <div class="option">
                <input type="submit" value="Generate"/>
            </div>
        </form>
        
        <h2>Bunk ID Cards</h2>
        
        <form action="generate.php" method="GET">
            <input type="hidden" name="type" value="bunk"/>
            <div class="option">
                <i class="fa fa-users" aria-hidden="true"></i> Shabbaton:
                <select id="gender" name="gender">
                    <option value="" >Both</option>
                    <option value="M">Boys</option>
                    <option value="F">Girls</option>
                </select>
            </div>
            <div class="option">
                <input type="submit" value="Generate"/>
            </div>
        </form>
        
    </body>
</html>