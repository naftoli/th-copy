<?php
session_start();

$admin_auth = array('school','user'); 
require('header.php'); 

if (isset($_SESSION['admin'])) unset($_SESSION['admin']);
$_SESSION['admin_id'] = $admin_user['admin_id']; 

/*
require_once 'class.achosStudent.php';
$as = new AchosStudent($admin_user['admin_id']);
$userID = $as->getStudentID();

require_once 'class.achosPoints.php';
$p = new AchosPoints( $userID );
    
$school_id = $as->getSchoolID();
$ui_type = 'admin'; 

$student = true;
*/
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<HTML>

    <HEAD>
        <TITLE><?=T_('Admin Menu'), ' - ', T_('Tzivos Hashem Management System')?></TITLE>
        <LINK href="admin_styles.css" rel="stylesheet" type="text/css">
        <SCRIPT type="text/javascript" src="jquery.js"></SCRIPT>
        <script src="scripts/jquery.styleselect.js"></script>
        <STYLE type="text/css">
            .points {
              margin: auto;
              width: auto;
              font-size: 16px;
              margin-left: 39%;
            }

            .points tbody th {
              text-align: <?=$align_start?>;
            }

            .points tbody td {
              text-align: right;
            }
            
            .regInfo {
                float: right;
                width: 200px;
                border: 1px dashed red;
                height: 200px;
                padding: 5px;
                font-size: 12px;
            }
            
            .red {
                color: red;
                font-weight:bold;
            }
            .achos {
                font-size: 14px;
                line-height: 2.0;
            }
            .images div {
                float: left;
            }
            .images .small {
                margin-top: 20px;
                margin-right: 20px;
            }
        </STYLE>
        
        <script>            
            $(document).ready( function() {
                $(".photo a").click( function() {
                    $(".upload").html( "<form action='upload_photo.php' method='post' enctype='multipart/form-data'>" +  
                            "<br />Upload Picture:<br /><input type='file' name='photo' /><br />" + 
                            "<input type='hidden' name='admin_id' value='<?=$admin_user['admin_id']?>' />" + 
                            "<input type='submit' name='submit' value='submit' />" + 
                        "</form>" );
                });
            });

        </script>
    </HEAD>
    
    <BODY>

        <? include('admin_header.php'); ?>
                
        <DIV CLASS="body">          
            
            <DIV class="admin">
                
                <h1>Home Page</h1>
                                
                <? //foreach ($admin_user['auths']['school'] as $school_id) : ?>

                <H2>
                    <?=T_('My Dashboard for')?>:
                </H2>
                
                <div class="photo">
                    <? 
                    $p = "select photo from admins where admin_id = " . $admin_user['admin_id'];
                    $res = mysql_query($p);
                    $pRow = mysql_fetch_assoc($res);
                    $photo = $pRow['photo'];
                    if (empty($photo)) { ?>
                        <form action="upload_photo.php" method="post" enctype="multipart/form-data">
                            Upload Personal Picture:<br /><input type="file" name="photo" /><br />
                            <input type="hidden" name="admin_id" value="<?=$admin_user['admin_id']?>" />
                            <input type="submit" name="submit" value="submit" />
                        </form>
                    <? 
                    } else {
                        echo "<img src='images/staff/$photo' height='120' />";
                        echo "<br /><a href='#'>update photo</a>";
                        echo "<span class='upload'></span>";
                    } ?>
                </div>
                            
                <div style="clear: both"></div>
                
                <DIV>
                    <?=T_('Welcome, Commanding Officer')?>: <?=$admin_user['display']?>
                </DIV>
                             
				<h2></h2>
				                    
                <? //endforeach; ?> <!-- foreach ($admin_user['auths']['school'] as $school_id) -->

<? if ($admin->is_parent) : ?>
    <H2><?=T_('Welcome')?>, <?=$admin->first?> <?=$admin->last?></H2> 
    
    <?
    $info = $as->getInfo(); 
    
    $p = "select photo from admins where admin_id = " . $admin_user['admin_id'];
    $res = mysql_query($p);
    $pRow = mysql_fetch_assoc($res);
    $photo = $pRow['photo'];
    ?>
    
    <div class="images">
    	<div class="small">
            <img src="images/logo-achos-hatemimim.png" height="80"/>
        </div>
    	<div class="small" style="float: right;">
            <? 
            if (empty($photo)) { ?>
                <form action="upload_photo.php" method="post" enctype="multipart/form-data">
                    Upload Personal Picture:<br /><input type="file" name="photo" /><br />
                    <input type="hidden" name="admin_id" value="<?=$admin_user['admin_id']?>" />
                    <input type="submit" name="submit" value="submit" />
                </form>
            <? 
            } else {
                echo "<img src='images/staff/$photo' height='90' />";
                echo "<br /><a href='#'>update photo</a>";
                echo "<span class='upload'></span>";
            } ?>
        </div>
        
        <!--
        <div>
            <img src="images/icon-ribbon-colors-<?=$info['medal']?>.png" height="200" />
        </div>
        -->
               
    </div>

    <div style="clear: both"></div>

    <div class="points">
        <?=$info['daily']?> Points Today<br />
        <?=$info['weekly']?> Points this Week<br />
        <?=$info['points']?> Points Total
    </div>  
    
    <div style="clear: both"></div>
    <h2></h2>
    <div class="module_content achos">
        <? include_once 'home_text.php'; ?>
    </div> 
    
<? else : ?>
<? $student = false; ?>
	<script>
		$( function(){
			$("#buttons").hide();
			$("#edit").focus( function() {
				$("#buttons").show();
			});
			$("#save").click( function() {
				var t = $("#text").text().trim();
				$.post('ajax/saveText.php', {text : t}, function(data) {
					alert(data);
					if (data == 'saved.') {
						window.location.href = 'admin.php';
					}
				});
			});
		});
	</script>
	<div id='buttons' align="center">
		<button id="save">Save</button>
	</div>
	<div id="edit" contenteditable="true">
	    <div class="module_content achos">
	    	<div id="text">
	        	<? //include_once 'home_text.php'; ?>
	       </div>
	    </div>
	</div>
    
<? endif; ?>

            </DIV>
            
        </DIV>

        <? include('admin_footer.php'); ?>
        
    </BODY>
    
</HTML>
