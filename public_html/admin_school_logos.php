<? $debug = false;
// enable debuging
if ($_GET['debug']) {
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
    $debug = true; // set debug to true
}

$admin_auth = array('school');
require_once( $_SERVER['DOCUMENT_ROOT'].'/header.php' ); // loads db.php, admin_auth.php, login.php....

/***************** IMPORTS **********************/
require_once( $_SERVER['DOCUMENT_ROOT'].'/file_save.php' ); // loads the file saving functions. we use saveFile(); see source for docs

// only superusers can access this page...
if( $admin_user['auth'] !== "super" ) {
    header("Location: /admin.php");
}

/***************** SAVE THE INFORMATION **********************/
// If we are posting new data to the server and have an image submitted...
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && count($_FILES) > 0 ) {
    $school_id = $_POST['school_id'];
    $school_name = $_POST['school_name'];
    // default to no new logos...
    $logo = false; $logo_2 = false;
    // get the first school id
    if( isset($_FILES["logo-$school_id"]) ) {
        $logo = saveFile($_FILES["logo-$school_id"], "schoolLogos/", $school_name . "_logo");
        $logo = $logo ? str_replace("schoolLogos/", "", $logo) : false; // remove the folder from the name as it will be added by whatever is accessing the file.
    }
    // get the second school logo if applicable...
    if( isset( $_FILES["logo_2-$school_id"] ) ) {
        $logo_2 = saveFile($_FILES["logo_2-$school_id"], "schoolLogos/", $school_name . "_logo_2");
        $logo_2 = $logo_2 ? str_replace("schoolLogos/", "", $logo_2) : false; // remove the folder from the name as it will be added by whatever is accessing the file.
    }
    
    // save the new logos to the database...
    if( $logo || $logo_2 ) {
        $logo_sql = "UPDATE schools SET ";
        // if we have the logo update that
        if($logo) $logo_sql .= " logo = '$logo' ";
        // if we have both add a comma
        if( $logo && $logo_2 )  $logo_sql .= ", ";
        // if we have the second logo save that too.
        if($logo_2) $logo_sql .= " logo_2 = '$logo_2' ";
        
        $logo_sql .= "WHERE school_id = '$school_id' ";
        
        mysql_query($logo_sql);
    }
}

// load all registered chayolei schools....
$schools_sql = mysql_query( "SELECT school_id, school_name, school_gender, logo, logo_2 FROM schools WHERE school_era IS NULL AND chayolei = '1' ORDER BY school_name" );
$schools = [];
while( $school = mysql_fetch_assoc( $schools_sql ) ){
    $schools[] = $school;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Master School Logo Report</title>
        <link href="/admin_styles.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
        <style>
            h2 { font-size: 1.3em; margin-bottom: 5px;}
            ol { list-style: decimal; line-height: 1.4; }
            .school_logos, .options { text-align: center; }
            .school_logo {
                display: inline-block;
                width: 45%;
                text-align: center;
                margin: 2.3% 0px;
                vertical-align: top;
            }
            .school_logo h3 { border-bottom: 1px solid; font-size: 1.15em; padding: 8px;}
            .school_logo img { margin: 15px; width: 250px; }
            
/*            custom file upload look */
            .inputfile { display: none; }
            .inputfile + label, button.submit {
                background: url(../images/bg_smallButton.png) repeat-x scroll 0 0 #D1D1D1;
                border: 2px solid;  border-color: #D3D3D3 #AAAAAA #888888;
                display: inline-block;  padding: 6px 10px;  margin: 3px 0;
            }
            .inputfile:focus + label,
            .inputfile + label:hover,
            button.submit:hover {
                background-position: bottom;
            }
/*          "hand" cursor */             
            .inputfile + label, button.submit:hover { cursor: pointer; }
/*            keyboard navigation... */
            .inputfile:focus + label { outline: 1px dotted #000; outline: -webkit-focus-ring-color auto 5px; }
/*           touch issues? */
            .inputfile + label * { pointer-events: none; }
            
            button.submit {
                font-size: 1.2em;
                padding: 6px 18px;
            }
        </style>
    </head>
    <body>
        <?php include($_SERVER["DOCUMENT_ROOT"].'/admin_header.php'); // $school_id is reset at this point if set above.?>
        
        <h1>Tzivos Hashem School Logos</h1>
        
        <p class="instructions">
            <strong>This form allows you to set logos for all registered chayolei schools in one place. Please note the following:</strong>
        </p>
        <ol>
            <li>You can only save one school at a time</li>
            <li>Images should be PNG with transparent background for best results.</li>
            <li>All images will be scaled up/down to 250px wide. Please consider this when uploading your logos.</li>
        </ol>
        
        <div class="options">
            <div class="row">
                <i class="fa fa-university" aria-hidden="true"></i> School: 
                <select id="school_id" name="school_id">
                    <option value="">All Schools</option>
                    <? foreach($schools as $school){?>
                        <option value="<?=$school['school_id']?>"><?=$school['school_name']?></option>
                    <?}?>
                </select>
            </div>
        </div>
        
        <?php foreach($schools as $school){
            $school_logo = isset($school['logo']) ? $school['logo'] : "TH-Blank%20Logo.gif";
            $school_gender = $school['school_gender'] == "B" ? "Mixed" : ( $school['school_gender'] == "M" ? "Boys" : "Girls" );?>
            <div class="school_logos" id="<?=$school['school_id']?>">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . ($debug ? "?debug=true" : "")?>" method="post" accept-charset="UTF-8" enctype="multipart/form-data">
                    <input type="hidden" name="school_id" value="<?= $school['school_id'] ?>">
                    <input type="hidden" name="school_name" value="<?= $school['school_name'] ?>">
                    <h2><?= $school['school_name'] ?> - <?= $school_gender ?></h2>
                    <div class="school_logo">
                        <h3><?=$school_gender === "Mixed" ? "Boys" : $school_gender;?></h3>
                        <img src="/schoolLogos/<?=$school_logo?>" alt="boys" id="img_logo-<?=$school['school_id']?>"/>
                        <div>
                            <input type="file" name="logo-<?=$school['school_id']?>" id="logo-<?=$school['school_id']?>" class="inputfile" />
                            <label for="logo-<?=$school['school_id']?>">
                                <i class="fa fa-upload" aria-hidden="true"></i> Choose a file
                            </label>
                        </div>
                    </div>
                    <?php if ($school_gender === "Mixed") { ?>
                        <div class="school_logo">
                            <h3>Girls</h3>
                            <img src="/schoolLogos/<?=$school['logo_2'] ? $school['logo_2'] : $school_logo?>" id="img_logo_2-<?=$school['school_id']?>" alt="girls"/>
                            
                            <div>
                                <input type="file" name="logo_2-<?=$school['school_id']?>" id="logo_2-<?=$school['school_id']?>" class="inputfile" />
                                <label for="logo_2-<?=$school['school_id']?>">
                                    <i class="fa fa-upload" aria-hidden="true"></i> Choose a file
                                </label>
                            </div>
                            
                        </div>
                    <?php } // end if school is mixed gender... ?>
                    <p>
                        <button class="submit"><i class="fa fa-cloud-upload" aria-hidden="true"></i> Save <?= $school['school_name'] ?></button>
                    </p>
                    
                </form>
            </div>
        <?} // end foreach school.... ?>
        
        <script>
            $("select#school_id").change(function ( event ) {
                var selected_id = event.target.value;
                
                if (!selected_id) {
                    $(".school_logos").show();
                } else {
                    $(".school_logos").hide();
                    $(".school_logos#"+selected_id).show();
                }
                
            });
            
            $(".inputfile").change( function( event ) {
                var input = event.target;
                
                if (input.files && input.files[0]) { // make sure a file was selected...
                    var reader = new FileReader(); // create a new reader
					
					reader.onload = function( e ) { // when the reader is loaded...
						$("#img_"+input.id).attr("src", e.target.result); // update the corrosponding image
					};
                    
					reader.readAsDataURL(input.files[0]); // read the first file that was provided...
                }
            });
        </script>
    </body>
</html>