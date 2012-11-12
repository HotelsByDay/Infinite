<?php defined('SYSPATH') or die('No direct script access.');

class Security extends Kohana_Security {


    /**
     * @static
     * @param bool $append_uid
     * @param int $base_length
     * @return string 45 characters long random unique string as default
     */
    public static function getRandomSecredCode($append_uid=true, $base_length=32)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randstring = '';
        for ($i = 0; $i < $base_length; $i++)
        {
            $randstring .= $characters[rand(0, strlen($characters))];
        }
        if ($append_uid) {
            $randstring .= uniqid();
        }
        return $randstring;
    }

}