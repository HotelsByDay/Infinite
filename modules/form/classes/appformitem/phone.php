<?php defined('SYSPATH') or die('No direct script access.');


class AppFormItem_Phone extends AppFormItem_Base
{

    protected $view_name = 'appformitem/phone';

    protected $config = array(
        // = Country prefixes codebook settings
        // Use false to turn of country prefixes
        'country_codebook' => 'Cb_CountryPhoneCode',
        'country_prefix_column' => 'phone_code',
        'country_name_column' => 'value',
        // Fallback value is $attr.'_prefix'
        'phone_prefix_column' => NULL,
        // Fallback is $attr.'_country_id'
        'phone_country_column' => NULL,
    );

    public function __construct($attr, $config, Kohana_ORM $model, ORM_Proxy $loaded_model, $form_data, $form)
    {
        parent::__construct($attr, $config, $model, $loaded_model, $form_data, $form);
        if ($this->config['country_codebook']) {
            if (empty($this->config['phone_prefix_column'])) {
                $this->config['phone_prefix_column'] = $this->attr.'_prefix';
            }
            if (empty($this->config['phone_country_column'])) {
                $this->config['phone_country_column'] = $this->attr.'_country_id';
            }
        }
    }

    public function getFullPhone()
    {
        $phone = $this->model->{$this->attr};
        if ($this->config['country_codebook']) {
            $phone = $this->model->{$this->config['phone_prefix_column']} . $phone;
        }
        return $phone;
    }

    public function check()
    {
        if ( ! empty($this->form_data['phone'])) {
            if ( ! Validate::phone_global($this->getFullPhone())) {
                return __('validation_error.phone');
            }

            if ($this->config['country_codebook'] and empty($this->form_data['country_id'])) {
                return __('validation_error.phone_prefix_not_empty');
            }
        } else {
            // Phone is empty - if prefix is selected then we have a validation error
            if ($this->config['country_codebook'] and ! empty($this->form_data['country_id'])) {
                return __('validation_error.phone');
            }
        }
    }

    public function setValue($value)
    {
        $phone = arr::get($value, 'phone');

        if ($this->config['country_codebook']) {
            $country_id = arr::get($value, 'country_id');
            $prefix = Codebook::value($this->config['country_codebook'], $country_id);
            $prefix = intval($prefix);

            $this->model->{$this->config['phone_country_column']} = $country_id;
            $this->model->{$this->config['phone_prefix_column']} = $prefix;
        }
        parent::setValue($phone);
    }


    protected function getPrefixes()
    {
        return Codebook::listing($this->config['country_codebook'], array('' => ''));
    }


    public function Render($render_style = NULL, $error_messages = NULL)
    {
        $view = parent::Render($render_style, $error_messages);

        if ($this->config['country_codebook']) {
            $view->country_codes = $this->getPrefixes();
            $view->country_id = $this->model->{$this->config['phone_country_column']};
        }
        return $view;
    }


}