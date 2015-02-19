<?php

class Core_Mailer
{

    public static function welcomeNewUser(Model_User $user, $plaintext_password)
    {
        $subject = __('welcome_new_user.email.subject');
        $message = view::factory('email/welcome_new_user', array(
            'user' => $user,
            'new_password' => $plaintext_password
        ));
        static::send($user->email, $subject, $message);
    }

    public static function resetPassword(Model_User $user, $plaintext_password)
    {
        $subject = __('resetpassword.email.subject');
        $message = view::factory('email/resetpassword', array(
            'user' => $user,
            'new_password' => $plaintext_password
        ));
        static::send($user->email, $subject, $message);
    }

    public static function getFromAddress()
    {
        return AppConfig::instance()->get('from_email', 'application');
    }

    /**
     * Shortcut to add email to the queue
     * @static
     * @param $email
     * @param $subject
     * @param $body
     */
    public static function send($email, $subject, $body, $plaintext_body=NULL, $bcc=NULL, $attachements=array())
    {
        if (empty($email)) {
            Kohana::$log->add(Kohana::ERROR, 'Mailer::send called with empty recipient address. Subject: '.$subject);
            return;
        }
        if ($bcc === true) {
            $bcc = AppConfig::instance()->get('bcc_email', 'application');
        }
        $body_with_layout = $body; /*View::factory('email/layout')
            ->set('content', $body)
            ->set('title', $subject);*/
        if ($plaintext_body) {
            $body = array($body_with_layout, $plaintext_body);
        } else {
            $body = $body_with_layout;
        }
        if ( ! empty($attachements)) {
            $attachements = json_encode($attachements);
        } else {
            $attachements = NULL;
        }
        Emailq::factory()->add_email($email, NULL, $bcc, static::getFromAddress(), $subject, $body, array(), $attachements);
    }

}