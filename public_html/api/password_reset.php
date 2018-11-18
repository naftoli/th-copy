<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// loads some globals as well as the functions that we need to polyfill from php 7
require_once( __DIR__ . '/header/header.php' );

// https://stackoverflow.com/questions/27728674/php-call-of-undefined-function-hash-equals
// * add hash_equals for php 5.4 and 5.5
if(!function_exists('hash_equals')) {
    function hash_equals($str1, $str2) {
        if(strlen($str1) != strlen($str2)) {
            return false;
        } else {
            $res = $str1 ^ $str2;
            $ret = 0;
            for($i = strlen($res) - 1; $i >= 0; $i--) {
                $ret |= ord($res[$i]);
            }
            return !$ret;
        }
    }
}

/**
 * gets the DBS query for the selector
 */
function getPasswordResetQuery( $selector ){
    global $MASHPIA_DB;

    $query = "SELECT * FROM password_reset WHERE selector = :selector AND expires >= :time";
    $query = $MASHPIA_DB->prepare( $query );
    $query->execute([ ':selector' => $selector, ':time' => time() ] );
    return $query;
}

/**
 * updatePassword - updates the password in the DBS if the selector, validator, and password are valid
 * 
 * @param selector      the selector for the password reset request
 * @param validator     the hex version of the validator, converted to bin and hashed to check for validity
 * @param password      non-blank password to update the admin with.
 */
function updatePassword( $selector, $validator, $password ) {
    global $MASHPIA_DB;

    $query = getPasswordResetQuery( $selector );

    if ( !$query )
        return 'There was an error processing your request. Error Code: 002 (Expired Token)';

    $row = $query->fetch();
    
    // convert back from hex to binary
    $calc = hash('sha256', hex2bin( $validator ) );

    if ( !hash_equals( $calc, $row['token'] ) )
        return 'There was an error processing your request. Error Code: 003 (Invalid Token)';

    try { 
        $admin = \Admin::find( $row['admin_id'] );
    } catch ( Exception $e ) {
        return 'There was an error processing your request. Error Code: 004 (Account Not Found)';
    }

    if ( !$password )
        return 'There was an error processing your request. Error Code: 006 (Invalid/Blank Password)';

    // update the password
    $admin->password = $password;
    if ( !$admin->save() )
        return 'There was an error processing your request. Error Code: 005 (Password Not Updated)';
    // delete entry to prevent reusing the link
    $MASHPIA_DB->exec( 'DELETE FROM password_reset WHERE admin_id = '. $admin->admin_id .';' );
    return false; // all is good.
}

// define variables used in JS and set in PHP
$expired = false;       $expires = false;

if( $_SERVER['REQUEST_METHOD'] == "POST" ) {
    $selector  = filter_input( INPUT_POST, 'selector'  );
    $validator = filter_input( INPUT_POST, 'validator' );
    $password  = filter_input( INPUT_POST, 'password'  );
    $confirm   = filter_input( INPUT_POST, 'confirm'   );

    if ( !$password || $password !== $confirm )
        $status = 'There was an error processing your request. Error Code: 001 (Mismatched Passwords)';
    else 
        $status = updatePassword( $selector, $validator, $password );
// check for expired queries on GET
} else if( $_SERVER['REQUEST_METHOD'] == "GET" ) {
    // get the keys
    $selector  = filter_input( INPUT_GET, 'selector'  );
    $validator = filter_input( INPUT_GET, 'validator' );
    
    if ( ctype_xdigit( $selector ) ) {
        $expired_query = getPasswordResetQuery( $selector );
        $expired = $expired_query->rowCount() <= 0;
        $expires = $expired_query->fetch()['expires'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Mashpia.com Password Reset</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap for styles -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.5.0/css/all.css" integrity="sha384-B4dIYHKNBt8Bc12p+WXckhzcICo0wtJAoU8YZTY5qE0Id1GSseTk6S+L3BlXeVIU" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" rel="stylesheet">
    <style>
        body{ font-family: 'Poppins', sans-serif; }
        /* Styles for just this static page */
        #content{ height: 100vh;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            display: -ms-flexbox; ms-flex-direction: column; -ms-flex-align: center; -ms-flex-pack: center;      
        }
        img { width: 20%; margin-top: 1%; max-width: 250px; min-width: 200px; }
        .form { width: 90%; margin-top: 2%; max-width: 520px; text-align: center; transition: .25s; -webkit-transition: .25s; -o-transition: .25s; }
        .form .input-group-prepend {
            width: 48px;    position: absolute; height: 100%; z-index: 10;
            align-items: center;    justify-content: center;
            -ms-flex-pack: center;  -ms-flex-align: center;
        }
        .form .input-group-prepend i { font-size: 1.5em; opacity: .5; }
        .form input.form-control:not(:first-child) { padding-left: 56px; border-radius: .3rem; }
        .input-group-append { position: absolute; right: 0px; height: 100%; z-index: 10; }
        label { width: 100%; text-align: left; margin-bottom: 0px; margin-top: 12px; }

        button.toggle-password { color: #3e6dc4; border-color: #ccc; }
        button.toggle-password:hover, button.toggle-password:focus,
        button.toggle-password:active { background-color: #3e6dc4; color: #fff; }

        .submit { width: 100%; margin-top: 12px; background-color: #3e6dc4; border-color: #335eac;}
        .submit p { display: inline-block; margin-bottom: 0px; -webkit-transition: .25s; -o-transition: .25s; transition: .25s; }
        .submit:hover p, .submit:focus p { margin-right: .5em }

        span.pulse { animation: pulse 1s infinite; }
        @keyframes pulse {
            30% { opacity: 1; } 50% { opacity: 0; }
            80% { opacity: 0; } 99% { opacity: 1; }
        }
        .alert { margin-top: 12px; margin-bottom: 0px; }

        .expires { margin: .5rem;}
    </style>
</head>
<body>
    <div id='content'>
        <img src='static/img/th.svg' alt='logo' />
        <div class='form'>
            <?php if( $_SERVER['REQUEST_METHOD'] == "GET" ) { ?>
                <?php if ( $expired ) { ?>
                    <div class='alert alert-danger fade show'>
                        Sorry, your token is expired.
                    </div>
                <?php } else if ( ctype_xdigit( $selector ) && ctype_xdigit( $validator ) ) { ?>
                    <form id='new-password' method='post' action='/api/password_reset' onsubmit='submitForm(event)'>
                        <h4>Tzivos Hashem Password Reset</h4>

                        <input type='hidden' name='selector' value=<?= $selector ?> />
                        <input type='hidden' name='validator' value=<?= $validator ?> />

                        <label for='password'>New Password</label>
                        <div class="Password input-group input-group-lg">
                            <div class="input-group-prepend">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input required type="password" id='password' class="form-control" name="password" placeholder="New Password">
                            <div class="input-group-append">
                                <button type="button" class="toggle-password btn btn-outline-primary" onclick='togglePassword( this )'>
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <label for='confirm'>Confirm Password</label>
                        <div class="Password input-group input-group-lg">
                            <div class="input-group-prepend">
                                <i class="fas fa-lock"></i>
                            </div>
                            <input required type="password" id='confirm' class="form-control" name="confirm" placeholder="Confirm Password">
                            <div class="input-group-append">
                                <button type="button" class="toggle-password btn btn-outline-primary" onclick='togglePassword( this )'>
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <button class='btn btn-primary btn-lg submit'>
                            <p>Reset Password</p> 
                            <i class="fas fa-ellipsis-h"></i><span class='pulse'>|</span>
                        </button>

                        <p class='expires'>
                            Your token expires in <span id='expires-in'></span>
                        </p>
                    </form>

                    <div id='client-error' class='alert alert-danger fade'>
                        Error: Passwords do not match.
                    </div>
                <?php } else { ?>
                    <div class='alert alert-danger fade show'>
                        Sorry, it appears that your tokens are invalid
                    </div>
                <?php } ?>
            <?php } else { // POST request, show status ?>
                <div class='alert alert-<?= $status ? 'danger' : 'success' ?> fade show'>
                    <?= $status ? $status : 'Password Reset Successfull' ?>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- open source script for generating the countdown clock -->
    <script src='/api/static/scripts/countdown.min.js'></script>
    <script>
        function togglePassword( element ) {
            var input = element.parentElement.parentElement.querySelector('input');
            var icon = element.querySelector('i');
            if ( input.type === 'password' ) {
                input.type = 'text';
                icon.className = 'fas fa-eye';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye-slash';
            }
            // input.focus();
        }

        function submitForm( event ) {
            // check that the two match
            var password = event.target.querySelector('input#password');
            var confirm = event.target.querySelector('input#confirm');
            // validate that the passwords match client side
            if ( password.value !== confirm.value ){
                event.preventDefault();
                document.querySelector('#client-error').classList.add('show');
                confirm.focus();
            }
        }
        <?php if ( $_SERVER['REQUEST_METHOD'] == "GET" && $expires ) { ?>
            var app = function() {
                var expires = parseInt( <?= json_encode( $expires )?>, 10 );
                var ts = Math.round((new Date()).getTime() / 1000);

                function updateUI() {
                    var countdownObj = countdown( new Date( expires * 1000 ) ); // for formatting the text
                    document.querySelector('#expires-in').innerText = countdownObj.toString();
                }
                // if the token is not expired
                if ( ts < expires ){
                    // update the UI every second
                    var interval = setInterval( function() {
                        console.log( 'UI update interval ran' );
                        // get the current timestamp
                        var ts = Math.round((new Date()).getTime() / 1000);

                        if ( ts >= expires ) {
                            document.querySelector('form#new-password').style.display = 'none';
                            document.querySelector('#client-error').innerText = 'Sorry, your token is expired.';
                            document.querySelector('#client-error').classList.add('show');
                            return clearInterval( interval );
                        } else {
                            updateUI();
                        }
                    }, 1000);
                }
                // update the UI
                updateUI();
            }();
        <?php } ?>
    </script>
</body>
</html>
