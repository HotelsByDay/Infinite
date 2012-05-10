<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vyber 1 z N pomoci jQuery UI Button
 * Stejna funkcionalita jako ItemSelect. Pouze jine GUI.
 * Config parametry tohoto prvku: (? znaci nepovinne, ! znaci povinne)
 *  !'label' => <string>  ... Label elementu ve formulari
 *  ?'free'  => <bool>    ... Prida polozku '-- nezvoleno --' jako prvni option
 */
class AppFormItem_ButtonSet extends AppFormItem_Select
{
    
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/buttonset';
    
    // Retezec reprezentujici "free" hodnotu (klicem je $this->empty_key)
    protected $empty_value = 'Nezvoleno';

    /**
     * Inicializace prvku - vlozeni JS
     */
    public function init()
    {
        parent::init();
        $view = View::factory('js/jquery.AppFormItemButtonSet-init.js');
        $view->attr = $this->attr;
        parent::addInitJS($view);
    }
    
    
    
}