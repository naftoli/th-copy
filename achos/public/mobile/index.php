<?
session_start();
if (isset($_SESSION['user_id'])) {
	//header("Location: home.php");
	//exit;
}
?>
<!doctype html>
<html class="no-js" lang="">
    <head>
    	<? include 'inc/head.php' ?>
        <title></title>
		<style>
			.logos {
				margin: auto;
			}
			.logos img {
				width: 50px;
			}
		</style>
    </head>
		
    <body class="page-login">
        <header class="navbar" id="top" role="banner">
            <div class="container">
				<div align="center" style="margin-top: 10px;">
					<img id="logo" src="" width="85" />
				</div>
				<!--
                <div class="navbar-header">
                    <a href="#" class="navbar-brand"></a>
                </div>
				-->
            </div>
        </header>
        
        <div class="container">
            <div class="content">
            	<div class="panel panel-default">
                	<div class="panel-body">
                		
                        <form method="post" class="form-login">
                            <div class="form-group">
                                <label for="login" class="sr-only">Username</label>
                                <input type="text" id="username" name="login" value="" class="form-control input-lg" placeholder="Username">
                            </div>
                            <div class="form-group">
                                <label for="password" class="sr-only">Password</label>
                                <input name="password" type="password" id="password" class="form-control input-lg" placeholder="Password">
                            </div>
                            <div class="form-group">
                                <input type="submit" id="submit" name="commit" class="btn btn-default btn-block btn-lg btn-submit" value="Sign In">
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

		<div align="center" class="logos" style="display: none">
			<img src="/achos/images/hoofinal.gif" />
			<img src="/achos/images/FC-logo.gif" />
			<img src="images/logo.png" />
		</div>

    	<? include 'inc/foot.php' ?>
        
        <script>
			var br = true;
			var url = location.href;
			if (url.indexOf('?fc') != -1) br = false;
            $(function() {
				if (br) {
					$(".logos").show();
					$("#logo").attr('src', 'http://mashpia.com/achos/mobile/images/brch.gif');
				} else {
					$("#logo").attr('src', 'http://mashpia.com/achos/mobile/images/fc.png');
				}
                $('.form-login').submit( function(e) {
					e.preventDefault();
                    var username = $("#username").val().trim();
                    var password = $("#password").val().trim();
                    
                    $.post('login.php', {
                        username : username, 
                        password : password
                    }, function(data) {
                        if (data == 1) {
                            window.location.href = 'home.php';
                        } else if (data == 0) {
                            alert("Incorrect username / password.");
                        } else {
                        	alert(data);
                        }
                    });
                    
                    return false;
                });
            });
        </script>
        
    </body>
</html>


