<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Tento helper slouzi ke snadnejsi praci s cURL funkcemi.
 */
class Helper_cURL
{
    /**
     * Timeout interval.
     */
    const DEFAULT_TIMEOUT = 10;

    /**
     * Stahuje obsah predane URL.
     * @param <string> $url URL adresa na kterou ma byt pozadavek odeslan
     * @param <array> $params Paramtry, ktere maji byt s pozadavkem odeslany
     * @param <bool> $post Pro POST TRUE jinak bude pouzit GET
     * @param <int> $timeout Timeout interval. Pokud je ponechano jako prazdna hodnota
     * tak je pouzita defaultni hodnota DEFAULT_TIMEOUT.
     *
     * @return <string> Vraci obsadh stazeny z dane URL.
     */
    static function get_contents($url, $params = array(), $post = TRUE, $timeout = NULL)
    {
        //kontrola zda je nastaveny parametr $timeout
        if ( ! $timeout)
        {
            $timeout = self::DEFAULT_TIMEOUT;
        }

        //data pudou postem nebo getem
        if($post)
        {
            //inicializace curl objektu
            $curl_handler = curl_init($url);

            // predani parametru
            curl_setopt($curl_handler, CURLOPT_POST, 1);
            curl_setopt($curl_handler, CURLOPT_POSTFIELDS, $params);
        }
        else
        {
            // uprava url + pridani parametru
            if( ! empty($params))
            {
                $url.= ('?'.http_build_query($params, ''));
            }
            
            //inicializace curl objektu
            $curl_handler = curl_init($url);
        }

        //nastavim timeout interval
        curl_setopt($curl_handler, CURLOPT_CONNECTTIMEOUT, $timeout);

        //odezva na dane URL bude vracena jako navratova hodnota funkce curl_exec
        curl_setopt($curl_handler, CURLOPT_RETURNTRANSFER, 1);

        //vyvolani pozadavku
        $curl_output = curl_exec($curl_handler);

        //uvolneni zdroju
        curl_close($curl_handler);

        return $curl_output;
    }
}