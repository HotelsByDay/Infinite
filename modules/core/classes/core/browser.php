<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Trida poskytuje metodu 'compatible', ktera slouzi k detekci kompatibility
 * prohlizece. Typ prohlizece je detekovan na zaklade hodnoty pole 'HTTP_USER_AGENT'.
 *
 */
class Core_Browser
{
    /**
     * Detekuje kompatibilni prohlizec na zaklade pole 'HTTP_USER_AGENT'
     *
     * @return <string> Vraci TRUE pro tyto prohlizece (ve vsech verzich):
     * Safara
     * Google Chrome
     * Mozilla Firefox
     * Opera
     */
    static public function compatible()
    {
        //retezec popisujici prohlizec uzivatele
        $user_agent = arr::get($_SERVER, 'HTTP_USER_AGENT', '');

        //prohlizece safara, chrome, firefox a operu podporujeme ve vsech verzich
        if (preg_match('#applewebkit|BrowserKit|safari|chrome|firefox|opera|MSIE 7|MSIE 8|MSIE 9#i', $user_agent) == TRUE)
        {
            return TRUE;
        }

        return FALSE;
    }
}