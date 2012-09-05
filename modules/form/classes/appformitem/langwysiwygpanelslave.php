<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vkladani retezcove hodnoty ve vice jazycich.
 * Config parametry tohoto prvku: (? znaci nepovinne, ! znaci povinne)
 *  ?'label'       => string    ... Label elementu ve formulari
 *  !'locales'     => array     ... asociativni pole ('en_US' => 'Anglictina', ...)
 */
class AppFormItem_LangWysiwygPanelSlave extends AppFormItem_LangWysiwyg
{

    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/langwysiwygpanelslave';


    /**
     * Nacteme seznam povolenych jazyku pro editovany objekt.
     */
    public function __construct($attr, $config, Kohana_ORM $model, ORM_Proxy $loaded_model, $form_data, $form)
    {
        parent::__construct($attr, $config, $model, $loaded_model, $form_data, $form);


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
    }


    /**
     * Pripojeni potrebnych JS souboru pro LangString prvek
     */
    public function init()
    {
        // Pripojime JS soubor s pluginem
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemLangWysiwygPanelSlave.js'));
        // A jeho inicializaci
        $init_js = View::factory('js/jquery.AppFormItemLangWysiwygPanelSlave-init.js');

        // Poskladame config prvku
        $config = Array(
            // Predame seznam jazyku do pluginu
            'placeholders' => $this->placeholders,
            'attr'         => $this->attr,
        );

        // Pokud je povolen uplaod obrazku tak predame url na upload controller a dalsi parametry
        if ($this->config['images_upload']) {
            $get_params = Array(
                'reltype' => $this->getImageRelType(),
                'relid'   => $this->model->pk(),
            );
            $config['images_upload'] = AppUrl::directupload_file_action('wysiwyg.images_upload', $get_params);
        }
        $init_js->config = $config;

        //prida do sablony identifikator tohoto prvku a zajisti vlozeni do stranky
        parent::addInitJS($init_js);
        $this->assignValue();
        return;
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

        // Vzdy zobrazime vsechny enabled jazyky
        // Add undefined translates and keep result in order of enabled_locales
        $result = $this->enabled_locales;
        foreach ($this->enabled_locales as $locale => $foo) {
            $result[$locale] = arr::get($translates, $locale, '');
        }
        Kohana::$log->add(Kohana::INFO, 'getTranslates result: '.json_encode($result));
        // Vratime preklady
        return $result;
    }


}