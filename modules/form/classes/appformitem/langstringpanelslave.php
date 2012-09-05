<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vkladani retezcove hodnoty ve vice jazycich.
 * Config parametry tohoto prvku: (? znaci nepovinne, ! znaci povinne)
 *  ?'label'       => string    ... Label elementu ve formulari
 *  !'locales'     => array     ... asociativni pole ('en_US' => 'Anglictina', ...)
 */
class AppFormItem_LangStringPanelSlave extends AppFormItem_Base
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/langstringpanelslave';
    
    // Prvek je virtualni - nezapisuje primo do atributu modelu nad kterym stoji
    protected $virtual = TRUE;

    // Tohle se nacte v init z $this->config['locales']
    // a nasledne doplni o uzivatelske nazvy 'locales'
    protected $locales = Array();

    // Tohle se nacte v init z $this->config['locales']
    protected $placeholders = array();
    
    // Vychozi locale - optional, fallback je prvni ze seznamu locales
    protected $default_locale = NULL;
    
    // Nazev lang_modelu na ktery je vytvorena vazba v $this->model
    // - hodnota je dana konvenci a je v init metode odvozena z $this->model->object_name()
    protected $lang_model = NULL;
    
    // Zde bude pole s uzivatelskymi hodnotami
    // - to je potreba ve zde ulozenem tvaru predat do sablony, zaroven je to vyhodnejsi pro ukladani
    // - v requestu je vsak vyhodnejsi to mit usporadane jinak
    //   proto se to v metode setValue prevede a ulozi sem
    protected $virtual_value = Array();

    // Zda v setValue metode byla nalezena prazdna hodnota (nedefinovany text prekladu)
    protected $empty_value_found = false;

    // Prvek muze byt v rezimu AppForm::LANG_MASTER nebo AppForm::LANG_SLAVE nebo NULL (nezavysly prvek)
    protected $mode = NULL;

    // Seznam povolenych locale - toto se pouziva jen pokud je prvek slave
    // - v takovem pripade budou vynucene zobrazeny inputy pro vsechna tato locale a nepujde pridat dalsi
    protected $enabled_locales = Array();
    /**
     * Precteme si casto potrebne hodnoty z configu do lokalnich atributu
     * a zkontrolujeme zda je v configu vse potrebne
     */
    public function __construct($attr, $config, Kohana_ORM $model, ORM_Proxy $loaded_model, $form_data, $form) 
    {
        parent::__construct($attr, $config, $model, $loaded_model, $form_data, $form);

        //Konfiguracni polozka 'locales' obsahuje asoc. pole kde na klici je
        //hodnota typu 'en_US' (tedy oznaceni locale) a jako hodnota je tzv.
        //placeholer (pouzije se jako html5 atribut 'placeholder')
        $this->placeholders = (array)arr::get($this->config, 'locales');

        //v locales chci mit na klici oznaceni 'locale' a na hodnote bude
        //jeji nazev (napr. English)
        $this->locales = array();

        foreach ($this->placeholders as $locale => $_)
        {
            $this->locales[$locale] = __('locale.'.$locale);
        }

        // Pokud neni nastaven seznam locales, pak nemuzeme pokracovat
        if (empty($this->locales)) {
            throw new Kohana_Exception('AppFormItem_LangString used with empty "locales" parametr value.');
        }

        // load enabled languages from model
        // Check that model implements needed interface
        if ( ! ($this->model instanceof Interface_AppFormItemLang_SlaveCompatible)) {
            throw new Exception(__CLASS__.' can be used in slave mode only if parent model ('.$this->model->object_name().') implements "Interface_AppFormItemLang_SlaveCompatible"');
        }

        // Read enabled langs list
        $langs = $this->model->getEnabledLanguagesList(array($this->default_locale));
        // Fill labels into array
        foreach ($langs as $key => $foo) {
            $langs[$key] = arr::get($this->locales, $key);
        }
        // Store langs as new locales
        $this->enabled_locales = $langs;


        // Nazev has_many vazby dopocitame automaticky (zavedena konvence pri pouziti tohoto prvku)
        // zaroven udava nazev modelu pro ukladani prekladu
        $this->lang_model = $this->model->object_name().'_lang';
    }
    
    
    /**
     * Pripojeni potrebnych JS souboru pro LangString prvek
     */
    public function init()
    {
        parent::init();
        // Pripojime JS soubor s pluginem
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemLangStringPanelSlave.js'));
        // A jeho inicializaci
        $init_js = View::factory('js/jquery.AppFormItemLangStringPanelSlave-init.js');

        // Poskladame config prvku
        $config = Array(
            // Predame seznam jazyku do pluginu
            'placeholders' => $this->placeholders,
            'attr'         => $this->attr,
        );

        $init_js->config = $config;
        //prida do sablony identifikator tohoto prvku a zajisti vlozeni do stranky
        parent::addInitJS($init_js);
    }
    
    
    /**
     * Vychozi chovani je - zapis do modelu
     * v odvozenych tridach se toto chovani bude rozsirovat
     * V odvozenych tridach zde mohou byt konverze (napr. datum, relSelect...)
     * @param <mixed> $value hodnota pro zapis do modelu 
     */
    public function setValue($value)
    {
        // Toto ulozime do $this->virtual_value
        $data = Array();
        
        // Ulozime preklady - projdeme hodnoty ze SELECT items
        foreach ($value as $locale => $content)
        {
            // Odstranime okrajove prazdne znaky
            $content = trim($content);

            // Pokud je obsah prazdny, pak preskocime - jakoby vubec nebyl
            if ($content == '') {
                $this->empty_value_found = true;
                continue;
            }
            
            // Pokud jiz mame hodnotu, tak nedovolime zapsat prazdnou
            if (isset($data[$locale]) and empty($content)) {
                continue;
            }

            // Ulozime si preklad pro dane locale
            $data[$locale] = $content;
        }

        // Osetrime pripad, kdy nekdo v requestu prepise hodnoty - prijmeme jen 
        // ta locale, ktera jsou povolena v configu
        $this->virtual_value = $data;
    }
    
    
    
    /**
     * Validace - v priade ze je prvek nastaven jako required pak musi byt uvedeny hodnoty pro vsechna localse
     * @return type 
     */
    public function check() 
    {
        if (isset($this->config['required']) and $this->config['required'] and $this->empty_value_found) {
            return __($this->model->object_name().'.validation.'.$this->attr.'.incomplete');
        }
        return parent::check();
    }
    
    
    
    /**
     * Vrati asociativni pole s texty pro jednotlive jazyky.
     * Pokud zadny preklad zatim neni definovan, vrati pole s prazdnym
     * retezcem pro prvni jazyk (abychom se vyhnuli logice v sablone).
     * @return array
     */
    protected function getTranslates()
    {
        // Pokud mame form_data (form byl odeslan a ted se prvek bude renderovat po validacni chybe)
        if ( ! empty($this->form_data)) {
            $translates = $this->virtual_value;
        }
        else {
            // Jinak precteme preklady z DB
            $translates = $this->getDbTranslates();
        }
        
        // Pokud nejsou zadne preklady, pak vratime alespon prazdny preklad pro prvni jazyk 
        // aby se zobrazil alespon jeden input
        if (empty($translates)) {
            // Zapiseme defaultni hodnotu pro prvni locale
            $translates[$this->default_locale] = '';
        }

        // Pokud jsme v rezimu SLAVE nebo MASTER pak vzdy zobrazime vsechny enabled jazyky
        // Add undefined translates and keep result in order of enabled_locales
        $result = $this->enabled_locales;
        foreach ($this->enabled_locales as $locale => $foo) {
            $result[$locale] = arr::get($translates, $locale, '');
        }
        Kohana::$log->add(Kohana::INFO, 'getTranslates result: '.json_encode($result));
        // Vratime preklady
        return $result;
    }
    
    
    /**
     * Tohle se vola v getTranslates a take v processFormEvent, kde chceme orm iterator
     * @param bool $as_array - zda vratit asociativni pole nebo orm iterator
     * @return mixed preklady z DB
     */
    protected function getDbTranslates($as_array=true)
    {
        // Precteme vsechny preklady
        $translates = $this->model->{$this->lang_model}
                // Zajimaji nas jen preklady aktualniho atributu
                ->where('field', '=', $this->attr)
                // A chceme jen ty, ktere mame v seznamu locales pro editaci
                // - diky tomu muzeme pouzit vice prvku
                //   pro editaci vice podmnozin jazykovych mutaci zaroven
                ->where('locale', 'IN', array_keys($this->locales))
                ->find_all();
                
        // Na zaklade parametru rozhodneme v jakem tvaru vratime vysledek
        return $as_array ? $translates->as_array('locale', 'content') : $translates;
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
            //volano po uspesne ulozeni zaznamu
            case AppForm::FORM_EVENT_AFTER_SAVE:
                
                // Data prijata z formulare upravena na asociativni tvar
                $form_data = $this->virtual_value;
                
                // Projdeme zaznamy v DB a aktualizujeme je nebo je smazeme
                $db_translates = $this->getDbTranslates(false);
                
                // Projdeme zaznamy z DB
                foreach ($db_translates as $translate)
                {
                    // Pokud je toto locale v prijatych datech, pak aktualizujeme content
                    // - pokud je vsak prijaty text prazdny, tak jakoby nebyl a dojde ke smazani
                    if (isset($form_data[$translate->locale]) and $form_data[$translate->locale] != '') {
                        // Prepiseme preklad
                        $translate->content = $form_data[$translate->locale];
                        // Ulozime zaznam
                        $translate->save();
                        // Odebereme ze seznamu
                        unset($form_data[$translate->locale]);
                    }
                    else {
                        // Jinak locale prectene z DB nema ekvivalent v prijatych datech
                        // takze databazovy zaznam smazeme
                        $translate->delete();
                    }
                }
                
            //    Kohana::$log->add(Kohana::ALERT, json_encode($form_data).' - '.json_encode($this->virtual_value));
                
                // Projdeme zaznamy co zustaly ve $form_data a pridame je do DB
                foreach ($form_data as $locale => $content)
                {
                    // Vytvorime zaznam s prekladem
                    $translate = ORM::factory($this->lang_model);
                    $translate->{$this->model->object_name().'id'} = $this->model->pk();
                    $translate->field = $this->attr;
                    $translate->locale = $locale;
                    $translate->content = $content;
                    $translate->save();
                }
                
            break;
        }
    }
    
    /**
     * Generuje HTML kod formularoveho prvku
     * @return <View>
     */
    public function Render($render_style = NULL, $error_message = NULL)
    {
        // Zavolame base Render, ktera vytvori pohled a preda zakladni atributy
        $view = parent::Render($render_style, $error_message);

        // Predame seznam jazyku
        $view->locales = $this->locales;

        // Predame mod prvku
        $view->mode = $this->mode;

        // Predame seznam definovanych prekladu
        $view->translates = $this->getTranslates();
        
        // Vratime $view
        return $view;
    }

}