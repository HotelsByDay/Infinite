<?php defined('SYSPATH') OR die('No direct access allowed.');


/**
 * Vycet udalosti, ktere cron spousti vcetne jejich intervalu.
 * @param <array>
 */
return array(
    /**
     * Vycet jednotlivych typu cronu a jejich intervalu v sekunda.
     * Spousteni cronu je rizeno podle tabulky 'cron' kde jsou zapsany
     * tyto typy. Pro aktualizaci tabulky 'cron' je potreba pustit
     * '/cron/setup'.
     */
    'events' => array(
        '10m'=> 360,    //10 minut
        '1hh'=> 1800,   //pul hodiny (half hour)
        '1h' => 3600,   //1 hodina
        '1d' => 3600*24,//1 den
        '1w' => 3600*24*7,  //1 tyden
     ),

    /**
     * Definice IP adres ze kterych je mozne vyvolat spusteni cron uloh.
     */
    'allowed_ip' => array(
        '::1',  //IPv6 localhost
    )
);