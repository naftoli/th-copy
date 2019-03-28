<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tzivos Hashem | Missing Email</title>        
        <link rel="alternate" media="print" href="index.php">
        <link href="/camps/styles/reset.css" rel="stylesheet" type="text/css" />
        <link href="/camps/styles/styles.css" rel="stylesheet" type="text/css" />
        <script src="/camps/scripts/jquery.tools.min.js"></script>
        <style>
            #nav ul.list_first  li, .side_menu li  {
                background:url("/images/bg_gradientTrans.png") repeat-x scroll 0 25% #dadee6;
                border-top:1px solid #fff;border-bottom:1px solid #787e85;
            }
            #nav ul.list_first  li:hover, .side_menu li:hover {background:url("/images/bg_gradientTrans.png") repeat-x scroll 0 25% #cfdbf3;}
            #nav ul.list_second ul, #nav .list_small .list_second   {
                background: #D5D8DE;position:absolute;margin-left:220px;z-index:1;
                width:250px;top:-1px;display:none;box-shadow:0px 0px 10px #666;
            }
            #nav ul.list_second ul li {background-color:#cbd4e6;}
            #nav ul.list_second li:hover ul   {display:block;}
            #nav li.submenu > a {background:url("/images/icon_control_right.png") no-repeat scroll 95% 50% transparent;}
            #nav ul.list_second  li a {line-height:1.6;}
            #nav ul.list_second > li, #nav ul.list_small > li {padding-left:30px;position:relative;}
            .module h1{padding: 15px;}
            form li input[type="submit"] {padding: 5px;margin: 5px;}
            form li input[type="submit"]:hover{cursor: pointer;}
		</style>
    </head>
    <body>
        <div id="wrapper">
            <div id="nav">		
                <div class="col_title_bg"></div>
                <div class="col_title">Menu</div>
                <ul class="list_first">
                    <li class="list_parent current">
                        <a href="#"><img src="/images/icon_door_in.png" width="28" height="28" alt="Login" />Login</a>
                    </li>
                    <li>
                        <a href="/helpdesk" title="support"><div><span class="icon"><img height="28" width="28" alt="Support" src="/images/parentIcons/support icon.gif"></span>Support</div></a>
                    </li>
                    <li>
                        <a href="https://www.mashpia.com/donate.php"><div><span class="icon"><img height="28" width="28" alt="Donate" src="/images/parentIcons/donate icon.gif"></span>Donate</div></a>
                    </li>
                    <li>
                        <a href="/logout.php"><div><span class="icon"><img height="28" width="28" alt="LogOut" src="/images/parentIcons/logout.gif"></span>Log Out</div></a>
                    </li>
                </ul>
            </div>
            <div id="content">
                <div class="col_title_bg"></div>
                <div class="slider_container">
                    <div class="slider">
                        <div class="col_title"><span>Tzivos Hashem | Missing Email-Address</span></div>
                        <div class="col_content">
                            <h1>Chayolei Tzivos Hashem School and Camp Login</h1>
                            
                            <div class="module" id="module-info">
                                
                                <h1>Please enter a valid email address:</h1>
                                    
                                <div class="module_content">
                                    
                                    <div class="list form">
                                            
                                        <form action="" method="post" id="new_email" accept-charset="UTF-8" name="new_email">
                                            <ul>
                                                <li>
                                                    <span class="icon bullet"></span>
                                                    <span class="label"><label for="email">E-mail Address</label></span>
                                                    <span class="input">
                                                        <input type="email" name="email" id="email" required>
                                                    </span>
                                                    <div class="clear"></div>
                                                </li>
                                                <li>
                                                    <input type="submit" id="update_email" value="Update"/>
                                                    <div class="clear"></div>
                                                </li>
                                            </ul>
                                        </form>
                                    </div> <!-- end .list.form -->
                                </div> <!-- end .module_content -->
                            </div> <!-- end .module -->
                        </div> <!-- end .col_content -->
                    </div> <!-- end .slider -->
                </div> <!-- end .slider_container -->
            </div> <!-- end #content -->
        </div> <!-- end #wrapper -->
        
        <script>
            $("form#new_email").submit(function(event){
                event.preventDefault(); // prevent the from from submitting...
                var email = $(event.target).find("#email").val();

                if (email.match(/^[_.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z.-]+.)+[a-zA-Z]{2,6}$/i)) {
                    $.post("/ajax/helpdesk/update_admin_email.php", {email: email}, function(response){
                        // attempt to parse the server response
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            alert("Could not understand server response. Please contact support at bugs@tzivoshashem.org");
                            return false;
                        }
                        // check if that response makes any sense
                        if (!response.success) {
                            alert(response.error);
                        } else {
                            window.location.reload(); // reload the page...
                        }
                    });
                } else {
                    alert("Invalid Email Address");
                }
            });
        </script>
    </body>
</html>