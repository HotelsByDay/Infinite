<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Tato trida predstavuje bazovou tridy ze ktere musi dedit vsechny formularove
 * prvky (AppFormItem*).
 *
 */
class AppFormItem_Base
{
    //Reference na model nad kterym stoji formulare
    protected $model = NULL;

    protected $loaded_model = NULL;

    //Konfigurace pro tento formularovy prvek
    protected $config = array();

    //kopie formularovych data - prve ma moznost prisoupit k datum jineho prvku
    protected $form_data = NULL;

    /**
     * @var AppForm
     * Reference na rodicovsky objekt AppForm.
     */
    protected $form = NULL;

    //nazev sablony pro GUI prvku
    protected $view_name = 'null';

    //nazev atributu nad kterym stoji tento formularovy prvek
    protected $attr = NULL;

    //pokud je prvek virtualni tak tato trida pocita s tim ze v modelu
    //neexistuje atribut $this->attr.
    protected $virtual = FALSE;

    protected $virtual_value = NULL;

    //Unikatni identifikator formularoveho prvku. Pouziva se jako ID atribut a
    //je generovan nahodne. Tento odentifikator se vklada do html sablony jako
    //hodnota atributu "id" a zaroven se pouziva jako selector pri inicializaci
    //jQuery pluginu
    protected $uid = NULL;

    
    /**
     * Konstruktor objektu.
     * @param ORM $model Reference na ORM model nad formular stoji.
     * @param <array> $config Konfigurace tohoto formularoveho prvku
     * @param <array> $form_data Formularova data pro tento prvek
     * @param <AppForm> $form Reference na rodicovsky objekt AppForm
     */
    public function __construct($attr, $config, Kohana_ORM $model, ORM_Proxy $loaded_model, $form_data, $form)
    {
        //ulozim nazev atributu
        $this->attr = $attr;

        //konfigurace pro tento formularovy prvek
        $this->config = array_merge($this->config, (array)$config['items'][$attr]);

        //reference na ORM model nad kterym formular stoji
        $this->model = $model;
        $this->loaded_model = $loaded_model;

        //priznak, ktery rika ze je prvek virtualni
        $this->virtual = arr::get($this->config, 'virtual', $this->virtual);

        //provede reset hodnoty prvku - vymaze obsah $this->form_data a
        //podle konfigurace (['default_value']) muze nastavit defaultni hodnotu
        $this->clear();

        //formularova data pro tento prvek - v $this->form_data mohou byt default hodnoty
        $this->form_data = is_array($this->form_data)
                            ? array_merge($this->form_data, (array)$form_data)
                            : $form_data;

        //reference na rodicovsky AppForm objekt
        $this->form = $form;

        //vygeneruju nahodny identifikator - spoleham se na nahodnost hodnot,
        //protoze se takto generuje identifikator pro vsechny prvky na formulari
        $this->uid = 'i'.mt_rand();

    }

    /**
     * Metoda slouzici pro inicializaci objektu, predevsim v odvozenych tridach.
     * Resi se v ni predevsim pripojovani JS souboru, uprava configu atp.
     * Abychom nemuseli pretezovat konstruktor, ktery ma mnoho parametru, pretizime
     * pouze tuto metodu, ktera v konstruktoru bude vzdy volana.
     */
    public function init()
    {
        //pokud ma prve v konfiguraci nastaveno 'required' tak je potreba tuto
        //validaci dynamicky pridat do ORM modelu
        if (arr::get($this->config, 'required'))
        {
            //u virtualnich prvku musim kontrolu provadet mimo ORM
            if ( ! $this->virtual)
            {
                $this->model->addValidationRule($this->attr, 'not_empty');
            }
        }

        $this->assignValue();
                
        return;
    }

    /**
     * Tato metoda slouzi ke zpracovani formularovych udalosti.
     *
     * @param <int> $type Identifikator typu udalosti. Definovano konstantami
     * AppForm::FORM_EVENT_*.
     *
     * @param <array> $data K obsluze udalosti mohou byt pripojeny data. Napr.
     * k udalosti FORM_EVENT_SAVE_FAILED muze byt pripojena reference na vyjimku,
     *  ktera zpusobyla neuspesne ulozeni zaznamu.
     */
    public function processFormEvent($type, $data)
    {
        switch($type)
        {
            //volano pred ulozenim zaznamu (po uspesne validaci)
            case AppForm::FORM_EVENT_BEFORE_SAVE:
                break;

            //volano po uspesne ulozeni zaznamu
            case AppForm::FORM_EVENT_AFTER_SAVE:
                break;

            //volano v pripade ze doslo k vyjimce pri ukladani zaznamu
            case AppForm::FORM_EVENT_SAVE_FAILED:
                break;

            //neznama udalost - zaloguju
            default:
                $this->_log('Not processing unknown event of type "'.$type.'" with data "'.serialize($data).'".');

        }
    }
    
    /**
     * Zkontroluje form_data a pripadne vyvola zapis do modelu
     * pro zmenu chovani v odvozenych tridach je urcena metoda setValue
     */
    protected function assignValue()
    {
        if ($this->form_data !== NULL)
        {
            $this->setValue($this->form_data);
        }
    }
    
    /**
     * Vychozi chovani je - zapis do modelu
     * v odvozenych tridach se toto chovani bude rozsirovat
     * V odvozenych tridach zde mohou byt konverze (napr. datum, relSelect...)
     * @param <mixed> $value hodnota pro zapis do modelu 
     */
    public function setValue($value)
    {
        if ( ! $this->virtual)
        {
            // If item is nullable then convert empty values to NULL
            if (arr::get($this->config, 'nullable', false) and empty($value)) {
                $value = NULL;
            }
            $this->model->{$this->attr} = $value;
        }
        else
        {
            $this->virtual_value = $value;
        }
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
        //pokyd je prvek virtualni tak neexistuje prislusny atribut v modelu,
        //tak se vratim primo data, ktera prisla z formulare
        if ($this->virtual)
        {
            return $this->virtual_value;
        }

        //prvek ma zobrazovat hodnotu zaznamu, ktera je aktualne v DB
        if (arr::get($this->config, 'loaded_value'))
        {
            return $this->loaded_model->{$this->attr};
        }
        else
        {
            return $this->model->{$this->attr};
        }
    }

    /**
     * Tato metoda je volana rodicovskou tridou AppForm a je urcena k implementaci
     * custom validace dat tohoto prvku - predevsim relacnich zaznamu nebo prvku
     * ktere obsahuji nekolik vstupu a je nutne specialne validovat kazdy z nich.
     *
     * @return <array> V pripade uspesne validace vraci NULL nebo nevraci nic.
     * Jinak vraci retezec (nebo jiny datovy typ), ktery je predan do metody
     * Render jako parametr $error_messages. V pripade prvku, ktere obsahuji
     * nekolik inputu muze vracet i asoc. pole s chybami pro jednotlive inputy.
     */
    public function check()
    {
        if (arr::get($this->config, 'required') //prvek je required - musi byt vyplneny
                && $this->virtual               //prvek je virtualni
                && ! Validate::not_empty($this->virtual_value)) //a nesplnil kontrolu
        {
            return __($this->model->table_name().'.'.$this->attr.'.validation.required');
        }

        //validace se provadi pouze virtualnich prvku
        //TODO: tohle bych chtel i u nevirtualnich prvku, ale ted uz nezjistim proc
        //sem ten kus kodu vyse delal jen pro virtualni, tak to radeji tak delam i tady
        if ($this->virtual && ($validate = arr::get($this->config, 'validate')) != NULL)
        {
            // vytvorim si novy validacni objekt - posilam tam je jednu hodnotu
            $validation = Validate::factory(array('value' => (string)$this->virtual_value));

            //pridam pozadovana pravidla
            foreach ((array)$validate as $rule)
            {
                $validation->rule('value', $rule);
            }

            if ( ! $validation->check())
            {
                //vratim nalezenou chybu
                $errors = $validation->errors('object', TRUE, $this->model->table_name());

                return arr::get($errors, 'value');
            }
        }

        //zadna chyba nedetekovana
        return NULL;
    }

    /**
     * Vraci bool hodnotu, ktera rika zda je prvek virtualni.
     * @return <bool> Vraci TRUE pokud je virtualni, FALSE v opacnem pripade.
     */
    public function isVirtual()
    {
        return $this->virtual;
    }

    /**
     * Tato metoda zajistuje resetovani aktualni hodnoty prvku. V pripade
     * "jednoduchych" ne-virtualnich prvku staci aby trida Form resetovala
     * model volanim model->clear() ale virtualni prvky mohou hodnotu (relace,
     * strukturovanou hodnotu) uchovavat mimo model a je potreba aby mohli na
     * pozadavek na resetovani hodnoty specialne reagovat.
     *
     * @chainable
     */
    public function clear()
    {
        //pokud je definovana v konfiguraci defaultni hodnota, tak bude prirazena
        //modelu. Is_scalar je true pro string,int,float,boolean - chci hlavne
        //vyloucit closure
        if (($default_value = arr::get($this->config, 'default_value')) != NULL
                && is_scalar($default_value)
                && empty($this->model->{$this->attr}))
        {
            $this->model->{$this->attr} = $default_value;
        }
        
        $this->form_data = NULL;

        return $this;
    }

    /**
     * Metoda slouzi k vlozeni inicializacniho JS kodu do stranky. Zajistuje
     * vlozeni jedinecneho identifikatoru prvku do dane sablony. Tento identifikator
     * je urcen k pouziti jako "#ID" selector pro jQuery plugin.
     * 
     * @param View $view Sablona obsahujici inicializaci kod pro prislusny jQuery plugin.
     */
    public function addInitJS(View $view)
    {
        //do sablony pridam jeste ID prvku, ktere musi byt pouzito v selectoru
        $view->uid = $this->uid;

        //vlozi JS do stranky
        Web::instance()->addMultipleCustomJSFile($view);
    }

    /**
     * Metoda vraci nazev sablony pro vykresleni na zaklade pozadovaneho 
     * zpusobu zobrazeni.
     *
     * @param <const> $render_style Pozadovany zpusob zobrazeni.
     * Ocekava jednu z konstant AppForm::RENDER_STYLE_*
     * @return <string>
     */
    public function getViewName($render_style)
    {
        switch ($render_style)
        {
            case AppForm::RENDER_STYLE_READONLY:
                //4.1.2011 - z nejakeho duvodu tady uz $this->view_name obsahuje suffix
                //"_readonly", nemuzu najit misto kde se nastavuje, nevim si s tim rady
                //tak to resim timto zpusobem.
                return strstr($this->view_name, '_readonly') !== FALSE
                        ? $this->view_name
                        : $this->view_name.'_readonly';
            break;

            default:
                return $this->view_name;
        }
    }

    /**
     * Generuje HTML kod formularoveho prvku
     *
     * @param <const> $render_style Definuje zpusob zobrazeni formularoveho prvku.
     * Ocekava jednu z konstant AppForm::RENDER_STYLE_*.
     *
     * @param <string> $error_messages Vycet vsech validacnich chyb na formulari
     * indexovanych dle nazvy atributu. Prvek si vytahne validacni chybu odpovidajici
     * 'jeho' atributu. U prvku ktere stoji nad vice atributy si tak mohou vytahnout
     * validacni chyby pro vsechny atributy se kterymi pracuji.
     * 
     * @return <View>
     */
    public function Render($render_style = NULL, $error_messages = NULL)
    {
        // nactu pozadovanou sablonu
        $view = View::factory($this->getViewName($render_style));

        // doplnim zakladni parametry pro sablonu
        $view->attr  = $this->form->itemAttr($this->attr);

        // label nemusi byt vzdy definovany
        $label = arr::get($this->config, 'label', NULL);
        
        // Pokud label nebyl definovan v configu - vezmeme ho z jazykoveho souboru
        // na zaklade vygenerovaneho klice
        if ($label === NULL) {
            $label = FormItem::getLabel($this->model->table_name(), $this->attr);
        }

        // pokud je prvek required, tak se automaticky prida znacka k labelu
        if ($this->isRequired())
        {
            //required znacka '*' nemusi byt vzdy zobrazena
            if (arr::get($this->config, 'display_required_symbol', TRUE))
            {
                $label .= '<span class="required_label"></span>';
            }
        }

        $view->label = $label;

        // precte aktualni hodnotu pro tento prvek z ORM modelu
        $view->value = $this->getValue();

        // pokud ma mit prvek specialni css classu, tak ji predam sablone
        $view->css = arr::get($this->config, 'css', '');

        // do sablony bude vlozena textova napoveda k prvku, ktera muze byt
        // definovana v konfiguracnim souboru
        $view->hint = arr::get($this->config, 'hint');

        //html atribut autocompete
        $view->html_autocomplete = arr::get($this->config, 'html_autocomplete');

        // do sablony se vlozi rozsirena napoveda, ktera je zobrazena jako
        // tooltip
        if (($tooltip = arr::get($this->config, 'tooltip')) != NULL)
        {
            $view->tooltip = View::factory('widget/tooltip', array(
                'tooltip' => $tooltip,
                // pozice tooltipu k prvku se nacita z konfigurace, pokud neni explicitne
                // definovana, tak se pouziji defaultni hodnoty
                'tooltip_position_my' => arr::get($this->config, 'tooltip_position_my', 'left center'),
                'tooltip_position_at' => arr::get($this->config, 'tooltip_position_at', 'right center')
            ));
        }
        else
        {
            // princip Nullable object - v sablone neni potreba kontrola na tuto promennou
            $view->tooltip = View::factory('null');
        }

        // vlozim ID prvku - s tim pracuje JS (pri inicializaci se prvek selectuje
        // pres #uid)
        $view->uid = $this->uid;

        // predam text validacni chyby
        $view->error_message = $this->getErrorMessage($error_messages);

        //is the field supposed to be editable (the different between RENDER_STYLE_READONLY is
        //that 'not editable' item is sending its value to the server, while RENDER_STYLE_READONLY is only displaying
        //the value and its not being submitted back to the server
        $view->editable = arr::get($this->config, 'editable', TRUE);

        //vracim inicializovanou sablonu
        return $view;
    }

    /**
     * @return bool - true if the field is required in current form
     */
    public function isRequired()
    {
        return (((arr::get($this->config, 'required')
                // nektere prvky pouzivaji spcialne tento atribut aby se vyhly standardnimu zpracovani
                // a mohli udelat svoje custom (napr. AppFormItemFile)
                // @TODO: Tohle by asi slo vyresit lepe, ale neni na to ted cas (31.1.2012)
                || arr::get($this->config, '_required'))
            || $this->model->IsRequired($this->attr)) && ! $this->form->is_readonly());
    }

    /**
     * Vrati klice vsech validacnich error zprav, ktere dany prvek sam zobrazuje
     * (aby form vedel ze je nemuzi zobrazovat samostatne na zacatku formulare)
     * @return array
     */
    public function getHandledErrorMessagesKeys()
    {
        // Implicitne prvek obstarava pouze hlasku na klici odpovidajici nazvu jeho atributu
        return array($this->attr);
    }

    /**
     * Vrati validation error message ktera bude nasledne predana do sablony
     * @param $error_messages - vsechny error messages aktualniho formulare
     * @return mixed
     */
    protected function getErrorMessage($error_messages)
    {
        $result = Array();
        // Projdeme vsechny klice ktere prvek obstarava
        foreach ((array)$this->getHandledErrorMessagesKeys() as $key) {
            if (isset($error_messages[$key]) and is_string($error_messages[$key])) {
                $result[] = $error_messages[$key];
            }
        }
        // Slepime hlasky do jednoho retezece - kazda bude na samostatnem radku
        return implode('<br />', (array)$result);
    }

    /**
     * Provede zapis do logu a navic prida informace o aktualnim objektu
     * a formularovych datech a formularovem prvku pro snadnejsi diagnozu problemu.
     * @param <string> $message Hlavni zprava, ktera bude zapsana do logu.
     */
    protected function _log($message)
    {
        Kohana::$log->add(Kohana::ERROR, $message.'Form item attr:"'.$this->attr.'". Object name:"'.$this->model->_object_name.'". Serialized data:"'.serialize($this->form_data).'"');
    }
}
