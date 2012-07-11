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
    

}