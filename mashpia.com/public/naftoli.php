<?php
if (! isset($_COOKIE['naftoli'])) {
    setcookie('naftoli', '1');
    setcookie('naftoli', '1', 0, "/mobile/");
}
echo "Cookie set.";