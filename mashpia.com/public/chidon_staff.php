<?php
// ini_set('display_errors', 1);
// redirect to https
if ((empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') || $_SERVER['SERVER_PORT'] != 443) {
    header("Location: https://" . $_SERVER['SERVER_NAME'] . "chidon_staff.php");
}
//********************* AUTHENTICATION *********************//
$admin_auth = array('school'); 
require('header.php');

// and get the chidon year....
require_once 'class.globalSettings.php';
$year = GlobalSettings::getChidonYear();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Chidon Staff | Tzivos Hashem</title>
        <link href="admin_styles.css" rel="stylesheet" type="text/css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<!--        Modal and other form elements -->
        <link href="/styles/admin/modal.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/forms.css" rel="stylesheet" type="text/css"/>
        <!--        Rotating Spinner, grey dropdowns and fancy checkboxes... -->
        <link href="/styles/admin/loader.css" rel="stylesheet" type="text/css"/>
        <link href="/styles/admin/grey_select.css" rel="stylesheet" type="text/css"/>
        <style type='text/css'>
            table {font-size: 12px;width: 100%;}
            th, td {padding: 3px 5px;}
            caption {border-bottom: dashed 1px black;}
            .options{text-align: center;}
            
            div.fullProgram {
                font-size: 14px;
                line-height: 1.5;
            }
            div.fullProgram ul {
                margin-left: 10px;
                list-style-type: circle;
                font-style: italic;
            }
            a.button{display: inline-block;}
            a#next_page{float: right;margin-bottom: 20px;}
            a#prev_page{float: left;}
            .warning{
                font-size: 16px; font-weight: bold; color: red;
            }
            div#chaperones {
                margin-bottom: 25px;
            }
            div h3 {
                margin: 10px 0px;
                padding: 3px 10px;
                font-size: 1.2em;
                border-bottom: 1px solid #888;
            }
            .s-size {
                width: 95%;
                margin-left: 5%;
                margin-top: 10px;
            }
            .s-size select {
                width: auto;
            }
            .modal-content {
                width: 850px;
                margin: 5% auto;
            }
        </style>
    </head>

    <body>
        <? include('admin_header.php'); ?>
        <?php include($_SERVER['DOCUMENT_ROOT']."/chidon_passwords.php"); // require a password to use this page... ?>
        <h1>Chidon Staff</h1>

<!--         
        <div class="modal" id="chap_modal">
            <div class="modal-content">
                <h1>
                    <span id="heading">Create</span> Staff Member
                    <span class="close" id="update_cc_exit">×</span>
                </h1> -->

                <form>
                    <h3>Staff Info</h3>
                    <div class="input_group input_half">
                        <label>
                            First Name<br/>
                            <input type="text" id="first_name" name="first_name" required />
                        </label>
                    </div>
                    <div class="input_group input_half">
                        <label>
                            Last Name<br/>
                            <input type="text" id="last_name" name="last_name" required />
                        </label>
                    </div>
                    <div class="input_group input_half">
                        <label>
                            Cell Number<br/>
                            <input type="text" id="number" name="number" required />
                        </label>
                    </div>
                    <div class="input_group input_half">
                        <label>
                            E-Mail<br/>
                            <input type="text" id="email" name="email" required />
                        </label>
                    </div>
                    <div class="input_group input_half ">
                        <label>
                            DOB<br/>
                            <input type="date" id="dob" name="dob" min="1950-01-01" max="2009-12-31" required />
                        </label>
                    </div>
                    <div class="input_group input_half edit_chidon_info">
                        <label>
                            Sweater Size<br/>
                            <select name="s_size" id="s_size">
                                <option value="s">Small</option>
                                <option value="m">Medium</option>
                                <option value="l">Large</option>
                                <option value="xl">XLarge</option>
                                <option value="xxl">XXLarge</option>
                            </select>
                        </label>
                    </div>
                    
                    <div class="input_group input_half">
                        <label>
                            Chidon Type<br/>
                            <select name="chidon_type" id="chidon_type">
                                <option value="boys">Boys</option>
                                <option value="girls">Girls</option>
                            </select>
                        </label>
                    </div>

                    <div class="input_group input_half">
                        <label>
                            Position<br/>
                            <select name="position" id="position">
                                
                            </select>
                        </label>
                    </div>
                    
                    <h3>Accommodation Info</h3>
                    <div class="input_group input_half">
                        <label>
                            Name<br/>
                            <input type="text" id="accName" name="accName" required />
                        </label>
                    </div>
                    <div class="input_group input_half">
                        <label>
                            Address<br/>
                            <input type="text" id="accAddress" name="accAddress" required />
                        </label>
                    </div>
                    
                    <div class="input_group input_half">
                        <label>
                            Phone Number<br/>
                            <input type="text" id="accPhone" name="accPhone" required />
                        </label>
                    </div>
                    <div class="input_group input_half">
                        <label>
                            Vehicle<br/>
                            <input type="radio" name="vehicle" class="vehicle vehicle_0" value="0" /> NO
                            <input type="radio" name="vehicle" class="vehicle vehicle_1" value="1" /> YES
                        </label>
                    </div>
                    
                    <div class="input_group input_full" style="line-height: 1.4">
                        <h3>Terms</h3>
                        <input type="checkbox" name="terms" id="terms" />
                        I understand that I will be responsible to take a group of chayolim home on Thursday night, Friday afternoon, and Motzei Shabbos after the program. 
                    </div>
                    
                    <div style="clear: both"></div>
                    <div class="input_group input_full" style="text-align: center">
                        <input type="submit" name="submit" value="Create Staff Member" style="padding: 10px;" />
                    </div>
                <!-- </form>
            </div>
        </div> -->
    </body>
    <script>
        const types = ['walking supervisor', 'medical coordinator', 'safety coordinator', 'grade commander', 'head councillor', 'head councillor assistant', 'councillor', 'on site director', 'head runner', 'runner'];
        types.sort();
        let html = '';
        for ( let type of types ) {
            html += "<option>" + type + "</option>";
        }
        $("#position").append( html );

        $("form").submit( e => {
            e.preventDefault();
            const fields = ['first_name', 'last_name', 'number', 'email', 'dob', 's_size', 'chidon_type', 'accName', 'accAddress', 'accPhone', 'position'];
            let values = [];
            for ( let f of fields ) {
                values[f] = $("#" + f).val();                
            }
            if ( !$(".vehicle:checked").length ) {
                alert("You must whether you have a vehicle or not.");
                return false;
            } else {
                values['vehicle'] = parseInt( $(".vehicle:checked").val() );
            }
            if ( !$("#terms:checked").length ) {
                alert("You must agree to terms.");
                return false;
            }
            console.log( values );
            $.post("ajax/chidon/createStaff.php", values, success => {
                const res = JSON.parse( success );
                if ( res.success ) {
                    alert("Saved.");
                } else {
                    alert( res.error );
                }
            });
        });
    </script>
</html>