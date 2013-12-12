<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'mail_options' => array(
//        'driver' => 'smtp',
//        'host' => 'mail.ctech.tuxin.cz',
//        'port' => '465',
//        'auth' => true,
//        'username' => 'root',
//        'password' => 'aaa',
//        'encryption' => 'tls',
//        'debug'    => false,
        'sender_email' => AppConfig::instance()->get('application', 'from_email'),
        'sender_name'  => AppConfig::instance()->get('application', 'from_name'),
    ),
);