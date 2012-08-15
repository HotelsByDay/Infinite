<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vyber bool hodnoty (0/1)
 * 
 * Config parametry tohoto prvku: (? znaci nepovinne, ! znaci povinne)
 *  !'label' => <string>  ... Label elementu ve formulari
 */
class AppFormItem_BoolCheckbox extends AppFormItem_Base
{

    protected $view_name = 'appformitem/boolcheckbox';


    /**
     * Zkontroluje form_data a pripadne vyvola zapis do modelu
     * pro zmenu chovani v odvozenych tridach je urcena metoda setValue
     */
    protected function assignValue()
    {
        $this->setValue((bool)$this->form_data);
    }

}