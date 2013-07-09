<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Bazovy kontroler pro kontrolery, ktere zobrazuji poradace (vypis,formular,...).
 *
 * Kontrolery, ktere dedi nesmi v nazvu obsahovat '_controller'.
 *
 *
 * @author: Jiri Melichar
 */
abstract class Controller_Base_Object extends Controller_Layout {

    /**
     * Nazev objektu nad kterym tento objekt stoji.
     *
     * Standardne je $object_name dan nazvem classy bez casti '_controller' (doplnuje se
     * v konstruktoru).
     * Ale muzeme chtit nad jednim objektem (DB tabulkou) mit vice kontroleru - k cemuz
     * je potreba specifikovat nazev objektu explicitne protoze kazdy z kontroleru
     * musi by pojmenovany jinak.
     *
     * @var <string>
     */
    protected $object_name = NULL;

    /**
     * Nazev kontroleru. Doplni se automaticky pri inicializaci.
     *
     * Nazev kontroleru se muze lisit od hodnoty atributu $object_name a pouziva
     * se k automatickemu vygenerovani nazvu trid, ktere tento kontroler vyuziva.
     * Jedna se napriklad o nazev tridy pro zpracovani filtru pro tabulkovy
     * vypis apod. Duvod je vysvetlen v komentari pro atribut $object_name.
     *
     * @var <string>
     */
    protected $controller_name = NULL;

    /**
     * Definuje defaultni akci - pokud neni v URL definovana akce kontroleru
     * tzn. pristupuje se pouze na "/object/" dojde k presmerovani (302) na
     * URL, kde je definovana defaultni akce.
     * @var <string>
     */
    protected $default_action = 'table';

    /**
     * Reference na aktualne prihlaseneho uzivatele.
     * @var <User>
     */
    protected $user = NULL;

    /**
     * Provadi inicializaci kontroleru.
     *
     */
    public function before()
    {
        parent::before();

        //nazev kontroleru - pouziva se k vygenerovani implicitniho jmena tridy
        //pro filtrovani, apod
        $this->controller_name = strtolower(substr(get_class($this), 11));

        //standardne doplnim nazev classy pokud neni definovan
        if (empty($this->object_name))
        {
            $this->object_name = $this->controller_name;
        }

        //Z parametru pozadavku ziskam data
        $this->request_params = Request::instance()->get_data();

        //Ulozim si ID prihlaseneho uzivatele jehoz pozadavek tento kontroler bude
        //zpracovavat
        $this->user = Auth::instance()->get_user();
    }

    /**
     * Metoda vraci parametry tohoto pozadavku.
     * Sama neobsahuje zadnou logiku, ale pouze vola metody appurl::get_url_params,
     * ktera parametry vezme z POST nebo GET a pokud je to potreba tak je
     * dekoduje a vraci indexovane pole, ktere je rozdeleno na jednotlive casti
     * jako data, navratovy odkaz apod.
     *
     * @return <array>
     */
    protected function getRequestParams()
    {
        return appurl::get_url_params();
    }

    /**
     * Metoda provadi oblushu situace kdy na kontroler pristupuje uzivatel s nedostatecnym
     * opravnenim. Standardne je zobrazeno hlaseni pro uzivatele a provede se zapis do logu.
     * @return <type>
     */
    protected function runUnauthorizedAccessEvent()
    {
        //pouziju sablonu, ktera zobrazuje uzivateli spravu o tom ze se snazi
        //pristoupit k poradaci na ktery nema opravneni.
        $this->template = View::factory('unauthorized_access');
    }

    /**
     *
     * @return <type> 
     */
    protected function getSessionActionResultKey()
    {
        return 'table_action_result.'.$this->object_name;
    }

    /**
     * Presmeruje na URL kde je specifikovana defaultni akce.
     */
    public function action_index()
    {
        $this->request->redirect(appurl::object_action($this->controller_name, $this->default_action));
    }


    /**
     * 
     */
    public function action_do()
    {
        return $this->process_action(TRUE);
    }

    public function action_undo()
    {
        return $this->process_action(FALSE);
    }

    /**
     * Metoda zajistuje provedeni hromadne akce nad jednim nebo vice zaznamy
     * daneho objektu.
     * 
     * @param <string> $action_name Nazev akce, ktera ma byt provedena
     * @param <string> $items Retezec carkou oddelenych ID zaznamu nad kterymi ma
     * byt akce provedena. Napriklad "1,2,3,4,5".
     * @param <bool> $do Pokud je TRUE tak se bude provade 'do' varianta akce.
     * V opacnem pripade bude provedena 'undo' varianta akce.
     * @return <bool> 
     */
    protected function process_action($action_name, $items=NULL, $do = TRUE, & $action_result_ref = NULL)
    {
        //nactu si konfiguracni soubor objektu tohoto kontroleru a vyhledam
        //pozadovanou akci - k tomu pridavam jeste konfiguraci zakladnich systemovych
        //akci, kde je treba definovana akce 'delete' apod.
        $object_config = arr::merge((array)kohana::config('object'), (array)kohana::config($this->object_name));

        //ocekavam ze tam bude klic 'actions' ve kterem hledam s nazvem
        //pozadovane akce
        $action_config = NULL;

        if ( ! isset($object_config['actions']))
        {
            throw new Kohana_Exception('Undefined item action "'.$action_name.'" on object "'.$this->object_name.'".');
        }

        //pokud akce neni definovana na prvni urovni menu, tak budu hledat
        //akce v jednotlivych polozkach
        if ( ! isset($object_config['actions'][$action_name]))
        {
            foreach ($object_config['actions'] as $_action_name => $_action_config)
            {
                //vycet "pod-akci" polozky
                $sub_actions = arr::get($_action_config, 'items', array());

                if (isset($sub_actions[$action_name]))
                {
                    //konfigurace pro tuto specifickou akci - obsahuje funkci 'do' i 'undo'
                    $action_config = $sub_actions[$action_name];
                }
            }
        }
        else
        {
            //konfigurace pro tuto specifickou akci - obsahuje funkci 'do' i 'undo'
            $action_config = $object_config['actions'][$action_name];
        }

        if ($action_config === NULL)
        {
            throw new Kohana_Exception('Undefined item action "'.$action_name.'" on object "'.$this->object_name.'".');
        }

        //zajima me 'do' nebo 'undo' akce
        $type = $do ? 'do' : 'undo';

        if ( ! isset($action_config[$type]))
        {
            throw new Kohana_Exception('Undefined item action "'.$action_name.'" type ('.$type.') on object "'.$this->object_name.'".');
        }

        //objekt Closure - anonym. funkce, ktera predstavuje danou akci
        $action_function = $action_config[$type];

        //pro kazdou polozku provedu danou akci
        $item_id_list =  explode(',', $items);

        //do tohoto pole budou vlozeny chyby ke kterym dojde pri provadeni akci
        $action_errors = array();

        // Pokud nejsou zvoleny zadne zaznamy pro pozadovanou akci
        if (empty($item_id_list)) {
            // A v configu je receno ze akce se neprovadi nad zvolenymi zaznamy
            if ( ! arr::get($action_config, 'need_selection', true)) {
                // Provedeme akci
                try
                {
                    call_user_func($action_function);
                }
                catch (Exception $e)
                {
                    //k popisu chyby pridam preview zaznamu i popis chyby
                    $action_errors['no_selection'] = array(
                        '',
                        $e->getMessage()
                    );
                }
            } else {
                // Akce ktera je urcena k volani nad zvolenymy zaznamy byla zavolana bez zvolenych zaznamu
                throw new AppException('Table action called with empty selection.');
            }
        }

        foreach ($item_id_list as $id)
        {
            $model = ORM::factory($this->object_name, $id);

            //jeste pred provedenim akce si vytvorim preview zaznamu (akce by
            //mohla zaznam klidne odstranit nebo jinym zpusobem pozmenit)
            $preview = $model->preview();

            try
            {
                call_user_func($action_function, $model);
            }
            catch (Exception $e)
            {
                //k popisu chyby pridam preview zaznamu i popis chyby
                $action_errors[$id] = array(
                    $preview,
                    $e->getMessage()
                );
            }

        }

        //if no reference is passed, then the action result view will not be generated
        if ($action_result_ref != NULL)
        {
            //na vystup bude vygenerovan panel, ktery obsahuje informaci o
            //vysledku provedene akce vcetne tlacitka 'Undo' pokud je v konfiguraci
            //akce
            $object_action_result = View::factory('object_action_result');

            //sablone predam chybove hlasky
            $object_action_result->action_errors = $action_errors;

            //pocet zaznamu nad kterymi byla akce provedena uspesne
            $success_count = count($item_id_list) - count($action_errors);

            //pocet chyb
            $error_count = count($action_errors);

            //jazykove zpravy
            $prefix = $do ? '' : 'undo_';

            $message_ok    = arr::get($action_config, $prefix.'message_ok',    'object.item_'.$prefix.'action_message_ok');
            $message_error = arr::get($action_config, $prefix.'message_error', 'object.item_'.$prefix.'action_message_error');

            //sprava informujici uzivatele o uspechu
            $object_action_result->success_message = __($message_ok,    array(':count' => $success_count));
            $object_action_result->error_message   = __($message_error, array(':count' => $error_count));

            //vraceni akce umoznim pouze v pripade ze se provadela 'do' akce -
            //pak se provede 'undo' a zaroven musi byt 'undo' akce definovana
            $object_action_result->allow_undo = $do && isset($action_config['undo']);

            //pres referenci takto predam "ven z metody" - navratova hodnota signalizuje zda
            //probehlo vse OK nez doslo k chybam
            $action_result_ref = $object_action_result;
        }

        //vraci TRUE pokud nedoslo k zadnym chybam
        return empty($action_errors);
    }
    
    /**
     * Generuje zakladni stranku s tabulkovy vypisem dat.
     * @return <type>
     */
    public function action_table()
    {
        //vyvolani globalni udalosti 'system.action_table_pre'
        Dispatcher::instance()->trigger_event('system.action_table_pre', Dispatcher::event(array('controller' => $this)));

        //kontrola opravneni uzivatele na konkretni akci tohoto kontroleru
        if ( ! $this->user->HasPermission($this->object_name, Request::instance()->action))
        {
            return $this->runUnauthorizedAccessEvent();
        }

        //do stranky vlozim obsahovou sablonu pro "/table" vypis
        $this->template->content = $this->_view_table_content();

        //vlozim nazev objektu do sablony aby podle toho bylo mozne stylovat
        $this->template->content->controller_name = $this->controller_name;

        //pro lepsi moznosti custom stylovani pridam do sablony i nazev akce
        $this->template->content->action_name = Request::instance()->action;

        //do stranky dale vlozim JS Set 'table' - do tohoto setu jsou vlozeny JS
        //soubory ktere mohou byt potreba ve strance
        Web::instance()->addJSFileSet('table');

        //dale vlozim JS Set 'form', kvuli formularum, ktere mohou jQuery.objectfilter nacitat
        // - napr pro ukladani oblibenych filtru nebo exportu
        //@TODO: tohle tady neni potreba kdyz neni aktivni funkce pro ukladani oblibenych filtru
        //Web::instance()->addJSFileSet('form');

        //nactu konfiguracni souboru pro objekt
        $object_table_config = $this->_config_object_table(Request::instance()->action);

        //vytvorim si instanci dane tridy a predam mu parametry vyhledavani
        $filter_instance = $this->loadAndInitFilterClassInstance($object_table_config);

        //vygeneruje mi formular filtru
        $this->template->content->filter_form = $filter_instance->getForm();

        //do stranky vlozim panel s ulozenymi filtry
        $this->template->content->user_filter_panel = $filter_instance->getUserFilterPanel();

        //vyvolani globalni udalosti 'system.action_table_post'
        Dispatcher::instance()->trigger_event('system.action_table_post', Dispatcher::event(array('controller' => $this)));
    }

    /**
     * Metoda vypise na stdout panel, ktery obsahuje data pro tabulkovy vypis
     * nad poradacem - pouze sablonu, ktera obsahuje data a strankovani.
     * Metoda je urcena pro ajaxove pozadavky na nacteni dat.
     *
     * Na vystup vypise asoc. pole v JSONu, ktere ma tento obsah:
     *
     * 'data_panel' => sablona, ktera obsahuje vlastni data (v tabulce nebo jine
     * forme, strankovani a odkazy pro zmenu velikosti stranky).
     */
    public function action_table_data($table_type = NULL)
    {
        //kontrola opravneni uzivatele na konkretni akci tohoto kontroleru
        if ( ! $this->user->HasPermission($this->object_name, $table_type))
        {
            return $this->runUnauthorizedAccessEvent();
        }

        $this->template = new View('table_data_response');

        //polozky v poli content se poslou klientovi ve formatu JSON jako odpoved na pozadavek
        //zpracovava se pluginem jquery.objectFilter
        $this->template->content = array();

        //jeste pred pripravou samotnych dat dojde k provedeni akce nad zaznamy
        $action_name = arr::get($this->request_params, 'a');
        $items       = trim(arr::get($this->request_params, 'i'), ' ,');
        $do          = (bool)arr::get($this->request_params, 'd', TRUE);

        if ( ! empty($action_name))
        {
            //metoda do teto promenne vlozi sablonu, ktera predstavuje vysledek akce
            //(pres referenci)
            $action_result_view_ref = TRUE;

            try
            {
                //navratova hodnota je TRUE pokud nedoslo k zadnym chybam
                //a false pokud doslo k minimalne jedne chybe
                $at_least_one_error = ! $this->process_action($action_name, $items, $do, $action_result_view_ref);

                //vysledek akce bude do stranky vlozen pouze v pripade ze se provadi 'do' akce (nikoli 'undo')
                //NEBO doslo k chybe (napr. chyba pri undo akci)
                if ($do || $at_least_one_error)
                {
                    //vysledek hromadne akce (muze byt NULL v pripade ze zadna akce nebyla pozadovana)
                    $this->template->content['action_result'] = (string)$action_result_view_ref;
                    //tyto parametry se do odpovedi vkladaji aby bylo mozne snadno vyvolat 'undo' akci
                    $this->template->content['action_name'] = (string)$action_name;
                    $this->template->content['action_selected'] = $items;
                }
            }
            catch (Kohana_Exception $e)
            {
                //vyjimka signalizuje ze pozadovana akce neni definovana
                //provedu zapis do logu a do odpovedi pozadavaku se nebude vkladat
                //zadna informace o provedeni akce
                Kohana::$log->add(Kohana::ERROR, $e->getMessage());
            }
        }

        //nactu konfiguraci pro dany tabulkovy vypis
        $table_config = $this->_config_object_table(Request::instance()->param('type'));

        //vytvori instanci tridy, ktera zajistuje logiku filtrovani
        $filter_instance = $this->loadAndInitFilterClassInstance($table_config);

        //Vraci ORM_Iterator predstavici vysledky vyhledavani
        list($results, $filter_state_id, $filter_state_stat) = $filter_instance->getResults();

        //celkovy pocet nalezenych vysledku
        $total_found_results = $filter_instance->getResultsTotalCount();

        //pokud bylo nalezeno 0 vysledku, tak zobrazim specialni sablonu, ktera
        //uzivateli rekne aby pouzil jiny filtr
        if ($total_found_results != 0)
        {
            //dale nactu sablonu, ktera tvori obal pro sablonu s daty
            //pridava strankovani, hromadne akce, apod
            $table_data_container_view = $filter_instance->view_table_data_container();
        }
        else
        {
            //tato sablona slouzi k informovani uzivatele o tom ze podle
            //daneho filtru nebyly nalezeny zadne zaznamy
            $table_data_container_view = $filter_instance->view_empty_table_data_container();
        }

        //nejdrive si nactu sablonu, ktera pouze zobrazuje tabulku s daty - ta je
        //custom pro kazdy poradac
        $data_table_view = $filter_instance->_view_table_data();

        //sablona dostava referenci na instanci filtru
        $data_table_view->filter_params = $filter_instance->getFilterParams();

        //predam vastni data data
        $data_table_view->results = $results;

        //callback na metodu, ktera bude generovat nadpisy jednotlivych sloupcu,
        //tak aby slouzily jako tlacitka k razeni
        $data_table_view->generate_table_order_col_element = array($filter_instance, 'generate_table_order_col_element');

        //metoda, ktera provadi generovani bunky, ktera obsahuje ovladaci prvky
        //pro oznaceni zaznamu. Muze take zvyraznit odstranene zaznamy.
        $data_table_view->generate_table_control_col_td = array($filter_instance, 'generate_table_control_col_td');

        //nazev kontroleru pro generovani odkazu pro editaci , overview apod.
        $data_table_view->controller_name = $this->controller_name;

        //do sablony vlozim zakodovanou URL pro navrat zpet na tento kontroler
        $data_table_view->current_object_table_url = appurl::object_action($this->controller_name, Request::instance()->param('type'), NULL, $this->request_params);
        //dale pridam popis aktualniho vypisu, ktery bude uzivateli zobrazen jako
        //odkaz pro navrat zpet
        $data_table_view->current_object_table_label = $filter_instance->getReturnLinkFilterLabel();

        //predam data se kterymi sablona potrebuje
        $table_data_container_view->data_table = $data_table_view;

        //do sablony vlozim panel pro ovladani hromadnych akci nad zaznamy
        $table_data_container_view->item_action_panel = Panel::factory($this->controller_name)->getPanel();

        //formular pro ovladani strankovani
        $table_data_container_view->top_pager     = $filter_instance->getPager(TRUE);
        $table_data_container_view->bottom_pager  = $filter_instance->getPager(FALSE);
       

        //do sablony vlozim i celkovy pocet nalezenych dat aby to mohlo byt zobrazeno uzivateli
        $table_data_container_view->total_found = $total_found_results;

        //vystup vracim ve forme JSONu tak aby jej bylo mozne dobre zpracovat
        //na strane klienta

        //hlavni nadpis - muze reprezentovat aktualni nastaveni filtru
        $this->template->content['headline'] = $filter_instance->getHeadline();
        //vlastni panel s daty (+strankovac a veci kolem)
        $this->template->content['content'] = (string)$table_data_container_view;
        //aktualizovana statistika FilterState zaznamu (pokud bylo filtrovano podle nejakeho)
        $this->template->content['fs_stat'] = (string)$filter_state_stat;
        //ID filtru podle ktereho se filtrovalo (pokud bylo filtrovano podle nejakeho)
        $this->template->content['_fs'] = (string)$filter_state_id;

    }

    /**
     * Metoda vypise na stdout panel, ktery obsahuje data pro tabulkovy vypis
     * nad poradacem - pouze sablonu, ktera obsahuje data a strankovani.
     * Metoda je urcena pro ajaxove pozadavky na nacteni dat.
     *
     * Na vystup vypise asoc. pole v JSONu, ktere ma tento obsah:
     *
     * 'data_panel' => sablona, ktera obsahuje vlastni data (v tabulce nebo jine
     * forme, strankovani a odkazy pro zmenu velikosti stranky).
     */
    public function action_export_data()
    {
        //kontrola opravneni uzivatele na konkretni akci tohoto kontroleru
        if ( ! $this->user->HasPermission($this->object_name, 'table'))
        {
            return $this->runUnauthorizedAccessEvent();
        }

        //timto se v layout kontroleru zajisti ze nebude automaticky do sablony
        //vkladat zakladni veci jako nadpis, JS soubory, paticku apod.
        $this->tempate = NULL;

        //na parametru 'id' ocekavam ID zaznamu user_export, ktery definuje
        //parametry exportovanych dat - format, kodovani, atd
        $user_export = ORM::factory('userexport', arr::get($this->request_params, 'user_exportid'));

        //pokud dany user_export zaznam neexistuje, tak se nebude nic provadet
        if ( ! $user_export->loaded())
        {
            $this->template = '';
            return;
        }

        //vytvori instanci tridy, ktera zajistuje logiku filtrovani
        $filter_instance = $this->loadAndInitFilterClassInstance();

        //Vraci ORM_Iterator predstavici vysledky vyhledavani - 1. argument
        //rika ze se nema pouzivat strankovani
        list($results, $filter_state_id, $filter_state_stat) = $filter_instance->getResults(FALSE);

        //nejdrive si nactu sablonu, ktera pouze zobrazuje tabulku s daty - ta je
        //custom pro kazdy poradac
        $export_data_view = $this->_view_export_data($user_export);

        //predam vlastni data data
        $export_data_view->results = $results;

        //pridam si definici sloupcu a jejich popisku
        $columns = $user_export->_columns;

        //definice sloupcu, ktere mohou byt v exportu a jejich popisku
        $columns_config = (array)kohana::config($this->controller_name.'_export.columns');

        //do sablony poslu pouze informace o tech sloupcich, ktere jsou definovane
        //v userexport zaznamu a v poradi ve kterem tam jsou
        $export_data_view->metadata = array();

        foreach ($columns as $name)
        {
            if ( ! isset($columns_config[$name]))
            {
                continue;
            }
            $export_data_view->metadata[] = $columns_config[$name];
        }

        //cilove kodovani - odhaduje se podle platformy na ktere bezi klient
        $export_data_view->target_encoding = $this->getSuitableEncoding();

        //timto definuji obsah souboru
        $this->request->response = $export_data_view;

        //sestavim nazev souboru
        $filename = text::webalize($user_export->name) . '.' . $user_export->format_type;

        //odeslani souboru - metoda podporuje i pausnuti downloadu
        $this->request->send_file(TRUE, $filename);
    }

    /**
     * Metoda vraci kodovani, ktere by melo byt nejvhodnejsi pro platformu
     * uzivatele, ktera je detekovana podle HTTP hlavicek.
     *
     * @return <string> Pro Windows vraci retezec 'cp1250' a pro ostatni
     * vraci 'UTF-8'.
     */
    protected function getSuitableEncoding()
    {
        if (stristr(Request::instance()->user_agent('platform'), 'windows'))
        {
            return 'cp1250';
        }
        else
        {
            return 'UTF-8';
        }
    }

    /**
     * Vraci sablonu, ktera predstavuje uzivatelsky filtr nebo jen jeho statistiku.
     * To zda se vraci cela polozka filtru nebo jen statistika je definovano
     * parametrem pozadavku 'c' (complete item). Pokud je definovan a ma
     * hodnotu TRUE tak vraci celou polozku, jinak pouze statistiku.
     */
    public function action_load_filter_item()
    {
        //sablona, ktera zaridi format dat odpovedi na pozadavek pro aktualizaci
        //statistik filtru
        $this->template = new View('filter/filter_state_update_response');
        
        //parametr pozadavku 'c' definuje zda ma byt nactena kompletni sablona
        //filtru - tedy ne jen statistiky
        $complete_item = arr::get($this->request_params, 'c', FALSE);

        //z aprametru pozadavku si vytahnu ID filterstate zaznamu
        $filter_id = arr::get($this->request_params, 'id', NULL);

        //pokud neni definovano, tak se nebude nic provadet
        if (empty($filter_id))
        {
            $this->template = new View('null');
            return;
        }

        //vytvori instanci tridy, ktera zajistuje logiku filtrovani
        $filter_instance = $this->loadAndInitFilterClassInstance();

        //nacteme jen statistiku nebo kompletni polozku
        if ($complete_item)
        {
            $this->template->view = $filter_instance->getUserFilterItem($filter_id);
        }
        else
        {
            $this->template->view = $filter_instance->getUserFilterItemStat($filter_id);
        }

    }

    /**
     * Odstranuje uzivatelsky filtr, ktery je identifikovan hodnotou sveho PK -
     * tj. hodnotou filter_state.filter_stateid.
     */
    public function action_remove_filter_item()
    {
        //z aprametru pozadavku si vytahnu ID filterstate zaznamu
        $filterstate_id = arr::get($this->request_params, 'id', NULL);

        //nactu z DB
        $filterstate = ORM::factory('filterstate', $filterstate_id);

        //pokud byl nalezen tak odstranim
        if ($filterstate->loaded())
        {
            $filterstate->delete();
        }
        //vystup bude prazdny
        $this->template = new View('null');
    }
    
    /**
     * Metoda vypise na stdout hodnoty pro autocomplete daneho poradace
     *
     * Na vystup vypise asoc. pole v JSONu, ktere ma tento obsah:
     *
     * ??
     */
    public function action_cb_data()
    {
        $this->template = new View('cb_data_response');

        //do parametru pridam filtrovaci podminku, ktera zajisti nacteni pouze
        //tech polozek, ktere jsou aktivni (status=1).
        $this->request_params['status'] = '1';

        //vytvori instanci tridy, ktera zajistuje logiku filtrovani
        $filter_instance = $this->loadAndInitFilterClassInstance();

        //Vraci ORM_Iterator predstavici vysledky vyhledavani
        list($results, $filter_state_id, $filter_state_stat) = $filter_instance->getResults();

        $data = Array();

        foreach ($results as $row)
        {
            $data[] = $this->get_cb_data_item($row);
        }

        $this->template->data = $data;
    }

    /**
     * Metoda vytvari datovou polozku pro autocomplete plugin na strane klienta.
     *
     * V teto metode je mozne do polozky doplnit klic 'fill' a do toho vlozit pole
     * hodnot ve tvaru:
     *
     * 'selector' => 'hodnota',
     * 'cb_brandid[name] => 'Znacka A',
     * 'cb_brandid[id]   => '1',
     * 'note' => 'predvyplnena poznamka...',
     *
     * coz u klienta zajisti ze prislusnym formularovym prvkum bude prirazena
     * dana hodnota.
     *
     * @param <ORM> $model
     * @return <array>
     */
    protected function get_cb_data_item($model)
    {
        //vystup vracim ve forme JSONu tak aby jej bylo mozne dobre zpracovat
        //na strane klienta
        $preview_format = arr::get($this->request_params, 'preview', '');

        return array(
            'name'  => $model->preview($preview_format),
            'value' => $model->pk(),
        );
    }

    /**
     * Metoda generuje na vystup data v JSONu, ktera obsahuji tabulku s daty, ktera
     * je urcena k zobrazeni uvnitr objectDataPanel. V prvnim argumentu prijima
     * doplnujici filtrovaci parametry, zakodovane pomoci tridy Encoder.
     * Tyto parametry napriklad obsahuji podminku pro filtrovani zajmu pro specifickou
     * nabidku.
     *
     * Na vystup vypise asoc. pole v JSONu, ktere ma tento obsah:
     *
     * 'data_panel' => sablona, ktera obsahuje vlastni data (v tabulce nebo jine
     * forme, strankovani a odkazy pro zmenu velikosti stranky).
     */
    public function action_table_obd_panel($table_type)
    {
        //kontrola opravneni uzivatele na konkretni akci tohoto kontroleru
        if ( ! $this->user->HasPermission($this->object_name, 'table'))
        {
            return $this->runUnauthorizedAccessEvent();
        }
        
        $this->template = new View('table_data_response');

        //polozky v poli content se poslou klientovi ve formatu JSON jako odpoved na pozadavek
        //zpracovava se pluginem jquery.objectFilter
        $this->template->content = array();

        //jeste pred pripravou samotnych dat dojde k provedeni akce nad zaznamy
        $action_name = arr::get($this->request_params, 'a');
        $items       = trim(arr::get($this->request_params, 'i'), ' ,');
        $do          = (bool)arr::get($this->request_params, 'd', TRUE);

        if ( ! empty($action_name) && ! empty($items))
        {
            //metoda do teto promenne vlozi sablonu, ktera predstavuje vysledek akce
            //(pres referenci)
            $action_result_view_ref = NULL;

            try
            {
                //navratova hodnota je TRUE pokud nedoslo k zadnym chybam
                //a false pokud doslo k minimalne jedne chybe
                $at_least_one_error = ! $this->process_action($action_name, $items, $do, $action_result_view_ref);

                //vysledek akce bude do stranky vlozen pouze v pripade ze se provadi 'do' akce (nikoli 'undo')
                //NEBO doslo k chybe (napr. chyba pri undo akci)
                if ($do || $at_least_one_error)
                {
                    //vysledek hromadne akce (muze byt NULL v pripade ze zadna akce nebyla pozadovana)
                    $this->template->content['action_result'] = (string)$action_result_view_ref;
                    //tyto parametry se do odpovedi vkladaji aby bylo mozne snadno vyvolat 'undo' akci
                    $this->template->content['action_name'] = (string)$action_name;
                    $this->template->content['action_selected'] = $items;
                }
            }
            catch (Kohana_Exception $e)
            {
                //vyjimka signalizuje ze pozadovana akce neni definovana
                //provedu zapis do logu a do odpovedi pozadavaku se nebude vkladat
                //zadna informace o provedeni akce
                Kohana::$log->add(Kohana::ERROR, $e->getMessage());
            }
        }

        //nactu konfiguraci pro dany tabulkovy vypis
        $table_config = Kohana::config($table_type);

        //vytvori instanci tridy, ktera zajistuje logiku filtrovani
        //pokud je v konfigurace pro zobrazeni vypisu definovana konfigurace, tak ji
        //predam - prebije defaultni konfiguraci
        $filter_instance = $this->loadAndInitFilterClassInstance($table_config);

        //$results je ORM_Iterator, ktery predtavuje vysledky vyhledavani urcene
        //pro danou stranku (s s ohledem na strankovani)
        list($results, $filter_state_id, $filter_state_stat) = $filter_instance->getResults();

        //celkovy pocet nalezenych vysledku
        $total_found_results = $filter_instance->getResultsTotalCount();

        //pokud bylo nalezeno 0 vysledku, tak zobrazim specialni sablonu, ktera
        //uzivateli rekne aby pouzil jiny filtr
        if ($total_found_results != 0)
        {
            //dale nactu sablonu, ktera tvori obal pro sablonu s daty
            //pridava strankovani, hromadne akce, apod
            $table_data_container_view = $this->_view_table_data_container();
        }
        else
        {
            //tato sablona slouzi k informovani uzivatele o tom ze podle
            //daneho filtru nebyly nalezeny zadne zaznamy
            $table_data_container_view = $this->_view_table_empty_data_container();
        }

        //nejdrive si nactu sablonu, ktera pouze zobrazuje tabulku s daty - ta je
        //custom pro kazdy poradac
        $data_table_view = $filter_instance->_view_table_data();

        //sablona dostava referenci na instanci filtru
        $data_table_view->filter_params = $filter_instance->getFilterParams();

        //predam vastni data data
        $data_table_view->results = $results;

        //callback na metodu, ktera bude generovat nadpisy jednotlivych sloupcu,
        //tak aby slouzily jako tlacitka k razeni
        $data_table_view->generate_table_order_col_element = array($filter_instance, 'generate_table_order_col_element');

        //metoda, ktera provadi generovani bunky, ktera obsahuje ovladaci prvky
        //pro oznaceni zaznamu. Muze take zvyraznit odstranene zaznamy.
        $data_table_view->generate_table_control_col_td = array($filter_instance, 'generate_table_control_col_td');

        //nazev kontroleru pro generovani odkazu pro editaci , overview apod.
        $data_table_view->controller_name = $this->controller_name;

        //do sablony vlozim zakodovanou URL pro navrat zpet na tento kontroler
        $data_table_view->current_object_table_url = appurl::object_table($this->controller_name, $this->request_params);
        //dale pridam popis aktualniho vypisu, ktery bude uzivateli zobrazen jako
        //odkaz pro navrat zpet
        $data_table_view->current_object_table_label = $filter_instance->getReturnLinkFilterLabel();

        //predam data se kterymi sablona potrebuje
        $table_data_container_view->data_table = $data_table_view;

        //formular pro ovladani strankovani
        $table_data_container_view->top_pager     = $filter_instance->getPager(TRUE);
        $table_data_container_view->bottom_pager  = $filter_instance->getPager(FALSE);

        //do sablony vlozim i celkovy pocet nalezenych dat aby to mohlo byt zobrazeno uzivateli
        $table_data_container_view->total_found = $total_found_results;

        //vystup vracim ve forme JSONu tak aby jej bylo mozne dobre zpracovat
        //na strane klienta
        $this->template->content['html'] = (string)$table_data_container_view;
    }

    /**
     * Tato akce slouzi k vytvoreni noveho zaznamu nad objektem.
     * Zajistuje pouze kontrolu opravneni 'new' a pak vola akci edit
     * s argumentem $item_id=NULL.
     *
     * @return <type>
     */
    public function action_new()
    {
        //kontrola opravneni na danou akci
        //kontrola zda ma uzivatel pozadovane opravneni na danou akci
        if ( ! $this->user->HasPermission($this->object_name, 'new'))
        {
            return $this->runUnauthorizedAccessEvent();
        }

        return $this->action_edit(NULL);
    }

    /**
     * Ake slouzi k odstraneni specifickeho zaznamu objektu nad kterym stoji
     * kontroler.
     * @param <int> $item_id ID zaznamu, ktery ma byt odstranen.
     */
    public function action_delete()
    {
        $item_id = $this->request->param('id');

        //priprava sablony
        $this->template = $this->_view_delete_ajax_template();

        //zde se vlozi data, ktera budou vlozena na vystup
        $output = array();
                
        //kontrola zda ma uzivatel pozadovane opravneni na danou akci
        if ( ! $this->user->HasPermission($this->object_name, 'delete'))
        {
            $this->template->output = array('error' => __('object.action_delete.not_authorized'));
            return;
        }

        //nactu model pozadovaneho zaznamu
        $this->model = ORM::factory($this->object_name, $item_id);

        //pokud nebyl pozadovany zaznam nalezen, tak uzivateli zobrazim hlasku,
        //ktera informuje o tom ze zaznam nebyl nalezen
        if ( ! $this->model->loaded())
        {
            $this->template->output = array('error' => __('object.action_delete.item_not_found'));
            return;
        }

        //kontrola zda ma uzivatel opravneni pro odstraneni tohoto konkretniho zaznamu
        if ( ! $this->model->testUserDeletePermission(Auth::instance()->get_user()))
        {
            $this->template->output = array('error' => __('object.action_delete.not_authorized_on_item'));
            return;
        }
        
        try
        {
            //vlastni odstraneni zaznamu
            $this->model->delete();
        }
        catch (Exception $e)
        {
            //zalogovani vyjimky
            Kohana::$log->add(Kohana::ERROR, 'Unable to delete item [:id] of object :object_name due to error: ":error"', array(
                ':error' => $e->getMessage(),
                ':id'    => $item_id,
                ':object_name' => $this->object_name
            ));

            $this->template->output = array('error' => __('object.action_delete.error_occured'));
            return;

        }

        //prazdny vystup - akce provedena uspesne
        $this->template->output = array();
    }

    /**
     * Akce slouzi k vygenerovani stranky, ktera umoznuje editaci nebo vlozeni
     * noveho zaznamu, ktery je dan prvnim parametrem (v pripade vkladani je
     * prvni argument roven 'new').
     * @param <int> $item_id ID zaznamu, ktery ma byt editovan. Pokud je NULL
     * tak je vygenerovan prazdny formular pro vlozeni noveho zaznamu.
     */
    public function action_edit($item_id)
    {
        //vyvolani globalni udalosti 'system.action_edit_pre'
        Dispatcher::instance()->trigger_event('system.action_edit_pre', Dispatcher::event(array('controller' => $this)));

        //kontrola zda ma uzivatel pozadovane opravneni na danou akci
        if ( ! $this->user->HasPermission($this->object_name, 'edit'))
        {
            return $this->runUnauthorizedAccessEvent();
        }

        //nactu model pozadovaneho zaznamu
        $this->model = ORM::factory($this->object_name, $item_id);

        //pokud nebyl pozadovany zaznam nalezen, tak uzivateli zobrazim hlasku,
        //ktera informuje o tom ze zaznam nebyl nalezen
        if ($item_id !== NULL && ! $this->model->loaded())
        {
            throw new Exception('Vytvorit sablonu, ktera informuje o tom ze uzivatel nema opravneni zaznam cist nebo neexistuje.');
        }

        //nactu si konfiguracni soubor pro dany formular
        $form_config = $this->_config_form();

        //pridam JS soubor, ktery zajisti zakladni funkci editacniho formulare
        Web::instance()->addCustomJSFile(View::factory('js/jquery.objectForm.js'));


        // inicializace pluginu
        // Pokud model implementuje Slave_Compatible interface pak pluginu formulare predame seznam povolenych jazyku
        $config = array();
        if ($this->model instanceof Interface_AppFormItemLang_SlaveCompatible) {
            // Get languages enabled for current model
            $enabled_languages = $this->model->getEnabledLanguagesList();
            // Add languages labels
            $config['enabled_languages'] = Languages::fillLanguagesLabels($enabled_languages);
        }
        Web::instance()->addMultipleCustomJSFile(View::factory('js/jquery.objectForm-init.js', array('config' => $config)));

        //file set - standardni formularove prvky
        Web::instance()->addJSFileSet('form');

        //do stranky vlozim obsahovou sablonu pro "/edit" vypis
        $this->template->content = $this->_view_edit_content();
        
        //pro stylovani predam nazev kontroleru
        $this->template->content->controller_name = $this->controller_name;

        // --- FORMULAR --- //

        //metoda vraci nazev tridy, ktera implementuje praci s formulari
        //jedna se bud o bazovou tridu AppForm nebo nejakou z ni dedici
        $form_class_name = arr::get($form_config, 'class', $this->_action_edit_form_class_name());

        $form = FormFactory::Get($form_class_name, $this->model, $form_config, $this->request_params, FALSE);

        //vytvorim si novy objekt formulare
//        $form = new $form_class_name($this->model, $form_config, $this->request_params, FALSE);

        //metoda muze vyhodit vyjimku, ktera muze zaridit presmerovani na jinou stranku
        try
        {
            //zpracovani vstupnich dat formulare
            $form->Process();
        }
        //tato vyjimka definuje ze ma dojit k presmerovani na editaci zaznamu
        //se specifickym ID - toto se pouziva jako presmerovani na editacni
        //formulare prave vytvoreneho zaznamu
        catch (Exception_RedirToActionEdit $e)
        {

            //presmeruju na editacni stranku pozadovaneho zaznamu
            Request::instance()->redirect(appurl::object_edit($this->controller_name, $e->getItemID()));
        }

        //URL na kterou ma formular smerovat]
        $form_action_url = appurl::object_edit_ajax(
                                        $this->controller_name,
                                        $form_config->get_group_name(),//$this->object_name.'_form',
                                        $this->model->pk(),
                                        arr::get($this->request_params, 'defaults') //defaultni parametry promitnu do URL.
                                                                                    //Pokud by nastala validacni chyba, tak je
                                                                                    //potrebuji udrzet na formulari i pres mozny
                                                                                    //reload (pri neuspesnem ulozeni).
        );

        //vlastni formular bude vlozen do sablony
        $this->template->content->form = $form->Render($form_action_url);

        //hlavni nadpise ve strance generuje trida formulare
        $this->template->content->headline = $form->getHeadline();

        //navratovy odkaz na tabulkovy vypis
        $this->template->content->return_link       = Request::instance()->get_retlink();
        $this->template->content->return_link_label = Request::instance()->get_retlink_label();

        //vyvolani globalni udalosti 'system.action_edit_post'
        Dispatcher::instance()->trigger_event('system.action_edit_post', Dispatcher::event(array('controller' => $this)));
    }

    /**
     * Generuje variantu editacniho formulare, ktera se pouziva v jQuery.dialog
     * a je nacitana ajaxem. Znamena to predevsim nacteni jinych sablon a
     * predani vysledku formularove akce do zakladni sablony ($this->template),
     * ktera zajistuje vystup ve formatu json_encode, ktery zpracovava
     * jQuery.objectDataPanel.
     * @param <int> $item_id ID zaznamu, ktery ma byt editovan.
     */
    public function action_edit_ajax($form_type, $item_id = NULL)
    {
        //kontrola opravneni uzivatele na konkretni objekt tohoto kontroleru
        if ( ! $this->user->HasPermission($this->object_name, 'edit'))
        {
            return $this->runUnauthorizedAccessEvent();
        }

        //ID zaznamu muze byt jeste defionvano v parametrech pozadavku (tato
        //moznost se vyuziva v jQuery pluginech aby nebylo potreba retezcove
        //manipulovat s URL). Pokud je definvano v parametrech tak ma prednost
        //pred tim co prislo v URL
        $item_id  = arr::get($this->request_params, '_id', $item_id);

        //nactu ORM pozadovaneho objektu
        $this->model = ORM::factory($this->object_name, $item_id);

        //chci pouzit custom sablonu, ktera zajisti vystup ve formatu JSON v pozadovanem tvaru
        $this->template = $this->_view_edit_ajax_template();

        //do stranky vlozim obsahovou sablonu pro "/edit_ajax" vypis
        $this->template->content = $this->_view_edit_ajax_content();

        //pro stylovani predam nazev kontroleru
        $this->template->content->controller_name = $this->controller_name;

        //nactu pozadovanou konfiguraci formulare
        $form_config = Kohana::config($form_type);

        //metoda vraci nazev tridy, ktera implementuje praci s formulari
        //jedna se bud o bazovou tridu AppForm nebo nejakou z ni dedici
        $form_class_name = arr::get($form_config, 'class', $this->_action_edit_form_class_name());

        $form = FormFactory::Get($form_class_name, $this->model, $form_config, $this->request_params, TRUE);

        //vytvorim si novy objekt formulare
//        $form = new $form_class_name($this->model, $form_config, $this->request_params, TRUE);

        //metoda muze vyhodit vyjimku, ktera muze zaridit presmerovani na jinou stranku
        try
        {
            //zpracovani vstupnich dat formulare
            $form->Process();

            $this->_event_form_process_after($form);
        }
        //tato vyjimka definuje ze ma dojit k presmerovani na editaci zaznamu
        //se specifickym ID - toto se pouziva jako presmerovani na editacni
        //formulare prave vytvoreneho zaznamu
        catch (Exception_Redir $e)
        {
            //presmerovani v tomto pripade ignoruji
        }

        //pokud je definovana v konfiguraci volba pro presmerovani po uspesnem
        //prvedeni fomrularo akce tak presmerujeme
        if ($form->getRequestedActionResult() == Core_AppForm::ACTION_RESULT_SUCCESS
            && ($closure = arr::get($form_config, 'on_success_redir')) != NULL)
        {
            $redirect_url = call_user_func($closure, $this->model);
            return $this->request->redirect($redirect_url);
        }

        //do sablony vlozim vysledek provedene akce
        $this->template->action_name   = $form->getRequestedAction();
        $this->template->action_result = $form->getActionResult();
        $this->template->action_status = $form->getActionResultStatus();
        //predam i ID ulozeneho zaznamu
        $this->template->id = $this->model->pk();
        //dale i preview zaznamu - format preview muze byt explicitne definovan
        //v parametrech pozadavku
        $this->template->preview = $this->model->preview(arr::get($this->request_params, '__preview'));

        //vlastni formular bude vlozen do sablony
        $this->template->content->form = $form->Render();

        //hlavni nadpise ve strance generuje trida formulare
        $this->template->headline = $form->getHeadline();

        //do sablony vlozim pouze ty soubory, ktere mohou byt vlozeny vicekrat
        $script_include_tag = Web::instance()->getJSFiles(TRUE);
        $script_include_tag.= '<script type="text/javascript">$(document).ready(function(){if(typeof $.waypoints !== "undefined"){$.waypoints("refresh");}});</script>';

        //vlozim do sablony aby doslo k nacteni prislusnych souboru do stranky
        $this->template->content->script_include_tag = $script_include_tag;

        // Posleme spravne hlavicky
        $this->request->headers['Content-Type'] = 'application/json';
    }

    /**
     * Vraci referenci na instanci tridy, ktera implementuje logiku pro filtrovani
     * dat nad danym poradecem. Dana trida musi dedit z tridy FilterBase.
     *
     * Logika nacteni tridy filtru je v samostatne metode, protoze zahrnuje
     * vygenerovani ocekavaneho nazvu tridy podle nazvu kontroleru, dale osetreni
     * situace kdy pozadovany filtr neexistuje anebo nededi z bazove tridy
     * pro filtry.
     *
     *
     * @return <FilterBase> V pripade uspechu vraci referenci na vytvorenou
     * instanci tridy dedici z FilterBase, ktera implementuje logiku filtrovani
     * dat na danem proadaci.
     */
    protected function loadAndInitFilterClassInstance($config = NULL)
    {
        //nazev tridy ktera implementuje filtrovani standardne sestavim - pokud neni
        //explicitne definovan v konfigu
        $class_name = arr::get($config, 'filter_name', 'Filter_'.$this->controller_name);
        
        //provedu kontrolu zda existuje a zda jde o spravny typ
        if ( ! class_exists($class_name))
        {
            throw new FilterClassNotFoundException('Unable to load "'.$class_name.'".');
        }

        //vytvorim si instanci se kterou budu dale pracovat
        $class_instance = new $class_name($this->controller_name, $this->object_name, $this->request_params, $this->user->pk(), $config);

        //pokud trida existuje ale nededi ze tridy FilterBase, tak dojde k vyjimce
        if ( ! is_subclass_of($class_instance, 'Filter_Base'))
        {
            throw new FilterParentClassIncorrectException('Instance of filter class "'.$class_name.'" must inherit from FilterBase.');
        }

        //trida je v poradku, vracim referenci na vytvorenou instanci
        return $class_instance;
    }

    /**
     * Generuje prehledovou stranku pro zaznam s ID $item_id.
     * @param <int> $item_id
     * @return <type> 
     */
    public function action_overview($item_id)
    {
        //vyvolani globalni udalosti 'system.action_overview_pre'
        Dispatcher::instance()->trigger_event('system.action_overview_pre', Dispatcher::event(array('controller' => $this)));

        //kontrola opravneni uzivatele na konkretni objekt tohoto kontroleru
        if ( ! $this->user->HasPermission($this->object_name, 'overview'))
        {
            return $this->runUnauthorizedAccessEvent();
        }

        //nactu ORM pozadovaneho objektu
        $this->model = ORM::factory($this->object_name, $item_id);
        
        //pokud neexistuje tak...
        //@TODO: presmerovat na /table nebo hodit 404 nebo nejakou stranku, ktera informuje o tom ze dany zaznam nefunguje ?
        if ( ! $this->model->loaded())
        {
            throw new Kohana_Exception('TODO!');
        }

        //nahodny identifikator (html id atribut), ktery bude pouzit pro inicializaci
        //objectOverview pluginu na prislusnem div bloku
        $overview_container_id = rand();

        //dale pridam tyto JS soubory, ktere zajistuji zakladni funkce overview stranky
        Web::instance()->addCustomJSFile(View::factory('js/jquery.objectOverview.js'));

        //provede incializaci pluginu objectOverview
        Web::instance()->addMultipleCustomJSFile(View::factory('js/jquery.objectOverview-init.js', array(
            'overview_container_id' => $overview_container_id
        )));
        
        //jQuery.objectDataPanel pro funkcnost panelu, ktere se budou nacitat do obsahove castu
        Web::instance()->addCustomJSFile(View::factory('js/jquery.objectDataPanel.js'));

        //dale vlozim JS Set 'form', kvuli formularum, ktere mohou jQuery.objectDataPanel nacitat
        Web::instance()->addJSFileSet('form');

        //do stranky dale vlozim JS Set 'table' - do tohoto setu jsou vlozeny JS
        //soubory ktere mohou byt potreba ve strance
        Web::instance()->addJSFileSet('overview');

        //do stranky vlozim obsahovou sablonu pro "/overview" vypis
        $this->template->content = $this->_view_overview_content();

        //tohle 'id' se pouziva pro inicializaci prislusneho jquery pluginu na
        //spravnem elementu ve strance
        $this->template->content->overview_container_id = $overview_container_id;

        //navratovy odkaz na tabulkovy vypis
        $this->template->content->return_link       = Request::instance()->get_retlink();
        $this->template->content->return_link_label = Request::instance()->get_retlink_label();

        //vlozim nazev objektu do sablony aby podle toho bylo mozne stylovat
        $this->template->content->controller_name = $this->controller_name;

        //doplnim jednotlive casti overview strnaky
        $this->template->content->header = $this->_view_overview_header();
        
        //predam referenci na ORM model zaznamu
        $this->template->content->header->model = $this->model;

        //k odkazu pro editaci pridam jeste navratovy odkaz na vypis pokud je
        //definovan
        $this->template->content->header->edit_link = $this->user->HasPermission($this->object_name, 'edit')
                                                        ? appurl::object_edit($this->controller_name,
                                                                              $this->model->pk(),
                                                                              array(
                                                                                   Request::instance()->get_retlink(),
                                                                                   Request::instance()->get_retlink_label()
                                                                              ))
                                                        : NULL;

        $this->template->content->model = $this->model;

        //sablona, ktera zobrazuje postrani menu
        $this->template->content->submenu = $this->_view_overview_submenu();
        
        //predam potrebne parametry
        $this->template->content->submenu->object_name = $this->object_name;

        //vyvolani globalni udalosti 'system.action_overview_post'
        Dispatcher::instance()->trigger_event('system.action_overview_post', Dispatcher::event(array('controller' => $this)));
    }

    /**
     *
     * @param <int> $item_id ID zaznamu jehoz atribut ma byt zmenen.
     */
    public function action_change_attr($item_id)
    {
        //zruseni sablony, ktera standardne tvori vystup
        $this->template = View::factory('null');

        $attr  = arr::get($_GET, 'attr', NULL);
        $value = arr::get($_GET, 'value', NULL);

        $model = ORM::factory($this->object_name, $item_id);

        //pokud nebyl model nalezen, tak se nebude nic provadet
        if ( ! $model->loaded())
        {
            echo json_encode(array('error' => 'Model not found.'));
        }
        
        //zmena hodnoty atributu
        $model->{$attr} = $value;
        
        //ulozeni zmen
        $model->save();

        echo json_encode(1);
    }

    /**
     * Generuje obsah casti 'subcontent' pro prehledovou stranku (overview).
     * Pozadavky na subcontentjsou Ajaxove takze vraci pouze HTML kod pozadovaneho
     * panelu, zabaleny v JSONu.
     * @param <string> $panel Nazev pozadovaneho panelu, ktery ma byt nahran
     * do 'subcontent' casti.
     * @param <int> $item_id
     */
    public function action_overview_subcontent($panel, $item_id)
    {
        //kontrola opravneni uzivatele na konkretni objekt tohoto kontroleru
        if ($this->user->HasPermission($this->object_name.'-'.$panel) === FALSE)
        {
            return $this->runUnauthorizedAccessEvent();
        }

        //nactu ORM pozadovaneho objektu
        $this->model = ORM::factory($this->object_name, $item_id);

        //pokud neexistuje tak...
        //@TODO: presmerovat na /table nebo hodit 404 nebo nejakou stranku, ktera informuje o tom ze dany zaznam nefunguje ?
        if ( ! $this->model->loaded())
        {
            throw new Kohana_Exception('TODO!');
        }

        $this->template = new View('overview_subcontent_response');

        $subcontent_panel = $this->_view_overview_subcontent_panel($panel);

        //vystup vracim ve forme JSONu tak aby jej bylo mozne dobre zpracovat
        //na strane klienta
        $html = (string)$subcontent_panel;

        //po vykresleni formulare:
        //do sablony vlozim pouze ty soubory, ktere mohou byt vlozeny vicekrat

       $html .= Web::instance()->getJSFiles(TRUE);

        $this->template->content = array(
                                       'html' => $html,
                                   );
    }

    /**
     * Vraci nazev tridy pro praci s formulari, ktera se pouzije pro zpracovani
     * formulare na akci action_edit. Pokud je na dedidich kontrolerech potreba
     * pouzit jinou tridu (ktera dedi z AppForm) tak je potreba pretizit tuto metodu.
     * @return <string>
     */
    protected function _action_edit_form_class_name()
    {
        return 'AppForm';
    }

    /**
     * Vraci panel ktery predstavuje submenu pro prehledovou stranku objektu.
     * Jednotlive polozky submenu se predavaji prvnim argumentem.
     * Pokud je argument roven NULL tak se submenu negeneruje - metoda vraci
     * prazdnou sablonu.
     *
     * 
     *
     * @param <array> $items Pole definujici polozky submenu v nasledujicim tvaru:
     * array(
     *      'offers' => array(
     *          'label' => 'Nabidky',
     *          'default' => true
     *      ),
     *      'history' => array(
     *          'label' => 'Historie'
     *          'default' => false
     *      )
     * )
     *
     * @param <string> $viewname = NULL Nazev sablony, ktera definuje vzhled postraniho
     * podmenu. Pokud neni explicitne definovano, tak je nactena zakladni sablona
     * 'overview_submenu_standard'.
     *
     * @throws MissingObjectSubviewException V pripade ze pozadovana sablona nebyla nalezena.
     * @return <View> Vraci nactenou sablonu.
     */
    public function _view_overview_submenu($items = NULL, $view_name = NULL)
    {
        //nazev sablony, kterou budu nacitat
        empty($view_name) AND $view_name = 'overview_submenu_standard';

        //z menu jeste vyhodim odkazy na objekty na ktere nema uzivatel vubec
        //zadne opravneni
        foreach ((array)$items as $object_name => $config)
        {
            //kontrola opravneni uzivatele na konkretni objekt tohoto kontroleru
            if ($this->user->HasPermission($this->model->object_name().'-'.$object_name) === FALSE)
            {
                unset($items[$object_name]);
            }
        }
        
        return $this->_load_view($view_name, array('items' => $items, 'model' => $this->model));
    }

    /**
     * Metoda nacita sablonu, ktera definuje obsah casti "overview_subcontent"
     * na /overview strance zaznamu.
     * @param <string> $viewname Nazev sablony. Automaticky je pridan prefix $this->object_name.'_overview_subcontent_'.
     * @throws MissingObjectSubviewException V pripade ze pozadovana sablona nebyla nalezena.
     * @return <View>
     */
    public function _view_overview_subcontent_panel($view_name)
    {
        $view_name = $this->object_name.'_overview_subcontent_'.$view_name;
        
        return $this->_load_view($view_name, array('model' => $this->model));
    }

    /**
     * Metoda vraci nactenou 'hlavni' sablonu, ktera definuje rozlozeni prvku na /table
     * podstrance objektu.
     *
     * @params <String> $viewname V pripade ze je potreba v dedicim objektu
     * nacist jinou sablonu tak staci jeji nazev predat timto parametrem.
     * Tento mechanismus slouzi k tomu aby nebylo nutne celou logiku nacitani sablony
     * re-implementovat vzdy kdyz je potreba nacist sablonu s jinym nazvem.
     *
     * @throws MissingObjectSubviewException v pripade ze pozadovanou sablonu
     * nelze nacist.
     *
     * @returns <View> Vraci nactenou sablonu v podobe tridy View.
     */
    protected function _view_table_content($view_name = NULL)
    {
        //nazev sablony, kterou budu nacitat
        empty($view_name) AND $view_name = 'table_content_standard';
        
        return $this->_load_view($view_name);
    }

    /**
     * Metoda vraci nactenou sablonu, ktera definuje vyhledavaci formular pro
     * dany objekt.
     *
     * @params <String> $viewname V pripade ze je potreba v dedicim objektu
     * nacist jinou sablonu tak staci jeji nazev predat timto parametrem.
     * Tento mechanismus slouzi k tomu aby nebylo nutne celou logiku nacitani sablony
     * re-implementovat vzdy kdyz je potreba nacist sablonu s jinym nazvem.
     *
     * @throws MissingObjectSubviewException v pripade ze pozadovanou sablonu
     * nelze nacist.
     *
     * @returns <View> Vraci nactenou sablonu v podobe tridy View.
     */
    protected function _view_table_filter($view_name = NULL)
    {
        //nazev sablony, kterou budu nacitat
        empty($view_name) AND $view_name = $this->object_name.'_filter';
        
        return $this->_load_view($view_name);
    }

    /**
     * Metoda vraci nactenou sablonu, ktera definuje zobrazeni vlastni dat - zaznamu.
     *
     * @params <String> $viewname V pripade ze je potreba v dedicim objektu
     * nacist jinou sablonu tak staci jeji nazev predat timto parametrem.
     * Tento mechanismus slouzi k tomu aby nebylo nutne celou logiku nacitani sablony
     * re-implementovat vzdy kdyz je potreba nacist sablonu s jinym nazvem.
     *
     * @throws MissingObjectSubviewException v pripade ze pozadovanou sablonu
     * nelze nacist.
     *
     * @returns <View> Vraci nactenou sablonu v podobe tridy View.
     */
    protected function _view_table_data($view_name = NULL)
    {
        //nazev sablony, kterou budu nacitat
        empty($view_name) AND $view_name = $this->object_name.'_table';
        
        return $this->_load_view($view_name);
    }

    /**
     * Nacita sablonu, ktera se pouzije pro generovani souboru, ktery bude
     * obsahovat exportovana data. Sablona se nacita podle daneho zaznamu
     * UserExport
     *
     * @param Model_UserExport $user_export
     */
    protected function _view_export_data(Model_UserExport $user_export)
    {
        //podle hodnoty atributu format_type nactu prislusnou sablonu
        return $this->_load_view('export/'.$user_export->format_type);
    }

    /**
     * Metoda vraci nactenou sablonu, ktera definuje obal vlastnich dat - tato sablona
     * zajistuje napr. zobrazeni strankovani a odkazy pro zmenu velikosti stranky.
     *
     * @params <String> $viewname V pripade ze je potreba v dedicim objektu
     * nacist jinou sablonu tak staci jeji nazev predat timto parametrem.
     * Tento mechanismus slouzi k tomu aby nebylo nutne celou logiku nacitani sablony
     * re-implementovat vzdy kdyz je potreba nacist sablonu s jinym nazvem.
     *
     * @throws MissingObjectSubviewException v pripade ze pozadovanou sablonu
     * nelze nacist.
     *
     * @returns <View> Vraci nactenou sablonu v podobe tridy View.
     */
    protected function _view_table_data_container($view_name = NULL)
    {
        //nazev sablony, kterou budu nacitat
        empty($view_name) AND $view_name = 'table_data_container';
        
        return $this->_load_view($view_name);
    }

    /**
     * Metoda vraci nactenou sablonu, ktera ma byt zobrazena namisto tabulky
     * s tady v pripade ze bylo nalezeno 0 vysledku.
     *
     * @params <String> $viewname V pripade ze je potreba v dedicim objektu
     * nacist jinou sablonu tak staci jeji nazev predat timto parametrem.
     * Tento mechanismus slouzi k tomu aby nebylo nutne celou logiku nacitani sablony
     * re-implementovat vzdy kdyz je potreba nacist sablonu s jinym nazvem.
     *
     * @throws MissingObjectSubviewException v pripade ze pozadovanou sablonu
     * nelze nacist.
     *
     * @returns <View> Vraci nactenou sablonu v podobe tridy View.
     */
    protected function _view_table_empty_data_container($view_name = NULL)
    {
        //nazev sablony, kterou budu nacitat
        empty($view_name) AND $view_name = 'table_empty_data_container';

        return $this->_load_view($view_name);
    }

    /**
     * Metoda vraci nactenou sablonu, ktera definuje rozlozeni prvku na
     * "/overview" strance.
     *
     * @params <String> $viewname V pripade ze je potreba v dedicim objektu
     * nacist jinou sablonu tak staci jeji nazev predat timto parametrem.
     * Tento mechanismus slouzi k tomu aby nebylo nutne celou logiku nacitani sablony
     * re-implementovat vzdy kdyz je potreba nacist sablonu s jinym nazvem.
     *
     * @throws MissingObjectSubviewException v pripade ze pozadovanou sablonu
     * nelze nacist.
     *
     * @returns <View> Vraci nactenou sablonu v podobe tridy View.
     */
    protected function _view_overview_content($view_name = NULL)
    {
        //nazev sablony, kterou budu nacitat
        empty($view_name) AND $view_name = 'overview_content_standard';
        
        return $this->_load_view($view_name);
    }

    /**
     * Metoda vraci nactenou sablonu, ktera definuje hlavicku na strance /overview.
     *
     * @params <String> $viewname V pripade ze je potreba v dedicim objektu
     * nacist jinou sablonu tak staci jeji nazev predat timto parametrem.
     * Tento mechanismus slouzi k tomu aby nebylo nutne celou logiku nacitani sablony
     * re-implementovat vzdy kdyz je potreba nacist sablonu s jinym nazvem.
     *
     * @throws MissingObjectSubviewException v pripade ze pozadovanou sablonu
     * nelze nacist.
     *
     * @returns <View>
     */
    protected function _view_overview_header($view_name = NULL)
    {
        //nazev sablony, kterou budu nacitat
        empty($view_name) AND $view_name = $this->controller_name.'_overview_header';
        
        return $this->_load_view($view_name);
    }

    /**
     * Metoda nacita zakladni sablonu, ktera definuje rozlozeni prvku na editacni
     * strance (/object/edit/X).
     * @throws MissingObjectSubviewException V pripade ze pozadovana sablona neni nalezena.
     * @param <string> $view_name Nazev pozadovaneho view. Pokud je argument prazdny
     * tak se doplni defaultni nazev "edit_content_standard".
     * @return <View>
     */
    protected function _view_edit_content($view_name = NULL)
    {
        empty($view_name) AND $view_name = 'edit_content_standard';
        
        return $this->_load_view($view_name);
    }

    protected function _view_edit_ajax_template($view_name = NULL)
    {
        empty($view_name) AND $view_name = 'edit_ajax_template';

        $view = $this->_load_view($view_name);

        //pridam definici specialni kotev, ktere se pouzivaji k predani
        $view->extra = array();

        return $view;
    }

    protected function _view_delete_ajax_template($view_name = NULL)
    {
        empty($view_name) AND $view_name = 'delete_ajax_template';

        $view = $this->_load_view($view_name);

        return $view;
    }

    /**
     * Metoda nacita zakladni sablonu, ktera definuje rozlozeni prvku pri editaci
     * zaznamu v dialogovem okne (sablona formulare, se nacita pres ajax).
     * @throws MissingObjectSubviewException V pripade ze pozadovana sablona neni nalezena.
     * @param <string> $view_name Nazev pozadovaneho view. Pokud je argument prazdny
     * tak se doplni defaultni nazev "edit_content_standard".
     * @return <View>
     */
    protected function _view_edit_ajax_content($view_name = NULL)
    {
        empty($view_name) AND $view_name = 'edit_ajax_content_standard';

        return $this->_load_view($view_name);
    }


    /**
     * Nacita sablonu, ktera zobrazuje vysledek akce odstraneni zaznamu.
     * @param string $view_name
     * @return <type> 
     */
    protected function _view_delete_action_result($view_name = NULL)
    {
        empty($view_name) AND $view_name = 'action_delete_result';

        return $this->_load_view($view_name);
    }

    /**
     * Tato metoda se pouziva k nacitani sablon a je urcena k volani z ostatnich
     * metod, ktere jsou urceny k nacteni specifickeho typu sablony.
     *
     * @throws MissingObjectSubviewException V pripade ze pozadovana sablona neni nalezena
     *
     * @param <string> $view_name Nazev sablony
     * @param <array> $params Parametry, ktere budou do sablony vlozeny
     * @return <View>
     */
    protected function _load_view($view_name, $params = array())
    {
        try
        {
            return View::factory($view_name, $params);
        } catch (Exception $e)
        {
            throw new MissingObjectSubviewException('Cannot find "'.$view_name.'" view.', NULL, $e);
        }
    }

    /**
     * Vraci nacteny konfiguracni soubor ve forme Kohana_Config objektu, ktery
     * obsahuje konfiguraci pro dany typ tabulkoveho vypisu.
     *
     * Konfigurace pro tabulkove vypisy se pouzivaji na url /object/table a
     * /object/table_data/table.
     *
     * V akci, ktera obsluhuje /object/table se predava druhy segment url jako
     * argument teto metody, tedy 'table' a nacita se konfigurace pro 'table'
     * vypis.
     *
     * V akci, ktera obsluhuje /object/table_data/table se teto metode predava
     * treti segment, ktery definuje typ vypisu.
     *
     * Prave podle typu tablkoveho vypisu, ktery je predany parvnim argumentem
     * se nacita prislusny konfiguracni soubor, ktery definuje sablonu pro
     * filtre, tabulkovy vypis a dalsi nataveni pro Filter tridu.
     * 
     * @return <Kohana_Config>
     */
    protected function _config_object_table($type = NULL)
    {
        return Kohana::config($this->object_name.'_'.$type);
    }

    protected function _config_object($config_name = NULL)
    {
        //defaultni nazev konfiguracniho souboru pro formular
        empty($config_name) AND $config_name = $this->object_name;

        //vraci nactenou konfiguraci
        return Kohana::config($config_name);
    }

    /**
     * Nacita konfiguracni pro formular nad danym objektem.
     * Nazev konfiguracniho souboru sestavuje jako $this->object_name.'_form'.
     *
     * @return <string>
     */
    protected function _config_form($config_name = NULL)
    {
        //defaultni nazev konfiguracniho souboru pro formular
        empty($config_name) AND $config_name = $this->object_name.'_form';

        //vraci nactenou konfiguraci
        return Kohana::config($config_name);
    }

    
    public function __call($name, $arguments)
    {
        switch ($name)
        {
            case '_event_form_process_after':
            break;

            default:
                return parent::__call($name, $arguments);
        }
    }

    /**
     * Processes data export Ajax requests (see DataExport module for more info)
     */
    public function action_table_data_export()
    {
        //kontrola opravneni uzivatele na konkretni akci tohoto kontroleru
        if ( ! $this->user->HasPermission($this->object_name, 'table'))
        {
            return $this->runUnauthorizedAccessEvent();
        }

        $table_config_group  = $this->request->param('table_config');
        $export_config_group = $this->request->param('export_config');

        $table_config  = kohana::config($table_config_group);
        $export_config = kohana::config($export_config_group);

        try{
            //vytvori instanci tridy, ktera zajistuje logiku filtrovani
            $filter_instance = $this->loadAndInitFilterClassInstance($table_config);

            //Vraci ORM_Iterator predstavici vysledky vyhledavani
            list($results, $filter_state_id, $filter_state_stat) = $filter_instance->getResults(FALSE);

            $export_filename = DataExport::Factory($export_config, $results)
                ->generateExport()
                ->getFilePath();

            $response = array(
                'f' => URL::site(str_replace(DIRECTORY_SEPARATOR, '/', $export_filename)),
            );
        }
        catch (Exception $e)
        {
            kohana::$log->add(KOHANA::ERROR, $e->getMessage());

            $response = array(
                'e' => __('object.data_export.server_side_error')
            );
        }

        die(json_encode($response,  JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP));
    }

}