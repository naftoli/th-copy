<?php
// AUTHENTICATION
$admin_auth = array('school');
require_once( $_SERVER['DOCUMENT_ROOT'] . '/header.php' );

// load up the school ID's for the user
require_once( $_SERVER['DOCUMENT_ROOT'] . '/class.adminSchools.php' );
$as = new AdminSchools($admin_user['admin_id'], $admin_user['auth']);
$schools = $as->getSchools();
$classes = [];

// if we only have one school, pre-render the list
if ( count( $schools ) == 1 ) {
    $school_id = array_keys( $schools )[0];

    $classes_query = mysql_query(
        "SELECT class_id, class_grade, class_sub
        FROM classes
        WHERE school_id = " . $school_id . " AND class_era = 0 
        ORDER BY class_grade, class_sub;"
    );

    while( $class = mysql_fetch_assoc( $classes_query ) ) {
        $classes[] = [ 
            "class_id"  => $class['class_id'],
            "name"      => $class['class_grade'] . ( $class['class_sub'] ? " - ". $class["class_sub"] : ""),
            "students"  => $class['students']
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tzivos Hashem | Create Tanya Soldiers</title>
    <link href="/admin_styles.css" rel="stylesheet" type="text/css">
    <link href="/reports/inc/css/report.css" rel="stylesheet" type="text/css">
<!--        Rotating Spinner, grey dropdowns and fancy checkboxes... -->
    <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
    <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
<!--        Nice quick icons... -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <style>
        .options { margin-bottom: 10px; }
        p#info { padding: 15px; border: 1px solid; border-radius: 15px; background: #eee; font-size: 1em; margin-bottom: 30px; }
        input#user_count { border: none; background: none; border-bottom: 1px solid; font-size: 1em; }
    </style>
</head>
<body>
    <?php include( $_SERVER['DOCUMENT_ROOT'] . '/admin_header.php'); ?>
    <h1>Create Tanya Soldiers</h1>
    <p id="info">
        This form will allow you to create users in bulk for your grades already enrolled in the Tanya Campaign.<br/>
        <strong>Please note:</strong> This form will create regular Tzivos Hashem users. Please use with caution.
    </p>

    <?php
    // render the list of schools if there are more then one
    if ( count($schools) == 1 ) {?>
        <select id="school_id" name="school_id" class="hidden" disabled>
            <option value="<?=array_keys($schools)[0]?>"><?=array_values($schools)[0]?></option>
        </select>
    <?php } else { ?>
        <div class="options">
            <div class="row">
                <i class="fa fa-university" aria-hidden="true"></i> Limit To School: 
                <select id="school_id" name="school_id">
                    <option value="" selected>All Schools</option>
                    <? foreach($schools as $school_id => $school_name){?>
                        <option value="<?=$school_id?>"><?=$school_name?></option>
                    <?}?>
                </select>
            </div>
        </div>
    <?php } ?>

    <form id="tanya_soldiers">
        <div class="options">
            <i class="fa fa-graduation-cap" aria-hidden="true"></i> 
            <label for="class_id">Class: </label>
            <select id="class_id" required>
                <option id="not_selected" value="" selected disabled>
                    Please select a <?= count( $classes ) ? "class" : "school above"?>
                    <?php foreach ( $classes as $class ) { ?>
                        <option value="<?=$class['class_id']?>"><?=$class['name']?></option>
                    <?php } ?>
                </option>
            </select>
        </div>

        <div class="options">
            <i class="fa fa-users" aria-hidden="true"></i> 
            <label for="user_count">Number of New Soldiers: </label>
            <input type="number" id="user_count" required> <br/>
        </div>
        <div class="options">
            <input type="submit" value="Create"/>
        </div>
    </form>

    <div id="status"></div>

    <div class="options">
        <a class="button" href="/editSoldierLines2.php" style="float: right;">
            Mark Tanya Lines <i class="fa fa-arrow-right" aria-hidden="true"></i>
        </a>
    </div>
    <script>
    // small script to make the page more responsive
    $("select#school_id").change( function( event ) {
        $.get("/ajax/getClasses.php?flat=true&id=" + event.target.value, function( response ){
            response = JSON.parse( response );
            $("option#not_selected").text("Please select a class"); // update the text in the class selector
            // start with the <option /> HTML that we want to keep
            var optionsHTML = $("option#not_selected")[0].outerHTML;

            for ( class_number in response ) {
                option = response[class_number];
                optionsHTML += "<option value='" + option[0] + "'>" + option[1] + "</option>";
            }

            $("#class_id").html(optionsHTML);
            $("#class_id").val(""); // reset the value
        })
    });

    $("form#tanya_soldiers").submit( function( event ){
        event.preventDefault();
        
        var school_id = $("#school_id").val();
        var class_id = $("#class_id").val();
        var user_count = $("#user_count").val();

        // make sure that they selected a class
        if ( !class_id || !school_id ) {
            alert( "You have to select a class" );
            return false;
        }

        var postData = {
            class_id: class_id, 
            user_count: user_count, 
            school_id: school_id 
        }

        $("#status").html("<div class='loader'></div>"); // show the loading spinner

        $.post("/ajax/createTanyaSoldiers.php", postData, function( response ){
            response = JSON.parse( response );
            
            if( response.success ){
                $("#status").html("");
                $("#status").text(" Created " + postData.user_count + " new Tanya Users! ");
            } else {
                alert ( response.error );
            }
        });
    })
    </script>
</body>
</html>