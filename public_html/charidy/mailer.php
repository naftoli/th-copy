<?php
require '../PHPExcel/IOFactory.php';
require '../PHPMailer/PHPMailerAutoload.php';

function getMessage($templateNumber, $name, $rank5776, $rank5777, $donation5776, $donation5777, $email) {
    switch ($templateNumber) {
        case '1A':
            $msg = "<html>
                    <head>
                    <style>
                    body {
                        font-size: 14px;
                    }
                    </style>
                    </head>
                    <body>
                    Dear $name,
                    <br /><br />
                    As you know Tzivos Hashem has had an incredible year of growth.
                    <br /><br /> 
                    This year we are committed to continue growing the army and we hope you will continue to grow your commitment with us as well!    
                    <br /><br /> 
                    On <b>Tuesday at 2pm EST</b>, we are having our annual fundraiser.  
                    <br /><br /> 
                    <b>Last year</b> you were promoted to $rank5776 because of your generous donation of $$donation5776.  
                    <br /><br /> 
                    <b>This Year</b> can I count on you to grow your rank to $rank5777 by giving $$donation5777?   
                    <br /><br />   
                    Please go to <b>GrowYourRank.Today</b> and use the email <b>$email</b> to donate.
                    <br /><br /> 
                    With your help we will raise $1M to grow the army.
                    <br /><br />
                    Please <a href='https://joom.ag/pRDW'>click here</a> and take a look at some of the incredible accomplishments the army has had this year, you will be blown away.  
                    <br /><br /> 
                    Thank you so much,
                    <br /><br />
                    Shimmy Weinbaum <br />
                    General of the Chayolei Tzivos Hashem Brigade 
                    </body>
                    </html>
                ";
            break;
        case '1B':
            $msg = "<html>
                    <head>
                    <style>
                    body {
                        font-size: 14px;
                    }
                    </style>
                    </head>
                    <body>
                    Dear $name,
                    <br /><br />
                    As you know, Tzivos Hashem has had an incredible year of growth. Please <a href='https://joom.ag/pRDW'>click here</a> to look at some of the incredible accomplishments. You will be blown away.  
                    <br /><br />
                    <b>On Tuesday at 2pm EST</b>, we are making our annual fundraiser. Please donate as much as you can. With your help we will raise $1M to continue growing the army.  
                    <br /><br />
                    To donate go to <b>GrowYourRank.today</b>
                    <br /><br />
                    Thank you so much!
                    <br /><br />
                    Rabbi Yerachmiel Benjaminson 
                    Executive director of Tzivos Hashem 
                    </body>
                    </html>
                ";
            break;
        case '1C':
            $msg = "<html>
                    <head>
                    <style>
                    body {
                        font-size: 14px;
                    }
                    </style>
                    </head>
                    <body>
                    Dear $name,
                    <br /><br />
                    As you know, Tzivos Hashem has had an incredible year of growth. Please <a href='https://joom.ag/pRDW'>click here</a> to look at some of the incredible accomplishments. You will be blown away.  
                    <br /><br />
                    <b>On Tuesday at 2pm EST</b>, we are making our annual fundraiser. Please donate as much as you can. With your help we will raise $1M to continue growing the army.  
                    <br /><br />
                    To donate go to <b>GrowYourRank.today</b>
                    <br /><br />
                    Thank you so much!
                    <br /><br />
                    Rabbi Sholom Ber Baumgarten 
                    Director of Tzivos Hashem 
                    </body>
                    </html>
                ";
            break;
        case '1D':
            $msg = "<html>
                    <head>
                    <style>
                    body {
                        font-size: 14px;
                    }
                    </style>
                    </head>
                    <body>
                    Dear $name,
                    <br /><br />
                    As you know, Tzivos Hashem has had an incredible year of growth. Please <a href='https://joom.ag/pRDW'>click here</a> to look at some of the incredible accomplishments. You will be blown away.  
                    <br /><br />
                    <b>On Tuesday at 2pm EST</b>, we are making our annual fundraiser. Please donate as much as you can. With your help we will raise $1M to continue growing the army.  
                    <br /><br />
                    To donate go to <b>GrowYourRank.today</b>
                    <br /><br />
                    Thank you so much!
                    <br /><br />
                    Chaim Benjaminson 
                    Tzivos Hashem 
                    </body>
                    </html>
                ";
            break;
        case '1E':
            $msg = "<html>
                    <head>
                    <style>
                    body {
                        font-size: 14px;
                    }
                    </style>
                    </head>
                    <body>
                    Dear $name,
                    <br /><br />
                    As you know, Tzivos Hashem has had an incredible year of growth. Please <a href='https://joom.ag/pRDW'>click here</a> to look at some of the incredible accomplishments. You will be blown away.  
                    <br /><br />
                    <b>On Tuesday at 2pm EST</b>, we are making our annual fundraiser. Please donate as much as you can. With your help we will raise $1M to continue growing the army.  
                    <br /><br />
                    To donate go to <b>GrowYourRank.today</b>
                    <br /><br />
                    Thank you so much!
                    <br /><br />
                    Rabbi Yerachmiel Benjaminson 
                    Executive director of Tzivos Hashem
                    <br /><br />
                    Rabbi Sholom Ber Baumgarten 
                    Director of Tzivos Hashem
                    <br /><br />
                    Rabbi Shimmy Weinbaum  
                    General of the Chayolei Tzivos Hashem Brigade  
                    </body>
                    </html>
                ";
            break;
    }
    return $msg;
}

$addresses = array(
    '1A'    =>  'shimmy@jcm.museum',
    '1B'    =>  'yerachmiel@jcm.museum',
    '1C'    =>  'sholomber@jcm.museum',
    '1D'    =>  'chaim@jcm.museum',
    '1E'    =>  'shimmy@jcm.museum'
);

$sentEmails = array();
if (($handle = fopen("SuccessfulEmails.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $sentEmails[] = $data[0];
    }
    fclose($handle);
}
//echo "<pre>"; print_r($sentEmails); echo "</pre>"; exit;

if (($handle = fopen("emailsSent.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $sentEmails[] = $data[0];
    }
    fclose($handle);
}

$badEmails = array(
    'akullock@tdshouston.org',
    'Mushka@chabadwi.org',
    'rabbi.brofman@ktc.nsw.edu.au',
    '1051notheast180terrace@gmail.com',
    '1234@gmail.com',
    'bina@Gmail.com',
    'bubbylngsam@gmail.com',
    'chayalementz@gmail.com',
    'chayaligh1234@gmail.com',
    'cmg@gmail.com',
    'cshaglow@gmail.com',
    'devschechtman@gmail.com',
    'ecued@gmail.com',
    'ekleinman@770.com',
    'estklyne@gmail.com',
    'freidahs@gmail.com',
    'gbarnett@tdshouston.org',
    'ghdgh@gmail.com',
    'ght@gmail.com',
    'Hashemlea@gmail.com',
    'igotobr@gmail.com',
    'iinsuri1@gmail.com',
    'jteghetty@gmail.com',
    'kosherhealthygormet@gmail.com',
    'lakjdkf@gmail.com',
    'laskyfamily5775@gmail.com',
    'lavon@Gmail.com',
    'lehaimleime@gmail.com',
    'leviy@gmail.com',
    'meirrafael@gmail.com',
    'menahelohm@gmail.com',
    'menchem592@gmail.com',
    'mfeigenson@tdshouston.org',
    'mica@gmail.com',
    'michalrosenbum@gmail.com',
    'michoellipskier@gmail.com',
    'Miller@Gmail.com',
    'miriamstolik@gmail.com',
    'mmsdfmarm@gmail.com',
    'morah124@gmail.com',
    'mzneubauer@gmail.com',
    'nechamapo7@gmail.com',
    'nechamdc@gmail.com',
    'rabbifriedman@chedermenachem.com',
    'rfsygen@gmail.com',
    'rochalelustg@gmail.com',
    'Rochelber955@gmail.com',
    'rochelwuenscv@gmail.com',
    'ryshomer@gmail.com',
    'saragniv@gmail.com',
    'sherrfsympatico@gmail.com',
    'shlomleh@gmail.com',
    'shmnuliturk@gmail.com',
    'vlmdayton@gmail.com',
    'zalmy@gmail.com',
    'Zeldi@themtc.com',
    'zelzelost@gmail.com',
    'zevbukher@gmail.com'
);
//echo count($badEmails); exit;
$file = "donors.xlsx";
$objPHPExcel = PHPExcel_IOFactory::load( $file );
$objWorksheet = $objPHPExcel->getActiveSheet();

$emails = array();

$info = array();
$first = true;
$line = 0;
foreach ( $objWorksheet->getRowIterator() as $row ) {
    $line++;
    if ($first) {
        $first = false;
        continue;
    }
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);
    $i = 0;
    foreach ( $cellIterator as $cell ) {
        $value = trim( $cell->getValue() );
        switch ($i++) {
            case 0:
                $donorID = $value;
                break;
            case 1:
                $donation5776 = $value;
                break;
            case 2:
                $rank5776 = ucwords(strtolower($value));
                break;
            case 3:
                $template = strtoupper($value);
                break;
            case 4:
                $fname = $value;
                break;
            case 5:
                $lname = $value;
                break;
            case 6:
                $donation5777 = $value;
                break;
            case 7:
                $rank5777 = ucwords(strtolower($value));
                break;
            case 8:
                $email = $value;
                break;
            case 9:
                $plname = $value;
                break;
            case 10:
                $pfname = $value;
                break;
        }
    }
    
    if ($pos = strpos($email, ',') !== false) {
        $email = explode(',', $email);
        if (in_array($email[0], $sentEmails)) continue;
    } else {
        if (in_array($email, $badEmails)) continue;
        if (in_array($email, $sentEmails)) continue;
    }
    if (!empty($email)) $emails[] = $email;
    
    $name = '';
    if (!empty($fname)) {
        if (strlen($fname) > 2) $name = $fname;
        else $name = $fname . ' ' . $lname;
    } else if (!empty($pfname)) {
        if (strpos($pfname, '/') !== false) {
            $arrName = explode('/', $pfname);
            $num = count($arrName);
            for ($i = 0; $i < $num; $i++) {
                if (!empty($arrName[$i]) && strlen($arrName[$i]) > 2) {
                    $name = $arrName[$i];
                    break;
                }
            }
        } else {
            if (strlen($pfname) > 2) $name = $pfname;
            else if (!empty($plname)) $name = $pfname . ' ' . $plname;
            else $name = $pfname;
        }
    }
    if (empty($name) && !empty($plname)) $name = $plname;
    
    //if (is_array($email)) {
        $info[] = array(
            'donorID'   =>  $donorID, 
            'template'  =>  $template, 
            'name'      =>  ucwords(strtolower($name)),
            'email'     =>  $email, 
            'last_rank' =>  $rank5776,
            'next_rank' =>  $rank5777,
            'last_don'  =>  $donation5776,
            'next_don'  =>  $donation5777
        );
    //}
}
//echo "<pre>"; print_r($emails); echo "</pre>"; exit;
echo "<pre>"; print_r($info); echo "</pre>"; exit;

$sent = array();
foreach ($info as $index => $row) {
    $msg = getMessage($row['template'], $row['name'], $row['last_rank'], $row['next_rank'], $row['last_don'], $row['next_don']);
    
    $mail = new PHPMailer;
    // Set PHPMailer to use the sendmail transport
    $mail->isSendmail();
    //Set who the message is to be sent from
    $mail->setFrom($addresses[$row['template']]);
    //Set an alternative reply-to address
    $mail->addReplyTo($addresses[$row['template']]);
    //Set the subject line
    $mail->Subject = 'Can I count on you?';
    
    if (is_array($email)) {
        foreach ($email as $address) {
            if (!empty($address)) $mail->addAddress(trim($address));
        }
    } else {
        $mail->addAddress($row['email']);
    }
    //$mail->addAddress('naftolir@gmail.com');
    $mail->msgHTML($msg);
    
    if (!$mail->send()) {
        echo "Mailer Error with Donor ID #:" . $row['donorID'] . "<br />" . $mail->ErrorInfo . "<br />";
    } else {
        $sent[] = $row['email'];
    }
}
echo "<pre>"; print_r($sent); echo "</pre>";