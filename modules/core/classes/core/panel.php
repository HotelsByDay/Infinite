<?php


class Core_Panel {

    //nazev kontroleru pro ktery se panel s akcemi generuje
    //podle tohoto nazvu se vystup cachuje
    protected $controller_name;

    // Vlastni config
    protected $config = Array();
    
    
    /**
     * Obaleni celeho vysledneho panelu
     * @param type $panel
     * @return type 
     */
    protected function wrapPanel($panel) 
    {
        return '<ul class="filter-nav">'.$panel.'</ul>';
    }
    
    /**
     * Obali cele "submenu"
     * @param type $submenu 
     */
    protected function wrapSubmenu($submenu)
    {
        return '<div class="submenu"><ul>'.$submenu.'</ul></submenu>';
    }
    
    
    /**
     * Obaleni jednotlivych tlacitek (jak hlavni urovne tak pripadneho "submenu" u group polozky)
     * @param type $button - tlacitko, aktualne html odkaz
     * @param class - pripadna css trida
     */
    protected function wrapButton($button)
    {
        return '<li>'.$button.'</li>';
    }
   
    
    
    /**
     * Jedina public metoda - vrati vysledne html panelu
     */
    public function getPanel()
    {
        $key = $this->countCacheKey();
        // Zkusime precist z cache
        $panel = Cache::instance()->get($key, NULL);

        // Pokud v cache nebyl vygenerujeme a zacachujeme
        if ($panel == NULL) {
            $panel = $this->parsePanel();
            Cache::instance()->set($key, $panel);
        }
        
        // Vratime panel
        return $panel;
    }
    
    
    protected function parsePanel()
    {
        $panel = '';
        // Projdeme polozky panelu
        foreach ($this->config as $key => $item)
        {
            //pokud je polozka prazdna tak preskakuji - toto se vyuziva, napr.
            //pri zruseni jedne z defaultnich akci (napr. delete)
            if (empty($item) || arr::get($item, 'hidden') == TRUE)
            {
                continue;
            }
            
            // Kazdou zpracujeme zvlast
            $panel .= $this->parseItem($key, $item);
        }
        return $this->wrapPanel($panel);
    }
    
    /**
     * Spocita klic do cache pod kterym se uklada aktualni panel
     * @return string
     */
    protected function countCacheKey()
    {
        return md5($this->controller_name);
    }
    
    /**
     * Zpracovani jedne poloky panelu (na hlavni urovni)
     * @param type $action
     * @param type $item 
     */
    protected function parseItem($action, $item)
    {
        // Jedna se o group polozku?
        if (isset($item['items'])) {
            // Z polozek ziskame cele (obalene) submenu
            $submenu = $this->parseSubmenu($item['items']);
            
            // Nastavime odkazu specialni tridu a nenastavime ji atribut action
            // - odkaz slouzi pouze pro zobrazeni akcnich tlacitek, sam akcni nebude
            $link_attr = ' class="drop"';
        } else {
            $submenu = ''; // at nemusime testovat isset
            $link_attr = ' class="action_button" action="'.$action.'"';
        }

        //pokud je definovany atribut 'confirm', tak pridam do atributu prvku
        if (isset($item['confirm']))
        {
            $link_attr .= ' confirm="'.htmlentities($item['confirm']).'" ';
        }
        
        // Tady se primo generuje odkaz, nevim jestli to ma cenu davat do specialni wrap metody
        // Pokud submenu neexistuje obsahuje prazdny retezec
        $link = '<a href="#" '.$link_attr.'>'.arr::get($item, 'label', '-').'</a>'.$submenu;
        return $this->wrapButton($link);
    }
    
    
    /**
     * Pouze projde items a posklada je za sebe do retezce
     * Vola parseItem, takze muze vzniknout nekonecna neprima rekurze, ale
     * predpoklada se ze polozky v 'items' uz dalsi 'items' obsahovat nebudou
     * @param type $items 
     */
    protected function parseSubmenu($items)
    {
        $submenu = '';
        // Projdeme polozky skupiny (group)
        foreach ($items as $action => $item) {
            // Kazdou parsujeme
            $submenu .= $this->parseItem($action, $item);
        }
        // Submenu obalime 
        $submenu = $this->wrapSubmenu($submenu);
        // A vratime
        return $submenu;
    }    
    
    
    public static function factory($controller)
    {
        return new Panel($controller);
    }
    
    public function __construct($controller)
    {
        //pripravim si konfiguraci pro hromadne akce nad zaznamy
        //defaultni systemove akce (jako odstranit zaznam, apod) jsou definovany
        //v objects.actions - v konfiguraci specificke pro dny kontroler je mozne
        //ji prebit a pridat dalsi
        $this->config = arr::merge(kohana::config('object.actions'), (array)kohana::config($controller.'.actions'));

        //vygenerovany HTML kod budu cachovat pod klicem, ktery odpovida nazvu
        //kontoleru
        $this->controller_name = $controller;
    }
    
}

?>
