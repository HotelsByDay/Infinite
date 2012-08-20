<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vlozeni 24b barvy v hex reprezentaci
 */
class AppFormItem_CssSize extends AppFormItem_String
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/csssize';

    // Default config params
    protected $config = Array(
        'min' => '0',
        'max' => '1000',
        'show_slider' => true,
        'enabled_units' => array('em', 'px', 'pt', 'cm'),
    );

    /**
     * Inicializace objektu - volano v konstruktoru AppFormItem_Base
     */
    public function __construct($attr, $config, Kohana_ORM $model, ORM_Proxy $loaded_model, $form_data, $form)
    {
        parent::__construct($attr, $config, $model, $loaded_model, $form_data, $form);

        // tohle potrebuje jQuery plugin prvku
        $config = Array(
            'min' => $this->config['min'],
            'max' => $this->config['max'],
            'show_slider' => $this->config['show_slider'],
            'enabled_units' => $this->config['enabled_units'],
        );

        $this->addInitJS(View::factory('js/jquery.AppFormItemCssSize-init.js')->set('config', $config));

    }

}
