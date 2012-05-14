<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vyber 1 z N pomoci jQuery UI Button
 * 
 * Config parametry tohoto prvku: (? znaci nepovinne, ! znaci povinne)
 *  !'label' => <string>  ... Label elementu ve formulari
 *  ?'free'  => <bool>    ... Prida polozku '-- nezvoleno --' jako prvni option
 */
class AppFormItem_Bool extends AppFormItem_Select
{
    
    // Prvni radio nezustava zvolene automaticky
    protected $empty_key = '-1'; // 0=>Ne, 1=>Ano
    // Retezec reprezentujici "free" hodnotu (klicem je $this->empty_key)
    protected $empty_value = 'Nezvoleno';
    
    
    /**
     * Vraci bool hodnoty pro zobrazeni v GUI.
     */
    public function getValues() {
        $values = parent::getValues();
        return array_merge($values, Array('0'=>__('general.bool_no'), '1'=>__('general.bool_yes')));
    }
    
   
    
}