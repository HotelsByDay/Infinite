<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Tento kontroler implementuje funkci fulltextoveho vyhledavani napric vsemi
 * zaznamy v systemu.
 */
class Controller_Fulltext extends Controller_Layout{

    /**
     * Tato promenna bude obsahovat defaultni konfiguraci pro kazdy objekt zvlast.
     * Bude zde defaultni:
     *  - pocet vysledku pri zobrazeni vsech objektu
     *  - pocet vysledku pri zobrazeni jednoho objektu
     *  - closure pro generovani URL odkazu
     *  -
     * @var <array>
     */
    protected $default_object_config = array();

    /**
     * GET klic na kterem se predava retezec pro fulltextove vyhledavani.
     */
    const FULLTEXT_QUERY_KEY = 'q';

    /**
     * GET klic na kterem se predava nazev objektu jehoz zaznamy maji byt zobrazeny.
     * Vyhledava se ve vsech objektech, ale zobrazi se vysledky pouze tohoto.
     */
    const OBJECT_NAME_KEY = 'o';

    /**
     * Nastavuje promennou $this->default_object_config, protoze closure nemuzu
     * nastavit jako atribut.
     * 
     * @return <type>
     */
    public function before()
    {
        $this->default_object_config = array(
            //pocet vysledku zobrazenych kdyz se zobrazuji vysledky vsech objektu
            'size' => 3,
            //pocet vysledku kdyz je aktivni pouze hledani v jednom objektu
            'size_long' => 10,
            //closure pro generovani odkazu na dany zaznam
            'url_generator' => function($record){
                return appurl::object_overview($record->table_name(), $record->pk());
            }
        );
        
        return parent::before();
    }

    /**
     * Tato metoda provadi vlastni filtrovani dat.
     * Retezec podle ktereho se ma vyhledavat ocekava v parametru $_POST['q'].
     */
    public function action_search()
    {
        //vytahnu si retezec podle ktereho hledat
        $query = arr::getifset($_GET, Controller_Fulltext::FULLTEXT_QUERY_KEY, '');

        //vytahnu si objekt na kterem se ma hledat (ostatni se prohledaji taky
        //ale zobrazim pouze vysledky tohoto a bude jich vice
        $object_name = arr::getifset($_GET, Controller_Fulltext::OBJECT_NAME_KEY, '');

        //pokud je vyhledavaci retezec prazdny, tak se tiskne pouze napoveda
        if (empty($query))
        {
            $this->template->content = View::factory('fulltext_help');

            return;
        }
        else
        {
            //do layout sablony vlozim obsahovou sablonu
            $this->template->content = View::factory('fulltext_content');
        }

        //jQuery plugin, ktery zajistuje AJAX nacitani dat
        Web::instance()->addCustomJSFile(View::factory('js/jquery.globalFulltext.js'));

        //defaultni parametry pro nacteni dat
        $default_params = array(
            Controller_Fulltext::FULLTEXT_QUERY_KEY => $query,
            Controller_Fulltext::OBJECT_NAME_KEY    => $object_name,
        );

        Web::instance()->addCustomJSFile(View::factory('js/jquery.objectFulltext-init.js', array('default_params' => $default_params)));

        //do sablony vlozim vyhledavaci parametr, tak aby mohl byt pomoci ajaxu
        //ihned odeslan dotaz na nacteni vlastnich dat - uzivateli uz bude
        //zobrazen progress indicator
        $this->template->content->query = $query;

        //tato url se pouzije pro AJAX pozadavek pro nacteni vlastnich dat
        $this->template->content->data_url = appurl::fulltext_search_data($query, $object_name);

        //do fulltextoveho vyhledavaciho formulare vlozim defaultni retezec podle ktereho
        //uzivatel aktualne hleda
        $this->template->fulltext_form->query = $query;
    }

    /**
     * Tato akce zajistuje vygenerovani dat - vysledku fulltextoveho vyhledavani.
     * @return <type>
     */
    public function action_search_data()
    {
        //vyhledavaci retezec
        $query = arr::get($_GET, Controller_Fulltext::FULLTEXT_QUERY_KEY);

        //objekt jehoz vysledky maji byt zobrazeny
        $active_object_name = arr::get($_GET, Controller_Fulltext::OBJECT_NAME_KEY);
        
       //pokud je parametr prazdny, tak se nebude vyhledavat
        if (empty($query))
        {
            //uzivateli bude zobrazena napoveda k fulltextovemu vyhledavani
            $this->template = '';

            return;
        }

        //sablona pro zobrazeni nalezenych vysledku
        $this->template = View::factory('fulltext_data');

        //nactu konfiguraci kde je vycet poradacu ve kterych se ma vyhledavat
        $config = kohana::config('fulltext');

        //zde budu vkladat komplet vsechny vysledky vyhledavani
        $search_results = array();

        $object_search_params = array(
            Controller_Fulltext::FULLTEXT_QUERY_KEY => $query,
        );

        //na klici 'objects' je vycet objektu ve kterych se ma vyhledavat
        foreach ((array)arr::get($config, 'objects') as $object_name => $object_config)
        {
            //konfiguraci specifikovanou v konfiguraku mergnu s defaultni konfiguraci
            $object_config = arr::merge($this->default_object_config, $object_config);

            //pokud se ma hledat na specifickem objektu, tak se zobrazi jiny pocet
            //vysledku na danem objektu a na ostatnich bude 0 vysledku
            if ($active_object_name != NULL )
            {
                $object_config['size'] = $active_object_name == $object_name    
                                            ? $object_config['size_long']
                                            : 0;
            }

            //nacteni vysledku fulltextoveho vyhledavani
            list($orm_results, $total_found) = Fulltext::factory($object_name, $object_config)->Search($query);

            $object_search_params[Controller_Fulltext::OBJECT_NAME_KEY] = $object_name;

            //pridam k vysledkum vyhledavani
            $search_results[$object_name] = array(
                //nazev objektu
                'label'     => $object_name,
                //vlastni vysledky
                'data'      => $orm_results,
                //celkovy pocet nalezenych zaznamu
                'total'     => $total_found,
                //odkaz pro filtrovani pouze nad danym objektem
                'parameters'=> str_replace('"', '\'', (json_encode($object_search_params, JSON_FORCE_OBJECT))),
                //predavam Closure (anonym. funkci), ktera slouzi k vygenerovani odkazu na
                //ktery uzivatel prejde po kliknuti na zaznam - muze jit na editaci, prehled
                //nebo jiny pohled na zaznam
                'url_generator' => $object_config['url_generator'],
            );
        }

        //asoc. pole seradim podle hodnoty polozky 'total' - tedy celkovy pocet
        //nalezenych vysledky v danem objektu
        $search_results = arr::sort_by_key($search_results, 'total', FALSE);

        //predam nazev aktivniho objektu
        $this->template->active_object_name = $active_object_name;

        //vysledky vyhledavani vlozim do sablony
        $this->template->search_results = $search_results;

        //url pro hledani na vsech objektech
        $this->template->all_url = appurl::fulltext_search_data($query);

        //predam i vyhledavaci retezec, ktery bude zobrazen uzivateli
        $this->template->query = $query;
    }
}