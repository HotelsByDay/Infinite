<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vkladani date hodnoty pomoci datePickeru
 */
class AppFormItem_Date extends AppFormItem_String
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/date';
    
    protected $check = TRUE;
    
    /**
     * Inicializace objektu - volano v konstruktoru AppFormItem_Base
     */
    public function init()
    {
        $init_js = View::factory('js/jquery.AppFormItemDate-init.js');

        $config = array(
            'dateFormat' => arr::get($this->config, 'js_date_format', DateFormat::getDatePickerDateFormat()),
            'showWeek' => true,
            'changeMonth' => true,
            'changeYear' => true,
            'showAnim' => 'fadeIn',
        );
        $init_js->config = array_merge($config, (array)arr::get($this->config, 'js_config'));
        parent::addInitJS($init_js);
        return parent::init();
    }
    
    /**
     * Vraci aktualni hodnotu tohoto prvku, kterou bere z ORM modelu.
     *
     * Uzitecne to je napriklad v pripade prvku pro vlozeni datumu. Od klienta
     * prijde datum v "ceskem" formatu. Metoda setValue zmeni na "MySQL" format.
     * A v teto metode bude opet prevedeno na cesky format.
     *
     * @return <string>
     */
    public function getValue()
    {
        $value = parent::getValue();

        //trimovani kvuli tomu ze by time_format mohl byt prazdny
        return DateFormat::getUserDate($value, arr::get($this->config, 'php_date_format'));
    }

    /**
     *
     */
    public function check()
    {
        if ( ! $this->check) {
            // Tohle mi nefungovalo
            return __($this->model->table_name().'.form.validation.'.$this->attr.'.format');
        }

        return NULL;
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
            $formatted_value = DateFormat::getMysqlDate($value);

            //datum je ve spatnem formatu - ulozim si priznak, ze je spatna hodnota
            if ($formatted_value === FALSE)
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
    
    public function Render($render_style = NULL, $error_messages = NULL)
    {
        $view = parent::Render($render_style, $error_messages);

        //pokud byl vstupni format datumu a casu nerozeznan, tak budu
        //na formulari zobrazovat hodnoty ktere primo z formulare prisly
        if ( ! $this->check)
        {
            $view->value = $this->form_data;
        }
        else
        {
            $value = $this->getValue();

            $view->value = empty($value)
                            ? ''
                            : $value;
        }

        return $view;
    }
}