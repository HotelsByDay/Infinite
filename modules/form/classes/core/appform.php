<?php defined('SYSPATH') or die('No direct script access.');

/**
 * K cemu slouzi konstanty typu ACTION_ ?
 * **************************************
 * Formularova tlacitka pro ulozeni nebo odstraneni zaznamu maji definovan
 * atribut "name" tak aby se propsaly do odeslanych dat formulare. Konstanta
 * ACTION_KEY definuje klic (a tedy hodnotu atributu "name" tlacitka) na kterem
 * ma byt pozadovana akce.
 * Pozadovane akce mohou byt: ACTION_SAVE, a dalsi.
 * Tyto konstanty se pouzivaji pri detekci pozadovane akce a zaroven se predavaji
 * jako argument metode getActionResult, ktera je muze vyuzit k sestaveni defaultni
 * zpravy pro uzivatele.
 * Mezi atributy tridy je definovana i konstanta ACTION_DELETE, ktera slouzi pouze
 * k identifikaci tlacitko, ktere vyvolava tuto akci. Odstraneni zaznamu, ale uz
 * neni odpovednosti formulare, ale uzivatel je presmerovan na akci kontroleru,
 * ktera zajisti odstraneni zaznamu a zobrazeni vysledku akce.
 *
 * K cemu slouzi konstanty typu ACTION_RESULT_ ?
 * *********************************************
 * Tyto konstanty slouzi k definici vysledku akce - uspech, neuspech apod.
 * Predavaji se jako argument metode getActionResult, ktera je pouzije
 * k vygenerovani defaultniho zpravy pro uzivatele.
 *
 * K cemu slouzi konstanty typu FORM_EVENT_ ?
 * ******************************************
 * Tyto konstanty slouzi k identifikaci formularovych udalosti, ktere predchazeji
 * a nasledujici ulozeni nebo odstraneni zaznamu.
 *
 * RENDER_STYLE konstanty:
 *************************
 * Render style konstanty se predavaji jako parametr metodam Render jednotivych
 * formularovych prvku a ovlivnuji zpusob zobrazeni prvku. Napriklad zajisti
 * zobrazeni prvku jako read-only apod.
 *
 * 
 * $this->_requested_action a $this->_requested_action_result:
 * ***********************************************************
 * V metode Process se do techto atributu zapise typ pozadovane akce a jeji vysledek.
 * Na konci metody se tyto hodnoty poslou do setActionResult, aby se podle nich
 * vygenerovala defaultni F pro uzivatele.
 * Navic pri generovani formulare v metode Render() se kontroluje zda prave
 * nedoslo k uspesnemu odstraneni zaznamu - pokud ano tak se vlastni formular
 * nevykresluje.
 *
 * Popis akci pri standardni praci s formularem:
 * *********************************************
 *
 * 1. konstruktor - ulozeni parametru, nacteni formularovych prvku dle konfigurace
 * 2. Process() - detekce pozadovane akce, jeji provedeni, a ulozeni vysledku a
 *                  provedene akce, nastaveni hlaseni pro uzivatle pomoci setActionResult.
 * 3. Render() - vykresleni formulare
 *
 */
class Core_AppForm {

    //definuje klic ve formularovych datech kde je ocekavana pozadovana akce
    //ktera je definovana jednou z konstant ACTION_VALUE_
    const ACTION_KEY = '_a';

    //Definuje akci formulare - uloze zaznamu
    const ACTION_SAVE = 'save';

    //Definuje akci formulare - odstraneni zaznamu (ta neni zpracovana formularem
    //ale samostatnou akci prislusneho kontroleru)
    //Tato konstanta se pouziva k identifikaci ovladaciho prvku, ktery slouzi k
    //vyvolani dane akce (vice viz metoda $this->getFormActionButtons)
    const ACTION_DELETE = 'delete';

    
    const ACTION_VALIDATE = 'validate';

    //definuje formularovou udalost, ktera je vyvolana pred ulozenim (po uspesne validaci)
    const FORM_EVENT_BEFORE_SAVE = 1;

    //definuje formularovou udalost, ktera je vyvolana po ulozeni
    const FORM_EVENT_AFTER_SAVE = 2;

    //definuje formularovou udalost, ktera je vyvolana po udalosti
    //FORM_EVENT_BEFORE_SAVE a tesne po zachyceni vyjimky pri ukladani pokud se
    //se nepodarilo zaznam ulozit
    const FORM_EVENT_SAVE_FAILED = 3;

    //Ke kazde formularove udalosti mohou byt pripojeny data.
    //V pripade udalosti jako je FORM_EVENT_SAVE_FAILED se k datum automaticky
    //prida reference na vyjimku, ktera nastala (pred vyvolanim udalosti) a
    //bude predana prave na tomto klici
    const FORM_EVENT_DATA_EXCEPTION_KEY = 'e';

    //Definuje vysledek akce - uspech. Pouziva se jako argument metody setActionResult.
    //Hodnota konstanty definuje nazev sablony, ktera je pouzita pro zobrazeni
    //zpravy uzivateli, takze je neni mozne menit.
    const ACTION_RESULT_SUCCESS = 'success';

    //Definuje vysledek akce - neuspech. Pouziva se jako argument metody setActionResult.
    //Hodnota konstanty definuje nazev sablony, ktera je pouzita pro zobrazeni
    //zpravy uzivateli, takze je neni mozne menit.
    const ACTION_RESULT_FAILED = 'failed';

    //Definuje read-only zpusob zobrazeni formularoveho prvku
    const RENDER_STYLE_READONLY = 'readonly';

    //CSS trida, ktera bude prirazena formularovym tlacitkum (Ulozit, Odstranit, apod)
    const FORM_BUTTON_CSS_CLASS = 'form_button';

    //CSS trida, ktera bude prirazena tlacitko pro zavreni formulare bez ulozeni
    const FORM_BUTTON_CLOSE_CSS_CLASS = 'form_button_close';

    // Tato trida je formulari dynamicky pridelena jeho jQuery pluginem
    const FORM_CSS_CLASS = '__appform';

    // Lang form items 'mode' parameter value
    const LANG_MASTER = 'master';

    // Lang form items 'mode' parameter value (slave lang item shown languages list is always synchronized
    // with  master item
    const LANG_SLAVE = 'slave';

    // Na tomto klici se v postu ajaxem posilaji povolene jazyky z master prvku
    const ENABLED_LANGUAGES_POST_KEY = 'enabled_languages';

    //Model nad kterym formular stoji
    protected $_model = NULL;

    protected $_loaded_model = NULL;

    //Formularova data, ktera jsou urcena k ulozeni
    protected $_form_data = array();


    protected $_form_data_original = array();

    //Zde budou ulozeny jednotlive formularove prvky ve forme ReflectionClass
    protected $_form_items = array();

    protected $_config_original;

    //slouzi k ulozeni konfigurace pro tento formular - jsou tady fallback hodnoty
    protected $_config = array(
        
        //Sablona do ktere se vklada vlastni formular. Definuje hlavicku a paticku
        'container_view_name' => 'form_container_standard'
    );

    //pokud dojde k validacni chybe pred ulozenim, tak zde budou zapsany chyby
    protected $_error_messages = array();

    //do teto promenne se vklada sablona, ktera informuje uzivatele o vysledku akce
    protected $_action_result_view = NULL;

    /**
     * Do tohoto atributu se ulozi status provedene akce v metode setActionResult.
     * Status definuje zda byla akce provedena uspesne nebo neuspesne.
     */
    protected $action_result_status = NULL;

    //zde se ulozi konstanta, ktera definuje zda vysledek provedene akce byl
    //v poradku nebo ne
    protected $requested_action_result = NULL;

    //do teto promenne se uklada konstanta, ktera definuje pozadovanou akci
    protected $requested_action = NULL;

    //pokud
    protected $in_itemlist = FALSE;

    //zde se uklada nahodne ID formulare pri pouziti na appformitemadvanceditemlist
    //toto ID musi byt jedinecne v ramci vsech formularu, ktere jsou v advanceditemlist
    //nactene.
    protected $in_itemlist_id = NULL;

    //Definuje zda je instance formulare vytvrane v ramci ajax pozadavku - tedy
    //formular v dialogovem okne
    protected $is_ajax = NULL;

    /**
     * Provadi inicializaci objektu, nacita formularove prvky.
     * @param ORM $model
     * @param Kohana_Config_Reader $config
     * @param array $form_data 
     */
    public function __construct(Kohana_ORM $model, Kohana_Config_Reader $config, array $form_data, $is_ajax = FALSE)
    {
        //ulozim si referenci na ORM model nad kterym formular stoji
        $this->_model = $model;

        $this->_config_original = $config;

        $this->_form_data_original = $form_data;

        //ulozim si hodnotu, ktera rika ze je instance formulare pouzita
        //v ajax pozadavku
        $this->is_ajax = $is_ajax;
    }

    public function init()
    {
        //tato reference bude predana do jednotlivych formitemu a v pripade
        //uspesneho ulozeni nebo odstraneni $this->_model ji potrebuji prepsat
        //coz zaridim pomoci Proxy navrhoveho vzoru.
        //@TODO: Odmena 500Kc pro toho kdo tohle dokaze vyresit bez Proxy
        $this->_loaded_model = ORM_Proxy::factory(clone $this->_model);

        //je formular nacitan do itemlistu
        $this->in_itemlist = arr::get($this->_form_data_original, 'itemlist', FALSE);

        if ($this->in_itemlist)
        {
            $this->_config['container_view_name'] = 'form_container_itemlist';
        }
        //pokud je formular v ajax rezimu tak se pouzije jina defaultni
        //sablona pro form container
        else if ($this->is_ajax)
        {
            $this->_config['container_view_name'] = 'form_container_ajax';
        }

        // nactu konfiguraci pro tento formular - merguje se s fallback hodnotami
        // udelam to rucne, protoze chci zachovat Kohana_ConfigFile
        foreach ($this->_config as $key => $value)
        {
            $this->_config_original->set($key, $value);
        }
        $this->_config = $this->_config_original;

        //these values will be used as default
        $this->_form_data_defaults   = arr::get($this->_form_data_original, 'defaults', array());
        //these valus will overwrite whatever arrived from the form
        $this->_form_data_overwrites = arr::get($this->_form_data_original, 'overwrite', array());

        //wont be needed anymore
        unset($this->_form_data_original['defaults'], $this->_form_data_original['overwrite']);

        //initialize with the default data
        if ( ! $this->_model->loaded())
        {
            $this->applyFormDataValues($this->_form_data_defaults);
        }

        //load form data
        $this->_form_data = arr::merge($this->_form_data, $this->_form_data_original);

        //vlozi data do ORM modelu anebo do $this->_form_data
        $this->applyFormDataValues($this->_form_data_overwrites);

        //detekce pozadovane akce
        $this->requested_action = arr::getifset($this->_form_data, self::ACTION_KEY, NULL);

        //nactu formularove prvky
        $this->loadFormItems();

        // Pokud je vyzadovano v configu, automaticky ulozime non-loaded model
        if (arr::get($config, 'autosave_model', false) and ! $this->_model->loaded()) {
            $this->_model->save();
        }

        return $this;
    }

    /**
     * Takes the array passed as argument and puts the values either in the ORM model
     * (for attributes that do not have a Form Item)
     * or in the $this->_form_data array, which is a data source for the
     * form items.
     *
     * @param $values
     */
    public function applyFormDataValues($values)
    {
    //    Kohana::$log->add(Kohana::INFO, 'applyFormDatavalues: '.json_encode($values));
        foreach ($values as $attr => $value)
        {
            //if there is not Form Item for the attribute, then
            //the value will be set in the ORM model directly
            if ( ! isset($this->config['items'][$attr]) && $this->_model->hasAttr($attr))
            {
                $this->_model->{$attr} = $value;
            }
            else
            {
                $this->_form_data[$attr] = $value;
            }
        }
    }
    /**
     * Provede zapis do logu a navic prida informace o aktualnim objektu
     * a formularovych datech pro snadnejsi diagnozu problemu.
     * @param <string> $message Hlavni zprava, ktera bude zapsana do logu.
     */
    protected function _log($message)
    {
        Kohana::$log->add(Kohana::ERROR, $message.'Object name:"'.$this->_model->object_name().'". Serialized data:"'.serialize($this->_form_data).'"');
    }

    /**
     * Metoda slouzi k nastaveni vysledku akce, ktery bude zobrazen uzivateli
     * na formulari.
     * 
     * @param <string> $action Identifikator provedene akce (jedna z konstant self::ACTION_*)
     * @param <type> $result Identifikator vysledku akce (jedna z konstant self::ACTION_RESULT_*)
     *
     * Na zaklade predchozizch dvou argumentu je sestavena defaultni zprava pro
     * uzivatele.
     *
     * @param <string> $message Explicitni definice zpravy pro uzivatele - pokud
     * neni rovno NULL tak se se nebude zprava sestavovat podle prvnich dvou
     * argumentu.
     */
    protected function setActionResult($action, $result = NULL, $message = NULL)
    {
        //pokud je exlicitne definovano v konfiguraci ze se nema zobrazovat vysledek akce,
        //tak se do dane promenne vlozi prazdna hodnota
        if ( ! arr::get($this->_config, 'display_action_result', TRUE))
        {
            $this->_action_result_view = NULL;

            return;
        }

        //ulozim si vysledek akce
        $this->action_result_status = $result;

        //pokud ma argument pro zpravu hodnotu NULL, tak doplnim defaultni zpravu
        if ($message === NULL)
        {
            //jazykovy klic - jedna se o specificky klic pro objekt se kterym formular
            //pracuje. Pokud neni kotva definovana tak se tam hodi obecne hlaska
            $message = ___($this->_config->get_group_name().'.form_action_result_message.'.$action.'_'.$result, array(),
                           'form_action_result.message_'.$action.'_'.$result);
        }

        //pokud neni definovana sablona tak doplnim defaultni
        if ($this->_action_result_view === NULL && ! empty($result))
        {
            $this->_action_result_view = View::factory('formaction/'.$result);

            //nastavim zpravu pro uzivatele
            $this->_action_result_view->user_message = $message;
        }

        //do view se vzdycky prida vycet validacnich chyb - pro prvky, ktere
        //nejsou na formulari - takove pripady by nemely nastat, ale v pripade
        //ze nastavnou bude uzivatel schopen chybejici hodnoty doplnit. Toto
        //muze nastat kdyz se jeden zaznam edituje na vice nez jednom formulari.
        $foreign_error_messages = $this->_error_messages;

        foreach ($this->_form_items as $attr => $item)
        {
            // Zjistime jake validacni hlasky si prvek zpracuje a ty odebereme
            $handled_keys = $item->getHandledErrorMessagesKeys();
            foreach ($handled_keys as $key) {
                unset($foreign_error_messages[$key]);
            }
        }

        $this->_action_result_view->error_messages = $foreign_error_messages;
    }

    /**
     * Nacte vsechny formularove prvky podle konfigurace formulare.
     */
    protected function loadFormItems()
    {
        //Pokud je definovana tato globalni konstanta, tak se bude zvlast profilovat
        //inicializace formulare
        if (AppConfig::instance()->get('extended_profiling', 'system'))
        {
            //nacitani formularovych prvku budu stopovat
            $profiler_token = Profiler::start('Form', 'loadFormItems'); //@TODO: if s konstantou
        }

        //definice formularovych prvku
        $form_items = arr::getifset($this->_config, 'items', array());

        foreach ($form_items as $attr => $item_config)
        {
            $class_name = $this->getClassName(arr::getifset($item_config, 'type'));

            try
            {
                //instance formularovych prvku budu tvorit pomoci reflexe
                $form_item = new ReflectionClass($class_name);
            }
            catch (ReflectionException $e)
            {
                $this->_log('Reflection exception - unable to load class "'.$class_name.'".');
                continue;
            }

            //nova instance - konstruktoru predavam referenci na ORM model a komplet formularova data
            $form_item_class = $form_item->newInstance($attr,
                                                       $this->_config,  //prvku predavam cely objekt Kohana_Config_File - vytahne si svou konfiguraci
                                                       $this->_model,
                                                       $this->_loaded_model,
                                                       arr::getifset($this->_form_data, $attr),
                                                       $this);

            //vyvola incializaci prvku
            $form_item_class->init();

            //non-virtual items need to be based on an attribute of the underlying model,
            //otherwise they are ignored
            if ( ! $form_item_class->isVirtual() && ! $this->_model->hasAttr($attr))
            {
                continue;
            }

            //referenci na form prvek si ulozim
            $this->_form_items[$attr] = $form_item_class;
        }

        //pokud je aktivni profilovani inicializace formulare
        if (AppConfig::instance()->get('extended_profiling', 'system') && $profiler_token !== NULL)
        {
            //stopnu profiler
            Profiler::stop($profiler_token);
        }
    }

    /**
     * Pro pozadovany typ prvku vraci nazev tridy, ktera jej implementuje.
     * @param <string> $type
     */
    protected function getClassName($type)
    {
        return 'AppFormItem_'.ucfirst($type);
    }

    /**
     * Detekuje zda ma aktualni uzivatel opravneni pro odstraneni zaznamu - na
     * zaklade tohoto je pak v Ajaxovych formularich zobrazeno tlacitko Odstranit.
     * @return <bool> TRUE v pripade ze uzivatel ma opravneni na odstraneni zaznamu,
     * FALSE pokud ne.
     */
    protected function is_deletable()
    {
        return Auth::instance()->get_user()->HasPermission($this->_model->object_name(), 'delete')
                && $this->_model->testUserDeletePermission(Auth::instance()->get_user())
                && arr::get($this->_config, 'deletable');
    }

    /**
     * Detekuje zda ma podle konfigurace byt formular vygenerovan jako readonly.
     * Dale kontroluje opravneni uzivatele na dany objekt - pokud nema opravneni
     * 'edit' tak je take vraceno TRUE.
     * 
     * @return <bool> TRUE v pripade ze v konfiguraci formulare je explicitne
     * definovano, ze ma byt formular vygenerovan jako readonly. FALSE v
     * opacnem pripade.
     */
    public function is_readonly($attr = NULL)
    {
        //pokud se testuje pouze atribut, tak v pripade ze se jedna o relacni
        //objekt tak provedu kontrolu opravneni na dany objekt
        if ($attr != NULL)
        {
            //pokud se jedna o "relacni" atribut - tedy relacni objekt
            if ($this->_model->hasRelAttr($attr))
            {
                //tak provedu strandardni kontrolu opravneni
                if ( ! Auth::instance()->get_user()->HasPermission($attr, 'edit'))
                {
                    return TRUE;
                }
            }
        }

        //pokud je v konfiguraci explicitne definovan priznak readonly, tak je
        //cely formular readonly
        $readonly_param = arr::get($this->_config, 'readonly', FALSE);

        if ($readonly_param === TRUE)
        {
            return TRUE;
        }
        else if ($readonly_param instanceof Closure)
        {
            $retval = call_user_func($readonly_param, $this->_model);

            if ($retval !== NULL)
            {
                return (bool)$retval;
            }
        }

        if ($this->_model->loaded())
        {
            //anebo pokud nema uzivatel opravneni 'edit' na danem objektu, tak mu
            //je take zobrazen jako readonly
            if ( ! Auth::instance()->get_user()->HasPermission($this->_model->object_name(), 'edit'))
            {
                return TRUE;
            }

            //uzivatel muze mit pravo na editaci nad celym objektem, ale nemusi mit opravneni
            //na editaci na konkretni zaznam
            if ( ! $this->_model->testUserUpdatePermission(Auth::instance()->get_user()))
            {
                return TRUE;
            }
        }
        else
        {
            //anebo pokud nema uzivatel opravneni 'edit' na danem objektu, tak mu
            //je take zobrazen jako readonly
            if ( ! Auth::instance()->get_user()->HasPermission($this->_model->object_name(), 'new'))
            {
                return TRUE;
            }

            //uzivatel muze mit pravo na editaci nad celym objektem, ale nemusi mit opravneni
            //na editaci na konkretni zaznam
            if ( ! $this->_model->testUserInsertPermission(Auth::instance()->get_user()))
            {
                return TRUE;
            }
        }

        //jinak neni readonly vyzadovano (i presto muze byt vyzadano argumentem
        //metody renderItem na kazdem prvku zvlast)
        return FALSE;
    }

    /**
     * Nalezne pozadovanou akci k provedeni (ve formularovych datech), tu provede
     * a zajisti zpracovani vysledku akce (zobrazeni uzivateli apod.).
     */
    public function Process($requested_action = NULL)
    {
        if (empty($requested_action))
        {
            $requested_action = $this->requested_action;
        }

        //nemusi byt pozadovana zadna akce anebo pokud je v konfiguraci formulare
        if (empty($requested_action) || $this->is_readonly())
        {
            return;
        }

        //v pripade ze dojde k chybe pri ukladani, coz znamena chyceni
        //vyjimky z metody runAction ( a nasledne save nebo delete v ORM)
        //bude do teto promenne ulozena custom zprava, kterou nastavi
        //zachycena vyjimka. Je to proto aby mohla prima specificka akce ORM
        //definovat co ma byt zobrazeno uzivateli
        $special_message = NULL;

        //definuje zda bude nastavena hlaska, ktera informuje o vysledku
        //provedene akce
        $show_action_result = TRUE;

        //ocekavam ze pri ukladani muze dojit k problemu:
        // - neuspesna validace
        // - vyjimka pri ulozeni
        // - custom vyjimka
        try
        {
            $this->requested_action_result = $this->runAction($requested_action);
        }
        catch (Exception_ModelDataValidationFailed $e)
        {
            //v konfiguraci muze byt nastaveno ze se nema zobrazovat
            //obecna chybova hlaska informujici o validacni chybe nekde ve formulari
            if ( ! arr::get($this->_config, 'display_general_validation_error', TRUE))
            {
                $show_action_result = FALSE;
            }
            else
            {
                //nastavim vysledek akce
                $this->requested_action_result = self::ACTION_RESULT_FAILED;

                //vyjimka definuje chybovou zpravu, ktera bude uzivateli zobrazena
                //namisto standardni zpravy, ktera je definovana uspechem nebo neuspechem
                //jedne ze zakladnich akci (napr. CHYBA pri UKLADANI, CHYBA pri MAZANI)
                $special_message = $e->getUserMessage();
            }
        }
        //vyjimka, ktera muze byt "zobrazena" na formulari
        catch (Exception_FormAction $e)
        {
            //nastavim vysledek akce
            $this->requested_action_result = self::ACTION_RESULT_FAILED;

            //vyjimka definuje chybovou zpravu, ktera bude uzivateli zobrazena
            //namisto standardni zpravy, ktera je definovana uspechem nebo neuspechem
            //jedne ze zakladnich akci (napr. CHYBA pri UKLADANI, CHYBA pri MAZANI)
            $special_message = $e->getUserMessage();
        }
        //neocekavana vyjimka
        catch (Exception $e)
        {
            kohana::$log->add(Kohana::ERROR, 'Error while processing form action: :text', array(
                'text' => kohana::exception_text($e)
            ));
            
            //nastavim vysledek akce
            $this->requested_action_result = self::ACTION_RESULT_FAILED;
        }

        if ($show_action_result)
        {
            //nedoslo k zadne vyjimce, zajistim zobrazeni vysledku akce na formulari
            $this->setActionResult($this->requested_action,
                $this->requested_action_result,
                $special_message);
        }

        //pokud ma byt formular resetovan po uspesnem provedeni akce, tak se
        //zde nahraje prazdny ORM model namisto aktualniho (ulozeneho)
        if (arr::get($this->_config, 'reset_after_action', FALSE) && $this->getRequestedActionResult() == self::ACTION_RESULT_SUCCESS)
        {
            $this->resetForm();
        }
    }

    /**
     * Provadi reset stavu formulare a modelu.
     * Nejdrive vyresetuje stav modelu, potom stav jednotlivych formularovych
     * prvku - v ramci toho prvky mohou nastavit svou defaultni hodnotu dle konfigurace
     * a nakonec provede nove nastaveni defaultnich hodnot modelu podle parametru
     * v konfiguraci formulare (ty jsou predany v konstruktoru teto tridy).
     */
    protected function resetForm()
    {
        $this->_model->clear();

        foreach ($this->_form_items as $attr => $instance)
        {
            $instance->clear();
        }

        //initialize with the default data
        if ( ! $this->_model->loaded())
        {
            $this->applyFormDataValues($this->_form_data_defaults);
        }
        //vlozi data do ORM modelu anebo do $this->_form_data
        $this->applyFormDataValues($this->_form_data_overwrites);
    }

    /**
     * Metoda na zaklade pozadovane akce vyvola prislusnou metodu. Pokud pro
     * danou akci neni definovana zadna obsluzna metoda, tak vraci FALSE.
     * @param <type> $requested_action
     * @return <bool|int> Vraci FALSE v pripade ze pro pozadovanou akci neni
     * definovana zadna obsluzna metoda V opacnem pripade vraci stav provedeni
     * akce - jendu z konstant ACTION_RESULT_*.
     */
    protected function runAction($requested_action)
    {
        switch ($requested_action)
        {
            case self::ACTION_SAVE:

                //provede validaci a ulozeni dat pomoci ORM modelu, vyvola prislusne formularove udalosti
                return $this->ActionSave();

            //break tu je jen tak aby se nereklo
            break;


            //provadi pouze validaci - toto vyuziva prvek AppFormItemAdvancedItemlist
            //ktery vytvari instanci Form tridy pro kazdou polozku a ulozit polozky
            //muze jen kdyz jsou vschny zvalidovane uspesne
            case self::ACTION_VALIDATE:

                return $this->ActionValidate();
                
            break;

            //provadi odstraneni zaznamu - toto vyuziva prvek AppFormItemAdvancedItemlist
            //ktery vytvari instanci Form tridy, ktera zajistuje i smazani polozky
            case self::ACTION_DELETE:

                return $this->ActionDelete();

            break;

            //defaultni akce - neprovede se nic, ale zapisu do logu ze k tomu doslo
            default:
                $this->_log('Form processing - no action defined.');
        }

        //pozadovana akce nebyla provedena
        return FALSE;
    }

    /**
     * Provadi ostraneni zaznamu, nad kterym stoji formular.
     */
    protected function ActionDelete()
    {
        //vlastni ulozeni zaznamu
        $this->_model->delete();

        //$this->_loaded_model je Proxy trida - po uspesnem ulozeni zaznamu
        //chci aby "ukazovala" na aktualni model
        $this->_loaded_model->setORM($this->_model);

        //resetuje aktualni stav modelu
        $this->resetForm();

        //akce probehla uspesne
        return self::ACTION_RESULT_SUCCESS;
    }

    /**
     * Provadi validaci a ulozeni formularovych dat pomoci ORM modelu.
     * Spousti prislusne formularove udalosti.
     *
     * @throws Exception_ModelDataValidationFailed V pripade neuspesne validace
     * formularovych dat.
     *
     * @throws Exception_SaveActionFailed V pripade ze doslo k chybe pri ukladani
     * a zaznam nebyl ulozen.
     *
     * @throws Exception_SaveActionFollowUpFailed V pripade ze doslo k chybe pri
     * ukladani zaznamu, ale zaznam byl ulozen (mohlo napr. dojit k chybe pri
     * zapisovani do historie zmen).
     *
     */
    protected function ActionSave()
    {
        $this->actionValidate();

        //validace uspesna, vyvolam formularovou udalost pred ulozenim
        $this->runFormEvent(self::FORM_EVENT_BEFORE_SAVE);

        // Ulozime model
        $this->saveModel();
        
        //vyvolam formularovou udalost po ulozeni
        $this->runFormEvent(self::FORM_EVENT_AFTER_SAVE);

        //akce probehla uspesne 
        return self::ACTION_RESULT_SUCCESS;
    }

    /**
     * Ulozi model
     * @throws Exception_SaveActionFailed
     */
    protected function saveModel()
    {
        //pri ukladani by mohlo dojit k neocekavane chybe
        try
        {

            //vlastni ulozeni zaznamu
            $this->_model->save();

            //$this->_loaded_model je Proxy trida - po uspesnem ulozeni zaznamu
            //chci aby "ukazovala" na aktualni model
            $this->_loaded_model->setORM($this->_model);
        }
            //doslo k nejake chybe pri ukladani
        catch (Exception $e)
        {
            kohana::$log->add(Kohana::ERROR, 'Error while saving form: :text', array(
                'text' => kohana::exception_text($e)
            ));

            //pokud byl zaznam v aktualnim stavu ulozen, tak je saved rovno TRUE
            if ( ! $this->_model->saved())
            {
                //k udalosti pridam i referenci na zachycenou vyjimku
                $event_data = array(self::FORM_EVENT_DATA_EXCEPTION_KEY => $e);

                //formularove prvky maji moznost reagovat
                $this->runFormEvent(self::FORM_EVENT_SAVE_FAILED, $event_data);

                //vyhodim vyjimku, ktera bude uzivatle informovat o problemu
                throw new Exception_SaveActionFailed('form_action_status.model_save_failed');
            }
            //zaznam byl ulozen, ale doslo k nejake chybe - uzivateli se zobrazi hlaseni
            //formularove prvky se o tomto nedozvi
            else
            {
                //vyhodim vyjimku, ktera bude uzivatele informovat o problemu
                throw new Exception_SaveActionFailed('form_action_status.model_saved_but_may_be_incosistent');
            }
        }
    }

    protected function ActionValidate()
    {
        //kazdy formularovy prvek pred ORM modelu aktualni data a ma moznost provest
        //svou custom validaci
        foreach ($this->_form_items as $attr => $item)
        {
            //samotny formularovy prvek ma moznost provest svou custom validaci
            //napriklad na relacnich zaznamech
            if (($check_retval = $item->check()) != NULL)
            {
                //ulozim si validacni chyby
                if ( ! is_array($check_retval))
                {
                    $check_retval = array($attr => $check_retval);
                }

                $this->_error_messages = arr::merge($this->_error_messages, $check_retval);
            }
        }

        //pustim validaci hlavniho ORM modelu
        $this->validateModel();

        //pokud jsme pri validaci ziskali nejake chybove hlasky, tak se nebude pokracovat v ulozeni zaznamu
        if ( ! empty($this->_error_messages))
        {
            //doslo k chybe pri validaci
            throw new Exception_ModelDataValidationFailed('form_action_result.model_validation_failed');
        }

        //vracim uspech - akce probehla uspesne
        return self::ACTION_RESULT_SUCCESS;
    }

    /**
     * Zvaliduje model a pripadne validation errors pri-mergne do lokalniho atributu
     */
    protected function validateModel()
    {
        if ( ! $this->_model->check())
        {
            //z ORM si vytahnu validacni chyby - chyby z form prvku vkladam do chyb
            //z ORM - chyby zachycene form prvky maji vyssi prioritu, a budou zobrazeny
            //na formulari "pred" chybami z ORM validace
            $this->_error_messages = arr::merge($this->_model->getValidationErrors(), $this->_error_messages);
        }
    }

    /**
     *
     * @param <int> $type Typ udalosti. Definovan jednou z konstant FORM_EVENT_*
     * @param <array> $data Data, ktera budou poslana formularovym prvku do
     * obsluzne metody udalosti. Defaultni hodnota je NULL.
     */
    protected function runFormEvent($type, $data = NULL)
    {
        //udalost vyvolam na kazdem formularovem prvku v poradi v jakem
        //byly vyvtoreny
        foreach ($this->_form_items as $item)
        {
            //vytvorim kopii dat jako ochrana proti modifikaci kdyby data byly
            //objektem
            $data_copy = $data;

            //spusteni udalosti na danem prvku
            $item->processFormEvent($type, $data_copy);

            //odstranim nepotrebnou kopii
            unset($data_copy);
        }
    }

    /**
     * Metoda vraci asoc. pole kde jsou definovany tlacitka pro formular.
     * Mezi tyto tlacitka patri tlacitko Ulozit, Odstranit apod.
     * Tlacitka jsou generovany na zaklade opravneni uzivatele.
     * Metodu je mozne pretizit a pridat custom tlacitka nebo odstranit
     * nektera z defaultnich.
     *
     * @return <array> Vraci asoc. pole kde jsou definovana tlacitka, ktera maji
     * byt vlozena na formular. Klicem v poli je definice akce tlacitka
     * (jedna z konstant ACTION_SAVE nebo ACTION_DELETE) a hodnotou je popisek
     * tlacitka. Obsah vraceneho pole je rozdelen do dvou casti, podle pozice
     * v paticce formulare. Zde je priklad:
     *
     * return array(
     *      //tlacitka, ktera budou zobrazena vleve casti paticky formulare
     *      'l' => array(
     *          self::ACTION_SAVE => 'Ulozit'
     *      ),
     *      'r' => array(
     *          self::ACTION_DELETE => 'Odstranit'
     *      )
     * )
     */
    protected function getFormActionButtons()
    {
//        //nactu si sablonu, ktera definuje tlacitka pro formular - bud defaultni
//        //nebo explicitne definovany v konfiguraci
//        $view_name = arr::get($this->_config, 'button_panel_view', 'form_button_panel');
//
//        $view = View::factory($view_name);
//
//        //reference na aktualni ORM model
//        $view->model = $this->_model;
//
//        //reference na aktualni formulare
//        $view->form = $this;
//
//        $view->config_group_name = $this->_config->get_group_name();

        //do tohoto pole vlozim tlacitka
        $buttons = array
        (
            //zde budou tlacitka, ktere maji byt zobrazeny na leve strane paticky
            'l' => array(),
            //zde budou tlacitka, ktere maji byt zobrazeny na prave strane paticky
            'r' => array()
        );

//        //pokud je v konfiguraci formulare explicitne definovano ze ma byt vygenerovan
//        //jako readonly, tak se nezobrazi zadna tlacitka
//        if ($this->is_readonly())
//        {
//            return $buttons;
//        }

        //rozlisujeme rozdil mezi vkladanim a editaci
        if ($this->_model->loaded())
        {
            if ( ! $this->is_readonly())
            {
                //tlacitko ulozit bude vlevo
                $buttons['l'][self::ACTION_SAVE] = array(self::ACTION_KEY,
                                                                ___('form_action_button.'.$this->_config->get_group_name().'.update_label','form_action_button.update_label'),
                                                                array(
                                                                    'confirm' => ___('form_action_button.'.$this->_config->get_group_name().'.update_action_confirm', array(), NULL),
                                                                    'value' => self::ACTION_SAVE,
                                                                    'class' => self::FORM_BUTTON_CSS_CLASS.' button red',
                                                                    //tento popisek bude zobrazen v progress indicatoru po kliknuti na toto tlacitko
                                                                    'ptitle'    => ___('form_action_button.'.$this->_config->get_group_name().'.update_ptitle',
                                                                                       'form_action_button.update_ptitle')
                                                                ));


                //tlacitko pro odstraneni zaznamu bude vpravo - ale jen v ajax formularich
                if ($this->is_deletable())
                {
                    //tlacitko ulozit bude vlevo
                    $buttons['r'][self::ACTION_DELETE] = array(self::ACTION_KEY,
                                                                    ___('form_action_button.'.$this->_config->get_group_name().'.delete_label','form_action_button.delete_label'),
                                                                    array(
                                                                        'confirm' => ___('form_action_button.'.$this->_config->get_group_name().'.delete_action_confirm', array(), NULL),
                                                                        'value' => self::ACTION_DELETE,
                                                                        'class' => self::FORM_BUTTON_CSS_CLASS.' button blue',
                                                                        //tento popisek bude zobrazen v progress indicatoru po kliknuti na toto tlacitko
                                                                        'ptitle'    => ___('form_action_button.'.$this->_config->get_group_name().'.delete_ptitle',
                                                                                           'form_action_button.delete_ptitle')
                                                                    ));
                }
            }
        }
        else
        {
            if ( ! $this->is_readonly())
            {
                //tlacitko ulozit bude vlevo
                $buttons['l'][self::ACTION_SAVE] = array(self::ACTION_KEY,
                                                                ___('form_action_button.'.$this->_config->get_group_name().'.insert_label','form_action_button.insert_label'),
                                                                array(
                                                                    'confirm' => ___('form_action_button.'.$this->_config->get_group_name().'.insert_action_confirm', NULL),
                                                                    'value' => self::ACTION_SAVE,
                                                                    'class' => self::FORM_BUTTON_CSS_CLASS.' button red',
                                                                    //tento popisek bude zobrazen v progress indicatoru po kliknuti na toto tlacitko
                                                                    'ptitle'    => ___('form_action_button.'.$this->_config->get_group_name().'.insert_ptitle',
                                                                                       'form_action_button.insert_ptitle'),
                                                                ));
            }
        }

        //tlacitko 'zavrit'
        $buttons['l']['close'] = array('close',
                                         __('form_action_button.close_label'),
                                         array(
                                            'class'     => self::FORM_BUTTON_CLOSE_CSS_CLASS.' button no-color',
                                         ));

        //vyslednou definici tlacitek vratim
        return $buttons;
    }

    /**
     * Vraci status provedene akce. Pokud nebyl nastaven zadny vysledek akce
     * tak vraci NULL.
     * @return <int>
     */
    public function getActionResultStatus()
    {
        return$this->action_result_status;
    }

    /**
     * Vraci zpravu doplnujici status provedene akce.
     * @return <string>
     */
    public function getActionResult()
    {
        return $this->_action_result_view;
    }

    public function getRequestedAction()
    {
        return $this->requested_action;
    }

    public function getRequestedActionResult()
    {
        return $this->requested_action_result;
    }

    public function getFormType()
    {
        return $this->_config->get_group_name();
    }

    /**
     * Metoda generuje hlavni nadpis formulare.
     *
     * Rozlisuje dva pripady - vytvoreni noveho zaznamu nebo editaci jiz
     * existujiciho zaznamu.
     *
     * @return <string>
     */
    public function getHeadline()
    {

        //nadpis se generuje podle toho zda je zaznam jiz ulozen
        return $this->_model->loaded()
                ? ___($this->getFormType().'.form_edit_headline', array(':preview' => $this->_model->preview()), __($this->_model->object_name().'.form_edit_headline', array(':preview' => $this->_model->preview())))
                : ___($this->getFormType().'.form_new_headline', array(), __($this->_model->object_name().'.form_new_headline'));
    }

    /**
     * Pro pouziti prvku na AppFormItemAdvancedItemlist je potreba aby mel formular
     * moznost modifikovat nazev (atribut name) kazdeho formularoveho prvku, ktery
     * na nem je. Proto pri vkladani nazvu formularoveho prvku do sablony je
     * volana tato metoda, ktera bud vrati puvodni nazev (predany argumentem)
     * nebo jej vrati modifikovany.
     * 
     * @param <string> $attr
     * @return <string>
     */
    public function itemAttr($attr)
    {        
        //$this->in_itemlist rika zda je formular generovan jako jedna z polozek
        //na prvku AppFormItemAdvancedItemlist
        if ($this->in_itemlist)
        {
            //prvky na jednotivych formularich maji stejne nazvy, takze je potreba
            //jejich nazvy nejak rozlisit - u tech, ktere stoji nad ulozenym
            //modelem se pouzije PK daneho modelu a u ostatnich se vygeneruje
            //nahodna hodnota
            if ($this->in_itemlist_id == NULL)
            {
                $this->in_itemlist_id = 'p'.mt_rand();
            }

            //pokud se v puvodnim nazvu prvku nachazi znak '[' tak musim nazev
            //urpavit jinak nez kdyz se jedna o obycejny prvek
            if (strpos($attr, '[') === FALSE)
            {
                $attr = '['.$attr.']';
            }
            else
            {
                $main_attr = substr($attr, 0,  strpos($attr, '['));
                
                $attr = str_replace($main_attr, '['.$main_attr.']', $attr);
            }

            //tady ta specialita je popsana v dokumentaci pro tridu helper_delayedstring
            //priprava promennych do klauzule 'use'
            $model = $this->_model;
            $in_itemlist = $this->in_itemlist;
            $form = $this;
            $id = $this->in_itemlist_id;

            //tato konstrukce zajistuje ze se closure vyvola az ve chvili kdy
            //je volana __toString metoda dane tridy
            return new helper_delayedstring(function() use(& $model, $in_itemlist, $id, $attr, & $form) {

                                $requested_action = $form->getRequestedAction();
                                $requested_action_result = $form->getRequestedActionResult();

                                //pred pokusem o ulozeni hlavniho zaznamu nelze
                                //vyzadovat nazev atributu
                                if ($requested_action == Core_AppForm::ACTION_SAVE && empty($requested_action_result))
                                {
                                    //vytjimka z metody __toString neni spravne zpracovane,
                                    //protoze udela jeste zapis do logu
                                    kohana::$log->add(Kohana::ERROR, 'V pripade pouziti form prvku uvnitr AppFormItemAdvancedItemlist nelze vyzadovat hodnotu $this->attr drive nez po pokusu o ulozeni hlavniho modelu. Viz. dokumentace k helper_delayedstring.');

                                    throw new Kohana_Exception('V pripade pouziti form prvku uvnitr AppFormItemAdvancedItemlist nelze vyzadovat hodnotu $this->attr drive nez po pokusu o ulozeni hlavniho modelu. Viz. dokumentace k helper_delayedstring.');
                                }

                                return $model->loaded()
                                    ? $in_itemlist.'['.$model->pk().']'.$attr
                                    : $in_itemlist.'['.$id.']'.$attr;
            });

            //vracim upravny nazev prvku
            return ;
        }
        else
        {
            return $attr;
        }
    }

    protected function getReadonlyBanner()
    {
        //pokud je v konfiguraci explicitne definovan priznak readonly, tak je
        //cely formular readonly
        $readonly_banner_param = arr::get($this->_config, 'readonly_banner', FALSE);

        if (is_string($readonly_banner_param))
        {
            return $readonly_banner_param;
        }
        else if ($readonly_banner_param instanceof Closure)
        {
            return call_user_func($readonly_banner_param, $this->_model);
        }
    }

    /**
     * Vraci referenci na formularovy prvek dany nazvem atributu nad kterym stoji.
     * @param <string> $attr
     * @return <AppFormItemBase|NULL> Vraci referenci na tridu dedici z AppFormItemBase
     * v pripade ze byl prvek nad danym atributem nalezen a v opacnem pripade
     * vraci NULL.
     */
    public function getItem($attr)
    {
        return isset($this->_form_items[$attr])
                ? $this->_form_items[$attr]
                : NULL;
    }

    /**
     * Generuje HTML reprezentaci formularoveho prvku na atribut $attr.
     *
     * @param <string> $attr Nazev atributu
     * @return <type> 
     */
    public function RenderItem($attr, $style = NULL)
    {
        if ( ! isset($this->_form_items[$attr]))
        {
            //neexistujici prvek nebude vykreslen, provedu zapis do logu
            $this->_log('Unable to render AppFormItem for non-existing attr "'.$attr.'".');
            return NULL;
        }

        //pokud je v konfiguraci formulare explicitne definovano ze ma byt vygenerovan
        //jako readonly, tak vsechny prvky budou jako readonly
        if ($this->is_readonly(/*$attr*/))
        {
            $style = AppForm::RENDER_STYLE_READONLY;
        }

        //prvek existuje - bude vykreslen
        return $this->_form_items[$attr]->Render($style, $this->_error_messages);
    }

    /**
     * Metoda generuje kompletni formular.
     *
     * Vlastni sablona formulare, ktera definuje rozmisteni prvku je definovana
     * v konfiguraci na klici 'view_name'.
     * Tato sablona je pote vlozena do container sablony, ktera je definovana
     * atributem $this->_container_view_name.
     *
     * @return <View>
     */
    public function Render($form_action_link = NULL)
    {
        //zakladni sablona formulare, ktera definuje tlacitka ulozit, odstranit apod.
        $container_view = View::factory($this->_config['container_view_name']);

        //z konfigurace si nactu sablonu, ktera ma byt pouzita pro vykresleni formulare
        $form_view = View::factory($this->_config['view_name']);

        //sablone predam referenci na tento formular, pres kterou budou vykresleny
        //jednotlive prvky
        $form_view->form = $this;

        //reference na aktualni model
        $form_view->model = $this->_model;
        $form_view->loaded_model = $this->_loaded_model;

        //do container sablony vlozim vlastni sablonu formulare
        $container_view->form_view = $form_view;

        //sablone predam referenci na tento formular
        $container_view->form = $this;

        //tlacitka formulare
        $container_view->form_buttons = $this->getFormActionButtons();

        //pokud doslo k nejake akci, tak je action_result neprazdne a obsahu info o vysledku akce
        $container_view->action_result = $this->_action_result_view;

        //hodnota pro 'action' atribut <form> - aktualni URL
        $container_view->form_action_link = $form_action_link == NULL
                                                ? Request::instance()->current_url()
                                                : $form_action_link;

        //reference model zaznamu, se kterym se na formulari pracuje
        $container_view->model = $this->_model;
        $container_view->loaded_model = $this->_loaded_model;

        //custom css class
        $container_view->css = arr::get($this->_config, 'css');

        //vysledek provedene akce do stranky zobrazim pouze v pripade ze
        //byl vysledek akce neuspesny - pokud byla akce uspesna, tak se
        //uzivateli zobrazi hlaska, ktera jej nebude rusit (melo by to byt
        //neco jako ten zluty prhu ve stylu GMailu)
        //dale pak pokud je formular v itemlistu (advanceditemlist) tak se
        //zobrazuje jen globalni hlaska.
        if ($this->requested_action_result == AppForm::ACTION_RESULT_SUCCESS || $this->in_itemlist)
        {
            $container_view->action_result = '';
        }

        //readonly banner je zobrazen stejne jako standardni banner, ale
        //specificky pouze v pripade kdy je formular v readonly stavu
        if ($this->is_readonly() && ($readonly_banner = $this->getReadonlyBanner()) != NULL)
        {
            $banner_view = View::factory($readonly_banner);

            $banner_view->model = $this->_model;
            $banner_view->loaded_model = $this->_loaded_model;

            $container_view->banner = $banner_view;
        }
        else if (   isset($this->_config['banner'])
                 && ($this->requested_action_result == AppForm::ACTION_RESULT_SUCCESS || empty($this->requested_action_result)))
        {
            $banner_view = View::factory($this->_config['banner']);

            $banner_view->model = $this->_model;
            $banner_view->loaded_model = $this->_loaded_model;

            $container_view->banner = $banner_view;
        }

        // vracim sablonu
        return $container_view;
    }
}

