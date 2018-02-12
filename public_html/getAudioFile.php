<?
header('Content-type: application/octet-stream'); 
header('Content-disposition: attachment; 
filename="downloads/WWTC Guide.mp4"');                             
readfile("downloads/WWTC Guide.mp4");          
header("Location: admin.php");
?>