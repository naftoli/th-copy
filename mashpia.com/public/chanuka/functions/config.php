<?php
if (session_status() === PHP_SESSION_NONE) {
    session_name('chanukah_challenge');
    session_start();
}


if (isset($_POST["dataJson"])) {
    print_r($_POST["dataJson"]);


     // $url = './createAccounts.php';
     $url = '../testPost.php';
     $data = $_POST["dataJson"];
 
     $options = array(
         'http' => array(
             'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
             'method'  => 'POST',
             'content' => $data
         )
     );
     
    //  $context  = stream_context_create($options);
    //  $result = file_get_contents($url, false, $context);
    //  if ($result === FALSE) { 
    //      echo '{ "error": "problem connecting to server"';
    //   }
 
    //  print_r($result);


    //  send notification email

    $mail_to = "";
    $subject = "";
    $body = "";
    $headers = "From: ";
    // mail($mail_to, $subject, $body, $headers);

}



$missions_arr = [
    ['name' => "I lit Menorah", 'mission-img' => 'Asset 10.svg', 'prize' => "Silver menorah", 'prize-img' => "menorah.png", 'amount' => 2],
    ['name' => "I said V’al Hanisim", 'mission-img' => 'Asset 6.svg', 'prize' => "Helicopter ride (for 2)", 'prize-img' => "helicopter.png", 'amount' => 3],
    ['name' => "I received Chanukah Gelt", 'mission-img' => 'Asset 5.svg', 'prize' => "Set of shas", 'prize-img' => "seforim.png", 'amount' => 2],
    ['name' => "I ate food fried in oil", 'mission-img' => 'Asset 9.svg', 'prize' => "Latest camera", 'prize-img' => "camera.png", 'amount' => 3],
    ['name' => "I played Dreidel", 'mission-img' => 'Asset 8.svg', 'prize' => "Hoverboard", 'prize-img' => "hoverboard.png", 'amount' => 3],
    ['name' => "I hosted or attended a Chanukah party", 'mission-img' => 'Asset 7.svg', 'prize' => "Electronic keyboard", 'prize-img' => "keyboard.png", 'amount' => 3],
    ['name' => "I got another person to light Menorah", 'mission-img' => 'Asset 4.svg', 'prize' => "$600 at a nearby seforim store", 'prize-img' => "seforim.png", 'amount' => 2, 'notes' => "You found someone who was not planning to light Menorah & encouraged him to light."],
    ['name' => "I publicized the miracle of Chanukah", 'mission-img' => 'Asset 3.svg', 'prize' => "Electric scooter", 'prize-img' => "scooter.png", 'amount' => 3, 'notes' => "This can be done by sending a message on social media, putting up a menorah on your family car or any other way of publicizing Chanukah."],
    ['name' => "I did a mitzvah to bring Moshiach", 'mission-img' => 'Asset 2.svg', 'prize' => "Drone", 'prize-img' => "drone.png", 'amount' => 3, 'notes' => "You can choose any random Mitzvah. It might be your Mitzvah that will tip the scale & bring Moshiach!"]
];
