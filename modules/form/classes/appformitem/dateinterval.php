<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vkladani 2 date hodnot pro urceni intervalu
 */
class AppFormItem_DateInterval extends AppFormItem_Base
{
    // Item's view name
    protected $view_name = 'appformitem/dateinterval';

    // Item is virtual - there are 2 model attributes which are handled directly in this class
    protected $virtual = true;

    // Attribute name for from date storage
    protected $from_attr = 'from';

    // Attr name for 'to' date sotrage
    protected $to_attr = 'to';


    /**
     * Load from and to attr names
     */
    public function __construct($attr, $config, Kohana_ORM $model, ORM_Proxy $loaded_model, $form_data, $form)
    {
        parent::__construct($attr, $config, $model, $loaded_model, $form_data, $form);
        $this->from_attr = arr::get($this->config, 'from_attr', $this->from_attr);
        $this->to_attr = arr::get($this->config, 'to_attr', $this->to_attr);

    }


    /**
     * Inicializace objektu - volano v konstruktoru AppFormItem_Base
     */
    public function init()
    {
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemDateInterval.js'));

        $init_js = View::factory('js/jquery.AppFormItemDateInterval-init.js');
        $init_js->config = Array(
            'date_format' => arr::get($this->config, 'js_date_format', DateFormat::getDatePickerDateFormat()),
            'hide_year' => (int)arr::get($this->config, 'hide_year', 0),
            'months_count' => arr::get($this->config, 'months_count', 1),
        );
        parent::addInitJS($init_js);

        return parent::init();
    }



    /**
     * Transform date format from DB to user date
     * @return <string>
     */
    public function getValue()
    {
        // If we have form data - return it
        if ( ! empty($this->form_data)) {
            return $this->form_data;
        }

        // Return model data
        return Array(
            'from' => DateFormat::getUserDate($this->model->{$this->from_attr}, arr::get($this->config, 'php_date_format')),
            'to' => DateFormat::getUserDate($this->model->{$this->to_attr}, arr::get($this->config, 'php_date_format')),
        );
    }

    
    /**
     * Transform user date into db dates
     */
    public function setValue($value)
    {
        if (isset($value['from'], $value['to']))
        {
            $this->model->{$this->from_attr} = DateFormat::getMysqlDate($value['from'], arr::get($this->config, 'php_date_format'));
            $this->model->{$this->to_attr} = DateFormat::getMysqlDate($value['to'], arr::get($this->config, 'php_date_format'));
        }
    }


    /**
     * Rekneme formulari jake validacni hlasky si prvek zobrazi
     * @return array|void
     */
    public function getHandledErrorMessagesKeys()
    {
        return Array($this->from_attr, $this->to_attr);
    }



    public function Render($render_style = NULL, $error_messages = NULL)
    {
        $view = parent::Render($render_style, $error_messages);
        return $view;
    }
}