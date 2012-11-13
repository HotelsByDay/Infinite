<?php defined('SYSPATH') or die('No direct access allowed!');

class FormFactory
{
    /**
     * @param $class_name
     * @param $model
     * @param $form_config
     * @param array $form_data
     * @param bool $is_ajax
     * @return mixed
     * @throws Exception
     */
    public static function Get($class_name, $model, $form_config, array $form_data, $is_ajax = FALSE)
    {
        if ( ! is_string($class_name))
        {
            throw new Exception('FormFactory: Class name must be string');
        }

        if (empty($class_name))
        {
            throw new Exception('FormFactory: Class name must not be empty');
        }

        //vytvorim si novy objekt formulare
        $instance = new $class_name($model, $form_config, $form_data, $is_ajax);

        return $instance->init();
    }
}