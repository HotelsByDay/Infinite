<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Tento formularovy prvek slouzi k uploadu souboru.
 * V konfiguraci umoznuje nastavit validaci MIME-TYPU nahravanych souboru
 * a take kontrolu jejich velikosti.
 */
class AppFormItem_File extends AppFormItem_Base
{
    //nazev sablony pro GUI prvku
    protected $view_name = 'appformitem/file';

    //nazev ORM modelu se kterym budu pracovat (ktery bude reprezentovat ulozene soubory)
    protected $model_name = NULL;

    //pro tento prvek neni ocekavan prislusny atribut v ORM modelu
    protected $virtual = TRUE;

    //pri zpracovani dat se do tohoto pole vlozi ORM modely, ktere jsou urceny
    //k ulozeni
    protected $save_rel_models = array();

    //pri zpracovani dat se do tohoto pole vlozi ORM modely, ktere jsou urceny
    //k odstraneni
    protected $delete_rel_models = array();

    //nazev konfiguracniho souboru, ktery je potreba pri generovani
    //hlasky pro validacni chybu 'requried'
    protected $config_group_name = NULL;

    // Tells whether current item should handle more localization variants of some file attributes
    // if locales list is present in config, then this attr is set to true
    protected $is_languable = false;

    // List of defined locales - loaded from config
    protected $locales = array();

    // List of locales keys which will have visible inputs in item template
    // - this can be defined in config to force some languages to be implicitly shown
    // - this array is populated with all locales which does have at least one value defined
    // Strucutre: Array(
    //              'en' => 'en',
    //              'cz' => 'cz',
    //            );
    protected $active_locales = array();

    // List of localized attributes
    protected $lang_attrs = array();

    // Name of lang fields view
    protected $lang_view_name = '';

    // Lang items mode (null / slave)
    protected $mode = null;

    // Through this config parameter different foreign_key column can be used ($this->model->primary_key() column is default)
    // Given foreign_key_column must exist in both main model and file model
    protected $foreign_key_column = null;


    // Array with all localized attributes values in following form:
    // Array(
    //   's' => Array(
    //      0 => Array(
    //         'en' => Array(
    //            'title' => 'some title',
    //            'description' => 'description',
    //            ...
    //         ),
    //         'cz' => Array(
    //            'title' => 'nejaky titulek',
    //         ),
    //         ...
    //      ),
    //      ...
    //   ),
    //   'd' => array(),
    // )
    // Where "0" is index of the model in (save|delete)_rel_models list, "title" is localized field name (present in $lang_attrs)
    // and 'en' and 'cz' are keys present in $locaes
    // This is loaded from request data after form submit or from DB before render
    protected $lang_values = array(
        // Because image models are stored in array attrs we need to store their lang values separated too
        's' => Array(), // values for file models in save_rel_models array
        'd' => Array(), // values for file models in delete_rel_models array
    );


    // whether info messages should be logged
    protected $debug = false;

    /**
     * Do stranky vlozim potrebne JS soubory.
     */
    public function __construct($attr, $config, ORM $model, ORM_Proxy $loaded_model, $form_data, $form)
    {
        //ukladam si nazev konfiguracniho souboru - v pripade ze se ma provadet
        //required kontrola nad timto prvek (ktera se neprovdi pres ORM) tak
        //podle nazvu konfiguracniho souboru se bude generovat chybova hlaska
        $this->config_group_name = $config->get_group_name();

        //zakladni zpracovani konfigurace
        parent::__construct($attr, $config, $model, $loaded_model, $form_data, $form);

        // precteme mode prvku - pokud je
        $this->mode = arr::get($this->config, 'mode', $this->config);

        if (arr::get($this->config, 'required'))
        {
            $this->config['_required'] = TRUE;
            unset($this->config['required']);
        }

        // Check debug mode
        $this->debug = AppConfig::instance()->debugMode();

        // Find out if localization will be handled
        $this->is_languable = isset($this->config['locales']);

        // Load locales list and lang attrs list
        if ($this->is_languable) {
            // Tenhle mode ma smysl jen s custom sablonou ktera si podle active_locales generuje inputy
            if ($this->mode == AppForm::LANG_SLAVE) {
                $this->locales = $this->active_locales = $this->model->getEnabledLanguagesList();
            } else {
                $this->locales = (array)$this->config['locales'];
                $this->active_locales = (array)arr::get($this->config, 'active_locales');

                // If no active locale was in config - activate first defined locale
                if (empty($this->active_locales) and ! empty($this->locales)) {
                    reset($this->locales);
                    $first_locale = key($this->locales);
                    $this->active_locales[$first_locale] = $first_locale;
                }
            }
            $this->lang_attrs = (array)arr::get($this->config, 'lang_attrs');
            $this->lang_view_name = arr::get($this->config, 'lang_view_name');
        }


        //naformatuje vstupni data z formulare do podoby se kterou se s nimi bude lepe pracovat
        $this->form_data = $this->formatInputData($this->form_data);
        
        //URL na kterou se budou odesilat soubory k uploadu - ocekavam definici
        //konfiguracniho klice kde je definice prvku - odtud ziska definici povolenych
        //Mime typu, relacni model a dalsi
        // - pokud je prvek languable, tak upload controlleru predame seznam active jazyku - podle nich se muze generovat preview
        $get_params = ($this->is_languable) ? array('active_locales' => $this->active_locales) : array();
        $js_config['action_url'] = appurl::upload_file_action($config->get_group_name().'.items.'.$attr, $get_params);

        //nazev relacniho modelu se kterym se bude pracovat
        $this->model_name = $this->config['model'];

        //vytvorim si instanci relacniho modelu abych mohl pristoupit ke statickym atributum
        $model_instance = ORM::factory($this->model_name);

        //musi to byt model dedici z Model_File (ten je abstraktni trida)
        if ( ! is_subclass_of($model_instance, 'Model_File'))
        {
            throw new Kohana_Exception('AppFormItem_File expects relation model to be an instance of Model_File.');
        }

        //povolene koncovky souboru ziskam z povolenych Mime-Typu
        $js_config['allowed_extensions'] = array();
        
        //ze seznamu povolenych typu vytvorim seznam povolenych pripon souboru
        foreach ((array)$model_instance::$allowed_mime_types as $mime_type)
        {
            //najdu pozici posledniho lomitka
            $delimiter_pos = strrpos($mime_type, '/');

            //koncovku vlozim do seznamu povolenych koncovek
            $js_config['allowed_extensions'][] = substr($mime_type, - (strlen($mime_type) - $delimiter_pos - 1));
        }

        //dale si vytahnu maximalni povolenou velikost pro soubor
        $js_config['max_size'] = $model_instance::$allowed_max_filesize;

        //file uploader
        Web::instance()->addCustomJSFile(View::factory('js/FileUploader.js'));

        //plugin pro tento formularovy prvek
        $js_file = View::factory('js/jquery.AppFormItemFile-init.js');

        //parametry pro inicializaci JS objektu pro upload souboru
        $js_file->attr               = $this->attr;
        //pri pouziti na itemlistu muze formular nazeb atributu upravit - to
        //je pak potreba aplikovat na vsechny inputy v sablonach uploadovanych
        //souboru

        $js_file->itemlist_attr      = $this->form->itemAttr($this->attr);
        $js_file->action_url         = $js_config['action_url'];
        $js_file->delete_url         = appurl::delete_file_action($this->attr);
        $js_file->allowed_extensions = $js_config['allowed_extensions'];
        $js_file->max_size           = $js_config['max_size'];
        $js_file->multiple_files     = arr::get($this->config, 'multiple_files', TRUE);
        $js_file->params             = arr::get($this->config, 'params', array());
        $js_file->file_count         = arr::get($this->config, 'file_count', 0);
        $js_file->sortable           = arr::get($this->config, 'sortable', NULL);

        //vlozim do stranky
        parent::addInitJS($js_file);

        // If item is languable
        if ($this->is_languable) {
            // Add FileLang plugin
            Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemFileLang.js'));
            // And init file
            $lang_init_js = View::factory('js/jquery.AppFormItemFileLang-init.js');
            $lang_init_js->config = Array(
                'locales_count' => count($this->locales),
            );
            parent::addInitJs($lang_init_js);
        }

        $this->foreign_key_column = arr::get($this->config, 'foreign_key_column', $this->foreign_key_column);
    }

    /**
     * Metoda provadi preformatovani vstupni hodnoty, tak aby se s ni dale lepe
     * pracovalo. Prevadi vstup ve tvaru:
     *
     * Array
     *  (
     *      [n] => Array
     *          (
     *              [id] => Array
     *                   (
     *                      [0] => 6
     *                   )
     *              [description] => Array
     *                   (
     *                      [0] => petkova motokara
     *                   )
     *           )
     *  )
     *
     * Na tvar:
     *
     * Array
     * (
     *      [n] => Array
     *          (
     *              [6] => Array
     *                  (
     *                      [description] => petkova motokara
     *                  )
     *          )
     * )
     *
     * @param <array> $data
     * @return <array>
     */
    protected function formatInputData($data)
    {
        if ($data === NULL)
        {
            return NULL;
        }

        $output = $processed = array();

        // Log info message
        if ($this->debug) {
            Kohana::$log->add(Kohana::INFO, json_encode($data));
        }

        foreach (array('d', 'l', 'n') as $key)
        {
            foreach (arr::getifset($data, $key, array()) as $attr => $values)
            {
                if ( ! isset($processed[$key]))
                {
                    $processed[$key] = array();
                }

                // Lang fields have more complex strucutre so we need to process them differently
                if ($attr == '_lang') {
                    // Go through all defined locales
                    foreach ($this->locales as $locale => $foo_label) {
                        // Try to get values for given locale
                        $locale_values = (array)arr::get($values, $locale);
                        // Go through all defined lang attributes
                        foreach ($this->lang_attrs as $field) {
                            // try to get translations for current field and locale
                            $field_values = (array)arr::get($locale_values, $field);
                            // field values in given locale for all files
                            foreach ($field_values as $i => $value) {
                                if ( ! isset($processed[$key][$i])) {
                                    $processed[$key][$i] = Array();
                                }
                                if ( ! isset($processed[$key][$i]['_lang'])) {
                                    $processed[$key][$i]['_lang'] = Array();
                                }
                                if ( ! isset($processed[$key][$i]['_lang'][$locale])) {
                                    $processed[$key][$i]['_lang'][$locale] = Array();
                                }
                                $processed[$key][$i]['_lang'][$locale][$field] = $value;
                            }
                        }
                    }
                } else {
                    // Original algorithm
                    foreach ($values as $i => $value)
                    {
                        if ( ! isset($processed[$key][$i]))
                        {
                            $processed[$key][$i] = array();
                        }
                        $processed[$key][$i][$attr] = $value;
                    }
                }
            }
        }


        foreach ($processed as $key => $items)
        {
            foreach ($items as $item)
            {
                $item_id = $item['id'];
                unset($item['id']);
                $output[$key][$item_id] = $item;
            }
        }

        // Log info message
        if ($this->debug) {
            Kohana::$log->add(Kohana::INFO, 'Processed data: '.json_encode($processed));
        }


        return $output;
    }

    /**
     * Provadi inicializaci a validaci relacnich modelu, ktere jsou urceny
     * k ulozeni - tj. zaznamy v $this->save_rel_models.
     * Ty jsou do atributu vlozeny pri zpracovani vstupnich dat v metode assignValue.
     * 
     * @return <array> Vraci asoc. pole, ktere obsahuje chybeho hlasky.
     */
    public function check()
    {
        //validacni chyby budou vlozeny do tohoto pole a roztrizeny podle ID
        //relacnich zaznamu
        $error_messages = array();

        //pokud ma byt prvek validovan jako required - tak musi byt nahran alespon
        //jeden soubor
        if (arr::get($this->config, '_required'))
        {
            if (count($this->save_rel_models) == 0)
            {
                $error_messages['_'] = __($this->config_group_name.'.'.$this->attr.'.validation.required');
            }
        }

        if (($require_min = arr::get($this->config, 'require_min')))
        {
            if (count($this->save_rel_models) < $require_min)
            {
                $error_messages['_'] = __($this->config_group_name.'.'.$this->attr.'.validation.rmin_equired');
            }
        }

        //modely, ktere jsou urcene k ulozeni zvaliduju
        foreach ($this->save_rel_models as $i => $rel_model)
        {
            //validuj
            if ( ! $rel_model->check())
            {
                //z ORM si vytahnu validacni chyby
                $error_messages[$i] = $rel_model->validate()->errors($rel_model->table_name());
            }
        }

        return empty($error_messages)
                ? NULL
                : array($this->attr => $error_messages);
    }

    /**
     * Resetuje aktualni hodnotu prvku.
     */
    public function clear()
    {
        $this->save_rel_models = $this->delete_rel_models = array();
        $this->lang_values['s'] = $this->lang_values['d'] = array();

        return parent::clear();
    }

    /**
     * Nacita seznam uploadovanych souboru z DB. Prvek zobrazuje tyto polozky
     * pri zobrazeni formulare.
     */
    protected function loadCurrentSavedModels()
    {
        $models = array();
        // We need to have primary key of models in array which auto-indexes corresponds to $models auto-indexes
        $pks = array();

        $file_models = ORM::factory($this->model_name)
            ->where($this->getForeignKeyColumn(), '=', $this->getForeignKeyValue())
            ->where('deleted', 'IS', DB::Expr('NULL'))->find_all();

        foreach ($file_models as $model)
        {
            $models[] = $model;
            $pks[] = $model->pk();
        }

        // Load lang attrs values - if needed
        if ($this->is_languable and ! empty($this->lang_attrs) and ! empty($pks) and ! empty($this->locales))
        {
            // Load all localized values from db with one query
            $values = ORM::factory($this->model_name.'_lang')
                // All given fields
                ->where('field', 'IN', $this->lang_attrs)
                // For all given locales
                ->where('locale', 'IN', array_keys($this->locales))
                // For all found file models
                ->where($this->model_name.'id', 'IN', $pks)
                ->find_all();

            // Array used for translating primary key value into model index in $models array
            $pk2index = array_flip($pks);

            // Log info message
            if ($this->debug) {
                Kohana::$log->add(Kohana::INFO, 'pk2index: '.json_encode($pk2index));
            }

            // Walk through values and store them into local array property
            foreach ($values as $lang) {
                // Get current value's model index
                $index = arr::get($pk2index, $lang->{$this->model_name.'id'}, false);


                // Log info message
                if ($this->debug) {
                    Kohana::$log->add(Kohana::INFO, 'lang_model.property_imageid: '.$lang->{$this->model_name.'id'});
                }

                if ($index === false) {
                    continue;
                }
                // Store value
                // If current model has no record in local property - create empty record
                if ( ! isset($this->lang_values['s'][$index])) {
                    $this->lang_values['s'][$index] = Array();
                }
                // If current lang attribute has no record in local property - create empty record
                if ( ! isset($this->lang_values['s'][$index][$lang->locale])) {
                    $this->lang_values['s'][$index][$lang->locale] = Array();
                }
                // Finally store the value for given locale
                $this->lang_values['s'][$index][$lang->locale][$lang->field] = $lang->content;

                // If current locale is not active - activate it now (we have found value in this localization and we
                // want items for this locale to be shown in the form)
                if ( ! isset($this->active_locales[$lang->locale])) {
                    $this->active_locales[$lang->locale] = $lang->locale;
                }
            }

            // Log info message
            if ($this->debug) {
                Kohana::$log->add(Kohana::INFO, 'db loaded lang_values: '.json_encode($this->lang_values));
            }
        }

        return $models;
    }


    /**
     * Different foreign key can be used for images bindings
     * @return null
     */
    protected function getForeignKeyColumn()
    {
        return ( ! empty($this->foreign_key_column))
                ? $this->foreign_key_column
                : $this->model->primary_key();
    }

    protected function getForeignKeyValue()
    {
        return ( ! empty($this->foreign_key_column))
            ? $this->model->{$this->foreign_key_column}
            : $this->model->pk();
    }

    /**
     * Zpracovava vstupni hodnotu prvku.
     * Nahraje prislusne relacni modely, provede v nich zmeny a roztridi na soubory
     * ktere jsou urceny k ulozeni a odstraneni. Samotne akce ulozeni a odstraneni
     * budou provedeny v ramci AFTER_SAVE udalosti.
     */
    protected function assignValue()
    {
        //zrusim aktualne ulozene relacni modely - vytvorim je znovu
        $this->save_rel_models = $this->delete_rel_models = array();

        if ($this->form_data === NULL)
        {
            $this->save_rel_models = $this->loadCurrentSavedModels();
        }
        else
        {

            //z dat formulare vytahnu hodnotu aktualniho prvku - jedna se o asoc. pole
            //klice jsou 'n' - tam jsou nove soubory
            //nebo 'd' - tam jsou soubory k odstraneni
            //a 'l' kde jiz ulozene soubory

            //nahraju soubory, ktere maji byt uz ulozene
            foreach (arr::get($this->form_data, 'l', array()) as $item_id => $file_data)
            {
                $target_model = ORM::factory($this->model_name, $item_id);
                
                if ( ! $target_model->loaded())
                {//echo '5';
                    continue;
                }
                
                //do ciloveho modelu nasipu vsechny dalsi hodnoty, ktere prisly na danem klici
                //mohlo dojit ke zmene jen nektereho z atribut jako popisek apod.
                $this->applyModelValues($target_model, $file_data);

                $this->save_rel_models[] = $target_model;
                // And also store related (by auto-index) lang values
                $this->lang_values['s'][] = (array)arr::get($file_data, '_lang');
            }

            //zpracuji nove soubory
            foreach (arr::get($this->form_data, 'n', array()) as $item_id => $file_data)
            {
                //nactu si model pro docasny soubor
                $temp_file = ORM::factory('tempfile', $item_id);

                //pokud neexistuje tak soubor preskocim (mohlo dojit k nejake chybe pri uploadu)
                if ( ! $temp_file->loaded())
                {
                    //zapisu info do logu
                    kohana::$log->add(Kohana::ERROR, 'AppFormItem_File - unable to load tempfile model with ID '.$item_id.'.');
                    continue;
                }

                //nactu cilovy model a modelem docasneho souboru ho budu inicializovat
                $target_model = ORM::factory($this->model_name)->initByTempFile($temp_file);

                //nastavim vazbu na model nad kterym stoji tento formular
                $target_model->{$this->getForeignKeyColumn()} = $this->getForeignKeyValue();

                //do ciloveho modelu nasipu vsechny dalsi hodnoty, ktere prisly na danem klici
                $this->applyModelValues($target_model, $file_data);

                //pridam do pole pro soubory urcene k ulozeni
                $this->save_rel_models[] = $target_model;
                // And also store related (by auto-index) lang values
                $this->lang_values['s'][] = (array)arr::get($file_data, '_lang');
            }
            
            //soubory na klici 'd' jsou urceny k odstraneni
            if (isset($this->form_data['d']) && ! empty($this->form_data['d']))
            {
                $target_model = ORM::factory($this->model_name);

                $models = $target_model->where($target_model->primary_key(), 'IN', array_keys((array)$this->form_data['d']))
                                       ->find_all();

                foreach ($models as $model)
                {
                    if ( ! $model->loaded())
                    {
                        continue;
                    }
                    
                    $this->delete_rel_models[] = $model;
                    // And also store related (by auto-index) lang values
                    $file_data = (array)arr::get($this->form_data['d'], $model->pk());
                    $this->lang_values['d'][] = (array)arr::get($file_data, '_lang');
                }
            }

            // Set active locales based on lang_values
            $this->loadActiveLocales();

//            //dale k ulozenym souborum pridam jeste ty, ktere jsou jiz v DB
//            //a nejsou mezi temi, ktere jsou urceny ke smazani
//            $already_existing_files = ORM::factory($this->model_name)
//                                        ->where($target_model->primary_key(), 'NOTIN', array_keys((array)$this->form_data['d']))
//                                        ->where($target_model->primary_key(), 'NOTIN', array_keys((array)$this->form_data['l']))
//                                        ->find_all();
        }
    }


    /**
     * Walks through $this->lang_values and set all found locales as active
     */
    public function loadActiveLocales()
    {
        if ( ! $this->is_languable) {
            return;
        }

        foreach (array('s', 'd') as $type) {
            foreach ((array)$this->lang_values[$type] as $locales) {
                if (is_array($locales) and ! empty($locales)) {
                    $this->active_locales = array_merge($this->active_locales, array_combine(array_keys($locales), array_keys($locales)));
                }
            }
        }
    }

    /**
     * Po ulozeni hlavniho zaznamu ulozim i nahrane soubory, ktere jsou v 1:N
     * relaci.
     */
    public function processFormEvent($type, $data)
    {
        switch($type)
        {
            //volano po uspesne ulozeni zaznamu
            case AppForm::FORM_EVENT_AFTER_SAVE:

                //modely k ulozeni ulozim
                foreach ($this->save_rel_models as $key => $rel_model)
                {
                    // Nastavim souboru cizi klic (implicitne je to vazba na model nad kterym stoji form, ale lze to v configu zmenit)
                    $rel_model->{$this->getForeignKeyColumn()} = $this->getForeignKeyValue();

                    $rel_model->save();

                    // If item is languable then process lang fields
                    if ($this->is_languable) {
                        // Collect all lang field ids which should be kept
                        $valid_lang_ids = Array();
                        foreach ((array)arr::get($this->lang_values['s'], $key) as $locale => $fields) {
                            foreach ((array)$fields as $field => $content) {
                                // lang content
                                $content = trim($content);
                                // Load lang model
                                $lang_model = ORM::factory($rel_model->object_name().'_lang')
                                    ->where($rel_model->primary_key(), '=', $rel_model->pk())
                                    ->where('locale', '=', $locale)
                                    ->where('field', '=', $field)
                                    ->find();

                                // If content is empty
                                if (empty($content)) {
                                    // translation removal from Db will be processed after foreach
                                    // Skip futher processing
                                    continue;
                                }

                                // Save new translation
                                $lang_model->content = $content;
                                // For case we are creating new lang record
                                $lang_model->{$rel_model->primary_key()} = $rel_model->pk();
                                $lang_model->locale = $locale;
                                $lang_model->field = $field;
                                $lang_model->save();
                                $valid_lang_ids[] = $lang_model->pk();
                            }
                        }
                        // Remove all lang records for current file which should not be kept
                        // (if some locale is changed in UI then original locale needn't be present in lang_values at all and we can not detect it)
                        $del_model = ORM::factory($rel_model->object_name().'_lang')
                            // Delete only lang records for current file
                            ->where($rel_model->primary_key(), '=', $rel_model->pk());
                        // If some records should be kept
                        if ( ! empty($valid_lang_ids)) {
                            // Exclude them in delete query
                            $del_model->where($rel_model->object_name().'_langid', 'NOT IN', $valid_lang_ids);
                        }
                        $del_model->delete_all();
                    }

                }

                //modely k odstraneni odstranim
                foreach ($this->delete_rel_models as $i => $rel_model)
                {
                    try
                    {
                        $rel_model->delete();

                        //pokud se zdarilo odstraneni souboru, tak jej odeberu 
                        //z pole a uz nebude zobrazen na formulari
                        unset($this->delete_rel_models[$i]);
                    }
                    catch (Exception $e)
                    {
                        Kohana::$log->add(Kohana::ERROR,
                                          'Unable to delete rel file ORM ":table_name" with id ":pk" due to error ":error"',
                                          array(':table_name' => $rel_model->table_name(), ':pk' => $rel_model->pk(), ':error' => $e->getMessage()));
                    }
                }

                //pokud doslo k uspesnemu ulozeni zmen, tak provedu reload vsech relacnich
                //zaznamu - je to kvuli tomu aby se mohlo aplikovat razeni znovu
                //na celou skupinu relacnich zaznamu. Pri odstraneni polozky by razeni
                //melo zustat vporadku, ale pri pridani nove by ta nova byla na konci
                //coz nemusi odpovidat pozadovanemu razeni
                $this->clear()          //vyresetuje vitrni hodnotu
                     ->assignValue();   //takze tady se provede nacteni z DB

            break;
        }
        
        //necham i rodicovsky prvek udelat svou prci
        return parent::processFormEvent($type, $data);
    }

    /**
     * Do modelu souboru vlozi hodnoty z pole v druhem argumentu
     *
     * Implementovane jako stamostatna metoda to je z duvodu moznosti pretizeni
     * v dedidich tridach.
     *
     * @param Model_File $model
     * @param <array> $values
     */
    public function applyModelValues(Model_File $model, array $values)
    {
        $model->values($values);
    }

    /**
     * Generuje vystup formularoveho prvku.
     * @param <int> $render_style
     * @param <string> $error_message
     */
    public function Render($render_style = NULL, $error_messages = NULL)
    {
        //pokud nema aktualni uzivatel opravneni na insert na relacnim objektu
        //nad kterym stoji tento prvek, tak je vykreslen jako readonly
        if ( ! Auth::instance()->get_user()->HasPermission($this->attr, 'new'))
        {
            $view = parent::Render(Core_AppForm::RENDER_STYLE_READONLY, $error_messages);
        }
        else
        {
            $view = parent::Render($render_style, $error_messages);
        }

        //ma se vykreslovat seznam souboru jako tabulka ?
        if (($view_name = arr::get($this->config, 'as_table')))
        {
            $view->table_header = ($render_style == AppForm::RENDER_STYLE_READONLY)
                ? View::factory($view_name.'_readonly')
                : View::factory($view_name);

            // pokud je definovan seznam jazyku pro popisky, predame ho do hlavicky tabulky
            if ($this->is_languable) {
                // Set locales list into table header - for select creation
                $view->table_header->locales = $this->locales;
                // Set list of active locales - those can be generated as visible
                $view->table_header->active_locales = $this->active_locales;
                // Add attr into table header
                $view->table_header->attr = $this->form->itemAttr($this->attr);
            }
        }
        else
        {
            $view->table_header = FALSE;
        }

        //do sablony vlozim sablony s jednotlivymi soubory
        $files = array();

        //vytahnu si chybove zpravy pro tento prvek
        $error_messages = arr::get($error_messages, $this->attr, NULL);

        //pripravim nazev sablony, ktera ma byt pouzita pro zobrazeni preview souboru
        $item_view_name = $render_style == Core_AppForm::RENDER_STYLE_READONLY || ! Auth::instance()->get_user()->HasPermission($this->attr, 'db_update')
                            ? $this->config['file_view_name'].'_readonly'
                            : $this->config['file_view_name'];

        //vsecny ulozene relacni modely vlozim do stranky
        foreach ($this->save_rel_models as $i => $file_model)
        {
            $view_params = array(
                'file' => $file_model,
                'attr' => $this->form->itemAttr($this->attr),
                'error_message' => arr::getifset((array)$error_messages, $i, ''),
                // Set lang attributes values for current file model
                'lang' => (array)arr::get($this->lang_values['s'], $i),
                // Set list of active locales - those can be generated as visible
                'active_locales' => $this->active_locales,
                'lang_view_name' => $this->lang_view_name,
            );

            $files[] = View::factory($item_view_name, $view_params);
        }

        //a zaroven i ty urcene k odstraneni (kvuli validacni chybe nemuselo
        //skutecne k odstraneni dojit)
        foreach ($this->delete_rel_models as $i => $advertphoto)
        {
            $view_params = array(
                'file' => $advertphoto,
                'attr' => $this->form->itemAttr($this->attr),
                'error_message' => arr::getifset((array)$error_messages, $i, ''),
                // Set lang attributes values for current file model
                'lang' => (array)arr::get($this->lang_values['d'], $i),
                // Set list of active locales - those can be generated as visible
                'active_locales' => $this->active_locales,
                'lang_view_name' => $this->lang_view_name,
            );

            $files[] = View::factory($item_view_name, $view_params);
        }

        //do sablony vlozim "globalni" chybovou hlasku - ta muze napriklad rikat
        //ze musi byt nahran alespon jeden soubor apod.
        $view->error_message = arr::get($error_messages, '_');

        $view->files = $files;

        // Add lang view into global item view - if file is languable
        if ($this->is_languable and ! empty($this->lang_view_name)) {
            // This will be hidden and jQuery plugin will clone this view for each file language
            $view->lang_view = View::factory($this->lang_view_name)
                ->set('attr', $this->form->itemAttr($this->attr))
                // Following values will be replaced in JS
                ->set('locale', '_LOCALE_')
                ->set('type_key', '_TYPE_KEY_')
                ->set('values', Array());
        }

        return $view;
    }
}