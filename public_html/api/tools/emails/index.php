<?php

class MashpiaEmails {

    /**
     * send password reset email
     * 
     */
    public static function passwordReset( $email, $url ) {
        $subject = 'Your password reset link';

        $message = '<p>We recieved a password reset request. The link to reset your password is below. ';
        $message .= 'If you did not make this request, you can ignore this email</p>';
        $message .= '<p>Here is your password reset link:</br>';
        $message .= sprintf('<a href="%s">%s</a></p>', $url, $url);
        $message .= '<p>Thanks!</p>';

        return self::sendEmail( 'noreply@mashpia.com', $email, $subject, $message );
    }

    public static function passwordChanged( $to, $name ) {
        $subject = 'Your Password has been changed.';
        $email = self::load('templates/passwordChanged.html');
        return self::sendEmail( 'noreply@tzivoshashem.com', $to, $subject, $email );
    }

    public static function newBC( $to, $base, $name, $username, $password ) {
        $subject = 'Your new Base Commander Account.';
        $email = self::load('templates/newBC.html');
        return self::sendEmail( 'noreply@tzivoshashem.com', $to, $subject, $email );
    }

    public static function sendParentEmail( $to, $username, $password ) {
        $subject = "Your new account with Chayolei Tzivos Hashem.";
        $message = "Hi!<br /><br /> "
            ."A parent account has been created for your child (or children) in Chayolei Tzivos Hashem.<br /><br />"
            ."With it, you’ll able to mark your children’s missions daily, straight from any smartphone (or computer). You will also be able to check in on their progress reports, personalize their growth, and stay up-to-date on Tzivos Hashem news from bases around the world.  "
            ."<br /><br />"
            ."Darchei Hachassidus will come alive in your home as managing your kids’ Chayolei Tzivos Hashem accounts becomes easier than ever. Help your young soldier reach the greatest heights in Hashem’s army. "
            ."<br /><br />"
            ."Your Username is: $username <br />"
            ."Your Default Password is: $password <br />"
            ."<br />"
            ."To change your username/password simply log into your account on https://TzivosHashem.com/mobile and click 'edit profile' on the top right hand corner. "
            ."<br /><br />"
            ."For any questions, help, or feedback, contact your school's Base Commander."
            ."<br /><br />"
            ."Wishing you much Yiddishe and Chassidishe Nachas,"
            ."<br /><br />"
            ."CTH Headquarters";
        return self::sendEmail( 'cth@tzivoshashem.org', $to, $subject, $message );
    }

    public static function sendEmail( $from, $to, $subject, $msg ) {
        $headers  = "From: $from <$from>\r\n";
        $headers .= "Reply-to: $from\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    
        echo '<pre>';
        print_r([ $to, $subject, $msg, $headers ]);

        return mail( $to, $subject, $msg, $headers );
    }

    public static function load( $template ) {
        ob_start();
        include( $template );
        return ob_get_clean();
    }
}