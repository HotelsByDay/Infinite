<?php

/**
 * 
 *
 */
abstract class Filter_Base
{

    /**
     * Konfigurace pro danou instanci tridy.
     *
     * Toto pole je urceno k nastaveni v dedici tride a pote je k nemu doplnena
     * hodnota $this->default_config, coz zajisti doplneni defaultni hodnot
     * do tech parametru, ktere nebyly explicitne definovane.
     *
     * @var <array>
     */
    protected $config = array();

    /**
     * Defaultni konfigurace pro instanci filtru.
     *
     * V implementaci tridy neni nikde kontrola zda jeden z nize definovanych
     * klicu v poli skutecne existuje. Implementace tridy pocita s tim v poli
     * $this->default_config budou vzdy alespon tyto klice.
     *
     * @var <array>
     */
    protected $default_config = array(
        //moznosti pro vyber velikosti stranky
        'page_size' => array(
            '15' => '15',
            '30' => '30',
            '50' => '50'
        ),

        //pocet stranek zobrazenych pred posledni a za prvni strankou
        'near_end_items' => 2,

        //pocet stranek zobrazenych pred a za aktualne vybranou strankou
        'neighbour_items' => 2,

        //defaultni velikost stranky
        'default_page_size' => 15,
        //povolit funkci pro ulozeni stavu filtru ?
        'save_filtere_state' => TRUE,

        //Definuje defaultni smer razeni.
        //Musi to byt jedna z hodnot z atributu $this->orderby_dir_types.
        'default_orderby_dir' => 'desc',
        
        //Definuje atribut objektu podle ktereho se ma defaultne radit.
        'default_orderby'     => NULL,
    );

    /**
     * Vycet moznych zpusobu razeni.
     * Atribut $this->default_orderby_dir musi obsahovat jeden z techto retezcu.
     * @var <array>
     */
    protected $orderby_dir_types = array('asc', 'desc');

    /**
     * Definuje klic na kterem se v parametrech vyhledavani predava hodnota
     * pozadovaneho indexu stranky.
     * @var <string>
     */
    const PAGE_INDEX_KEY = '_pi';

    /**
     * Definuje klic na kterem se v parametrech vyhledavani predava pozadovana
     * velikos stranky s vysledky vyhledavani.
     * @var <string>
     */
    const PAGE_SIZE_KEY   = '_ps';

    /**
     * Definuje klic na kterem se v parametrech vyhledavani predava nazev atribut
     * podle ktereho se maji vysledky vyhledavani radit.
     * @var <string>
     */
    const ORDERBY_KEY     = '_ob';

    /**
     * Definuje klic na kterem se v parametrech vyhledavani predava pozadovany
     * smer razeni vysledku.
     * @var <string>
     */
    const ORDERBY_DIR_KEY = '_obd';

    /**
     * jQuery.objectDataPanel posila standardne fulltextove pozadavky na vyhledavani.
     * Vyhledavaci retezec posila na tomto klici.
     * @var <string>
     */
    const FULLTEXT_QUERY_KEY = '_q';

    /**
     * jQuery.objectDataPanel zasila standardne fulltextove pozadavky s prednastavenymi
     * filtry.
     * Seznam aktivnich prednastavenych filtru posila na tomto klici.
     * @var <string>
     */
    const PRESET_FILTER_KEY = '_f';

    /**
     * Na tomto klici se prenasi ID uzivatelskeho filtru (filterstate zaznam).
     * Pokud je pritomna v parametrech tak se nacte prislusny filtr a prijate
     * parametry se prepisi parametry daneho filtru.
     * @var <string>
     */
    const USER_FILTERSTATE_KEY = '_fs';

    /**
     * Na tomto klici se prenasi bool hodnota, ktera rika zda se maji filtrovat
     * pouze smazane zaznamy.
     * Pokud v parametrech neni tak se filtruji pouze nesmazane zaznamy. Pokud
     * ma hodnotu TRUE tak se filtruji jen smazane nabidky.
     * @var <string>
     */
    const USER_DELETED_KEY = '_d';

    /**
     * Do tohoto pole se ulozi paramtrey vyhledavani se kterymi bude instance
     * pracovat.
     * @var <array>
     */
    protected $filter_params = array();

    /**
     * Nazev objektu nad kterym filtr pracuje. Nacita ORM model s timto nazvem.
     * @var <string>
     */
    protected $object_name = NULL;

    /**
     * Typ tabulkoveho vypisu se kterym se pracuje. Standardne to byva 'table'.
     * Muzeme ale mit vice typu vypisu nad jednim poradacem (napr. 'table1', 'table2',
     * atd...)
     * @var <string>
     */
    protected $table_type = NULL;

    /**
     * Nazev kontroleru nad kterym filtr pracuje.
     * Slouzi pro generovani http odkazu pro cteni dat apod.
     * @var <type> 
     */
    protected $controller_name = NULL;

    /**
     * Zde je ulozene ID uzivatele, ktere se pouzije 
     * pro nacteni ulozenych filtru uzivatele pro dany objekt.
     *
     * @var <int>
     */
    protected $user_id = NULL;

    /**
     * Do tohoto atributu se uklada pocet nalezenych vysledku v metode getResults.
     * Tato hodnota se pak dale pouziva v metode getPager, ktera je volana az po
     * getResults.
     * @var <int>
     */
    protected $results_total_count = NULL;

    /**
     * Pokud je filtr inicializovat nejakym FilterState objektem (  tomu dochazi
     * tak ze se do $filter_params v konstruktoru prida identifikator FilterState
     * zaznamu podle ktereho se ma filtrovat, dojde k aktualizaci jeho 'size'
     * parametru.
     * @var <Model_FilterState>
     */
    protected $filterstate_object = NULL;

    /**
     * Do tohoto pole budou vlozeny parametry pro inicializaci jQuery pluginu,
     * ktery zajistuje funkci formulare pro filtrovani.
     */
    protected $jquery_objectFilter_init_params = array();

    /**
     * Do tohoto atributu se zapise zda jsou povoleny uzivatelske filtry.
     * Kontroluje se hlavni konfiguracni soubor a konfigurace tabulkoveho
     * vypisu, ktera je instanci predana. Nastaveni hodnoty probiha v konstruktoru.
     *
     * @var <bool>
     */
    protected $user_filters_enabled = FALSE;

    /**
     * Provadi nacteni konfigurace pro filtr.
     */
    public function __construct($controller_name, $object_name, $filter_params, $user_id, $config)
    {
        //ulozim si ID uzivatele - to se pouzije pro nacteni ulozenych filtru
        //daneho uzivatele
        $this->user_id = $user_id;

        //ulozim si nazev objektu a kontroleru
        $this->object_name     = $object_name;
        $this->controller_name = $controller_name;
        
        //pokud je v parametrech definovan klic definujici uzivatelsky filtr (FilterState zaznam)
        if (isset($filter_params[Filter_Base::USER_FILTERSTATE_KEY]))
        {
            //nactu si pozadovany ORM model filterstate zaznamu
            $filterstate = ORM::factory('filterstate', $filter_params[Filter_Base::USER_FILTERSTATE_KEY]);

            //pokud byl dany filtr nalezen, tak ziskam jeho parametry 
            if ($filterstate->loaded())
            {
                //prepisu argument konstruktoru a profiltruje parametry - pri nacitani
                //ulozeneho filtru me napriklad nezajima nastaveni strankovani, ktere v
                //nem muze byt ulozene
                $filter_params = $this->cleanFilterStateParams($filterstate->_filter_params, $filter_params);
                //ulozim si referenci na FilterState objekt, ktery byl pouzit
                //pro ziskani parametru - pri cteni dat budu aktualizovat
                //jeho 'size' hodnotu
                $this->filterstate_object = $filterstate;
            }
        }

        //pokud je konfigurace instanci Kohana_Config tak si vytahnu nazev konfigu
        //a podle neho se pak nacte i sablona pro vykresleni dat (jinak se pouzije
        //standardni)
        if ($config instanceof Kohana_Config_File)
        {
            //nazev konfiguraku musi byt ve tvaru "objectname_tabletype"
            $this->table_type = str_replace($this->object_name.'_', '', $config->get_group_name());
        }

        //pokud je definovana konfigurace v parametru metody, tak ji pouziju
        //a mergnu se zakladni konfiguraci tridy
        $config = array_merge($this->config, (array)$config);

        //provedu mergnuti defaultni konfigurace a explicitne nastavene konfigurace
        $this->config = array_merge($this->default_config, $config);

        //ulozim si parametry vyhledavani - cleanParams, zaridi trimovani hodnot
        //zkontroluje orderby nastaveni apod.
        $this->filter_params = array_merge(
                                    //vezmu defaultni parametry nastavene v $this->filter_params, to se prepise
                                    //defaultnimi aprametry z configu a potom se to prepise tim co prislo jako
                                    //filtrovaci parametry do konstruktoru
                                    array_merge($this->filter_params, (array)arr::get($this->config, 'default_filter_params')),
                                    $this->cleanParams($filter_params));

        //ukladani uzivatelskych filtru musi byt globalne povoleno a stejne tak i
        //v konfiguraci filtru pro dany objekt
        $this->user_filters_enabled = AppConfig::instance()->get('enable_user_filters', 'system')
                                        && arr::get($this->config, 'save_filtere_state') == TRUE;
    }

    /**
     * Metoda vlozi parametry do interni promenne odkud bude dostupna implementacim
     * filtru. 
     * Nepocita s tim ze $filter_params je multirozmerne pole.
     *
     * Dale provadi zpracovani hodnot nekterych parametru. Kazda hodnota je automaticky
     * trimovana. Dale pak:
     *  - pageindex: kontrola zda jde o ciselnou pozitivni hodnotu, horni limit je
     *            kontrolovan az pri cteni dat, tedy ve chvili kdy uz je znam
     *            celkovy pocet vysledku.
     *            Od uzivatele je ocekavana 1-based hodnota, zde se prevadi na 0-based.
     *  - oderby: Pokud neni v parametrech definovan atribut a smer razeni, tak
     *            je do parametru vlozena defaultni hodnota z $this->default_orderby
     *            a $this->default_orderby_dir. Ale jen v pripade ze
     *            $this->default_orderby neni prazdna hodnota.
     *
     *
     * @param <array> $filter_params
     */
    protected function cleanParams($filter_params)
    {
        //otrimovani vsech hodnot
        foreach ($filter_params as $key => $value)
        {
            if (is_string($value))
            {
                $filter_params[$key] = trim($value);
            }
        }
        
        //pokud neni definovan zpusob razeni v parametrech explicitne tak nastavim defaultni
        //ktery bude byt nastaven dedici classou
        if ( ! isset($filter_params[self::ORDERBY_KEY])  
                && isset($this->config['default_orderby']) && ! empty($this->config['default_orderby'])
                && isset($this->config['default_orderby_dir']) && ! empty($this->config['default_orderby_dir']))
        {
            $filter_params[self::ORDERBY_KEY]     = $this->config['default_orderby'];
            $filter_params[self::ORDERBY_DIR_KEY] = $this->config['default_orderby_dir'];
        }

        //osetrim nevalidni hodnoty v parametru urcujicim pozadovane stranky
        //uzivatel tuto hodnotu vklada rucne
        if (isset($filter_params[self::PAGE_INDEX_KEY]))
        {
            //s hodnotou budu mozna manipulovat - nize ji vratim zpet do pole
            //$filter_params
            $page_index = trim($filter_params[self::PAGE_INDEX_KEY]);

            //pokud hodnota neobsahuje pouze cislice tak ji budu "resetovat"
            //na hodnotu '0'
            if ( ! preg_match('#^[0-9]+$#', $page_index))
            {
                $page_index = 0;
            }
            //vratim zpatky do pole, ktere obsahuje parametry vyhledavani
            //NAVIC - vzdy odectu hodnotu '1' od indexu stranky, protoze z prohlizece
            //chodi 'uzivatelske hodnoty' - tj. 1-based, zatimco tady potrebuji
            //pracovat s hodnotami 0-based
            $filter_params[self::PAGE_INDEX_KEY] = max($page_index, 0);
        }

        //pokud neni exlicitne definovana velikost stranky tak vracim defaultni
        //podle konfigurace
        if ( ! isset($filter_params[self::PAGE_SIZE_KEY]) && ! empty($this->config['default_page_size']))
        {
            $filter_params[self::PAGE_SIZE_KEY] = $this->config['default_page_size'];
        }

        //vracim zpracovane parametry vyhledavani
        return $filter_params;
    }

    /**
     * Metoda ocekava parametry z FilterState zaznamu a standardni parametry filtru a
     * provadi jejich slouceni. V ulozenem filtru muze byt ulozena velikost stranky,
     * aktualni stav razeni a podobne. Tyto hodnoty prepise aktualnimi hodnotami,
     * ktere jsou ve $filter_params - tedy to co si uzivatel prave naklikal.
     * @param <array> $filterstate_params
     */
    public function cleanFilterStateParams($filterstate_params, $filter_params = array())
    {
        //vybrane parametre vezmu z filtrovacich parametru a pouzije je k prepsani
        //tech co jsou ulozene ve FilterState parametrech
        $priority_filter_params = array_intersect_key($filter_params,
                                            array_flip(array(
                                                self::PAGE_SIZE_KEY,
                                                self::PAGE_INDEX_KEY,
                                                self::ORDERBY_DIR_KEY,
                                                self::ORDERBY_KEY,
                                                //chci aby tam zustalo i ID uzivatelskeho filtru (zakoduje se do URL na prehled apod)
                                                self::USER_FILTERSTATE_KEY)
        ));

        return array_merge($filterstate_params, $priority_filter_params);
    }

    /**
     * Metoda generuje View vyhledavaciho formulare.
     * V prvnim argumentu ocekava View obsahujici formular, ktery definuje
     * dana implementace filtru. V teto metode k filtru pridano tlacitko pro
     * ulozeni filtru a dalsi standardni prvky.
     *
     * Zajistuje ze to co prislo ve $form_view bude obaleno v [name=<$object_name>_filter]
     * a [id=<$object_name>_filter].
     *
     * @param <View> $form_view View formulare od dedici tridy.
     */
    public function getForm($form_view = NULL)
    {
        //zakladni parametry pro jquery.objectFilter
        $this->jquery_objectFilter_init_params['newFilterStateFormUrl'] = appurl::object_new_ajax('filterstate', 'filterstate_form', array('reltype' => LogNumber::getTableNumber($this->object_name)));
        $this->jquery_objectFilter_init_params['updateFilterStateUrl']  = appurl::object_filterstate_item($this->controller_name);
        $this->jquery_objectFilter_init_params['removeFilterStateUrl']  = appurl::object_remove_filterstate_item($this->controller_name);

        //url na editacni formular pro vlozeni nove sestavy pro export
        $this->jquery_objectFilter_init_params['newUserExportFormUrl']  = appurl::object_new_ajax('userexport', 'userexport_form', array('reltype' => LogNumber::getTableNumber($this->object_name)));

        //url pro generovani exportu dat
        $this->jquery_objectFilter_init_params['userExportUrl']         = appurl::object_export_data($this->controller_name);



        if ($this->user_filters_enabled)
        {
            $this->jquery_objectFilter_init_params['filterStateParams'] = array();
        }
        
        //pokud je $form_view NULL, tak podle standardniho pravidla se pokusim
        //nacist

        if ($form_view === NULL) {
            $form_view = View::factory('filter/'.arr::get($this->config, 'filter_view_name', $this->object_name),
                                       array(
                                           'defaults' => $this->filter_params,
                                       )
            );
        }

        //pole bude obsahovat defaultni hodnoty pro systemove parametry vyhledavani
        //mezi ktere patri zpusob razeni, velikost a index stranky pozadovaneho
        //prvku - tyto parametry budou pouzity pri prvnim nacteni dat,coz zajisti
        //ze se ulozi jako aktualni hodnoty a uzivatel je pak muze zmenit pouzitim
        //prislusnych GUI prvku
        $default_system_params = array();
        foreach (array('orderby_dir_key', 'orderby_key', 'page_index_key', 'page_size_key', 'user_filterstate_key') as $key)
        {
            //toto je trosku "experimentalni" ale pro zatim necham tak abych nemusel definovat pole
            //pro foreach cyklus i s nazvy konstant
            $text_key = constant('Filter_Base::'. strtoupper($key));

            if (isset($this->filter_params[$text_key]))
            {
                $default_system_params[$text_key] = $this->filter_params[$text_key];
            }
        }

        // inicializacnimu skriptu pro jQuery-objectFilter predam defaultni
        // systemove parametry vyhledavani
        $this->jquery_objectFilter_init_params['defaults'] = $default_system_params;
        
        // pokud je povolena funkcnost pro ukladani filtru, tak nactu ulozene filtry
        // uzivatele - muze mit i nula ulozenych filtru
        if ($this->user_filters_enabled)
        {

            //metoda mi vyhleda pozadovane filtry a vrati jako ORM modely
            //TRUE znamena - nacti vsechny pro model nad kterym stoji Filter
            $filterstates = ORM::factory('filterstate')->where('reltype', '=', LogNumber::getTableNumber($this->object_name))
                                                       ->where('deleted', '=', '0')
                                                       ->find_all();

            //seznam filtru chci predat i jako parametry do jquery.obejctFilter pluginu.
            //Ten je bude potrebovat pro vyplneni filtrovaciho formulare pri aktivaci filtru
            //pripravim seznam parametru jednotlivych filterstate zaznamu ve forme JSON
            $filterstate_params = array();

            foreach ($filterstates as $filterstate)
            {
                //parametry filtru, ktere vlozim do JS
                $filter_params =  $filterstate->_filter_params;
                
                //pridam ID filtru
                $filter_params['_fs'] = $filterstate->pk();
                
                //parametry filtru
                $filterstate_params[] = $filter_params;
            }

            //vlozim do parametru pro jQuery plugin
            $this->jquery_objectFilter_init_params['filterStateParams'] = $filterstate_params;
        }

        //pokud existuje konfiguracni soubor pro exporty na danem poradaci
        $export_config = kohana::config($this->controller_name.'_export');

        //export bude povolen pokud je definovan klic 'enabled'
        $export_enabled = arr::get($export_config, 'enabled', FALSE);

        //pokud je definovan klic 'columns' tak bude povoleno i vytvoreni noveho exportu
        $export_columns_config = arr::get($export_config, 'columns', FALSE);

        $new_export_enabled = is_array($export_columns_config) && ! empty($export_columns_config);

        //zakladni trida, ktera zajistuje zapouzdreni forulare pro vlozeni filtrovacich
        //parametr
        Web::instance()->addCustomJSFile(View::factory('js/jquery.objectFilterForm.js'));

        //tento soubor zajistuje spusteni jquery.objectFilter s pozadovanymi parametry
        Web::instance()->addMultipleCustomJSFile(View::factory('js/jquery.objectFilter-init.js', array('init_params' => $this->jquery_objectFilter_init_params)));

        //URL na kterou maji byt posilany pozadavky na cteni dat se generuje
        //bud automaticky, anebo muze byt definovana explicitne 
        $action_link = arr::get($this->config, 'action_link', appurl::object_table_filter_action($this->controller_name, $this->table_type));

        $view = $this->_filter_container_view();

        //hlavni nadpis filtru - pokud se jedna o std. formular ve strance,
        //tak se pak nadpis aktualizuje pri kazdem ulozeni zaznamu
        $view->headline = $this->getHeadline();

        //Tento odka bude vozen do atributu 'action' elementu <form> a jQuery.objectFilter
        //plugin tuto URL pouzijek Ajax dotazum na cteni dat
        $view->action_link = $action_link;

        //vlastni formular
        $view->filter_form = $form_view;

        //defaultni systemove parametry (razeni, strankovani, apod)
        $view->default_system_params = $default_system_params;

        //je povolenou kladani uzivatelskych filtru ?
        $view->user_filters_enabled = $this->user_filters_enabled;

        //dostane se uzivatel k existujicim sestavam pro export
        $view->export_enabled = $export_enabled;

        //muze vytvaret nove sestavy pro export
        $view->new_export_enabled = $new_export_enabled;
        
        return $view;
    }

    /**
     * Generuje postrani panel s uzivatelskymi filtry.
     *
     * return <View>
     */
    public function getUserFilterPanel()
    {
        //pokud jsou uzivatelske filtry deaktivovany, tak metoda vraci prazdnou
        //hodnotu
        if ( ! $this->user_filters_enabled)
        {
            return View::factory('null');
        }

        //metoda mi vyhleda pozadovane filtry a vrati jako ORM modely
        //TRUE znamena - nacti vsechny pro model nad kterym stoji Filter
        $filterstates = ORM::factory('filterstate')->where('reltype', '=', LogNumber::getTableNumber($this->object_name))
                                                   ->where('deleted', '=', '0')
                                                   ->find_all();

        $items = array();

        foreach ($filterstates as $filterstate)
        {
            //nacte sablonu uzivatelskeho filtru
            $items[] = $this->getUserFilterItemView($filterstate);
        }

        return View::factory('table_side_panel', array(
            'items'  => $items,
            'header' => __('general.user_saved_filters_panel'),
            'id'     => 'user_filter_panel'
        ));

    }

    /**
     * Metoda generuje popisek pro navratovy odkaz, ktery ma vest na aktualni
     * tabulkovy vypis. Tento retezec se predava jako parametr v odkazech na
     * editaci jednotlivych zaznamu a prave na editacni strance je zobrazen
     * odkaz pro navrat zpet s timto popiskem.
     *
     * @return <string> Vraci popisek pro navratovy odkaz
     */
    public function getReturnLinkFilterLabel()
    {
        return __($this->object_name.'.return_link_label');
    }

    /**
     * Generuje hlavni nadpis pro stranku s vypisem nabidek.
     *
     * V dedicich classach je mozne tuto metodu pretizit a nadpis generovat v
     * zavislosti na aktualnich parametrech vyhledavani (nadpis se aktualizuje
     * pri kazdem filtrovani dat).
     *
     * @return <string>
     */
    public function getHeadline()
    {
        return __($this->object_name.'.filter_headline');
    }

    /**
     * Vraci instanci sablony, ktera ma byt pouzita jako obal pro filtrovaci formular.
     * Zakladni sablonou pro tento ucel je 'filter_container'.
     *
     * @return View
     */
    protected function _filter_container_view()
    {
        $filter_container = arr::get($this->config, 'filter_container', 'filter_container');
        return View::factory($filter_container);
    }

    /**
     * Vraci instanci sablony, ktera ma byt pouzita pro zobrazeni vlastnich
     * dat. Standardne se jedna o sablonu s nazvem OBJECT_NAME."_table"
     * @return View
     */
    public function _view_table_data()
    {
        //nazev sablony je bud explicitne definovan v konfiguraku nebo 
        //se sestavi defaultni nazev podle nazvu objektu
        $view_name = arr::getifset($this->config, 'view_name', $this->object_name.'_table');

        return View::factory($view_name);
    }

    /**
     * Metoda vraci celkovy pocet nalezenych vysledku.
     * @return <int>
     */
    public function getResultsTotalCount()
    {
        return $this->results_total_count;
    }

    /**
     * Metoda vraci indexovane pole, kde na prvnim indexu je celkovy pocet zaznamu,
     * ktery vyhovuje danemu filtru a na druhem indexu je ORM_Iterator, ktery
     * obsahuje vysledky pro danou stranku.
     * @return <ORM_Iterator> ORM_Iterator, ktery 'obsahuje' vysledky vyhledavani.
     */
    public function getResults($paginate = TRUE)
    {
        //podle parametru vyhledavani sesmolim vyhledavaci dotaz pres ORM modely
        //a vracim ORM_Iterator
        $orm = ORM::factory($this->object_name);

        //aplikuje specialni parametry jako napr. filtrovani jen smazanych zaznamu apod.
        $this->applySystemParams($orm);

        // metoda, ktera zajisti fulltext filtrovani
        // dalsi aplikuje "standardni" parametry, filtrovaciho formulare na /object/table.
        $fulltext_query = trim(preg_replace('/\s\s+/', ' ', arr::get($this->filter_params, Filter_Base::FULLTEXT_QUERY_KEY)));

        if ( ! empty($fulltext_query))
        {
            $this->applyFulltextFilter($orm, $fulltext_query);
        }

        $this->applyFilter($orm);
        $this->applyODPFilter($orm);

        //vraci celkovy pocet zaznamu co vyhovuji,
        //ukladam do atributu objektu, protoze pri generovani odkazu na strankovani
        //tuto hodnotu pouziju
        $this->results_total_count = $orm->count_all();

        //pokud uzivatel pozaduje stranku, ktera uz jakoby presahuje pocet nalezenych
        //vysledku tak index stranky upravim tak aby obsahovala posledni nalezeny
        //vysledek
        $page_index = $this->getCurrentPageIndex();
        $page_size  = $this->getPageSize();

        if ($page_index * $page_size > $this->results_total_count)
        {
            $this->filter_params[self::PAGE_INDEX_KEY] = (int)($this->results_total_count / $page_size);
        }

        //pokud existuje FilterState model, ktery byl pouzit pro ziskani parametru
        //vyhledavani tak provedu aktualizaci jeho 'size' parametru a zaroven
        //se spocita jeho statistika, ktera bude pridana k datum na vystup aby
        //byla provedena jeji aktualizace u klienta
        $filter_state_stats = $filter_state_id = NULL;

        if ($this->filterstate_object !== NULL)
        {
            //chci nastavit novou velikost filtru a zaroven musi dojit k resetovani
            //atributu delta, protoze uzivatel ma filtr aktivni tak mu nechci hodnotu
            //delta pocitat
            $this->filterstate_object->setSizeAndClearDelta($this->results_total_count);

            //dochazi k pouziti filtru - ulozim si aktualni hodnotu 'size'
            $this->filterstate_object->Save();

            //tuto hodnotu budu predavat na vystup
            $filter_state_id = $this->filterstate_object->pk();

            //pokud se filtruje podle FilterState zaznamu, tak je potreba poslat
            //i aktualizovane statistiky k danemu filtru (sablonu, ktera ji obsahuje)
            $filter_state_stats = $this->getUserFilterItemStatView($this->filterstate_object, FALSE);
        }

        //aplikuje specialni parametry jako napr. filtrovani jen smazanych zaznamu apod.
        $this->applySystemParams($orm);
        
        if ( ! empty($fulltext_query))
        {
            $this->applyFulltextFilter($orm, $fulltext_query);
        }
        
        $this->applyFilter($orm);
        $this->applyODPFilter($orm);

        //pokud neni strankovani explicitne vypnute, tak se pouzije
        if ($paginate)
        {
            //dale aplikuji nastaveni pageru
            $this->applyPagerSettings($orm);
        }

        //aplikuji razeni vysledku
        $this->applyOrderSettings($orm);

        //a vytahnu si ORM_Iterator, ktery definuje pozadovane vysledky vyhledavani
        return array(
            $orm->find_all(),
            $filter_state_id,
            $filter_state_stats);
    }

    public function getFilterParams()
    {
        return $this->filter_params;
    }

    /**
     * 
     */
    public function applySystemParams($orm)
    {
        //filtrovat podle hodnoty deleted (smazane nebo nesmazane) se bude pouze
        //v pripade ze model ma tento atribut
        if ($orm->hasAttr('deleted'))
        {
            if (arr::get($this->filter_params, Filter_Base::USER_DELETED_KEY, FALSE))
            {
                $orm->where($orm->table_name().'.deleted', 'IS NOT', DB::Expr('NULL'));
            }
            else
            {
                $orm->where($orm->table_name().'.deleted', 'IS', DB::Expr('NULL'));
            }
        }
    }

    /**
     * Metoda ma za ukol aplikovat 'where' a dalsi podminky na ORM objekt
     * dle parametru vyhledavani.
     * @param <&ORM> $orm
     */
    abstract protected function applyFilter($orm);
    
    abstract protected function applyFulltextFilter($orm, $query);

    /**
     * Wrapper pro applyFulltextFilter - ta je protected a my ji potrebujeme volat i z vnejsku
     * @param $orm
     * @param $query
     * @return mixed
     */
    public function staticApplyFulltextQuery($orm, $query)
    {
        $this->applyFulltextFilter($orm, $query);
        return $orm;
    }

    /**
     * Metoda provadi aplikaci ODP (Object Data Panel) filtru, ktere jsou definovany
     * v konfiguracnim souboru <object_name> na klici 'odp_filters'. Na ORM model
     * muze aplikovat pouze ty filtry, ktere maji v konfiguraci korektne definovan
     * atribut 'definition'. Korektni definici je ve tvaru indexovaneho pole, ktere
     * obsahuje nazev sloupce, operator a hodnotu. Tyto tri parametry jsou primo
     * vlozeny do metody 'where'.
     * @param <type> $orm
     * @return <array> Vraci seznam pozadovanych ODP filtru, ktere nebyla metoda
     * schopna aplikovat. Jedna se o ty filtry ktere nemaji v konfiguraci definovany
     * atribut 'definition'.
     */
    protected function applyODPFilter($orm)
    {
        //pokud je definovan seznam aktivnich ODP filtru daneho objektu
        if (isset($this->filter_params['_f']) && ! empty($this->filter_params['_f']))
        {
            //nactu definici filtru a prevedu do tvaru asoc. pole, kde klicem je
            //identifikator filtru (atribut 'value')
            foreach ((array)kohana::config($this->object_name.'.odp_filters') as $data)
            {
                //do $odp_filters vlozim pouze ty, ktere maji definovany a neprazdny
                //atribut 'definition' - pouze ty dokazu automaticky aplikovat
                if (arr::getifset($data, 'definition') != NULL)
                {
                    $odp_filters_params[$data['value']] = $data['definition'];
                }
            }

            //tyto filtry maji byt aplikovany
            $obd_filter = (array)$this->filter_params['_f'];

            //ty ktere obsahuji klic 'definition' muzu rovnou aplikovat na $orm
            foreach ($obd_filter as $i => $filter_value)
            {
                if (isset($odp_filters_params[$filter_value]))
                {
                    //aplikuju kazdou podminku
                    foreach ($odp_filters_params[$filter_value] as $filter_definition)
                    {
                        //definice filtru (sloupec, operator, hodnota)
                        $col = $filter_definition[0];
                        $op  = $filter_definition[1];
                        $val = $filter_definition[2];

                        //aplikuji na model
                        $orm->where($col, $op, $val);
                    }

                    //filtr byl aplikovan
                    unset($obd_filter[$i]);
                }
            }
            //vracim vycet filtru, ktere metoda nedokazala automaticky aplikovat
            //muze se jedna o filtry, ktere maji nejaky specialni filtr, ktery je
            //definovan az primo v kodu
            return $obd_filter;
        }

        //nebyly zpracovany zadne filtry
        return NULL;
    }

    /**
     * Metoda aplikuje na ORM model pravidla pro razeni vyslednych zaznamu.
     *
     * Pokud neni ve vyhledavacich parametrech definovan atribut podle ktereho
     * radit neprovede metoda nic.
     * Pokud je definovan jen atribut podle ktereho radit a ne jiz smer, tak
     * je vybran defaultni smer.
     *
     * @param <type> $orm
     */
    protected function applyOrderSettings($orm) {

        $current_orderby = arr::getifset($this->filter_params, Filter_Base::ORDERBY_KEY, NULL);

        //pokud je hodnota typu array, tak se jedna o specialni vyjimku kdy pro
        //ucely vyvolani filtrovani z kodu (ne od uzivatele) je mozne definovat
        //vice atributu pro razeni po sobe, tedy neco jako "ORDER BY attr1 ASC, attr2 DESC,..."
        if (is_array($current_orderby))
        {
            foreach ($current_orderby as $attr => $dir)
            {
                $orm->order_by($attr, $dir);
            }
            //po nastaveni parametru pro razeni metoda konci
            return;
        }

        //pokud neni explicitne definovan zadny atribut pro razeni tak necham
        //smer razeni na defaultnim nastaveni daneho ORM modelu.
        if ($current_orderby == NULL)
        {
            //pokud je v konfiguraci defaultni hodnota tak se pouzije ta
            if (isset($this->config['default_orderby']) && ! empty($this->config['default_orderby']))
            {
                $current_orderby = $this->config['default_orderby'];
            }
            else
            {
                return;
            }
        }

        //jinak ziskam jeste pozadovany smer razeni a nastavim do ORM modelu
        $current_orderby_dir = arr::getifset($this->filter_params, Filter_Base::ORDERBY_DIR_KEY, NULL);

        //pokud prisla neplatna hodnota v parametrech pozadavku, tak zmenim
        //na defaultni
        if ( ! in_array($current_orderby_dir, $this->orderby_dir_types))
        {
            $current_orderby_dir = $this->config['default_orderby_dir'];
        }

        //pokud $current_orderby retezec zacina znakem ":" (dvojtecka), tak se jedna
        //o lambda funkci, ktera je definovana v konfigurace. Tato funkce ocekava
        //jako argument ORM model a na nem nastavi potrebne filtrovani
        if (substr($current_orderby, 0, 1) == ':')
        {
            //nazev funkce je zbytek retezce za prvnim znakem ":"
            $function_name = substr($current_orderby, 1);

            //pokud dana lambda funkce existuje, tak ji najdu a vyvolam
            if (isset($this->config['order_by'], $this->config['order_by'][$function_name]) && is_callable($this->config['order_by'][$function_name]))
            {
                //lambda funkci vyvolam
                call_user_func($this->config['order_by'][$function_name], $orm, $current_orderby_dir);
            }
        }
        //pokud se ma kvuli razeni joinovat jina tabulka tak se join provede tady
        //toto je vyvolano zapisem 'codebook.value' u razeni, ale muze tam byt
        //klidne u tabulky client napsano 'client.name' - proto ta druha cast podminky
        else if (($rel_table = strstr($current_orderby, '.', TRUE)) != NULL && $rel_table != $orm->table_name())
        {
            //pokud se cizi klic nachazi v "tomto" model ($orm), tak udelam tento join
            // -> jedna se o belongs_to vazbu
            if ($orm->hasAttr($rel_table.'id'))
            {
                $orm->join($rel_table, 'LEFT')
                    ->on($rel_table.'.'.$rel_table.'id', '=', $orm->table_name().'.'.$rel_table.'id');
            }
            //jinak se jedna o has_one vazbu a udela se jiny join
            else
            {
                $orm->join($rel_table, 'LEFT')
                    ->on($rel_table.'.'.$orm->primary_key(), '=', $orm->table_name().'.'.$orm->primary_key());
            }

            //nyni mam pozadovany smer i atribut podle ktereho radit - nastavim do ORM
            $orm->order_by($current_orderby, $current_orderby_dir);
        } else
        {
            //nyni mam pozadovany smer i atribut podle ktereho radit - nastavim do ORM
            $orm->order_by($current_orderby, $current_orderby_dir);
        }
    }
    
    protected function applyPagerSettings($orm) {
       //aplikace strankovani
       $orm->limit($this->getPageSize())->offset($this->getCurrentPageIndex() * $this->getPageSize());
    }

    /**
     * Vraci pozadovany page index.
     * Metoda je verejna, protoze se vola z kontroleru - do sablon pro vypis dat
     * se krome nalezenych vysledku predava jeste page index prvni pozloky, tak aby
     * bylo mozne kazdy radek cislovat i s aplikaci strankovani (tj. prvni polozka
     * na druhe strance muze mit cislo 15. (napriklad)).
     * @return <string>
     */
    public function getCurrentPageIndex()
    {
        return arr::getifset($this->filter_params, Filter_Base::PAGE_INDEX_KEY, 0);
    }

    /**
     * Vraci pozadovanou velikost stranky.
     * @return <string>
     */
    protected function getPageSize()
    {
        $default_page_size = arr::getifset($this->config, 'default_page_size', 15);

        return arr::getifset($this->filter_params, Filter_Base::PAGE_SIZE_KEY, $default_page_size);
    }

    /**
     * Vraci sablonu, ktera predstavuje "strankovac" - panel se strankovani a volbou
     * poctu vysledku na stranku.
     *
     * Strankovac je aktualne generovan tak ze obsahuje N odkazu, za prvni strankou
     * a pred posledni strankou, a dale M odkazu pred a za aktualne vybranou strankou.
     * "Volna mista" jsou vyplnena retezcem '...'. Pokud je hodnota prvniho argumentu
     * rovna FALSE, tak se generuje strankovac urceny pod tabulku s daty a navic
     * obsahuje prvek pro vyber velikosti stranky (pocet polozek na strance).
     *
     * @param $top <bool> Definuje zda je strankovac urcen nad nebo pod tabulky
     * s daty. Stranovac pod tabulkou navic obsahuje prvek pro vyber velikosti
     * starnky (pocet polozek na stranku).
     *
     * @return <View>
     */
    public function getPager($top = TRUE)
    {
        //aktualni pozice mezi vysledky a velikost stranky
        $current_page_index = $this->getCurrentPageIndex();
        $current_page_size  = $this->getPageSize();

        //celkovy pocet stranek
        $total_page_count = ceil($this->results_total_count / $current_page_size);

        //vygeneruju jednotlive polozky ve strankovaci
        $near_end_items  = arr::get($this->config, 'near_end_items', 2);   //pocet polozek za zacatkem a pred koncem ve strankovaci
        $neighbour_items = arr::get($this->config, 'neightbour_items', 2);   //pocet sousedicich polozek

        //zde budou vysledne polozky ve forme 'popis pro uzivatele' => 'index' (standardne budou obe hodnoty stejne)
        $page_item_list = array();

        $i = 0;

        //stranky, ktere jsou na zacatku - "1,2..."
        for ($i = 0 ; $i < min($near_end_items, $current_page_index - $neighbour_items); $i++ )
        {
            $page_item_list[$i] = array($i, $i + 1);
        }

        //pokud vznika mezera mezi strankami na zacatku a pred aktualni strankou
        //tak tam vlozim neaktivni polozku '...'
        if ($i < $current_page_index - $neighbour_items)
        {
            $page_item_list[$i] = array(FALSE, '...');
        }

        //pokud je strankovani nastaveno tak ze odkazy pred posledni strankou
        //zacinaji "drive" nez odkazy pred aktualne vybranou strankou tak tyto
        //odkazy nebudu generovat v tomto cyklu, protoze by se pokazilo poradi
        //polozek - pracujeme s asoc. pole a nechci zbytecne provadet razeni
        //na konci
        if (($current_page_index - $neighbour_items) < ($total_page_count - $near_end_items + 1))
        {
            //stranky, ktere jsou zobrazeny pred aktualne vybranou strankou
            for ($i = max(0, $current_page_index - $neighbour_items) ; $i < $current_page_index; $i++ )
            {
                $page_item_list[$i] = array($i, $i + 1);
            }
        }
        //aktualne vybrana stranka
        $page_item_list[$i] = array(FALSE, $current_page_index + 1);

        //pokud je strankovani nastaveno tak ze odkazy pred posledni strankou
        //zacinaji "drive" nez odkazy za aktualne vybranou strankou tak tyto
        //odkazy nebudu generovat v tomto cyklu, protoze by se pokazilo poradi
        //polozek - pracujeme s asoc. pole a nechci zbytecne provadet razeni
        //na konci
        if (($current_page_index + 1) < ($total_page_count - $near_end_items + 1))
        {
            //stranky, ktere jsou zobrazeny za aktualne vybranou strankou
            for ($i = $current_page_index + 1; $i <= min($current_page_index + $neighbour_items, $total_page_count); $i++ )
            {
                $page_item_list[$i] = array($i, $i + 1);
            }

            //pokud vznika mezera mezi skupinou poslednich stranek a skupinou
            //stranek za tou aktivni tak tam vlozim neaktivni prvek '...'
            if ($i < $total_page_count - $near_end_items )
            {
                $page_item_list[$i] = array(FALSE, '...');
            }
        }

        //stranky, ktere jsou zobrazeny na konci strnakovace - posledni stranky
        for ($i = max($total_page_count - $near_end_items, $current_page_index + 1); $i < $total_page_count ; $i++)
        {
            $page_item_list[$i] = array($i, $i + 1);
        }

        //index predchozi stranky
        $prev_page_index = $current_page_index > 0 ? $current_page_index - 1: FALSE;
        //index nasledujici stranky
        $next_page_index = $current_page_index < $total_page_count ? $current_page_index + 1 : FALSE;

        //moznosti volby velikosti stranky
        $page_size_list = $this->config['page_size'];

        //vracim panel, ktery predstavuje pager
        $view = View::factory('table_pager_standard',
                             array(
                                 //celkovy pocet nalezenych vysledku
                                 'total_found'    => $this->results_total_count,
                                
                                 //index aktualni stranky
                                 'current_page_index' => $current_page_index,

                                 //celkovy pocet stranek
                                 'total_page_count' => $total_page_count,

                                 //index nasledujici stranky
                                 'next_page_index' => $next_page_index,

                                 //index predchozi stranky
                                 'prev_page_index' => $prev_page_index,

                                 //odkazy na jednotlive sousedici stranky
                                 'page_item_list' => $page_item_list,
                             )
        );

        //v navigaci ktera je urcena nad tabulku s daty bude navic
        //i moznost volby velikosti stranky
        if ( $top)
        {
            //velikost stranky
            $view->current_page_size = $current_page_size;

            //vyber velikosti stranky
            $view->page_size_list = $page_size_list;
        }

        return $view;
    }

    /**
     * Vraci sablonu, ktera zobrazuje celou polozku uzivatelskeho filtru (FilterState zaznam)
     * @param <int> $filterstate_id ID uzivatelskeho filtru (FilterState zaznamu)
     * @return <View>
     */
    public function getUserFilterItem($filterstate_id)
    {
        $filterstate_model = ORM::factory('filterstate', $filterstate_id);

        return $this->getUserFilterItemView($filterstate_model);
    }

    /**
     * Vraci sablonu, ktera zobrazuje statistiku uzivatelskeho filtru (FilterState zaznamu)
     * @param <int> $filterstate_id ID uzivatelskeho filtru (FilterState zaznamu)
     * @return <View>
     */
    public function getUserFilterItemStat($filterstate_id)
    {
        $filterstate_model = ORM::factory('filterstate', $filterstate_id);

        return $this->getUserFilterItemStatView($filterstate_model, TRUE);
    }

    /**
     * Vraci sablonu, ktera zobrazuje celou polozku uzivatelskeho filtru (FilterState zaznam)
     * @param Model_FilterState $filterstate FilterState zaznam, ktery predstavuje ulozeny
     * uzivatelsky filtr.
     * @return <View>
     */
    protected function getUserFilterItemView(Model_FilterState $filterstate)
    {
        //parametry pro sablonu
        $view_params = array(
            'filter_state' => $filterstate,
        );

        $view = View::factory('filter/filter_state_item', $view_params);

        return $view;
    }

    /**
     * Vraci sablonu, ktera zobrazuje statistiku uzivatelskeho filtru (FilterState zaznamu)
     * @param Model_FilterState $filterstate FilterState zaznam, ktery predstavuje ulozeny
     * @param <bool> $update_size Ma dojit k aktualizaci statistiky filtru
     * @return <View>
     */
    protected function getUserFilterItemStatView(Model_FilterState $filterstate, $update_size = TRUE)
    {
        //ma se aktualizovat velikost filtru ?
        if ($update_size)
        {
            //ulozim si parametry vyhledavani do teto instance Filter classy
            $this->filter_params = $this->cleanParams($filterstate->_filter_params);

            //aplikuju na ORM model
            $orm = ORM::factory($this->object_name);

            $fulltext_query = trim(preg_replace('/\s\s+/', ' ', arr::get($this->filter_params, Filter_Base::FULLTEXT_QUERY_KEY)));

            if ( ! empty($fulltext_query))
            {
                $this->applyFulltextFilter($orm, $fulltext_query);
            }

            $this->applyFilter($orm);
            $this->applyODPFilter($orm);

            $filterstate_size = $orm->count_all();

            //pokud filtr jeste nebyl nikdy pouzit, tak se jeho prvni velikost zapise
            //jakoby byl prave pouzit
            if (empty($filterstate->lastused))
            {
                $filterstate->setSizeAndClearDelta($filterstate_size);
            }
            else
            {
                //aktualizuju filterstate zaznam
                //ale neprovedu ulozeni zaznamu - to se provadi az pri pouziti filtru
                //nastaveni hodnoty 'size' zpusobi aktualizaci parametru 'delta', ktera
                //je spolecne se 'size' zbrazena v sablone
                $filterstate->size = $filterstate_size;
            }
        }

        //parametry pro sablonu
        $view_params = array(
            'filter_state' => $filterstate,
        );

        //nactu sablonu se statistikou
        return View::factory('filter/filter_state_item_stats', $view_params);
    }
    
    /**
     * Tato metoda slouzi k vygenerovani obsahu <th> bunky, ktera se pouziva
     * na tabulkovy vypisech (/object/table). Obsah <th> bunky je vytvoreni tak
     * aby jQuery.objectFilter rozeznal nadpis sloupce jako tlacitko pro razeni.
     * Metoda zajistuje vygenerovnani tlacitka tak aby se prepinal pozadovany
     * smer razeni a zaroven priradily prislusne CSS tridy.
     *
     * @param <string> $label Nadpis sloupecku
     * @param <string> $attr  Nazev atributu podle ktereho by se melo radit
     */
    public function generate_table_order_col_element($label, $attr)
    {
        //tady budu pridavat jednotlive CSS tridy pro generovany HTML element
        $css = '';
        //tady bude smer razeni, ktery bude aktivovan po kliknuti na prvek
        $next_orderby_dir = '';

        //pokud se podle daneho prvku neradi tak sestavim defaultni smer razeni
        //a nazev atributu
        if (arr::getifset($this->filter_params, Filter_Base::ORDERBY_KEY) != $attr) {
            //TODO: udelat moznost ze default_orderby_dir muze byt pole a bude to tedy
            //mozne nastavit pro jednotlive atributy
            $next_orderby_dir = $this->config['default_orderby_dir'];

            //tuto CSS tridu priradim prvku - zajisti zvyrazneni ze je aktivni
            //a take zobrazeni aktualniho smeru razeni
            $css = ($next_orderby_dir == 'desc' ? 'up' : 'down');
        } else {
            
            //aktualni smer razeni
            $current_orderby_dir = $this->filter_params[Filter_Base::ORDERBY_DIR_KEY];

            //smer razeni ktery bude aktivni pri kliknuti na prvek
            //pocitam s tim ze $this->orderby_dir_type je indexovane pole o velikosti 2
            //negace zajisti "preklad" 0->1 anebo 1->0
            $next_orderby_dir_type = (int)(! array_search($current_orderby_dir, $this->orderby_dir_types));

            //index prelozim na retezec (asc | desc)
            $next_orderby_dir = $this->orderby_dir_types[$next_orderby_dir_type];

            //tuto CSS tridu priradim prvku - zajisti zvyrazneni ze je aktivni
            //a take zobrazeni aktualniho smeru razeni
            $css = 'active ' . ($current_orderby_dir == 'asc' ? 'up' : 'down');
        }
        //vysledny prvek musi mit CSS classu 'of_order_button' na kterou se prichyti
        //jQuery.objectFilter
        return '<a href="#" name="'.($attr).'" dir="'.$next_orderby_dir.'" class="'.$css.'">'.$label.'</a>';
    }

    /**
     *
     * @param <type> $row
     * @param <type> $selectable
     * @return string 
     */
    public function generate_table_control_col_td($row, $selectable = FALSE)
    {
        //html kod obsahuje vzdy checkbox
        $html = '';

        //pokud ma byt zaznam "oznacitelny"
        if ($selectable)
        {
            $html .= '<input type="checkbox" class="item" item_id="'.$row->pk().'"/>';
        }

        //pokud je zaznam smazany tak tam bude ikona kose nebo jinak znazornen
        //tento fakt
        if ($row->hasAttr('deleted') && $row->deleted == 1)
        {
            $html .= '<strong>D</strong>';
        }
        
        return $html;
    }



    public function view_table_data_container($view_name=NULL)
    {
        //nazev sablony, kterou budu nacitat
        empty($view_name) AND $view_name = 'table_data_container';

        return View::factory($view_name);
    }

    public function view_empty_table_data_container($view_name=NULL)
    {
        //nazev sablony, kterou budu nacitat
        empty($view_name) AND $view_name = 'table_empty_data_container';

        return View::factory($view_name);
    }

}

?>
