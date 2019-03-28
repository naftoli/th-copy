<?php

/*
 * chidon_passowrds.php, created by Menachem Hornbacher on 2/14/2018
 *
 * This file is not designed to be hit directly and will fail if it does. (or just do nothing...)
 * This file is designed to be included in the header of each chidon page to password protect it as well as act as a single source of truth for all passwords...
 *
 * It must be included directly after admin_header.php so that jquery is present and style tags will still be applied....
 *
 * This is bad code, touching it may prime the death stars ignition system so please be careful. Aderaan depends on you....
 */

if (isset($admin_user)) { // this whole page requres admin_user to be set in advance (user must be logged in...) ?>
    <?php if ($admin_user['auth'] != 'super' || ($admin_user['auth'] == 'super' && isset($_POST['school']))) { // if this is a school or a school was selected by a superuser... ?>
        <style>
            /* Hide all content on the page...*/
            .col_content{
                display: none;
            }
        </style>
    <?php } ?>
    
    <script>
        $(document).ready(function(){
            var school = <?=$admin_user['auths']['school'][0]?>
            
            var schools = [176,54,30,106,2]; // this array contains the keys of the following array...
            // hardcoded client side passwords...
            var passwords = {
                176 : 'laky',
                54 : 'cth792ep',
                30 : 'Chaimke10',
                106 : 'Toronto',
                2 : '8650'
            };
            
            var show = true; // show the page!
            var password = false; // default password (no password is the best password... )
            
            <?php if ($admin_user['auth'] == 'super' && $_SERVER['REQUEST_URI'] == "/chidon_tests.php" ) { // superusers only have one password... and only when entering marks... ?>
                password = prompt("Please enter the password to access this page."); // ask them to enter a password
                if ('chidonvaad78' != password) { // if the password does not match
                    show = false;
                    alert('You have no permission to access this page. Redirecting you to the homepage'); // llet them know 
                    location.href = '/admin.php';
                }
            <? } else { // otherwise each school must use it's hardcoded password... ?>
                for (var s in schools) { // for each school with a hardcoded password
                    if (schools[s] == school) { // if the current school is one of these schools
                        password = prompt("Please enter the password to access this page."); // ask them to enter a password
                        if (passwords[school] != password) { // if the password does not match
                            show = false;
                            alert('You have no permission to access this page. Redirecting you to the homepage'); // llet them know 
                            location.href = '/admin.php';
                        }
                    }
                }
            <? } ?>
            
            // show the page...
            if( show ) {
                $(".col_content").show();
            }
        });
    </script>
    
<? } // end if admin_user is set... ?>