<?php

class Filter_Agenda extends Filter_Base {

    protected $default_config = array(
        //moznosti pro vyber velikosti stranky
        'page_size' => array(
            '100' => '100'
        ),
        //defaultni velikost stranky
        'default_page_size' => 100,
    );

    /**
     * Nastavuje defaultni parametry filtru.
     * 
     * Jedna se o parametr 'week' (cislo tydne), ktery se nenastavuje ve filtrovacim
     * formulari, ale primo v sablone jsou tlacitka, ktera vyvolavaji nacteni dat
     * s novym cislem tydne - pri prvnim cteni dat neni hodnota definovana v prichozich
     * parametrech.
     */
    public function __construct($controller_name, $object_name, $filter_params, $user_id, $config)
    {
        //cislo tydne, ktery bude uzivateli zobrazen
        $this->filter_params['w'] = date('W');

        parent::__construct($controller_name, $object_name, $filter_params, $user_id, $config);
    }

    protected function applyFilter($orm)
    {
        //fulltextove vyhledavani
        if (($value = arr::get($this->filter_params, Filter_Base::FULLTEXT_QUERY_KEY)) != NULL)
        {
            $orm->and_where_open()
                    ->like('note', $value)
                    ->or_like('name', $value)
                ->and_where_close();

            //pri fulltextovem filtrovani se bude radit takto (je to uzpusobeno
            //tomu aby se to snadno vypisovalo v sablone)
            $orm->order_by('datedue', 'desc');
            $orm->order_by('cb_agenda_typeid', 'asc');
            $orm->order_by('cb_agenda_priorityid', 'desc');
            $orm->order_by('time_from', 'asc');
        }
        //week_offset rika ktery tyden ma byt zobrazen relativne od aktualniho (plus nebo minus)
        else 
        {
            //pri ne-fulltextovem filtrovani se bude radit takto (je to uzpusobeno
            //tomu aby se to snadno vypisovalo v sablone)
            $orm->order_by('datedue', 'asc');
            $orm->order_by('cb_agenda_typeid', 'asc');
            $orm->order_by('cb_agenda_priorityid', 'desc');
            $orm->order_by('time_from', 'asc');

            if (($week = arr::get($this->filter_params, 'w')) != NULL)
            {
                $orm->where(DB::expr('WEEK(`datedue`,1)'), '=', $week);

                //cislo aktualniho tydne
                $current_week = date('W');

                //rozdil proti pozadovanemu tydnu (v poctu tydnu)
                $delta_weeks = $week - $current_week;

                //time pro pondeli tohoto tydne
                $current_monday_time = time() - ((date('N') - 1) * 3600 * 24);

                //doplnim 'umele' do parametru specialni atribut - tento atribut
                //bude pouzit v sablone pro generovani vypisu ukolu
                //Zacatek pozadovaneho tydne ziskam prictenim nebo odectenim jednotlivych tydnu
                //od aktualniho pondeli
                $this->filter_params[':monday_time'] = $current_monday_time + ($delta_weeks * 86400 * 7);
            }
        }

        //filtrovani podle typu zaznamu
        if (($value = arr::get($this->filter_params, 'cb_agenda_typeid')) != NULL)
        {
            //@TODO: dodelat az budou upravene ciselniky
            $orm->where('cb_agenda_typeid', '=', $value);
        }
        
        //vazba na relacni zaznam
        if (($relid = arr::get($this->filter_params, 'relid')) != NULL)
        {
            $orm->where('relid', '=', $relid);
        }
        if (($reltype = arr::get($this->filter_params, 'reltype')) != NULL)
        {
            $orm->where('reltype', '=', $reltype);
        }

        //filtrovani podle kategorie
        if (($value = arr::get($this->filter_params, 'cb_agenda_categoryid')) != NULL)
        {
            $orm->where('cb_agenda_categoryid', '=', $value);
        }

        return $this;
    }
    
    protected function applyFulltextFilter($orm, $query)
    {
        return $this;
    }

    /**
     * Zajistuje vlozeni potrebnych JS souboru do stranky.
     * @return <type>
     */
    public function getForm($form_view = NULL)
    {
        //do stranky vlozim potrebne JS soubory (zajistuje dynamicke funkce
        //na formulari pro vlozeni filtru)
        Web::instance()->addCustomJSFile(View::factory('js/jquery.agendaFilterForm.js'));

        //parametry pro inicializaci pluginu
        $params = array(
            'this_week' => date('W')
        );
        Web::instance()->addCustomJSFile(View::factory('js/jquery.agendaFilterForm-init.js', array('params' => $params)));

        //mezi parametry pro inicializaci jquery.objectFilter pridam callback
        //na udalost after_initDataContent, ktery zajisti funkci checkboxu
        //pro zaskrtnuti nebo udskrtnuti ukolu
        $this->jquery_objectFilter_init_params['after_initDataContent'] = (string)View::factory('js/agenda_after_initDataContent_callback.js');

        //standardni vygenerovani formulare
        return parent::getForm($form_view);
    }

    /**
     * Na tabulkovem vypisu poradace Agenda nebudou zobrazene strankovace, protoze
     * se tam pouziva specialni sablona pro kalendarove zobrazeni zaznamu.
     * @return <string>
     */
    public function getPager($top = TRUE)
    {
        return '';
    }

    protected function _filter_container_view()
    {
        return View::factory('filter_container_agenda');
    }

    /**
     * Vraci instanci sablony, ktera ma byt pouzita pro zobrazeni vlastnich
     * dat. Standardne se jedna o sablonu s nazvem OBJECT_NAME."_table"
     * @return View
     */
    public function _view_table_data()
    {
        if (arr::get($this->filter_params, Filter_Base::FULLTEXT_QUERY_KEY) != NULL)
        {
            return View::factory($this->object_name.'_table_2');
        }
        else
        {
            return View::factory($this->object_name.'_table');
        }
    }

}

?>
