<?php
function sendEnrollmentEmail( $email ) {
    $to = $email;
    $subject = "One more step to complete Chidon registration!";

    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';
    $headers[] = 'From: chidon@tzivoshashem.com';
    $headers[] = 'Reply-To: chidon@tzivoshashem.com';

    $message = <<<MSG
    <img src="Chidon-5780-Header-web.png" style="max-width: 100%" />
Thank you for completing your Chidon Shabbaton enrollment form.

PLEASE NOTE: In order for your child to be officially registered for Shabbaton, your information must be properly reviewed, signed, and returned to your child’s Chidon coordinator. 

If any changes need to be made, please indicate it clearly on this page when submitting.

IMPORTANT NOTE: The Chidon Shabbaton is a major undertaking, and requires extensive work and coordination to ensure everyone’s safety at all times. For this reason, changes to information submitted after enrollment will require much additional work, and therefore we must apply an additional charge. I understand that any changes to this application after registration closes: 
    
    After that point, changes can be made by emailing chidon@tzivoshashem.org, at an additional charge of $50.

    On Wednesday the day before the shabbaton begins, changes can be made between 12:00 and 5:00pm in the lobby of the Jewish children's Museum at a cost of $100.

    Any changes we are informed about during the actual Shabbaton will incur a $150 charge and must be submitted at the information desk.


Parent signature: _________________________
Child signature: _________________________

Chidon HQ
Tzivos Hashem
MSG;


    if ( @mail($to, $subject, $message, implode("\r\n", $headers)) ) return true;
    return false;
}