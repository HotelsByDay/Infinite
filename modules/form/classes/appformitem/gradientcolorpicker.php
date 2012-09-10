<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vlozeni 24b barvy v hex reprezentaci a 2 odvozenych barev definujiich gradient
 * - Atribut nad kterym stoji v modelu neexistuje, zato vsak musi existovat nasledujici ctyri atributy:
 * <attr>_color
 * <attr>_slider
 * <attr>_start
 * <attr>_end
 */
class AppFormItem_GradientColorPicker extends AppFormItem_Base
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/gradientcolorpicker';

    protected $virtual = true;


    /**
     * Inicializace objektu - volano v konstruktoru AppFormItem_Base
     */
    public function __construct($attr, $config, Kohana_ORM $model, ORM_Proxy $loaded_model, $form_data, $form)
    {
        parent::__construct($attr, $config, $model, $loaded_model, $form_data, $form);

        // tohle potrebuje jQuery plugin prvku
        $config = Array(
            'uid' => $this->uid,
        );
        $this->addInitJS(View::factory('js/jquery.AppFormItemGradientColorPicker-init.js')->set('config', $config));
    }


    /**
     * Vratime pole hodnot misto jedne
     * @return array|null
     */
    public function getValue()
    {
        // default slider value is always 50
        $slider = $this->model->{$this->attr.'_slider'};
        if (empty($slider)) {
            $slider = 50;
        }
        return Array(
            'color' => strtoupper($this->model->{$this->attr.'_color'}),
            'slider' => $slider,
            'start' => strtoupper($this->model->{$this->attr.'_start'}),
            'end' => strtoupper($this->model->{$this->attr.'_end'}),
            'gradient' => strtoupper($this->model->{$this->attr.'_gradient'}),
        );
    }

    /**
     * Nastavime hodnoty - polozky pole zapiseme do jednotlivych atributu
     * @param $value
     */
    public function setValue($value)
    {
        $value = (array)$value;
        $this->model->{$this->attr.'_color'} = strtoupper((string)arr::get($value, 'color'));
        $this->model->{$this->attr.'_slider'} = arr::get($value, 'slider');
        $this->model->{$this->attr.'_start'} = strtoupper((string)arr::get($value, 'start'));
        $this->model->{$this->attr.'_end'} = strtoupper((string)arr::get($value, 'end'));
        $this->model->{$this->attr.'_gradient'} = strtoupper((string)arr::get($value, 'gradient'));
    }

    


}