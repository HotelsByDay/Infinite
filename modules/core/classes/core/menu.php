
<?php

/**
 * Poskytuje metody pro generovani menu
 * Nazvoslovi:
 * Hlavni menu/main_menu - 1. uroven menu - jednotlive poradace
 * subnavigation - pevne zobrazeny panel s nabidkou aktivniho poradace
 * submenu - kontextova nabidka - muze byt v prvni main_menu i v subnavigation
 * polozka menu - aktualne LI html element
 */
class Core_Menu {
    
    // Konstanty pro specialni polozky menu

    // (!) Specialni typy lze pouzit pouze v 1. urovni menu (!)
    // Specialni typ polozky - odkaz DOMU
    const ITEM_TYPE_HOME = 1;
    // Specialni typ - nastaveni
    const ITEM_TYPE_SETTING = 2;
    // HR - vertikalni oddelovac
    const ITEM_HR = 3;
    
    // Pouzitelne v definici kontextoveho submenu
    const SECTIONS_ONLY = 11;
    const NEW_ONLY = 12;
    
    
    // Nazev implicitniho konfiguracniho souboru (lze predat jiny jako parametr instance() metody)
    protected $config_name = '_main_menu';
    
    // Nacteny obsah konfiguracniho souboru
    // - pomoci Kohana::config();
    protected $config = Array();
    
    
    // Css trida pridavana k aktivni polozce
    protected $active_class_name = 'active';
    
    // Oddelovac nazvu urovni v menu
    protected $label_separator = '&nbsp;-&nbsp;';

    
    
    // Do techto atributu se postupne nageneruje menu a subnavigation 
    protected $main_menu = '';
    protected $subnavigation = '';

    // Current controller and action in URL - for permission controls
    protected $controller = NULL;
    protected $action = NULL;

    // Controller - implies which menu item should be highlighted
    protected $nav_controller = NULL;

    // Subnav controller and action - implies which subnavigation item should be highlighted
    protected $subnav_action = NULL;
    protected $subnav_controller = NULL;

    
    // Neco jako globalni promenna pro ulozeni hloubky zanoreni pri generovani
    // submenu pomoci nepřímé rekurze - nevim jestli bude potřeba
    // protected $level = 0;
    
    
    
    /**
     * Predikat rikajici zda je polozka hlavniho menu se zadanou url aktivni
     * @param string $link URL testovane polozky
     * @return bool
     */
    protected function menuActive($link, $check_action_to_activate=false)
    {
        // Odstranime pripadny query string
        if (($_link = strstr($link, '?', TRUE)) !== FALSE) {
            $link = $_link;
//            $link = substr($link, 0, strpos($link, '?'));
        }
        // Pokud link obsahuje url::base, musime ji odstranit
        $base = url::base();
        if (substr($link, 0, strlen($base)) == $base) $link = substr($link, strlen($base));
        
        // + array nam zajisti ze prava strana vyrazu bude vzdy pole s alespon jednim prvkem
        list($controller, $action) = explode('/', $link) + array(NULL, NULL);
        if ($check_action_to_activate) {
            if ($action != $this->subnav_action) {
                return false;
            }
        }
        return ($controller == $this->nav_controller);
    }


    /**
     * Predikat rikajici zda polozka subnavigace se zadanou URL je prave aktivni
     * @param string $link URL testovane polozky
     * @return bool
     */
    protected function subNavigationActive($link)
    {
        if (empty($link)) return FALSE;
        
        // Odstranime pripadny query string
        if (($_link = strstr($link, '?', TRUE)) !== FALSE) {
            $link = $_link;
      //      $link = substr($link, 0, strpos($link, '?'));
        }
        
        // Pokud link obsahuje url::base, take ji odstranime
        $base = url::base();
        if (substr($link, 0, strlen($base)) == $base) $link = substr($link, strlen($base));
        
        // + array(null, null) zajisti ze prava strana vzdy bude pole s alespon dvema prvky
        list($controller, $action) = explode('/', $link) + array(NULL, NULL);
        return ($controller == $this->subnav_controller and $action == $this->subnav_action);
    }
    
    
    /**
     * Overeni, zda ma uzivatel pristup na zadanou url
     * - predpoklada url ve tvaru controller/action/*
     * - v pripade potreby by slo metodu upravit tak, ze by se pozadovane opravneni 
     *   mohlo definovat v configu nezavisle na cilove url polozky.
     * @param type $link 
     * @param bool $main_menu - zda overujeme odkaz hlavniho menu (pouze na zaklade shody controlleru) 
     */
    protected function hasAccess($item, $main_menu=false)
    {
        $link = arr::get($item, 'link', '');     
        if (empty($link) or arr::get($item, 'no_access_control', false))
        {
            return true; // odkaz asi neni definovan - poloka se muze zobrazit
        }
        // Odstranime pripadny query string
        if (strpos($link, '?')) {
            $link = substr($link, 0, strpos($link, '?'));
        }
        // Odstranime pripadne url::base na zacatku testovane url
        $link = preg_replace('#^'.preg_quote(url::base()).'#', '', $link);

        // Rozdeleni testovane url na controller a action
        list($controller, $action) = explode('/', $link) + array(NULL, NULL);

        return Auth::instance()->get_user()->HasPermission($controller, $action);
    }
    
    
    /**
     * Zpocita klic do cache pod kterym se uklada menu
     *  - na zaklade aktivniho controlleru, akce a (?) config_file
     */
    protected function countCacheKey()
    {
        return md5($this->controller.$this->action.Auth::instance()->get_user()->pk());
    }
    
  
// =========================================================================================
// Metody pro obaleni jednotlivych elementu - jsou samostatne aby se daly pretizit
// V podstate nahrazuje pristup s pouzitim views

    /** 
     * Vraci kompletni html menu. Mohlo by to cist primo z $this->main_menu a $this->navigation,
     * ale pro pretezovani bude prehlednejsi, kdyz se bude pracovat jen s parametry. 
     * @param string $main_menu - kompletni obsah hlavniho menu - 1. uroven
     * @param string $subnavigation - kompletni obsah subnavigation - obaleny svou wrap metodou
     */
    
    protected function wrapMenu($main_menu)
    {
        return '<div id="page-leftbar">
                    <div>
                        <ul id="sidebar">
                            '.$main_menu.'
                        </ul>
                    </div>
                </div><!-- page-leftbar -->';
    }
    
    
    // Obaleni submenu - kontextove nabidky
    protected function wrapSubmenu($submenu)
    {
        // Tim ze umoznime volat wrap s prazdnym submenu zjednodusime kod v neprehlednych castech tridy
        if (empty($submenu)) return '';
        return '<ul>
                    '.$submenu.'
                </ul>';
    }
    
    
    // Obaleni subnavigation - zobrazene podnabidky
    protected function wrapSubNavigation($subnavigation)
    {
        if (empty($subnavigation)) return '';
        return '
        <ul>
            '.$subnavigation.'
        </ul>
                ';
    }
    
    
    // Obaleni casti pro vytvoreni nove polozky - v subnavigaci
    protected function wrapSubNavigationNew($content)
    {
         // Ten pristup do jazykoveho soubrou nemusi vzdy fungovavt (!)
         return $content;
    }
 
// Konec wraperu ==========================================================================
    

    /**
     * Vytvoří obsah preddefinovane položky menu (pouze v hlavni urovni)
     * - musi byt bez obalovacitho <li> aby za odkaz slo vlozit pripadne submenu
     * - a bez obalovaciho <a> - href musime definovat zvlast aby slo kontrolovat aktivitu a opravneni
     * @param int $type - nektera z definovanych konstant
     *
     * @param array $active_submenu_item - Aktivni polozka v podmenu dane polozky.
     * Jeji label se muze propsat do "ouska" polozky prvni urovne.
     * Napr. Nastaveni je polozka hlavni urovne, Uzivatele je aktivni podpolozka Nastaveni.
     * Bude vygenerovan label "Nastaveni - Uzivatele".
     *
     * @return obsah polozky
     */
    protected function createItemByType($item, $active_submenu_item = NULL)
    {
        $label = isset($item['label']) ? $item['label'] : '';
        switch ($label)
        {
            case Menu::ITEM_HR: return '<hr />';

        }
        return $label;
    }
    
    /**
     * Vrati aktivni polozku ze zadaneho submenu - pokud existuje
     *  - porovnava pouze na zaklade shody controlleru
     * jinak vraci NULL
     * @param type $item
     * @return type 
     */
    protected function getActiveSubmenuItem($item) 
    {
        if ( ! is_array($item)) return NULL;
        $submenu = arr::get($item, 'submenu', NULL);
        if ( ! is_array($submenu)) return NULL;
        foreach ($submenu as $i) {
            if ($this->subNavigationActive(arr::get($i, 'link', NULL))) {
                return $i;
            }
            $subnav = arr::get($i, 'subnavigation', Array());
            $sections = arr::get($subnav, 'sections', Array());
            foreach ($sections as $s) {
                if ($this->menuActive(arr::get($s, 'link', NULL))) {
                    return $i;
                }
            }
            $sections = arr::get($subnav, 'new', Array());
            foreach ($sections as $s) {
                if ($this->menuActive(arr::get($s, 'link', NULL))) {
                    return $i;
                }
            }

            if (isset($i['submenu']) and $this->getActiveSubmenuItem($i) !== NULL) {
                return $i;
            }
        }
        return NULL;
    }

    
    /**
     * Vrati kompletni menu jako jeden string
     *  - Nacte z cache nebo necha vygenerovat a ulozi do cache
     *  - Jedina metoda volana z vnejsku 
     */
    public function getMenu($config_name = NULL)
    {
        if (empty($config_name))
        {
            $config_name = $this->config_name;
        }

        // Nacteme config
        $config_name = Kohana::config($config_name, Array());

        $config = array();
        foreach ($config_name as $key => $val) {
            $config[$key] = $val;
        }
        $this->config = $config;

        // Vygenerujeme
        $menu = $this->parseMenu();

        // Vratime menu
        return $menu;
    }
    
    
    /**
     * Vraci kompletni menu, generovani je rozdeleno pro levou a pravou cast
     * - volane metody pouze zapisi menu a subnavigation do $this->main_menu a
     *   $this->subnavigation, wrapMenu() je zde precte a obali dalsimi HTML elementy.
     */
    protected function parseMenu() 
    {
        // Vyprazdnime menu 
        $this->main_menu = $this->subnavigation = '';

        // Leva cast menu
        $left_menu = arr::get($this->config, 'items_left', Array());
        $this->createMenu($left_menu);

        // Predani argumentu neni nutne - zustavame v kontextu objektu
        // ale myslim se pro pretezovani to bude takhle jasnejsi
        return $this->wrapMenu($this->main_menu);
    }
    
    /**
     * Vytvori 1. uroven hlavniho menu
     */
    protected function createMenu($items, $classes=array()) 
    {
        // Projdeme polozky menu a kazdouu rozparsujeme
        foreach ((array)$items as $item) {
            // Vytvorime polozku, pripadna submenu a subnavigation
            $this->createMenuItem($item, $classes);
        }
    }
    
    
    /**
     * Vygeneruje vlastni polozku menu 1. urovne - tedy html LI element
     * @param type $item - polozka menu, muze obsahovat definici submenu nebo subnavigation
     * @param type $class - css trida, kterou polozka (LI element) dostane navic ke tridam z jeji definice
     */
    protected function createMenuItem($menu_item, $classes=array())
    {
        // Kontrola opravneni
        if ( ! $this->hasAccess($menu_item, true)) {
            return;
        };

        // Label muze obsahovat konstantu preddefinovaneho typu
        // Pokud neni preddefinovanou konstantou, pak metoda vrati co dostala jako argument
        $label = $this->createItemByType($menu_item);

        $classes = array();
        if (isset($menu_item['submenu'])) {
            $classes = array('active_submenu', 'dropdown-toggle', 'drop');
        }
        $content = $this->createLink(arr::get($menu_item, 'link', '#'), $label, $classes, arr::get($menu_item, 'icon'));

        // Nejprve nacteme submenu, abychom vedeli jestli mame odkazu pridat specialni class
        $submenu = arr::get($menu_item, 'submenu', NULL);
        $is_active = false;
        $submenu = $this->createSubmenu($submenu, $is_active);

        // pokud je aktivni nejaka polozka submenu
        if ($is_active) {
            $classes[] = 'active';
            $classes[] = 'open';
        }
        
        // Pridame v configu definovane CSS tridy k moznym implicitnim (argument $classes) tridam polozky
        $classes = array_merge($classes, $this->getItemClasses($menu_item));

        // Pokud je polozka aktivni, pridame ji CSS class a zaroven vygenerujeme subnavigation
        if ($this->menuActive(arr::get($menu_item, 'link', NULL), arr::get($menu_item, 'check_action_to_activate', false)))
        {
            // Vygenerujeme subnavigaci
            $this->createSubNavigation(arr::get($menu_item, 'subnavigation', array()));
            // Nastavime položku hlavního menu jako aktivní - přidáním css třídy
            $classes[] = $this->active_class_name; 
        }

        //pokud ma menu polozka submenu, tak dostane speicalni css tridu
        if ( ! empty($submenu))
        {
            $classes[] = 'hasChild';
        }

        // At je submenu prazdne nebo ne, muzeme ho pridat za odkaz
        $wrapped_submenu = $this->wrapSubmenu($submenu);

        //pokud neni definovan 'link' u polozky prvni urovne a neni ani
        //definovana zadna podpolozka tak se polozka prvni urovne nebude
        //ani vykreslovat
        if ( ! isset($menu_item['link']) && empty($submenu))
        {
            return;
        }

        $content .= $wrapped_submenu;


        // Nyni mame kompletni obsah polozky - odkaz a mozna i submenu, vygenerujeme vlastni polozku - LI element
        $this->main_menu .= $this->createItem($content, $classes, arr::get($menu_item, 'id', '')) . $this->wrapSubnavigation($this->subnavigation);
        $this->subnavigation = '';
    }    
    
    
    /**
     * Na zaklade seznamu odkazu vygeneruje polozky submenu (kontextovou nabidku)
     * @param <array> submenu - pole s definici polozek menu
     * @return type 
     */
    protected function createSubmenu($submenu, &$is_active)
    {
        if (empty($submenu)) return '';
        $result = '';
        foreach ($submenu as $item) {
            // Kontrola opravneni
            if ( ! $this->hasAccess($item)) break;

            $current_item_active = $this->subNavigationActive(arr::get($item, 'link'));

            if ($this->getActiveSubmenuItem($item) !== NULL) {
                $current_item_active = true;
            }

            // Css classy aktualni polozky submenu
            $classes = $this->getItemClasses($item);

            // Prevedeme pripadnou konstantu na preddefinovany typ
            $label = $this->createItemByType($item);

            // Odkaz uvnitr polozky
            $content = isset($item['link']) ? $this->createLink($item['link'], $label) : $label;

            $submenu = NULL;
            if (isset($item['submenu']) and $current_item_active) {
                $submenu = $this->wrapSubmenu($this->createSubmenu($item['submenu'], $is_active));
            }


            if ($current_item_active) {
                $is_active = true;
                $classes[] = 'active';
            }

            $result .= $this->createItem(
                $content,
                $classes,
                arr::get($item, 'id', ''),
                $submenu
            );
        }

        // Vratime vygenerovane polozky submenu - obaleni zajisti volajici metoda
        return $result;
    }
    
    
    /**
     * Pro submenu se ma pouzit subnavigation, ktera se potencialne sklada z casti
     * 'sections' a 'new' - slepime tyto casti do jednoho pole a volame metodu pro vytvoreni submenu
     * @param array $subnavigation - definice subnavigace
     * @return string submenu
     */
    protected function createSubmenuFromSubnavigation($subnavigation) 
    {
        $submenu = arr::get($subnavigation, 'sections', Array());
        // Tady by se
        $submenu += arr::get($subnavigation, 'new', Array());
//        return $this->createSubmenu($submenu);
    }
    
    
    /**
     * Vygeneruje subnavigaci do $this->subnavigation, nic nevraci
     * @param array $items pole s definici subnavigace
     * @return void
     */
    protected function createSubNavigation($items) 
    {
        // Promenna pro generovani cele subnavigation
        $this->subnavigation = '';
        
        // Pokud ma subnavigace sekce, vygenerujeme je
        if (isset($items['sections'])) {
            foreach ((array)$items['sections'] as $item) {

                //pokud nema uzivatel pristup na danou podsekci, tak se v menu
                //nezobrazi
                if ( ! $this->hasAccess($item))
                {
                    continue;
                }

                // Pripadna konverze preddefinovaneho typu
                $label = $this->createItemByType($item);
                // Precteme css tridy z configu
                $classes = $this->getItemClasses($item);
                // Pokud je polozka aktivni, pridame tridu a obsahem nebude odkaz, ale jen text
                if ($this->subNavigationActive(arr::get($item, 'link', NULL))) {
                    $classes[] = $this->active_class_name;
                }
                $content = isset($item['link']) ? $this->createLink($item['link'], $label) : $label;
                $this->subnavigation .= $this->createItem($content, $classes, arr::get($item, 'id', ''));
            }
        }
        
        // Pripadne odkazy na vytvoreni novych polozek
        if (isset($items['new'])) {
            $subnavigation_new = '';
            // Projdeme polozky
            foreach ($items['new'] as $item) {
                if (isset($item['link'], $item['label'])) {

                    //pokud nema uzivatel pristup na danou podsekci, tak se v menu
                    //nezobrazi
                    if ( ! $this->hasAccess($item))
                    {
                        continue;
                    }

                    $label = $this->createItemByType($item);
                    
                    // vytvorime odkaz
                    $content = $this->createLink($item['link'], $label);
                    // Pokud existuje submenu, vlozime za nej
                    if (isset($item['submenu'])) {
                        // wrapSubmenu se nemuse volat primo v createSubmenu, kvuli rekurzi
//                        $content .= $this->wrapSubmenu($this->createSubmenu($item['submenu']));
                    }

                    $classes = $this->getItemClasses($item);
                    // Pokud je polozka aktivni, pridame tridu a obsahem nebude odkaz, ale jen text
                    if ($this->subNavigationActive(arr::get($item, 'link', NULL))) {
                        $classes[] = $this->active_class_name;
                    }
                    $subnavigation_new .= $this->createItem(
                                $content,
                                $classes,
                                arr::get($item, 'id', '')
                            );
                }
            }

            //pokud do sekce "Pridat nove..." nebyla pridana ani jedna polozka
            //tak nebude vubec vlozena do stranky
            if ( ! empty($subnavigation_new))
            {
                $this->subnavigation .= $this->wrapSubNavigationNew($subnavigation_new);
            }
        }
    }
    
    
    
    
    
    /**
     * Nacteni pohledu polozky menu a predani promennych
     * @param type $content obsah polozky (obsah mezi tagy <li> a </li>)
     * @param type $classes pole CSS trid 
     * @param type $id ID polozky
     */
    protected function createItem($content, $classes=Array(), $id='', $submenu='')
    {
        // Konverze pole trid na retezec
        if (is_array($classes)) $classes = implode(' ', $classes);
        
        // Vytvorime retezec s atributy
        $attr = (empty($classes)) ? '' : ' class="'.$classes.'"';
        $attr = (empty($id)) ? $attr : $attr.' id="'.$id.'"';

        // Vratime polozku seznamu
        return '<li'.$attr.'>'.$content . ' ' . $submenu.'</li>';
    }
    
    
    /**
     * Vytvoří odkaz
     * @param type $href atribut href
     * @param type $content obsah uvnitr <a>ZDE</a>
     * @param type $classes pole css trid
     * @return string html odkaz
     */
    protected function createLink($href, $content, $classes=Array(), $icon_class=NULL)
    {
        $attr = '';
        // Pokud jsou tridy predane jako pole, prevedeme na retezec
        if (is_array($classes)) {
            if (in_array('drop', $classes)) {
                $attr .= ' data-toggle="dropdown"';
            }
            $classes = implode(' ', $classes);
            $attr .= ' class="'.$classes.'"';
        }

        // Main menu icon
        $icon = empty($icon_class) ? '' : '<i class="'.$icon_class.'"></i>';

        // Vratime odkaz
        return '<a href="'.$href.'"'.$attr.'>'. $icon . ' ' . $content.'</a>';
    }
    
    
    /**
     * Vraci seznam css trid dane polozky - podobe pole
     * - v configu muze byt vlastnost 'css' definovana jako pole nebo jako string
     * @param array $item
     */
    protected function getItemClasses($item)
    {
        // Precteme definici
        $classes = arr::get($item, 'css', array());
        // Pokud se precetl strin, prevedeme na pole
        if (is_string($classes)) $classes = explode(' ', $classes);

        // Vratime vysledne pole css trid
        return $classes;
    }
    
    
    // Singleton
    public static function instance()
    {
        static $instance;
        empty($instance) and $instance = new Menu();
        return $instance;
    }


    /**
     * Manually sets the controller name to change active main menu item
     * @param $ctrl
     * @return $this
     */
    public function setActiveMenu($controller)
    {
        $this->nav_controller = $controller;
        return $this;
    }

    /**
     * Manually sets the action name to change active menu item
     * @param $action
     * @return $this
     */
    public function setActiveMenuItem($controller, $action)
    {
        $this->subnav_action = $action;
        $this->subnav_controller = $controller;
        return $this;
    }

    protected function __construct()
    {        
        // Nacteni aktualniho controlleru a akce z Request tridy
        $this->controller = $this->nav_controller = $this->subnav_controller = Request::instance()->controller;
        $this->action = $this->subnav_action = Request::instance()->action;
    }
    
    private function __clone() {}    
}


?>
