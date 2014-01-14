<?php

class html extends Kohana_HTML
{

    public static function mailto($email, $title = NULL, array $attributes = NULL)
    {
        if (empty($email)) {
            return NULL;
        }
        return parent::mailto($email, $title, $attributes);
    }


    public static function external_website_link($url)
    {
        if (empty($url)) {
            return NULL;
        }
        $label = $url;
        if (stripos($url, 'http://') === false and stripos($url, 'https://') === false) {
            $url = 'http://' . $url;
        }
        return html::anchor($url, $label, array('target' => '_blank'));
    }

}