<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vlozeni 24b barvy v hex reprezentaci
 */
class AppFormItem_SimpleColorPicker extends AppFormItem_String
{

    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/simplecolorpicker';

    /**
     * Inicializace objektu - volano v konstruktoru AppFormItem_Base
     */
    public function __construct($attr, $config, Kohana_ORM $model, ORM_Proxy $loaded_model, $form_data, $form)
    {
        parent::__construct($attr, $config, $model, $loaded_model, $form_data, $form);

        // tohle potrebuje jQuery plugin prvku
        $config = Array('uid' => $this->uid);
        $this->addInitJS(View::factory('js/jquery.AppFormItemSimpleColorPicker-init.js')->set('config', $config));
    }


    /**
     * Convert all values to upper case
     * @param $value
     */
    public function setValue($value)
    {
        return parent::setValue(strtoupper($value));
    }

    /**
     * Convert all values to upper case
     * @param $value
     */
    public function getValue()
    {
        return strtoupper(parent::getValue());
    }

}