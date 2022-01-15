<?php defined('SYSPATH') or die('No direct script access.');

class AppFormItem_UrlName extends AppFormItem_String
{

    protected $virtual = true;

    protected $config = array(
        'if_empty_generate_from_attr' => NULL,
    );

    protected $valid = true;

    public function check()
    {
        $this->valid = true;
        $value = $this->form_data;
        $name_attr = $this->config['if_empty_generate_from_attr'];
        if (empty($value) and ! empty($name_attr)) {
            $value = $this->model->{$name_attr};
        } else {
            // Validate only if we are not auto-generating from object name attribute
            if ( ! UrlStorage::isUriAvailable($value, $this->model->object_name(), $this->model->pk())) {
                $this->valid = false;
                return __('afi_url_name.uri_not_available');
            }
        }

        return NULL;
    }

    /**
     */
    public function assignValue()
    {

    }


    protected function afterSave()
    {
        if ($this->form_data !== NULL && $this->valid)
        {
            $value = $this->form_data;

            $name_attr = $this->config['if_empty_generate_from_attr'];
            if (empty($value) and ! empty($name_attr)) {
                // User entered empty value and "if_empty_generate_from_attr" is sed
                // - auto-generate url_name
                UrlStorage::setObjectUriByTitle($this->model, $this->config['language'], $this->model->{$name_attr});
            } else {
                // User input is non-empty
                $current_uri = UrlStorage::getUri($this->model->object_name(), $this->model->pk(), $this->config['language']);
                if (trim($value) != $current_uri) {
                    // And it differs from current object url_name - set new url_name
                    UrlStorage::setUri($this->model->object_name(), $this->model->pk(), $this->config['language'], $value);
                }
            }
        }
    }


    public function processFormEvent($type, $data)
    {
        switch($type)
        {
            //volano pred ulozenim zaznamu (po uspesne validaci)
            case AppForm::FORM_EVENT_BEFORE_SAVE:
                break;

            //volano po uspesne ulozeni zaznamu
            case AppForm::FORM_EVENT_AFTER_SAVE:
                $this->afterSave();
                break;

            //volano v pripade ze doslo k vyjimce pri ukladani zaznamu
            case AppForm::FORM_EVENT_SAVE_FAILED:
                break;

            //neznama udalost - zaloguju
            default:
                $this->_log('Not processing unknown event of type "'.$type.'" with data "'.serialize($data).'".');

        }

        parent::processFormEvent($type, $data);
    }


    public function getValue()
    {
        if ( ! empty($this->form_data) and ! $this->valid) {
            return $this->form_data;
        }
        return UrlStorage::getUri($this->model->object_name(), $this->model->pk(), $this->config['language'], true);
    }





}
