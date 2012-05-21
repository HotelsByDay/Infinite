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

        if (arr::get($this->config, 'required'))
        {
            $this->config['_required'] = TRUE;
            unset($this->config['required']);
        }

        //naformatuje vstupni data z formulare do podoby se kterou se s nimi bude lepe pracovat
        $this->form_data = $this->formatInputData($this->form_data);
        
        //URL na kterou se budou odesilat soubory k uploadu - ocekavam definici
        //konfiguracniho klice kde je definice prvku - odtud ziska definici povolenych
        //Mime typu, relacni model a dalsi
        $js_config['action_url'] = appurl::upload_file_action($config->get_group_name().'.items.'.$attr);

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

        //vlozim do stranky
        parent::addInitJS($js_file);
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

        foreach (array('d', 'l', 'n') as $key)
        {
            foreach (arr::getifset($data, $key, array()) as $attr => $values)
            {
                if ( ! isset($processed[$key]))
                {
                    $processed[$key] = array();
                }

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

        foreach ($processed as $key => $items)
        {
            foreach ($items as $item)
            {
                $item_id = $item['id'];
                unset($item['id']);
                $output[$key][$item_id] = $item;
            }
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

        return parent::clear();
    }

    /**
     * Nacita seznam uploadovanych souboru z DB. Prvek zobrazuje tyto polozky
     * pri zobrazeni formulare.
     */
    protected function loadCurrentSavedModels()
    {
        $models = array();

        foreach ($this->model->{$this->attr}->where('deleted', 'IS', DB::Expr('NULL'))->find_all() as $model)
        {
            $models[] = $model;
        }

        return $models;
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
                $target_model->{$this->model->primary_key()} = $this->model->pk();

                //do ciloveho modelu nasipu vsechny dalsi hodnoty, ktere prisly na danem klici
                $this->applyModelValues($target_model, $file_data);

                //pridam do pole pro soubory urcene k ulozeni
                $this->save_rel_models[] = $target_model;
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
                }
            }

//            //dale k ulozenym souborum pridam jeste ty, ktere jsou jiz v DB
//            //a nejsou mezi temi, ktere jsou urceny ke smazani
//            $already_existing_files = ORM::factory($this->model_name)
//                                        ->where($target_model->primary_key(), 'NOTIN', array_keys((array)$this->form_data['d']))
//                                        ->where($target_model->primary_key(), 'NOTIN', array_keys((array)$this->form_data['l']))
//                                        ->find_all();
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
                foreach ($this->save_rel_models as $rel_model)
                {
                    //nastavim vazbu na model nad kterym stoji tento formular
                    $rel_model->{$this->model->object_name()} = $this->model;

                    $rel_model->save();
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
            $view->table_header = View::factory($view_name);
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
        foreach ($this->save_rel_models as $i => $advertphoto)
        {
            $view_params = array(
                'file' => $advertphoto,
                'attr' => $this->form->itemAttr($this->attr),
                'error_message' => arr::getifset((array)$error_messages, $i, '')
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
                'error_message' => arr::getifset((array)$error_messages, $i, '')
            );

            $files[] = View::factory($item_view_name, $view_params);
        }

        //do sablony vlozim "globalni" chybovou hlasku - ta muze napriklad rikat
        //ze musi byt nahran alespon jeden soubor apod.
        $view->error_message = arr::get($error_messages, '_');

        $view->files = $files;

        return $view;
    }
}