<?php
$names = [
    [
        'email' =>  'naftoli@tzivoshashem.org', 
        'link'  =>  'chidondrive.com/Rapoport/1264'
    ]
];

$sent = 0;
foreach ( $names as $name ) {
    $email = $name['email']; 

    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';
    $headers[] = 'From: chidon@tzivoshashem.com';
    $headers[] = 'Reply-To: chidon@tzivoshashem.com';

    $subject = "Your ChidonDrive page is live!";

    $message = '<img src="http://chidondrive.com/ajax/email-header.jpg" style="max-width: 100%; height: auto;" />';
    $message .= "

    <h2>Congratulations!</h2>

    <p>Your personalized webpage is now live at <a href='$name[link]'>$name[link]</a>.</p>

    <p>Now's the time to share it with family and friends so they can show their support!</p>

    <p>Keep in mind: The most effective method <strong>includes a personal message</strong>, image, or even a video to show the incredible impact that Chidon has had on your child.</p> 

    <p>To make it really simple for you, <strong>we have created a few great filters</strong> that you can customize with your own image and share on social media. We’ve included step-by-step instructions below.<br /> 
    Or, if you prefer, <a href='https://www.dropbox.com/sh/vofivk5fvkugx9j/AAD2COLAWDVuxNF5ZyS8v4QVa?dl=0'>here</a> <strong>are some prepared images</strong> that don't need your own photo.</p>

    <p>Use these steps to customize your personal photo for this campaign:</p>
    <ol>
    <li>Click <a href='https://www.canva.com/design/DADyJNsRl-Q/qVs_0RFVNfNPR2fr00zHjg/view?utm_content=DADyJNsRl-Q&utm_campaign=designshare&utm_medium=link&utm_source=sharebutton&mode=preview'>
        this link here for the boys version</a> or <a href='https://www.canva.com/design/DADyJFdodWY/RMRkrnoZaYm-DSx54H_Puw/view?utm_content=DADyJFdodWY&utm_campaign=designshare&utm_medium=link&utm_source=sharebutton&mode=preview'>this one for the girls</a></li>
    <li>Select <strong>'Edit design'</strong></li>
    <ul><li>You will need to log in or create a Canva account (free and simple).</li></ul>
    <li>Select <strong>'Use template'</strong></li>
    <li>Once the file opens you will see a bar on the left side of your screen. Select <strong>'Uploads'</strong> (the eighth option).</li>
    <li>Select <strong>'upload an image or video'</strong> and navigate to an image of your child that you’d like to use for this campaign.</li>
    <li>Once it uploads, <strong>scroll through the four design options and choose</strong> which one you want to use. Click on the photo you uploaded. It should show up on top of the templates.</li>
    <li><strong>Resize your photo</strong> so it fills the entire template</li>
    <li>Right-click on your image and select <strong>SEND TO BACK</strong></li>
    <li>Your image is now complete! Click on the white <strong>download</strong> button to download it. Change the file type to <strong>JPG</strong> and select the <strong>page</strong> your photo is on. Hit download and you should be ready to go :)</li>
    </ul>
    
    <p>Some ideas of messages you can use or adapt to accompany the image: </p>

    <blockquote style='font-size: 10px;'>Chidon Shabbaton is happening soon, and I have committed to raise $______ before I go! I have studied the mitzvos for months to get there. Please help me reach my goal!<br/ ><br />

    We are so proud of everything our son/daughter!<br />
    They have learned over 100 mitzvos in detail this year in their spare time BY CHOICE, and we want them to know how valuable that is.<br />
    Please help us reach our goal, the least we can do to support this incredible program and make sure it keeps growing strong!<br /><br />

    Thousands of children, including our own, have been transformed into enthusiastic Torah scholars over the past six months.<br />
    It's incredible to see and it's all <strong>because of Chidon.</strong><br />
    Help us support this movement. Every dollar counts!<br/ ><br />

    I'm looking forward to celebrating my achievements at the Chidon Shabbaton, a four-day incredible experience with trips and the most amazing game show and award ceremony. I’ll be joining thousands of kids from around the world who have each earned their place by studying hard for weeks and months. Please help me reach my goal!
    </blockquote>
    
    <p>Remember, every dollar raised not only shrinks your own fee, but goes a long way to ensuring that the Chidon Shabbaton stays alive and well for many years to come.</p>

    <p><strong>Thank you for partnering with us</strong> and investing in the future of our children.</p>

    <p>Sincerely,</p>

    <p><strong>Tzivos Hashem Chidon Headquarters<strong></p>

    <div align='center'>
    &copy;2020<br />
    790 Eastern Pkwy<br />
    Brooklyn, NY 11213
    <a href='privacy.html'>Privacy Policy</a><br />
    click <a href='unsubscribe.html'>here</a> to unsubscribe
    ";

    if ( @mail( $email, $subject, $message, implode("\r\n", $headers) ) ) $sent++;
}
echo "Sent: " . $sent;