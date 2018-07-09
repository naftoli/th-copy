<?  // allows schools to upload class files to server // ?>
<? ini_set('display_errors', TRUE); ?>
<? ini_set('max_execution_time', 300); ?>
<? $admin_auth = array('school'); ?>
<? require('header.php'); ?>
<?
$ui_type = 'school';
require_once('admin_ui.php');
require_once('file_save.php');

// contains email addresses
require_once('constant_file.php');

?>
<!DOCTYPE html>
<html dir="<?=$dir?>">
<head>
    <title><?=T_('School or Class Upload'), ' - ', T_('Tzivos Hashem Management System')?></title>
    <link href="admin_styles.css" rel="stylesheet" type="text/css">
    <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css">
    <style>
        th, td { font-size: 12px; padding-right: 10px;}
    </style>
</head>
<body>
    <?php include('admin_header.php'); ?>
    <h1><?=T_("Base Management")?></h1>
    <h2><?=T_('School Or Class Upload')?></h2>
    <div class="infobox">
        <p>
            Directions:<br />
            1. Download the <a href="students.xls" style="background-color: lightblue;">spreadsheet</a>.<br />
            2. Enter all information into spreadsheet.<br />
            <strong>Please Note: You MUST have the First Name, Last Name, First Name Hebrew, Last name Hebrew, English Date of Birth, Gender, and Mission Type fields of each student filled out.</strong>
            <br />3. Upload spreadsheet into system.<br />
        </p>
    </div>
    <? $row = mysql_fetch_assoc(mq("SELECT school_file_id FROM schools WHERE school_id = $school_id")); ?>

    <div class="box_content" style="border-color: #9ac9e1">
        <form action="admin_school_file.php" method="post" accept-charset="UTF-8" 
            enctype="multipart/form-data" id='file-form'>
            <input type="hidden" name="school_id" value="<?=$school_id?>">
            <label><?=T_('Upload your class / school list')?><br/>
                <input type="file" name="users" class="file" style="opacity:1;" required>
            </label>
            <?=T_('Maximum file size')?>: <?=bytes2units(maxFileSize())?>B<br/>
            <br/>
            <label>
                <input type="checkbox" id='confirm' required> 
                <?=T_('I confirm that these students have never been uploaded before and all fields follow the exact field in the spreadsheet. 
                        I understand that if I have not followed the directions correctly, my information will not be added to the system and 
                        I will need to re-upload it.')?>
            </label><br/>
            <br/>
            <input type="hidden" name="hschool" value="<?=$h_school?1:0?>" />
            <input class="submit" type="submit" name="save" value="<?=T_('Save')?>">
        </form>
    </div>
    <br style="clear: both;">
    <div class="box_content" style="border-color: #9ac9e1; display: none;" id="errros"></div>
    <div class='loader' style='display:none'></div>
    <? include('admin_footer.php'); ?>
    <script>
        $('#file-form').submit( function( event ){
            event.preventDefault();
            var formData = new FormData( event.target ); // get the formdata
            
            function handleResponse( response ) {
                // clear the form
                $('.loader').hide();
                $('#file-form input[type="file"]').val('');
                $('#file-form input[type="checkbox"]').attr('checked', false);
                // parse the response
                if ( !response.success ){
                    $('#errros').html(
                        response.error + '<br/><br/>' + response.data.join('<br/>')
                    ).show();
                } else {
                    $('#errros').html(
                        'Spreadsheet sucessfully uploaded and parsed.<br/>' + 
                        'The Soldiers should now be created for your base.<br/>' +
                        'Please visit <a href="/admin_class_transition.php">Platoon Transition</a> to assign them to a platoon.'
                    ).show();
                }
            }
            $('.loader').show();
            $('#errros').hide();
            postJSON( '/api/upload/users.php', formData, handleResponse );
        });
        // Since we are stuck on Jquery 1.4 we cannot use AJAX to send FormData
        function postJSON(url, data, callback) {
            var xhr = new XMLHttpRequest();

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    callback( JSON.parse( xhr.response ) );
                }
            }

            xhr.open('POST', url, true);
            xhr.send( data );
        }
    </script>
</body>
</html>
