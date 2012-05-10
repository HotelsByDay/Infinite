<?php

/**
 * Tato trida zajistuje obecne funkce pro aplikaci jako pro 'web'.
 *
 * Vkladani JavaScript souboru:
 * Trida umoznuje "implicitni" vkladani souboru - vsechny JavaScript soubory
 * umistene v adresarich 'views/ai_js' jsou automaticky vlozeny do stranky.
 * 'Explicitni' vkladani JavaScript souboru. Pomoci jedne z metod je mozne
 * vlozit JS soubor, ktery je umisteni v sablone (jako View), nebo je definovan
 * URL adresou (napriklad soubory v CDN).
 *
 *
 * Trida zajistuje automaticke zkompilovani a zacachovani vsech JS souboru.
 *
 */
class Core_Web
{

    /**
     * Konfigurace teto tridy. Nacita se ze standardnich kohanich konfiguracnich
     * souboru v konstruktoru.
     * @var <array>
     */
    protected $config = array();

    /**
     * Vycet custom zaregistrovanych sablon, ktere predstavuji JS soubory.
     *
     * @var <array>
     */
    protected $custom_js_views = array();

    /**
     *
     * @var <type> Vycet automaticky zaregistrovanych sablon, ktere predsatvuji
     * JS soubory.
     *
     * @var <array>
     */
    protected $ai_custom_js_views = array();

    /**
     * Vycet custom zaregistrovanych sablon, ktere mohou byt do stranky vlozeny
     * vicekrat.
     * Tyto sablony byvaji vlozeny na vystup napriklad pri generovani editacniho
     * formulare objektu v ajaxovem pozadavku (akce action_edit_ajax).
     *
     * @var <array>
     */
    protected $multiple_custom_js_views = array();

    /**
     * Obsahuje retezce ktere obsahuji html kod, ktery ma byt vlozen do stranky.
     * Jedna se vetsinou o takove casti, ktere nejsou vkladany do stranky vzdy.
     * @var <array>
     */
    protected $custom_html_views = array();

    /**
     * Obsahuje vycet URL adres JavaScript souboru, ktere budou pridany do stranky.
     * To se pouziva napriklad pro vlozeni Google Map Api souboru.
     * @var <array>
     */
    protected $remote_js_url = array();

    /**
     * Nese hodnotu title stranky.
     * Lze nastavit metodou setPageTitle.
     * @var <string>
     */
    protected $page_title = '';

    /**
     * Pri cachovani JS souboru do jednoho se jako nazev souboru pouziva md5()
     * hash jejich obsahu, casu poslendi upravy a velikosti. Navic je pred tento
     * hash pridan tento prefix pro vetsi odliseni.
     * - Tento prefix musi byt neprazdny a odlisny od prefixu pro CSS
     *   aby spravne fungoval JS compiler
     * @var <string>
     */
    protected $js_cache_prefix = 'js.';

    /**
     * V podadresari s timto nazvem uvnitr adresare 'views' se najdou vsechny sablony
     * a ty budou atuomaticky vlozeny do stranky pred ostatnimi explicitne
     * vlozenymi JS soubory.
     * @var <string>
     */
    protected $js_autoinclude_subdir = 'ai_js';

    /**
     * V podadresari s timto nazvem uvnitr adresare 'views' se nachazeji vsechny
     * JS soubory, se kterymi se pracuje jako s View.
     * Uvnitr tenchto adresaru (ve vsech aktivnich modulech) hledat metoda
     * addJSFileSet soubory (v pripade definice 'filter' u setu).
     * @param <string>
     */
    protected $js_include_subdir = 'js';

    /**
     * Singleton navrhovy vzor.
     */
    private function __construct()
    {
        //nacte konfiguraci pro tuto knihovnu
        $this->config = kohana::config('web');

        //vylistuje vsechnny soubory v podadresari views/ai_js - ty budou
        //automaticky vlozeny do stranky
        $auto_included_files = array_flip(kohana::list_files('views/'.$this->js_autoinclude_subdir));

        foreach ($auto_included_files as $filepath) {
            //upravim nazev souboru tak aby se jednalo o platny nazev sablon
            $kohana_file = preg_replace('#\.php#', '',
                               preg_replace('#^views/#', '', $filepath));

            $view = View::factory($kohana_file);
            //vlozim do seznamu vkladanych JS souboru (sablon)
            $this->ai_custom_js_views[$view->get_filename()] = $view;
        }
    }

    /**
     * Singleton navrhovy vzor.
     */
    private function __clone()
    {

    }

    /**
     * Vraci referenci na jedinou instance teto tridy.
     * @return <type>
     */
    static public function instance()
    {
        static $instance;

        $instance == NULL && $instance = new Web;

        return $instance;
    }

    /**
     * Vraci retezec, ktery predstavuje vsechny sablony nebo retezce, ktere byly
     * pridany metodou addCustomHTMLView sjednocene do jednoho retezce.
     * Jako oddelovac jednotlivych sablon nebo retezcu pouziva "\n".
     * @return <string>
     */
    public function getCustomHTML()
    {
        return implode("\n", $this->custom_html_views);
    }

    /**
     * Vraci retezec, ktery obsahuje <script> elementy, ktere predstavuji
     * JS soubory, ktere maji byt vlozeny do stranky.
     * V techto <script> elementech jsou vsechny JS soubory, ktere
     * byly v prubehu generovani stranky zaregistrovany jednou z metod teto tridy
     * a lokalne ulozene soubory jsou zacachovani a vlozeny do 1 vysledneho souboru.
     *
     * @param $only_multiple <bool> Pokud je TRUE tak jsou do stranky vlozeny pouze
     * ty soubory, ktere byly zaregistrovany metodou AddMultipleCustomJSFile. Jinak
     * jsou do stranky vlozeny vsechny pozadovane soubory.
     */
    public function getJSFiles($only_multiple = FALSE) {

        $profiler_token = NULL;
        // profilovani kontroly JS cache
        if (AppConfig::instance()->get('extended_profiling', 'system'))
        {
           $profiler_token = Profiler::start('resources', 'Checking JS file cache');
        }

        $js_include_block = '';

        //pridam dalsi soubory
        foreach ($this->remote_js_url as $file_url => $_)
        {
            $js_include_block .= "\n".'<script type="text/javascript" src="'.$file_url.'"></script>';
        }

        //do tohoto pole vlozim vsechny sablony, ktere maji byt vlozeny do stranky
        //podle argumentu tam muzou volitelne byt vlozeny i auto-include JS soubory
        $js_include_views = array();

        //pokud nemaji byt vlozeny pouze ty soubory, ktere mohou byt ve strance
        //vicekrat
        if ( ! $only_multiple)
        {
            //jako prvni pudou auto included JS soubory
            $js_include_views = $this->ai_custom_js_views;

            //pridam vsechny custom zaregistrovane soubory
            foreach ($this->custom_js_views as $key => $val)
            {
                $js_include_views[$key] = $val;
            }
        }

        //slepeni vsech souboru do jednoho, cachovani a vlozeni do stranky
        if ( ! empty($js_include_views))
        {
            //do stranky nejsou vkladany staticke soubory - ty jsou urceny
            //ke cachovani a kompilaci
            $script_element = $this->cacheFiles($js_include_views, $this->js_cache_prefix, TRUE);

            $js_include_block .= $script_element;
        }

        //tady posbiram vicenasobne vkladane soubory - ty se vkladaji zvlast
        //protoze se necachuji
        $js_multiple_include_views = array();

        //pridam soubory, ktere mohou byt ve strance vicekrat
        foreach ($this->multiple_custom_js_views as $key => $val)
        {
            $js_multiple_include_views[$key] = $val;
        }

        //slepeni vsech souboru do jednoho, cachovani a vlozeni do stranky
        if ( ! empty($js_multiple_include_views))
        {
            //do stranky jsou vkladany vicenasobne soubory, takze ne-staticke
            //a ty nebudou kompilovany a nekontroluje se zda uz jsou v cache
            //ale pokazde se cachuji znova
            $script_element = $this->cacheFiles($js_multiple_include_views, $this->js_cache_prefix, FALSE);

            $js_include_block .= $script_element;
        }

        // profilovani kontroly JS cache
        if (AppConfig::instance()->get('extended_profiling', 'system') && $profiler_token != NULL)
        {
            //stopovani cachovani a kompilovani souboru
            Profiler::stop($profiler_token);
        }

        return $js_include_block;
    }

    /**
     * Metoda projde predane soubory, vytvori z nich hash a soubory jako jeden
     * zkompiluje(prilepi jeden za druhy) a zacachuje. Klic na kterem se bude
     * cachovat metoda vraci.
     * @param <array> $files Seznam souboru, ktere maji byt zkompilovany do
     * vysledneho jednoho souboru. Jedna se o asoc. pole kde klicem je nazev soubor
     * relativne od adresare 'views' a hodnotou je asoc. pole s parametry, ktere
     * se predavaji tride View (kazdy JS soubor je View).
     * @param <string> $prefix Prefix, ktery vysledny souboru dostane. Toto je
     * dulezite, kvuli tomu ze se stejnym zpusobem muzou cachovat css i JS soubory.
     * @return <type>
     */
    protected function cacheFiles($views, $prefix, $static_files = TRUE) {

        //tady budu sackovat obsah vsech souboru
        $all_content = '';

        $all_hash = '';

        //ne-staticke soubory neocekavam v cache, protoze to jsou takove soubory
        //ktere pokazde meni svuj obsah - napr. inicializace jquery pluginu - identifikatory
        //jsou generovany nahodne - kontrola nize by prosla, protoze filesize
        //muze byt stejny, ale obsah je malinko jiny (nahodny identifikator je jiny)
        if ($static_files)
        {
            //jen si poskladam hash a uvidim jestli se soubory nezmenily
            foreach ($views as $filename => $view)
            {
                //nactu obsah souboru
                $file_content = (string)$view;
                //akdualizace celkoveho hashe
                $all_hash = md5($all_hash.filemtime($filename).filesize($filename).md5($file_content));
            }
        }
        
        //pokud je pro danou sestavu souboru neco v cache, tak vracim cache
        //klic a koncim
        if (Cache::instance()->get($prefix.$all_hash) != FALSE) {
            return '<script type="text/javascript" src="'.url::base().'js/'.($prefix.$all_hash).'" ></script>';
        }

        //jinak si resetuju $all_hash - tam poskladam novy hash kompilatu souboru
        $all_hash = '';

        foreach ($views as $filename => $view_or_view_array)
        {
            //kdyz se vkladaji "Multiple" sablony, tak hodnotou je pole, ktere
            //obshuje jednotlive instance
            if (is_array($view_or_view_array))
            {
                foreach ($view_or_view_array as $view)
                {
                    //nactu obsah souboru
                    $file_content = (string)$view;
                    //pridam do celkoveho obsahu
                    $all_content .= "\n".$file_content;
                    //aktualizuji hash
                    $all_hash = md5($all_hash.filemtime($filename).filesize($filename).md5($file_content));
                }
            }
            else
            {
                //nactu obsah souboru
                $file_content = (string)$view_or_view_array;
                //pridam do celkoveho obsahu
                $all_content .= "\n".$file_content;
                //aktualizuji hash
                $all_hash = md5($all_hash.filemtime($filename).filesize($filename).md5($file_content));
            }
        }

        if ( ! $static_files)
        {
            return '<script type="text/javascript">'.$all_content.'</script>';
        }

        //toto se zacachuje
        $data = array(
            'content' => $all_content,
            'static'  => $static_files
        );

        //soubor zacachuju na klici, ktery odpovida hashi obsahu vsech souboru
        //cachuje se s platnosti, ktera je definovana klicem resource_js
        Cache::instance()->set($prefix.$all_hash, $data, 'resource_js');

        //pokud se jedna o "kompilat" statickych JS souboru, tak je urcim
        //ke kompilaci
        if ($static_files) {
            Compiler::instance()->addForCompile($prefix.$all_hash);
        }

        return '<script type="text/javascript" src="'.url::base().'js/'.($prefix.$all_hash).'" ></script>';
    }


    /**
     * Zajisti vlozeni daneho js souboru do stranky.
     * Jednu sablonu umozni vlozit pouze jedenkrat. Pri vicenasobnem predani
     * stejne sablony bude do stranky pridana ta posledni vlozena.
     * $file_name musi odpovidat nazvu view, bez suffixu 'js'.
     * View musi byt v podsdresari js/.
     *
     * @param <string> $file_name
     * @param <type> $params
     */
    public function addCustomJSFile(Kohana_View $view)
    {
        //ziskam nazev souboru ze kterym bylo View inicializovano - to budu
        //potrebovat pri kontrole lastmtime pri praci s cache
        $view_filename = $view->get_filename();
        $this->custom_js_views[$view_filename] = $view;
        return $this;
    }

    /**
     * Zajisti vlozeni daneho js souboru do stranky. Umoznuje vlozit stejne View
     * do stranky vicekrat.
     * $file_name musi odpovidat nazvu view, bez suffixu 'js'.
     * View musi byt v podsdresari js/.
     *
     * Tento zpusob vkladani JS do stranky se pouziva pro inicializacni soubory
     * pro pluginy. Do techto souboru se vkladaji ahodne identifikatory
     * formularovych prvku - soubor jako celek je pokazde jiny a nema tdy smysl
     * ho cachovat.
     *
     * @param <string> $file_name
     * @param <type> $params
     */
    public function addMultipleCustomJSFile(Kohana_View $view)
    {
        //ziskam nazev souboru ze kterym bylo View inicializovano - to budu
        //potrebovat pri kontrole lastmtime pri praci s cache
        $view_filename = $view->get_filename();

        if ( ! isset($this->multiple_custom_js_views[$view_filename]))
        {
            $this->multiple_custom_js_views[$view_filename] = array();
        }
        //na dany klic pridam dalsi instanci sablony
        $this->multiple_custom_js_views[$view_filename][] = $view;
        return $this;
    }

    /**
     * Metoda slouzi k vlozeni Setu JS souboru do stranky.
     *
     * Vice viz. https://is.webcomplex.cz/projects/realsw-realhitcz/wiki/Pravidla_pro_psan%C3%AD_jQuery_plugin%C5%AF
     */
    public function addJSFileSet($set_name)
    {
        //seznam vsech setu
        $set_list = arr::get($this->config, 'set', array());

        //hledam pozadovany set
        $set_config = arr::get($set_list, $set_name, NULL);

        //pokud je definice setu prazdna nebo neexistuje tak metoda konci
        if (empty($set_config))
        {
            return $this;
        }

        //tady bude seznam souboru, ktere patri do Setu
        $file_list = arr::get($set_config, 'list', array());

        //po jednom je vlozim do stranky
        foreach ($file_list as $view_path)
        {
            //vrati mi upnou cestu k souboru - to je potreba kvuli kontrole
            //posledni upravy dane sablony vs. platnost cache
            $full_path = kohana::find_file('views', $view_path);

            $this->custom_js_views[$full_path] = View::factory($view_path);
        }

        //pokud je nastaven filtrovaci regular pro nalezeni souboru, tak zacnu hledat
        if (isset($set_config['filter']))
        {
            //pomoci tohoto regularu budu vyhledavat
            $filter = $set_config['filter'];

            //seznam vsech JS souboru
            $view_js_files = kohana::list_files('views/'.$this->js_include_subdir);

            foreach ($view_js_files as $filepath => $_)
            {
                //kohana::list_files vraci i s base adresarem a priponou .php - coz nechci
                $filepath = str_replace('views/', '', str_replace('.php', '', $filepath));

                if (preg_match($filter, $filepath))
                {
                    //nactu jako View
                    $view = View::factory($filepath);
                    //ziskam samotny nazev souboru
                    $filename = $view->get_filename();
                    //vlozim do seznamu sablon, ktere budou vkladani do stranky
                    $this->custom_js_views[$filename] = $view;
                }
            }
        }
        return $this;
    }

    /**
     * Zajisti vlozeni do stranky JS soubory, ktery je specifikovan svou url.
     * Toto se pouziva napriklad pro google map api.
     * @param <string> $file_url URL na ktere je JS soubor dostupny.
     */
    public function addRemoteJSFile($file_url)
    {
        $this->remote_js_url[$file_url] = NULL;
        return $this;
    }

    /**
     *
     * @param <type> $html
     */
    public function addCustomHTMLView($html)
    {
        $this->custom_html_views[] = $html;
        return $this;
    }

    /**
     * Nastavuje title generovane stranky.
     *
     * @param <string> $value
     * @return void
     * @author Jiří Melichar
     */
    public function setPageTitle($value)
    {
        $this->page_title = $value;
        return $this;
    }

    /**
     * Vraci title stranky.
     * K title, ktery je mozne nastavit metodou setPageTitle jeste pridava
     * suffix ' - [site_name]'.
     * @return <string>
     * @author Jiří Melichar
     */
    public function getPageTitle()
    {
        $site_name = AppConfig::instance()->get('system_name', 'application');
        if (empty($this->page_title)) {
            //defaultni page title
            return $site_name;
        }
        return $this->page_title.' - '.$site_name;
    }

}

?>
