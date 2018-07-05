<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
$admin_auth = array('school');
require('header.php');

require_once( __DIR__ . "/classes/admin.php" );
require_once( __DIR__ . "/classes/school_class.php" );
$query = mysql_query(
    "SELECT * FROM admins WHERE admin_id=" . $admin_user['admin_id']
);
$admin = new admin( mysql_fetch_assoc( $query ) );
$admin->get_schools();

if( count($_POST) > 0 && isset( $_POST['teachers'] ) ) {
    foreach( $_POST['teachers'] as $class_id => $teacher ) {
        $class_id = ms( $class_id );
        $class_teacher = ms($teacher['class_teacher']);
        $email = ms($teacher['email']);
        $cell = ms($teacher['cell']);
        $sql = "UPDATE classes SET class_teacher=$class_teacher, email=$email, cell=$cell, confirmed=1 "
            ."WHERE class_id = $class_id;";
        mysql_query( $sql );
    }
    $classes_confirmed = true;
} else {
    $school_ids = implode( ', ', array_map( 
        function( $school ) { return $school->school_id; }, 
        $admin->schools 
    ));
    $confirmed_query = mysql_query(
        "SELECT COUNT(*) as total FROM classes WHERE school_id IN ($school_ids) AND confirmed = 0"
    );
    $classes_confirmed = mysql_fetch_assoc($confirmed_query)['total'] == 0;
}

foreach( $admin->schools as $school ){
    $school->get_classes();
}
?>
<!DOCTYPE html>
<html DIR="<?=$dir?>">
<head>
    <title><?=T_('Platoon Transition'), ' - ', T_('Tzivos Hashem Management System')?></title>
    <link href="admin_styles.css" rel="stylesheet" type="text/css">
    <link href="styles/admin/grey_select.css" rel="stylesheet" type="text/css">
    <link href="styles/admin/loader.css" rel="stylesheet" type="text/css">
    <style>
        table { width: 100% }
        th, td { padding: 4px 8px; border: 1px solid #888; }
        #step2 td:first-child { width: 50px; text-align: center; }
        #step3 td { /* width: 50%; */ text-align: center; padding: 15px 0px ; }
        #step4 { text-align: center; }
        small { font-size: .7em; }
        #confirm-teachers input[type="text"],
        #confirm-teachers input[type="email"],
        #confirm-teachers input[type="tel"]
        { width: 100%; background: none; border: none; border-bottom: 1px solid; }
        a.button { display: inline-block; margin-top: 10px; }
    </style>
</head>
<body>
    <?php include_once('admin_header.php');?>
    <h1><?=T_('Platoon Transition')?></h1>

    <?php if ( $classes_confirmed ) { ?>
        <div class="infobox">
            <p><strong>Platoon Transition allows you to setup a large scale transition for multiple soldiers in your bases.</strong></p>
            <p>Select a platoon to see the current status of all soldiers in the platoon.</p>
            <p>Select one or more soldiers in a platoon and select one of the options in step 3 to set this change during the platoon transition process.</p>
            <p>Once you have finished configuring where you want all the soldiers to be moved to just press the "Make Live" button in step 4 to update all soldiers at once.</p>
        </div>

        <div id="step1">
            <h2>Step 1: Select Platoon</h2>
            <label for="platoon">Platoon:</label>
            <select id='platoon'>
                <?php foreach( $admin->schools as $school ) { ?>
                    <optgroup label="<?=$school->school_name?>">
                        <option value='<?=$school->school_id?>-0'>No Platoon</option>
                    <?php foreach( $school->classes as $platoon ) { ?>
                        <option value='<?=$school->school_id?>-<?=$platoon->class_id?>'><?=$platoon->name()?></option>
                    <?php } ?>
                    </optgroup>
                <?php } ?>
            </select>
        </div>

        <div id="step2">
            <h2>Step 2: Select Soldiers</h2>
            <div class="loader"></div>
            <table>
                <thead>
                    <tr>
                        <th>Selected</th>
                        <th>Name</th>
                        <th>Transitioning To</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>

        <div id="step3">
            <h2>Step 3: Select Option For Selected Soldiers:</h2>
            <table>
                <tbody>
                    <tr>
                        <td>
                            <label for="platoon-move">Select Platoon:</label>
                            <select id='platoon-move'>
                            <?php foreach( $admin->schools as $school ) { ?>
                                <optgroup label="<?=$school->school_name?>">
                                <?php foreach( $school->classes as $platoon ) { ?>
                                    <option value='<?=$school->school_id?>-<?=$platoon->class_id?>'><?=$platoon->name()?></option>
                                <?php } ?>
                                </optgroup>
                            <?php } ?>
                            </select>
                            <a class="button" id="change-platoon">Move</a>
                        </td>
                        <td>
                            <a class="button" id="school-remove">
                                Remove from School.
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div id="step4">
            <h2>Step 4: Make updates Live</h2>
            <a class="button" id="make-live">Deploy Platoon Transition</a>
        </div>

        <script src="/js/admin/platoonTransitionApp.js"></script>
    <?php } else { ?>
        <h1><?=T_('Platoon Transition - Confirm Teachers')?></h1>
        <div class="infobox">
            <p>Before you begin Platoon Transition you must confirm all your teachers information below:</p>
            <p><strong>Please note that this information is for communication and presentation purposes only and does not have to match your teacher logins.</strong></p>
            <p>
                If you have multiple teachers, please note that coming soon both will be able to login and have their accounts tied to the platoon. 
                This information is what is shown to the soldiers (Teacher Name) as well as on all platoon reports (e.g. WWTC).
            </p>
        </div>
        <form id='confirm-teachers' method='post'>
            <?php foreach(  $admin->schools as $school ) { ?>
                <h2><?=$school->school_name?></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Platoon</th><th>Teacher Name</th>
                            <th>Teacher E-Mail</th><th>Teacher Cell Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $school->classes as $platoon ){?>
                            <tr>
                                <td><?=$platoon->name()?></td>
                                <td>
                                    <input required type="text"
                                        name='teachers[<?=$platoon->class_id?>][class_teacher]' 
                                        value="<?=$platoon->class_teacher?>" />
                                </td>
                                <td>
                                    <input required type="email"
                                        name='teachers[<?=$platoon->class_id?>][email]' 
                                        value="<?=$platoon->email?>" />
                                </td>
                                <td>
                                    <input required type="tel"
                                        name='teachers[<?=$platoon->class_id?>][cell]' 
                                        value="<?=$platoon->cell?>" />
                                </td>
                            </tr> 
                        <?php } ?>
                        </tbody>
                    </table>
            <?php } ?>
            <input class="button" type='submit' value='Confirm Teacher Information'/>
        </form>
        <script>
            $('#confirm-teachers').submit( function( event ){
                if ( !confirm("Yes I have confirmed all the teachers information is correct and up to date") )
                    event.preventDefault();
            });
        </script>
    <?php } ?>
    <? include('admin_footer.php'); ?>
</body>
</html>
