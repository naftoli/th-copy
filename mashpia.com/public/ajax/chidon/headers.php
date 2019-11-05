<?php
header("Content-Type: text/html; charset=utf-8;");
header('Access-Control-Allow-Origin: '. ( isset( $_SERVER['HTTP_ORIGIN'] ) ? $_SERVER['HTTP_ORIGIN'] : "*" ) ); // CORS
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Methods: GET, POST, PATCH, OPTIONS, DELETE");
header('Access-Control-Allow-Headers: mobile, Content-Type, Authorization, login');