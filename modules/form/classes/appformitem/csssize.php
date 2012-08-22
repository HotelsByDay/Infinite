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
        'min' => 0,
        'max' => 100,
        'step' => 1,
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
            'step' => $this->config['step'],
            'show_slider' => $this->config['show_slider'],
            'enabled_units' => $this->config['enabled_units'],
        );

        $this->addInitJS(View::factory('js/jquery.AppFormItemCssSize-init.js')->set('config', $config));
    }


    /**
     * Prevedema hodnotu na validni format a ulozime
     * @param $value
     */
    public function setValue($value)
    {
        if ($this->attr == 'global_active_shadow_x') {
            Kohana::$log->add(Kohana::INFO, 'Global active shadow value: "'.$value.'"');
        }
        // prevedeme hodnotu na validni format
        $units_re = implode('|', $this->config['enabled_units']);
        if ( ! preg_match('/(^[0-9]*\.?[0-9]+).*?('.$units_re.').*$/', $this->form_data, $matches)) {
            // Pokud nelze poskladat validni hodnotu, bude hodnota 0
            $value = '0';
        } else {
            $value = $matches[1].$matches[2];
        }
        if ($this->attr == 'global_active_shadow_x') {
            Kohana::$log->add(Kohana::INFO, 'Global active shadow validated value: "'.$value.'"');
        }

        parent::setValue($value);
    }





}
