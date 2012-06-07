<?php defined('SYSPATH') or die('No direct script access.');

class AppFormItem_Wysiwyg extends AppFormItem_String
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/wysiwyg';

    /**
     * Inicializace objektu - volano v konstruktoru AppFormItem_Base
     */
    public function init()
    {
        //inicializace pluginu na teto instanci form prvku
        $this->addInitJS(View::factory('js/jquery.AppFormItemWysiwyg-init.js'));

        return parent::init();
    }
}