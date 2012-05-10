<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Proti zakladni implementaci Kohana_View navic pridava moznost ziskat
 * zpetne nazev souboru, ktery je ulozen jako protected promenna.
 * Tohoto vyuziva napriklad trida Core_Web.
 */
class View extends Kohana_View {

    /**
     * Vraci nazev souboru ze ktereho je nactena tato sablona.
     * @return <string>
     */
    public function get_filename()
    {
        return $this->_file;
    }
    
}
