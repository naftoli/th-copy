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
            h2 {
                font-size: 1.3em;
            }
            .half {
                width: 49.6%;
                display: inline-block;
            }
            .third {
                width: 32%;
                display: inline-block;
            }
            .option {
                font-size: 1.1em;
                text-align: center;
                margin-bottom: 10px;
            }
            select#grade, select#gender, input[type='submit'] {
                font-size: .8em;
            }
            input[type='text'] {
                background: none;
                border: none;
                border-bottom: 1px solid;
                padding: 2px;
                font-size: 1em;
                width: 50%;
            }
        </style>
    </head>
    
    <body>
        <?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); // $school_id is reset at this point if set above.?>
        <h1>Generate ID Cards</h1>
        
        <h2><i class="fa fa-graduation-cap" aria-hidden="true"></i> Student ID Cards</h2>
        
        <form action="generate.php" method="GET" target="_blank">
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
        
        <h2><i class="fa fa-user" aria-hidden="true"></i> Chaperone ID Cards</h2>
        
        <form action="generate.php" method="GET" target="_blank">
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
        
        <h2><i class="fa fa-bus" aria-hidden="true"></i> Bunk ID Cards</h2>
        
        <form action="generate.php" method="GET" target="_blank">
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
        
        <h2><i class="fa fa-certificate" aria-hidden="true"></i> Student Certificates</h2>
        
        <p>Please use <strong>Mozilla FireFox</strong> with the follwing print settings:</p>
        <ul>
            <li>Margins: 0 for all 4 sides (top, bottom, right and left).</li>
            <li>Scale: 100%</li>
            <li>Paper Size: Letter (8.5"x11")</li>
        </ul>
        <br/><br/>
        
        <form action="certs/generate.php" method="GET" target="_blank">
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
        
        <h2><i class="fa fa-id-badge" aria-hidden="true"></i> Custom ID Cards</h2>
        
        <form action="generate.php" method="GET" target="_blank">
            <input type="hidden" name="type" value="custom"/>
            <div class="option half">
                <label for="title"><i class="fa fa-font" aria-hidden="true"></i> Badge Title: </label>
                <input type="text" name="title" id="title"/>
            </div>
            <div class="option half">
                <i class="fa fa-users" aria-hidden="true"></i> Shabbaton:
                <select id="gender" name="gender">
                    <option value="M">Boys</option>
                    <option value="F" selected>Girls</option>
                </select>
            </div>
            
            <div class="option half">
                <label for="name"><i class="fa fa-user" aria-hidden="true"></i> Name: </label>
                <input type="text" name="name" id="name"/>
            </div>
            
            <div class="option half">
                <label for="id_number"><i class="fa fa-id-badge" aria-hidden="true"></i> ID Number: </label>
                <input type="text" name="id_number" id="id_number"/>
            </div>
            
            <div class="option half">
                <label for="school_name"><i class="fa fa-university" aria-hidden="true"></i> School Name: </label>
                <input type="text" name="school_name" id="school_name"/>
            </div>
            
            <div class="option half">
                <label for="school_location"><i class="fa fa-map-marker" aria-hidden="true"></i> School Location: </label>
                <input type="text" name="school_location" id="school_location"/>
            </div>
            
            <div class="option third">
                <label for="team"><i class="fa fa-users" aria-hidden="true"></i> Team: </label>
                <input type="text" name="team" id="team"/>
            </div>
            <div class="option third">
                <label for="bunk"><i class="fa fa-bus" aria-hidden="true"></i> Bunk: </label>
                <input type="text" name="bunk" id="bunk"/>
            </div>
            <div class="option third">
                <label for="grade"><i class="fa fa-graduation-cap" aria-hidden="true"></i> Grade: </label>
                <input type="text" name="grade" id="grade"/>
            </div>
            
            
            <div class="option">
                <input type="submit" value="Generate"/>
            </div>
        </form>
        
    </body>
</html>