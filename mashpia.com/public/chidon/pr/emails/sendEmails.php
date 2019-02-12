<?php
//Open the file.
$fileHandle = fopen("chidonEmailList.csv", "r");

//Loop through the CSV rows.
$info = [];
while ( ( $row = fgetcsv($fileHandle, 0, ",") ) !== FALSE ) {
  $first = $row[0];
  $last = $row[1];
  $email = $row[2];
  $info[$email][] = $first;
}

// // Multiple recipients
// $to = 'johny@example.com, sally@example.com'; // note the comma

// To send HTML mail, the Content-type header must be set
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-type: text/html; charset=iso-8859-1';

// Additional headers
$headers[] = 'From: shimmy@jcm.museum';
$headers[] = 'Bcc: shimmyweinbaum@gmail.com';

// Subject
$subject = 'Thank You';

foreach ( $info as $email => $names ) {
  if ( $email && filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
    $children = implode(',', $names);
    $numChildren = count( $children );
    // Message
    $message = "
      <html>
      <head>
        <title>Birthday Reminders for August</title>
        <style>
          .letter {
            margin-top: 50px;
            font-size: 16px;
            line-height: 1.4;
            text-align: justify;
            padding: 20px;
          }
        </style>
      </head>
      <body>
        <div class='letter'>
          <h2>Thank you for being such a great partner!</h2>

          Dear parents of $children,
          <br /><br />
          I would like to thank you. Your " . ($numChildren > 1 ? "children" : "child") . " $children " . ($numChildren > 1 ? "have" : "has") . " gained so much from learning with you and we truly recognize the time, effort, and encouragement you invested. They could not have done this without you, and for this, you deserve a chidon medal.<br /><br />
          When I helped restart Chidon six years ago, who would have believed the revolution in chinuch it would inspire? That it would so capture the hearts and minds of thousands of children worldwide? Thousands of children today voluntarily sacrifice their time and comfort to toil in Torah to master the 613 mitzvos, and spread that passion to those around them. They know details of the mitzvos that even their parents and teachers don’t know!<br /><br />
          Indeed, after six years of growing Chidon, the results are in: The participants’ voluntary learning fosters  study skills and a foundation of Torah knowledge they will build on for the rest of their lives, including (but certainly not limited to) the study of Rambam—cultivating greater Hiskashrus to the Rebbe and the development of their Yiras Shomayim. These are the children on the front lines to greet Moshiach and are truly prepared for the day when we will merit the fulfillment of all 613 Mitzvos, bekarov mamosh.
          <br /><br />
          Whether they made it to the Shabbaton or not, they already achieved the ultimate success. To me, to you, to all of their mechanchim, they are all truly winners.<br /><br />
          Wishing you yiddishe and chassidishe nachas, always,<br /><br />
          Rabbi Shimmy Weinbaum<br />
          Tzivos Hashem
        </div>
      </body>
      </html>
    ";
    // Mail it
    $email = 'naftolir@gmail.com';
    @mail($email, $subject, $message, implode("\r\n", $headers));
    break;
  }
}