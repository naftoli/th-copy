<?php
$to = 'naftolir@gmail.com';
$subject = "Email testing";
$msg = "Just testing";
$headers[] = 'From: chidon@tzivoshashem.org';
$headers[] = 'Reply-to: chidon@tzivoshashem.org';
mail($to, $subject, $msg, implode("\r\n", $headers));