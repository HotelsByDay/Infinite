<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Trida rozsiruje funkcnost jadra - specificky metodu 'config'.
 * A to o moznost specifikovat 'default' hodnotu pro pripad kdy pozadovana hodnota
 * neni v konfiguracnim souboru nalezena.
 */
class Kohana extends Kohana_Core {

    /**
     * Rozsiruje zakladni implementaci metody config tak aby bylo mozne
     * specifikovat $default hodnotu pro pripad kdy pozadovana hodnota
     * neni v konfiguracnim souboru nalezena.
     * @param <type> $group
     * @param <type> $default Defaultni hodnota - bude vracena pokud $group
     * hodnota neni definovana.
     * @return <type>
     */
    public static function config($group, $default = NULL)
    {
        $retval = parent::config($group);
        
        return $retval === NULL
                ? $default
                : $retval;
    }
}
