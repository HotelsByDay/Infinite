<?php defined('SYSPATH') or die('No direct script access.');

class Helper_Appurl
{
    
    //Definuje klic v parametrech pozadavku na kterem se prenasi data
    const DATA_URL_KEY = '__d';

    //Definuje klic v parametrech pozadavku na kterem se prenasi navratovy odkaz
    const RETLINK_URL_KEY = '__r';

    //Definuej klic v parametrech pozadavku na kterem se prenasi popisek k navratovemu odkazu
    const RETLINK_URL_LABEL = '__rl';

    //Pokud je pouzitova kodovani parametru pozadavku tak zakodovana data jsou na tomto klici
    const ENCODED_PACK_KEY = 'ed';


    /**
     * @param $object_name
     * @param $object_id
     * @param $config_key
     * @return string
     */
    static public function polymorphic_cb_data($object_name, $object_id, $config_key)
    {
        return url::site("polymorphicnnselect/cb_data/$object_name/$object_id/$config_key");
    }


    /**
     * Vraci odkaz na prihlasovaci stranku.
     * @return <string>
     */
    static public function login_page()
    {
        return url::base().'login';
    }

    /**
     * Vraci URL na kterou ma smerovat prihlasovaci formular.
     * @return <string>
     */
    static public function login_action()
    {
        if (defined('DOMAIN_ADMIN') && defined('BASE_URL_ADMIN')) {
            return DOMAIN_ADMIN . BASE_URL_ADMIN . 'login';
        } else {
            return url::base().'login';
        }
    }

    /**
     * Vraci URL ktera zajistuje odhlaseni uzivatele.
     * @return <string>
     */
    static public function logout_action()
    {
        return url::base().'logout';
    }

    /**
     * Metoda vraci odkaz na domovskou stranku systemu.
     * @return <string>
     */
    static public function homepage()
    {
        return url::base().'dashboard';
    }

    /**
     * Metoda vraci URL na ktere jsou ocekavany pozadavky nahlasujici JS chyby
     * u klienta.
     * @return <string>
     */
    static public function js_error_report()
    {
        return url::base().'error/js';
    }

    /**
     * Generuje URL na stranku s profilem uzivatele
     * @return <string>
     */
    static public function user_profile()
    {
        return url::base().'my_profile';
    }

    /**
     * Generuje URL na stranku s profilem RK
     * @return <string>
     */
    static public function estateagency_profile()
    {
        return url::base().'agency_profile';
    }

    /**
     * Generuje URL na kontroler, ktery provadu fulltextove vyhledani
     * napryc vybranymi poradaci v systemu (dle /core/config/fulltext.php).
     */
    static public function fulltext_search($query = NULL, $object_name = NULL)
    {
        return url::base().'fulltext/search?q='.urlencode($query).'&o='.$object_name;
    }

    static public function fulltext_search_data()
    {
        return url::base().'fulltext/search_data';
    }

    /**
     * Tato metoda slouzi jako zaklad pro generovani URL spojenych s akcemi
     * object kontroleru.
     *
     * @param <string> $controller Nazev kontroleru
     * @param <string> $action Nazev akce
     * @param <array> $params Dalsi parametry pozadavku - budou do URL vlozeny
     * ve forme dalsich segmentu
     * @param <string> $get_params GET parametry pozadavku.
     * @return <string>
     */
    static public function object_action($controller, $action, $params = NULL, $get_params = NULL, $url_hash = NULL)
    {
        //vytvorim z parametru retezec, ktery pridam do vysledne URL
        if ( ! empty($params))
        {
            $params = '/'.implode('/', (array)$params);
        }
        else
        {
            $params = '';
        }


        //dale zpracuju GET parametry
        //ridi se prepinacem, ktery definuje zda se maji data kodovat nebo jit
        //v "plaintextu"
        if ( ! empty($get_params))
        {
            if (AppConfig::instance()->get('encode_object_get_params', 'system'))
            {
                //na tomto klici se prenaseji zakodovana data, ktera cilovy object kontroler
                //automaticky dekoduje
                $url_data_key = appurl::ENCODED_PACK_KEY;
                
                //zakoduju si aktualni parametry - ty nebudu predavat v GETu ale
                //na specialnim klici tak aby byly pri zpracovani spravne dekodovany
                $encoded_data = Encoder::instance()->encode($get_params);

                $get_params = $url_data_key.'='.$encoded_data;
            }
            else
            {
                //data v "plaintextu"
                $get_params = '?'.http_build_query($get_params, '', '&');
            }
        }
        else
        {
            //get_params muze byt pole - prepisu na prazdny retezec aby se
            //bez efektu propsalo do vysledne url nize
            $get_params = '';
        }

        //pridam url hash pokud je definovany
        if ( ! empty($url_hash))
        {
            $get_params .= '#'.$url_hash;
        }

        //vracim vyslednou URL
        return url::base().$controller.'/'.$action.$params.$get_params;
    }

    /**
     *
     * @param <type> $controller
     * @return <string>
     */
    static public function object_do_action($controller)
    {
        return self::object_action($controller, 'do');
    }

    /**
     *
     * @param <type> $controller
     * @return <string>
     */
    static public function object_undo_action($controller)
    {
        return self::object_action($controller, 'undo');
    }

    /**
     * Generuje odkaz na prehledovou stranku nad danym kontrolerem.
     *
     * @param $controller <string> Nazev kontroleru. Primo se propisuje do URL
     * @param $item_id <int> Hodnota PK zaznamu, jehoz overview stranka ma byt zobrazena.
     * @param $return_link <string> Navratovy odkaz - na overview strance bude tlacitko
     * 'zpet'.
     * @param $subsection <string> Nazev podsekce ktera ma byt defaultne aktivovana.
     * Pokud je nevyplneno tak bude aktivovana defaultni dle konfigurace ciloveho
     * poradace. Nazev podsekce musi odpovidat klici v poli ktere je generovano
     * metodou _view_overview_submenu ciloveho kontroleru.
     * Primo se propisuje do URL.
     */
    static public function object_overview($controller, $item_id, $return_link = NULL, $subsection = NULL, array $get_params=array())
    {
        //navratovy odkaz muze byt retezec (jen odkaz) anebo indexovane pole
        //kde na indexu [0] je odkaz a na indexu [1] je popisek
        //typu "Vratit zpet na vypis nabidek" pod kterym bude odkaz zobrazen
        //na formulari
        if (is_array($return_link) && count($return_link) == 2 && ! empty($return_link[0]) && ! empty($return_link[1]))
        {
            $get_params[appurl::RETLINK_URL_KEY]   = $return_link[0];
            $get_params[appurl::RETLINK_URL_LABEL] = $return_link[1];
        }
        else
        {
            $get_params[appurl::RETLINK_URL_KEY] = $return_link;
        }

        //vygeneruje kompletni URL a tu vracim 
        return self::object_action($controller, 'overview', $item_id, $get_params, $subsection);
    }

    static public function object_overview_subcontent($controller, $subcontent_name, $item_id, array $get_params=array())
    {
        return self::object_action($controller, 'overview_subcontent', array($subcontent_name, $item_id), $get_params);
    }

    static public function object_overview_header($controller, $item_id)
    {
        return self::object_action($controller, 'overview_header', $item_id);
    }

    static public function object_change_attr($controller, $item_id)
    {
        return self::object_action($controller, 'change_attr', $item_id);
    }

    /**
     * Generuje URL na akci, ktera zajistuje export dat na /table strankach.
     * @param <type> $controller
     * @return <type>
     */
    static public function object_export_data($controller)
    {
        return self::object_action($controller, 'export_data');
    }

    /**
     * Generuje odkaz na prehledovou stranku nad danym kontrolerem.
     *
     * @param $controller <string> Nazev kontroleru. Primo se propisuje do URL
     * @param $item_id <int> Hodnota PK zaznamu, jehoz overview stranka ma byt zobrazena.
     * Primo se propisuje do URL.
     */
    static public function object_edit($controller, $item_id, $return_link = NULL)
    {
        $get_params = array();

        //navratovy odkaz muze byt retezec (jen odkaz) anebo indexovane pole
        //kde na indexu [0] je odkaz a na indexu [1] je popisek
        //typu "Vratit zpet na vypis nabidek" pod kterym bude odkaz zobrazen
        //na formulari
        if (is_array($return_link) && count($return_link) == 2 && ! empty($return_link[0]) && ! empty($return_link[1]))
        {
            $get_params[appurl::RETLINK_URL_KEY]   = $return_link[0];
            $get_params[appurl::RETLINK_URL_LABEL] = $return_link[1];
        }
        else
        {
            $get_params[appurl::RETLINK_URL_KEY] = $return_link;
        }
        
        //vygeneruje kompletni URL a tu vracim 
        return self::object_action($controller, 'edit', $item_id, $get_params);
    }

    /**
     * Vraci odkaz ktery slouzi k ajaxovemu nacteni editacniho formulare
     * daneho kontroleru - tento odkaz slouzi k nacteni formulare editaci
     * existujiciho formulare.
     * @param <string> $controller Nazev kontroleru
     * @param <int> $item_id ID zaznamu k editaci
     * @param <string> $action Akce, ktera bude na kontroleru vyvolana. Pokud
     * neni explicitne definovano, tak je nastaveno na hodnotu 'edit_ajax'.
     * @param <array> $defaults Defaultni hodnoty pro formular budou predany pres URL
     * @return <type>
     */
    static public function object_edit_ajax($controller, $form_type, $item_id, $defaults = array())
    {
        //defaultni identifikator konfigurace formulare
        $form_type != NULL || $form_type = $controller.'_form';
        
        //defaultni hodnoty pro formular
        $get_params['defaults'] = $defaults;
        
        return self::object_action($controller, 'edit_ajax', array($form_type, $item_id), $get_params);
    }

    /**
     * Vraci odkaz ktery vede na editacni formular pro vytvoreni noveho zaznamu
     * na danem kontroleru. Umoznuje formulari poslat defaultni hodnoty.
     * @param <string> $controller Nazev kontroleru
     * @param <array> $defaults Defaultni hodnoty, ktere budou nastaveny do
     * prislusneho ORM modelu
     * @param <string> $action Akce, ktera bude na kontroleru vyvolana. Pokud
     * neni explicitne definovano, tak je nastaveno na hodnotu 'edit'.
     * @return <string>
     */
    static public function object_new($controller, $defaults = array(), $action = NULL)
    {
        //je akce explicitne definovana ?
        $action == NULL && $action = 'new';

        //defaultni hodnoty pro formular
        $get_params['defaults'] = $defaults;

        return self::object_action($controller, $action, NULL, $get_params);
    }

    /**
     * URL vede na akci kontroleru, ktera zajistuje odstraneni zaznamu.
     * @param <string> $controller
     * @param <int> $item_id
     * @return <string>
     */
    static public function object_delete($controller, $item_id)
    {        
        return self::object_action($controller, 'delete', $item_id);
    }

    /**
     * Vraci odkaz ktery slouzi k ajaxovemu nacteni editacniho formulare
     * daneho kontroleru - tento odkaz slouzi k nacteni formulare pro vlozeni
     * noveho zaznamu.
     * @param <string> $controller Nazev kontroleru
     * @param <string> $form_type Identifikator typu formulare. Odpovida nazvu konfiguracniho
     * souboru kde jsou definovany prvky a sablona pro formular.
     * @param <array> $defaults Defaultni hodnoty, ktere budou nastaveny do
     * prislusneho ORM modelu
     * @param <string> $action Akce, ktera bude na kontroleru vyvolana. Pokud
     * neni explicitne definovano, tak je nastaveno na hodnotu 'edit_ajax'.
     * @return <string>
     */
    static public function object_new_ajax($controller, $form_type = NULL, $defaults = array(), $overwrite = array())
    {
        //defaultni identifikator konfigurace formulare
        $form_type != NULL || $form_type = $controller.'_form';

        //defaultni hodnoty pro formular
        $get_params['defaults']  = $defaults;
        $get_params['overwrite'] = $overwrite;

        return self::object_action($controller, 'edit_ajax', $form_type, $get_params);
    }

    /**
     * Vraci odkaz ktery slouzi k ajaxovemu nacteni editacniho formulare
     * daneho kontroleru - tento odkaz slouzi k nacteni formulare pro vlozeni
     * noveho zaznamu.
     * @param <string> $controller Nazev kontroleru
     * @param <string> $form_type Identifikator typu formulare. Odpovida nazvu konfiguracniho
     * souboru kde jsou definovany prvky a sablona pro formular.
     * @param <array> $defaults Defaultni hodnoty, ktere budou nastaveny do
     * prislusneho ORM modelu
     * @param <string> $action Akce, ktera bude na kontroleru vyvolana. Pokud
     * neni explicitne definovano, tak je nastaveno na hodnotu 'edit_ajax'.
     * @return <string>
     */
    static public function object_itemlist_new_ajax($controller, $form_type, $itemlist_attr, $defaults = array())
    {
        //defaultni identifikator konfigurace formulare
        $form_type != NULL || $form_type = $controller.'_form';

        //defaultni hodnoty pro formular
        $get_params['defaults'] = $defaults;

        //formular se nacita do itemlistu - bude specificky vygenerovan
        $get_params['itemlist'] = $itemlist_attr;

        return self::object_action($controller, 'edit_ajax', $form_type, $get_params);
    }

    /**
     *
     * @param <type> $controller
     * @return <type> 
     */
    static public function object_cb_data($controller, $filter = array())
    {
        return self::object_action($controller, 'cb_data', NULL, $filter);
    }

    /**
     * Generuje odkaz na stranku s tabulkovym vypisem.
     * @param <string> $controller Nazev kontroleru
     * @param <array> $parameters Parametry pozadavku
     * @return <string>
     */
    static public function object_table($controller, $parameters = NULL)
    {
        $url_hash = http_build_query((array)$parameters);
        
        return self::object_action($controller, 'table', NULL, NULL, $url_hash);
    }

    /**
     * Generuje URL, ktera se pouziva jako atribut 'dataUrl' pro jQuery.objectDataPanel.
     *
     * Metoda zajistuje ze se zavola spravna metoda ciloveho kontroleru a zaroven
     * dojde k zakodovani doplnujiho filtru.
     *
     * @param <type> $object Cilovy objekt ze ktereho se budou data cist
     * @param <type> $panel Nazev panelu, ktery bude pouzity k zobrazeni dat
     * @param <type> $filter_data Doplnujici filtr, ktery se pouziva k tomu
     * aby byly napriklad filtrovani zajmy jen pro urcitou nabidku apod.
     * @return <string> Vygenerovana URL, ktera se primo natavuje jako hodnota
     * atributu 'dataUrl' pri inicializaci jQuery.objectDataPanel.
     */
    static public function object_odp_dataUrl($controller, $panel, $filter_data = array())
    {
        return appurl::object_action($controller, 'table_obd_panel', $panel, $filter_data);
    }

    /**
     * Vraci odkaz ktery slouzi k nacteni dat pro /table zobrazni nad danym
     * kontrolerem.
     * @param <string> $controller_name
     * @return <string>
     */
    static public function object_table_filter_action($controller, $type = NULL)
    {
        //typ specifickuje typ konfigurace. standardne to je 'table' a nazev
        //konfiguraku je tedy 'object_table'. Ale muzeme chtit nekde explicitne
        //chtit pouzit jiny konfig. Napr kdyz je vice typu vypisu na objektu.
        empty($type) AND $type = 'table';

        return appurl::object_action($controller, 'table_data', $type);
    }

    /**
     * Vraci odkaz ktery slouzi k nacteni dat pro /trash zobrazni nad danym
     * kontrolerem.
     * @param <string> $controller_name
     * @return <string>
     */
    static public function object_table_trash_filter_action($controller)
    {
        return appurl::object_action($controller, 'table_trash_data');
    }

    /**
     * Vraci URL ktera slouzi nacteni uzivatelskeho filtru (filterstate) vcetne
     * jeho aktualizovane statistiky.
     * @param <string> $controller
     * @return <string>
     */
    static public function object_filterstate_item($controller)
    {
        return appurl::object_action($controller, 'load_filter_item');
    }

    static public function object_remove_filterstate_item($controller)
    {
        return appurl::object_action($controller, 'remove_filter_item');
    }

    /**
     * Generuje URL, ktera slouzi k nahrani souboru do systemu.
     * @param <string> $form_config_item
     * @param <string> $file_view
     */
    static public function upload_file_action($config_key, $get_params=array())
    {
        return appurl::object_action('file', 'upload', $config_key, $get_params);
    }


    /**
     * Generuje URL, ktera slouzi k nahrani souboru do systemu (s tim ze soubor se primo
     * ulozi do ciloveho modelu a neni ho treba ukladat pozdeji)
     * @param <string> $form_config_item
     * @param <string> $file_view
     */
    static public function directupload_file_action($config_key, $get_params=array())
    {
        return appurl::object_action('file', 'direct_upload', $config_key, $get_params);
    }

    /**
     * Generuje URL, ktera slouzi k odstraneni libovolneho souboru. Pouziva
     * se v AppFormItemFile - kde pri kliknuti na tacitko Odstranit je volana
     * tato URL pro okamzite odstraneni daneho souboru (neni potreba kliknout na
     * Ulozit na formulari).
     */
    static public function delete_file_action($object_name)
    {
        return appurl::object_action('file', 'delete', $object_name);
    }

    static public function delete_itemlist_item($object_name)
    {
        return appurl::object_action('file', 'delete', $object_name);
    }

    /**
     * Generuje cestu k souboru ktery je reprezentovan predanym ORM modelem.
     * @param <ORM> $file Model, ktery reprezentuje soubor ke kteremu bude vracena URL.
     * @return <string>
     */
    static public function object_file($file, $resize_variant = NULL, $absolute_url = FALSE)
    {
        // DOMAIN_ADMIN introduced on Amli - defined in settings.php
        return ($absolute_url ? DOMAIN_ADMIN : '') . $file->getUrl($resize_variant);
    }

    /**
     * Returns url for MASTER lang item. Item will send ajax requests with enabled languages list to this url.
     * @static
     * @param $object_name
     */
    static public function languages_syncer_url(Interface_AppFormItemLang_MasterCompatible $model)
    {
        return url::base().'synclanguages/set_enabled_languages/'.$model->object_name().'/'.$model->pk();
    }
}

?>
