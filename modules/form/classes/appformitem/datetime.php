<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vkladani date hodnoty pomoci datePickeru
 */
class AppFormItem_DateTime extends AppFormItem_String
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/datetime';

 //   protected $time_format = 'G:i';

    //tato promenna definuje ze se ma cas ukladat jako unix time
    //v opacnem pripade se bude ukladat v DATETIME formatu MySQL
    protected $save_as_unix_time = false;

    protected $check = TRUE;

    /**
     * Inicializace objektu - volano v konstruktoru AppFormItem_Base
     */
    public function init()
    {
        // Nacteme nastaveni formatu casu - pokud neni, pouzije se vychozi (aktualne nastavene)
    //    $this->time_format = arr::get($this->config, 'time_format', $this->time_format);

        //budeme ukladat ve formatu UNIT TIME nebo MySQL DATETIME
        $this->save_as_unix_time = arr::get($this->config, 'unix', $this->save_as_unix_time);
        
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemDateTime.js'));

        $init_js = View::factory('js/jquery.AppFormItemDateTime-init.js');
        $config = Array(
            'time_format' => 'G:i',
            'date_format' => DateFormat::getDatePickerDateFormat(),
        );
        $init_js->config = $config;
        $this->addInitJS($init_js);
        
        return parent::init();
    }


    /**
     * 
     */
    public function assignValue()
    {
        if (count($this->form_data) == 2)
        {
            $this->setValue($this->formDataValue());
        }
    }


    public function formDataValue()
    {
        if (isset($this->form_data['date'], $this->form_data['time']) and ! empty($this->form_data['date']) and ! empty($this->form_data['time']))
        {
            return trim(trim(arr::get($this->form_data, 'date')) . ' ' . trim(arr::get($this->form_data, 'time')));
        }
        return null;
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

        return Array(
            'date' => DateFormat::getUserDate($value),
            'time' => DateFormat::getUserDate($value, 'H:i'),
        );
    }


    /**
     * 
     */
    public function check()
    {
        if ( ! $this->check) {
            // Tohle mi nefungovalo
            return __('appformitem_datetime.format_error');
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
        Kohana::$log->add(Kohana::INFO, 'datetime.setValue called with: '.json_encode($value));
        if ( ! empty($value))
        {
            //prevedu na mysql date format - pokud je vstupni format netozeznan
            //tak vraci FALSE
            $formatted_value = DateFormat::getMysqlDateTime($value);



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

            //pokud pokud ukladame ve formatu unixtime tak bude hodnota
            //na pozadovany format prevedena
            if ($this->save_as_unix_time)
            {
                $formatted_value = strtotime($formatted_value);
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

        return $view;
    }


}