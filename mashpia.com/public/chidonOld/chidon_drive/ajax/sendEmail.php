<?php
function sendEmail( $amount, $trans_id, $email, $name ) {
    // send email confirmation
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=iso-8859-1';
    $headers[] = 'From: chidon@tzivoshashem.com';
    $headers[] = 'Reply-To: chidon@tzivoshashem.com';

    $subject = "Chidon Drive Donation";
    $message = '<img src="http://chidondrive.com/ajax/email-header.jpg" style="max-width: 100%; height: auto;" />';
    $message .= "<p>Dear " . $name . ",</p>";
    $message .= "<p>Thank you for your generous donation of $" . number_format( $amount, 2 ) . " from " . date('F-d-Y') . ".</p>
                <p>Your support enables us to show our children how meaningful their learning is, and to drive them to go mechayil el choyil.</p>";
    $message .= "<p>Your transaction id is: " . $trans_id . "</p>";
    $message .= "<p>Thank you.</p>";
    $message .= "<p>P.S. Please retain this as proof of receipt of your tax-deductible donation of $" . number_format( $amount, 2 ) . "USD. No goods or services were provided for this donation. Tzivos Hashem is a 501(c)3 nonprofit corporation. Tax ID: 11-2872082.</p>";
    if ( @mail( $email, $subject, $message, implode("\r\n", $headers) ) ) return true;
    else return false;
}
?>