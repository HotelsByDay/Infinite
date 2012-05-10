<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vkladani date hodnoty pomoci datePickeru
 */
class AppFormItem_DateTime extends AppFormItem_String
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/datetime';

    protected $time_format = 'G:i';

    //tato promenna definuje ze se ma cas ukladat jako unix time
    //v opacnem pripade se bude ukladat v DATETIME formatu MySQL
    protected $save_as_unix_time = TRUE;

    protected $check = TRUE;

    /**
     * Inicializace objektu - volano v konstruktoru AppFormItem_Base
     */
    public function init()
    {
        // Nacteme nastaveni formatu casu - pokud neni, pouzije se vychozi (aktualne nastavene)
        $this->time_format = arr::get($this->config, 'time_format', $this->time_format);

        //budeme ukladat ve formatu UNIT TIME nebo MySQL DATETIME
        $this->save_as_unix_time = arr::get($this->config, 'unix', $this->save_as_unix_time);
        
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemDateTime.js'));
        
        $this->addInitJS(View::factory('js/jquery.AppFormItemDateTime-init.js')->set('time_format', $this->time_format));
        
        return parent::init();
    }

    /**
     * 
     */
    public function assignValue()
    {
        if (count($this->form_data) == 2)
        {
            // Slepime datum a cas - zustava ceska reprezentace
            //pokud je 'date' i 'time' prazdne tak potrebuju otrimovat tu
            //mezeru uprostred, ktera se  doplnuje vyse
            $new_value = trim(trim(arr::get($this->form_data, 'date')) . ' ' . trim(arr::get($this->form_data, 'time')));

            $this->setValue($new_value);
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

        if (empty($value))
        {
            return NULL;
        }

        //pokud neni cas ulozen jako unixtime, tak ho prevedu na pozadovany
        //format aby byl parametr do metody czechDate ve spravnem formatu
        if ( ! $this->save_as_unix_time)
        {
            $value = strtotime($value);
        }

        //trimovani kvuli tomu ze by time_format mohl byt prazdny
        return trim(date('Y-m-d '.$this->time_format, $value));
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

        return parent::check();
    }

    /**
     * Pro nastaveni hodnoty vne tridy slouzi metoda assignValue()
     * ve ktere je nacitani z form_data a vyhazovani vyjimky
     * Tato metoda slouzi pro vlastni zapis do modelu.
     * Zde prevadi datum do DB formatu
     */
    protected function setValue($value)
    {
        if ( ! empty($value))
        {
            //prevedu na mysql date format - pokud je vstupni format netozeznan
            //tak vraci FALSE
            $formatted_value = Format::mysqlDateTime($value);

            //datum je ve spatnem formatu - ulozim si priznak, ze je spatna hodnota
            if ($formatted_value === FALSE)
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


    public function Render($render_style = NULL, $error_messages = NULL)
    {
        $view = parent::Render($render_style, $error_messages);

        //pokud byl vstupni format datumu a casu nerozeznan, tak budu
        //na formulari zobrazovat hodnoty ktere primo z formulare prisly
        if ( ! $this->check)
        {
            $view->date_value = arr::get($this->form_data, 'date');
            $view->time_value = arr::get($this->form_data, 'time');
        }
        else
        {
            // Precteme hodnotu atributu - vraci datum a cas v cz formatu
            $value = $this->getValue();

            //pokud je hodnota prazdna (NULL) tak se na formulari zobrazi prazdne pole
            if (empty($value))
            {
                $view->date_value = $view->time_value = '';
            }
            else
            {
                $view->date_value = date('j.n.Y', strtotime($value));
                $view->time_value = date($this->time_format, strtotime($value));
            }
        }

        return $view;
    }

}