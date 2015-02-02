<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vkladani date hodnoty pomoci datePickeru
 */
class AppFormItem_USDateTime extends AppFormItem_Base
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/usdatetime';

    protected $datetime_format = '@date g:ia';

    protected $check = true;

    /**
     * Inicializace objektu - volano v konstruktoru AppFormItem_Base
     */
    public function init()
    {
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemDateTime.js'));

        $init_js = View::factory('js/jquery.AppFormItemDateTime-init.js');
        $config = Array(
            'date_format' => DateFormat::getDatePickerDateFormat(),
        );
        $init_js->config = $config;
        $this->addInitJS($init_js);

        return parent::init();
    }


    public function formDataValue()
    {
        if (isset($this->form_data['date'], $this->form_data['time'], $this->form_data['time_type']) and ! empty($this->form_data['date']) and ! empty($this->form_data['time']))
        {
            return trim(trim(arr::get($this->form_data, 'date')) . ' ' . trim(arr::get($this->form_data, 'time')) . arr::get($this->form_data, 'time_type'));
        }
        return null;
    }


    /**
     *
     */
    public function assignValue()
    {
        if (count($this->form_data) == 3)
        {
            $this->setValue($this->formDataValue());
        }
    }




    /**
     * Vraci aktualni hodnotu tohoto prvku, kterou bere z ORM modelu.
     *
     * Uzitecne to je napriklad v pripade prvku pro vlozeni datumu. Od klienta
     * prijde datum v "ceskem" formatu. Metoda setValue zmeni na "MySQL" format.
     * A v teto metode bude opet prevedeno na cesky format.
     *
     * @return <array> datum a cas jako dva samostatne retezce, protoze cas je formatovan na zaklade configu
     */
    public function getValue()
    {
        $value = parent::getValue();

        Kohana::$log->add(Kohana::INFO, 'datetime.getValue returned: '.json_encode($value));
        if (empty($value)) {
            return array(
                'date' => '',
                'time' => '',
                'time_type' => '',
            );
        }
        return Array(
            'date' => DateFormat::getUserDate($value),
            'time' => DateFormat::getUserDate($value, 'g:i'),
            'time_type' => DateFormat::getUserDate($value, 'a'),
        );
    }


    /**
     *
     */
    public function check()
    {
        if ( ! $this->check) {
            // Tohle mi nefungovalo
            return __('appformitem_datetime.format_erroraa');
        }

        if (arr::get($this->config, 'required') //prvek je required - musi byt vyplneny
            &&
            ( ! Validate::not_empty($this->formDataValue()))) //a nesplnil kontrolu
        {
            return __($this->model->table_name().'.'.$this->attr.'.validation.required');
        }

        return parent::check();
    }

    /**
     * Pro nastaveni hodnoty vne tridy slouzi metoda assignValue()
     * ve ktere je nacitani z form_data a vyhazovani vyjimky
     * Tato metoda slouzi pro vlastni zapis do modelu.
     * Zde prevadi datum do DB formatu
     */
    public function setValue($value)
    {
        if ( ! empty($value))
        {
            //prevedu na mysql date format - pokud je vstupni format netozeznan
            //tak vraci FALSE
            $formatted_value = DateFormat::getMysqlDateTime($value, $this->datetime_format);



            Kohana::$log->add(Kohana::INFO, 'datetime.setValue formatted_value: '.json_encode($formatted_value));

            //datum je ve spatnem formatu - ulozim si priznak, ze je spatna hodnota
            if ( ! $formatted_value)
            {
                $this->check = FALSE;

                //hodnota se nebude zapisovat do modelu - pokud by se zapsisovala
                //jako UNIX TIME, tak bych mohl ztrati puvodni hodnotu - protoze
                //vstupni format je nedefinovany
                return;
            }
        }
        else
        {
            $formatted_value = NULL;
        }

        //bazova metoda setValue zajisti zapis do modelu nebo do uloziste pro
        //virtualni formularove prvky
        parent::setValue($formatted_value);
    }


    protected function getTimeValues()
    {
        return Array(
            '' => __('codebook.default_prepend_value'),
            '1:00' => '1:00',
            '1:30' => '1:30',
            '2:00' => '2:00',
            '2:30' => '2:30',
            '3:00' => '3:00',
            '3:30' => '3:30',
            '4:00' => '4:00',
            '4:30' => '4:30',
            '5:00' => '5:00',
            '5:30' => '5:30',
            '6:00' => '6:00',
            '6:30' => '6:30',
            '7:00' => '7:00',
            '7:30' => '7:30',
            '8:00' => '8:00',
            '8:30' => '8:30',
            '9:00' => '9:00',
            '9:30' => '9:30',
            '10:00' => '10:00',
            '10:30' => '10:30',
            '11:00' => '11:00',
            '11:30' => '11:30',
            '12:00' => '12:00',
            '12:30' => '12:30',
        );
    }

    /**
     * @param null $render_style
     * @param null $error_message
     * @return View
     */
    public function Render($render_style = NULL, $error_message = NULL)
    {
        $view = parent::Render($render_style, $error_message);

        if ( ! empty($error_message)) {
            $view->value = $this->form_data;
        }

        $view->time_values = $this->getTimeValues();

        return $view;
    }



}