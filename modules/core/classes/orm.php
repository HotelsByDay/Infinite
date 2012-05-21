<?php defined('SYSPATH') or die('No direct script access.');
/**
 * V teto bazove tride pro ORM nastavuji defaultni hodnoty modelu tak aby
 * vyhovovaly pouzitem DB schematu.
 */
class ORM extends Kohana_ORM {

    /**
     * Suffix cizich klicu v tabulkach je 'id'.
     * @var <string>
     */
    protected $_foreign_key_suffix = 'id';

    /**
     * Nazvy tabulek mame v jednotnem cisle.
     * @var <string>
     */
    protected $_table_names_plural = FALSE;

    /**
     * Atribut slouzi k nastaveni zda pri mazani zaznamu ma dojit k jeho odstraneni
     * z DB (DELETE) anebo ma dojit pouze k aktualizaci atributu 'deleted' na
     * hodnotu '1'.
     *
     * Pokud ma hodnotu TRUE tak dochazi k UPDATE (deleted => 1) a pokud ma hodnotu
     * FALSE tak dochazi k odstraneni zaznamu z DB.
     *
     * @var <bool>
     */
    protected $update_on_delete = FALSE;
        
    /**
     * Originalni data se plni postupne pri volani __set()
     * pokud se MENI hodnota atributu a atribut se ma logovat.
     * Lepsi reseni me nenapadlo - viz #61#15 (JME-podle me to je perfektni reseni)
     * @var <array> klicem nazev sloupce, hodnotou je originalni hodnota
     */
    protected $log_original_data = Array();
    
    /**
     * V jazykovem souboru se pod timto klicem nachazi format nahledu
     * pro dany objekt. Viz metoda preview v bazovem ORM.       
     * @var <string> klic do jazykoveho souboru
     */
    protected $_preview = '';
    
    /**
     * Definice ciselniku daneho modelu 
     * - seznam modelu s relacnimi ciselniky
     * Pro ziskani kompletniho ciselniku slouzi metoda get_rel_cb(ciselnik, filter_params).
     * 
     * Uzivatelska hodnota je dostupna pres virtualni atribut (codebook s prefixem '_').
     * 
     * V konstruktoru modelu jsou na zaklade tohoto pole generovany vazby na
     * relacni modely. Jako foreign_key se pouziva hodnota codebook se suffixem id.
     * @var <array> seznam ciselniku daneho modelu
     */
    protected $_rel_cb = Array();

    /**
     * Tato promenna se pouziva pro detekci zda aktualne dochazi k volani _wakeup
     * metody. Dulezite to je v tomto scenari:
     *
     * 1. kontroler v before() metode kontroluje Auth::instance()->is_logged()
     * 2. Auth modul vytahuje objekt uzivatele ze Session
     * 3. Je volana _wakeup metoda - dochazi k volani ORM->reload().
     * 4. ORM->reload() vola find() a dochazi ke kontrole opravneni uzivatele
     *      cist z z tabulky modelu user. Tzn. je volana metoda _applyUserSelectPermission
     * 5. V metode _applyUserSelectPermission je standardne volana metoda pro
     *      ziskani uzivatelskych roli uzivatele. A tady nastava problem, protoze
     *      objekt uzivatele jeste neni nacten ze session, takze Auth modul
     *      se ho pokusi vytahnout a dostavame se do smycky.
     *
     * RESENI: Na zacatku volani _wakeup metody je atribut $_waking_up nastaven
     * na TRUE a na jejim konci je nastaven na FALSE. V pripade ze je tento atribut
     * nastaven na TRUE tak metoda _applyUserSelectPermission nic nedela. Jinak
     * standardne vola applyUserSelectModificator, pokud je nejaky definovan.
     *
     */
    protected $_waking_up = FALSE;

    /**
     * Tento atribut slouzi k definici nazvu objektu od ktereho ma byt dedeno
     * opravneni.
     * Vysvetleni na prikladu: Mame napr. objekt "advert" (nabidky) a "advertphoto"
     * (fotky k nabidkam). Uzivatel ma urcite opravneni na objekt "advert" a stejne
     * opravneni ma mit i na objekt "advertphoto" a toho docilime prave natavenim
     * tohoto atributu v ORM Model_AdvertPhoto na hodnotu "advert".
     * Jinak by bylo potreba v konfiguraci opravneni definovat explicitne opravneni
     * na objekt "advertphoto", ktere by bylo stejne jako opravneni pro objekt
     * "advert".
     */
    protected $_inherit_permission = '';


    protected $_save_performed = FALSE;

    
    public static function factory($model, $id = NULL)
    {
        if (is_numeric($model))
        {
            $model_name = lognumber::getTableName($model);

            //pokud je prazdny model tak to urcite vyvola fatal error - pokus
            //o nacteni modelu s prazdnym nazvem, ktery
            //neni tak dobre zachytitelny jako vyjimka, takze tady vyhodim
            //vyjimku abych si usetril starosti
            if (empty($model) || empty($model_name))
            {
                throw new Kohana_Exception('Undefined reltype value ":reltype" caught in factory method.', array(
                    ':reltype' => $model,
                ));
            }

            //muzeme dale pokracovat
            $model = $model_name;
        }

        return parent::factory($model, $id);
    }

    /**
     * @param type $key nastavovany klic
     * @param type $value nastavovana hodnota klice
     */
    public function __set($column, $value) 
    {
        /**
         * Log action - pokud __set zpusobi ZMENU hodnoty databazoveho atributu
         * A jedna se o hodnotu, ktera se ma logovat, pak se ulozi puvodni hodnota
         * do $this->log_original_data
         * parent::__set() se vola vzdy
         */
        // Pristupuje se k DB atributu? (zkopirovano z base ORM)
        if (array_key_exists($column, $this->_object)) {
            if (isset($this->_table_columns[$column])) {

                // Ulozime puvodni hodnotu nactenou z DB, pokud dochazi k prvni zmene
                if ( ! isset($this->log_original_data[$column])
                    and lognumber::getColNumber($this->_object_name, $column) // aktualne ukladame puvodni hodnoty vsech sloupcu
                    // tohle muze zpusobit ( ! ZADOUCI ! ) nacteni zaznamu z DB, proto je to v podmince az nakonci
                    and $value != $this->{$column}
                ) {
                    $this->log_original_data[$column] = $this->_object[$column];
                }
            }
        }
        // Meni se hodnota ciziho klice skrz _belongs_to relaci? (zkopirovano z base ORM)
        elseif (isset($this->_belongs_to[$column])) {
            // Jen pro zkraceni zapisu ulozime nazev FK
            $fk = $this->_belongs_to[$column]['foreign_key'];
            /**
             * Dojde ke zmene aktualni hodnoty FK aniz bychom meli ulozenu puvodni hodnotu
             * sloupce, ktery se ma logovat?
             */
            if ( ! isset($this->log_original_data[$column]) 
                 and $this->_object[$fk] != $value->pk()
            //     and LogAction::logCol($this->_table_name, $fk) // aktualne ukladame vsechny zmeny
            ) {
                // Aktualni hodnota se zmeni - ulozime puvodni
                $this->log_original_data[$column] = $this->_object[$fk];
            }
        }
        
        /**
         * Pokud klic odpovida nazvu databazoveho sloupce a nastavuje se stejna
         * hodnota, ktera uz je ulozena, pak toto nastaveni chceme ignorovat
         * aby se pro sloupec neukladal logovaci zaznam 
         */
        /// Tohle nepropousti zapisy, ktere nemeni hodnotu atributu
        // Nastavuje se DB sloupec? 
        if (isset($this->_table_columns[$column])) {
            // Pokud je puvodni hodnota shodna s nastavovanou
            if ($this->{$column} == $value) {
                return; // Neni treba nastavovat ji znova
            }
        }
        // Nebo cizi klic pres belongs_to vazbu?
        elseif (isset($this->_belongs_to[$column])) {
            // Meni se cizi klic?
            if ($this->{$this->_belongs_to[$column]['foreign_key']} == $value->pk()) {
                // FK se sice nezmenil, ale value se zmenit mohla
                $this->_related[$column] = $value;
                return; // Nechceme aby se zmenilo _changed
            }
        }

        // Pokud jsme se dostali az sem, ma se provest standardni nastaveni
        // Nyni se originalni set zavola vzdy
        parent::__set($column, $value);
    }

    /**
     * Metoda slouzi k testovani zda objekt ma dany atribut.
     * Kontroluje i relacni atributy.
     * @param <string> $column
     * @return <bool>
     */
    public function hasAttr($column)
    {
        $this->_load();

        return
        (
            array_key_exists($column, $this->_object) OR $this->hasRelAttr($column)
        );
    }

    /**
     * Metoda slouzi k testovani zda objekt ma dany atribut.
     * Kontroluje pouze RELACNI atributy.
     * @param <string> $column
     * @return <bool>
     */
    public function hasRelAttr($column)
    {
        $this->_load();

        return
        (
            array_key_exists($column, $this->_related) OR
            array_key_exists($column, $this->_has_one) OR
            array_key_exists($column, $this->_belongs_to) OR
            array_key_exists($column, $this->_has_many)
        );
    }
    
    public function obj()
    {
        return $this->_object;
    }
    /**
     * Vraci puvodni hodnoty sloupcu u kterych došlo ke změně
     * @return <array>
     */
    public function getLogOriginalData()
    {
        return $this->log_original_data;
    }

    /**
     * Implementace virtualnich atributu pro ziskani uzivatelskych hodnot
     * ciselniku.
     * @param <string> $column
     * @return <mixed>
     */
    public function __get($column) {
        // Pokud chceme uzivatelskou hodnotu ciselniku - vratime ji skrz _has_one vazbu
        if (in_array(substr($column,1), $this->_rel_cb)) {
            // Skrz vygenerovanou has_one vazbu
            // return parent::__get(substr($column,1))->value;
            // Skrz codebook class - s podporou cachovani
            // Je to trochu krkolomny zapis, ale nevim jak to udelat lepe
            return Codebook::value(substr($column,1), $this->{substr($column,1).'id'});
        }

        switch ($column)
        {
            //pokud jsou definovany atributy relid a reltype, tak nacte ORM
            //prislusneho relacniho zaznamu a vrati referenci
            case '_rel':
                if ($this->hasAttr('relid') && $this->hasAttr('reltype'))
                {
                    if ( ! empty($this->reltype) && ! empty($this->relid))
                    {
                        try
                        {
                             return ORM::factory($this->reltype, $this->relid);
                        }
                        catch (Exception $e)
                        {
                            kohana::$log->add(Kohana::ERROR, 'Unable to load _rel attribute model on model ":model" [:id], reltype: ":reltype" and relid: ":relid".', array(
                                ':id'       => $this->pk(),
                                ':model'    => $this->_table_name,
                                ':reltype'  => $this->reltype,
                                ':relid'    => $this->relid
                            ));
                        }
                    }
                }

                return FALSE;

            default:
                return parent::__get($column);
        }
    }
    
    
    
    /**
     * Pridani funkcionality pri ukladani zaznamu 
     *  - Generovani cisel ze serie, pokud je to v modelu aktivovano
     *  - Logovani zmen, pokud je v modelu aktivovano
     * --- mozna by bylo vhodne jednotlive pridane akce provadet v samostatnych
     * metodach a ty odtud pouze volat...?
     * @uses Serie
     * @uses LogAction model
     * @throws SerieException V pripade ze nebylo mozne ziskat nove cislo
     * pozadovane ciselne rady.
     */
    public function save() 
    {   
        //metoda nastavuje defaultni hodnoty nekterych systemovych sloupecku
        //jako napriklad 'userid' nebo 'created', ktere byvaji ve vsech standardnich
        //tabulkach pro ulozeni uzivatelskych dat (napr. v ciselnikovych tabulkach nejsou)
        if ( ! $this->loaded())
        {
            //kontrola zda ma uzivatel opravneni 'delete' na tento objekt a popripade
            //zajisti aplikaci modifikatoru
            if ( ! $this->applyUserInsertPermission())
            {
                throw new Exception_UnauthorisedAction('Unauthorised db action "insert" on object "'.$this->_object_name.'".');
            }
        }
        else
        {
            //kontrola zda ma uzivatel opravneni 'delete' na tento objekt a popripade
            //zajisti aplikaci modifikatoru
            if ( ! $this->applyUserUpdatePermission())
            {
                throw new Exception_UnauthorisedAction('Unauthorised db action "update" on object "'.$this->_object_name.'".');
            }
        }

        //pokud nedoslo na modelu k zadnym zmenam, tak metoda save konci
        //(podminka prevzata z Kohana_ORM tridy)
        if (empty($this->_changed))
        {
            return $this;
        }

        // Rika zda doslo k volani parent::save()
        // nebo zda nemu ma dojit na konci metody
        $this->_save_performed = FALSE;
        
        // Logovani zmen - historie
        // Pokud dochazi k editaci a zmenily se nejake hodnoty
        // (druha cast podminky z parent ORM::save metody)
        if ( ! $this->empty_pk() AND ! isset($this->_changed[$this->_primary_key]))
        {
            // Ulozeni vlastnich zmen a poznamenani ze se ulozilo
            parent::save();
            $this->_save_performed = TRUE;
            // Pokud se zmeny ulozily, vytvorime zaznamy v LogAction
            if ($this->_saved)
            {
                // _object_name se zde predava umyslne - viz config/logaction.php
                LogAction::instance()->runEvent($this, 'update');
            }
        }
        // Pridavani - insert
        else
        {
            // Pokud se ma generovat cislo v ramci serie
            // Pokud se ukladala historie, tak probihal update a serie se 
            // generuje jen pri INSERT, proto jsem pro prehlednost zmenil if za elseif
            // ikdyz to na funkci nema vliv
            if (($serie_config = kohana::config('_serie.objects.'.$this->_object_name)) != NULL)//$this->serie_column !== FALSE)
            {
                // A zaznam se prave vytvari (probehne INSERT)
                if ($this->empty_pk() OR isset($this->_changed[$this->_primary_key]) )
                {

                    //konfigurace serie muze obsahovat vice sloupcu, ale pro zatim
                    //se bude pracovat pouze s jednim - a to s poslednim v konfiguraci
                    foreach ($serie_config as $attr => $type)
                    {
                        $serie_type   = $type;
                        $serie_column = $attr;
                    }

                    // Vytvorime objekt pro pristup k dane serii
                    $serie = new Serie($serie_type);
                    //metoda muze vyhodit vyjimku, pokud se ji nepodari zamknout
                    //pozadovanou ciselnou radu
                    $this->{$serie_column} = $this->formatSerie($serie->getNextValue());
                    // Tady take muze dojit k chybe, pri ktere je vzdy nutne zavolat unlock();
                    try
                    {
                        parent::save();
                        $this->_save_performed = TRUE;
                    } 
                    catch (Exception $e)
                    {
                        $serie->unlock();
                        throw $e;
                    }
                    // Pokud se zaznam ulozil, dojde k posunuti ciselne rady
                    if ($this->saved())
                    {
                        $serie->generateNextValue();
                    }
                    // Vzdy nakonec odemkneme serii
                    $serie->unlock();
                }
            }
            else
            {
                // Ulozeni
                parent::save();
                $this->_save_performed = TRUE;
            }
            
            // Zalogovani akce
            LogAction::instance()->runEvent($this, 'insert');
        }

        // parent::save() pouze meni stav objektu a vzdy vraci $this
        if ( ! $this->_save_performed) parent::save();
        return $this;
    }

    public function savePerformed()
    {
        return $this->_save_performed;
    }
    
    /**
     * Tato metoda obnovuje minulou ulozenou hodnotu daneho atributu.
     *
     * Minulou hodnotu hleda v systemove historii (tabulky log_action a log_action_detail).
     * Pokud ji tam nenajde (z duvodu ze nedochazi k logovani historie daneho atributu)
     * tak vraci FALSE. Pokud byla hodnota atributu uspesne zmenena tak vraci TRUE.
     *
     * @param <string> $attr Nazev atributu (db sloupce) jehoz hodnota ma byt
     * zmenena.
     *
     * @return <bool> Vraci TRUE pokud byla minula hodnota atributu uspesne nalezena
     * v systemove historii a jeho hodnota nastavena na tuto "minulou" hodnotu.
     * V opacnem pripade vraci FALSE.
     *
     */
    public function revertAttrValue($attr)
    {
        //pro dany atribut se pokusim najit jeho predchozi hodnotu
        //v systemove historii

        $colnumber   = lognumber::getColNumber($this->object_name(), $attr);
        $tablenumber = lognumber::getTableNumber($this->object_name());

        //timto dotazem si vytahnu minulou hodnotu daneho atributu pro tento zaznam
        $query = DB::select('log_action_detail.old_value')
                        ->from('log_action')
                        ->join('log_action_detail')
                        ->on('log_action.log_actionid', '=', 'log_action_detail.log_actionid')
                        ->where('log_action.relAid', '=', $this->pk())
                        ->where('log_action.relAtype', '=', $tablenumber)
                        ->where('log_action_detail.attr', '=', $colnumber)
                        ->order_by('log_action.log_actionid', 'DESC')
                        ->limit(1);

        $results = $query->execute();

        //pokud v historii neni zaznam i minule hodnote daneho atributu, tak
        //vracim neuspech
        if ( ! isset($results[0]))
        {
            return FALSE;
        }

        //jinak zmenim hodnotu prislusneho atributu
        $this->{$attr} = $results[0]['old_value'];

        //vracim uspech
        return TRUE;
    }
    
    /** 
     * Formatuje cislo z ciselne rady
     *  - zde tato metoda slouzi predevdim k tomu, aby ji nemuseli 
     *    implementovat vsichni potomci pouzivajici pocitadla Serie
     * @param <string> castecne formatovane cislo z ciselne rady
     * @return <string> dalsi "cislo" z ciselne rady
     * @author Jiri Dajc
     */
    protected function formatSerie($partially_formatted) 
    {
        // Zde se vraci jiz kompletne zformatovana hodnota
        return $partially_formatted;
    }

   
    /**
     * V konstruktoru nastavim nazev primarniho klice -> nazev objektu a suffix 'id'.
     * Dale se vytvori has_one vztahy pro ciselniky definovane v $this->_rel_cb seznamu.
     * @param <type> $id
     */
    public function __construct($id = NULL)
    {
        // Nastaveni nazvu primarniho klice - pouzivame "neKohani" konvenci
        $this->_primary_key = ! empty($this->_table_name)
                                ? $this->_table_name.'id'
                                : strtolower(substr(get_class($this), 6)).'id';
        
        
        // Automaticka definice belongs_to vztahu pro ciselniky daneho modelu
        foreach ($this->_rel_cb as $cb) {
            $fk = NULL;
            // Pokud je hodnota string, dopocteme FK
            if (is_string($cb)) {
                $model = $cb;
                $fk = $cb.'id';
            } 
            // Pokud definice byla validni
            if ($fk != NULL) {
                // Vazbu MUSI identifikovat puvodni klic (bez prefixu '_')
                // Protoze muze byt vice vazeb na stejny model
                $this->_belongs_to[$model] = Array('model'=>$model, 'foreign_key'=>$fk);
            }
        }

        parent::__construct($id);

        //pokud se vytvari novy model, tak dojde k nastaveni defaultnich hodnot
        //@TODO - je toto potreba kdyz je nastaveni defaultnicho hodnot implementovano
        //v metode clear ?
        if ( ! $this->loaded())
	{
            foreach ($this->getDefaults($this->getDefaultsModificators()) as $column => $value)
            {
                $this->_changed[] = $column;
                $this->_object[$column] = $value;
            }
        }
    }

    /**
     * Provadi nastaveni defaultnich hodnot modelu, ktere vraci metoda
     * $this->getDefaults().
     * 
     * @chainable
     * @return ORM
     */
    public function clear()
    {
        parent::clear();

        foreach ($this->getDefaults($this->getDefaultsModificators()) as $column => $value)
        {
            $this->_changed[] = $column;
            $this->_object[$column] = $value;
        }

        return $this;
    }

    protected function getDefaultsModificators()
    {
        $defaults_modificator = Auth::instance()->logged_in()
                                    ? Auth::instance()->get_user()->HasPermission($this->permissionObjectName(), 'db_insert')
                                    : NULL;

        //vicenasobne mezery zrusim
        $defaults_modificator = preg_replace('/[ ]+/', ' ', $defaults_modificator);

        //mezery na konci a zacatku
        $defaults_modificator = trim($defaults_modificator);

        return explode(' ', $defaults_modificator);
    }

    /**
     * Vraci asoc. pole kde klicem je hodnota atributu $key a hodnotou je hodnota
     * atributu $value.
     * Metoda je urcena jako alternativa k find_all, takze vsechny nastavene
     * SQL podminky jsou aplikovany pri cteni dat z DB.
     *
     * Metoda nekontroluje zda $key a $value jsou skutecne platne nazvy atributu
     * daneho ORM modelu.
     *
     * Pokud je metoda zavolana pouze s jednim parametrem, je povazovan za $value
     * a jako $key se dosadi primarni klic tabulky.
     * 
     * @param <string> $key
     * @param <string> $value
     * @return <array>
     */
    /*
    public function get_cb($key, $value=NULL)
    {
        // Zajisti ze PK dane tabulky bude defaultnim klicem
        if ($value == NULL) {
            $value = $key;
            $key = $this->_primary_key;
        }
        //Mozna optimalizace (#143)
        //$this->select($this->_table_name.'.'.$key, $this->_table_name.'.'.$value);

        //vyvolam spusteni SELECT dotazu
        $results = $this->find_all();
        //vytvorim asoc. pole v pozadovanem tvaru
        $codebook = array();
        foreach ($results as $result) {
            $codebook[$result->{$key}] = $result->{$value};
        }
        return $codebook;
    } */
    
    
    /** 
     * Metoda pro ziskani hodnot relacniho ciselniku. Je volana 
     * v kontextu modelu, ke kteremu ciselnik patri. To umoznuje sestavit filtr
     * pro ziskani ciselniku na zaklade atributu "nadrazeneho" modelu.
     * @param type $codebook
     * @param type $filter
     * @return type 
     */
    /*
    public function get_rel_cb($codebook, $filter=Array()) 
    {
        // Nacteme codebook, ten je definovan jako _belongs_to vztah 
        // v konstruktoru bazoveho ORM
        
        
        // $codebook = $this->{$codebook}; // Tohle zpusobi 1 SQL dotaz navic - pro nacteni relacniho zaznamu
        $codebook = ORM::factory($codebook); // Tohle nic nacitat nebude a pri dodrzovani konvenci to nicemu nevadi
        // Doplnime NULL hodnoty ve filtru za prislusne hodnoty v aktualnim modelu
        foreach ((array)$filter as $column=>$value) {
            // NULL value znaci, ze se ma precist z aktualniho modelu
            if ($value == NULL) $filter[$column] = $this->{$column};
        }
        return $codebook->get_cb($filter);
    } */
    
    /**
     * Pouze zapouzdreni volani where()
     * pravdepodobne to bylo ve starsi Kohane
     * @param type $column nazev sloupce
     * @param type $value hledana hodnota
     */
    public function like($column, $value) 
    {
        return $this->where($column, 'LIKE', "%$value%");
    }
    /**
     * Zapouzdreni where()
     * @param <string> $column hodnota kterou hledame
     * @param <string> $value hledana hodnota
     * @return <ORM> $this
     */
    public function or_like($column, $value) 
    {
        return $this->or_where($column, 'LIKE', "%$value%");
    }
     
    
    
    /**
     * Generuje textovy nahled daneho objektu. Format nahledu je zadan jednim retezcem ($preview),
     * ve kterem vystupuji znaky "@" nasledovane klicem (napr. @firstname), 
     * ktery muze nabyvat nasledujich hodnot:
     * 
     *  1. atribut modelu - pak je klic nahrazen hodnotou daneho atributu
     *  2. virtualni atribut modelu - stejne jako 1.
     *  3. nazev relacniho modelu - klic je nahrazen preview() relacniho objektu
     * 
     * Několik příkladů formátu:
     * FORMAT NAHLEDU                    VYSLEDNY RETEZEC
     * --------------------------------------------------------------
     * "Makléř - @firstname @surname"              -> "Makléř - Jiří Melichar"
     * "Nabídka - @code, @_price (Makléř @seller)" -> "Nabídka - B-123C, 1000Kč (Makléř Jiří Melichar)"
     * @param <string> $preview format nahledu
     * @return <string> Vysledny nahled
     */
    public function preview($preview=NULL) {

        // Pokud neni nacten zaznam, nemuzeme delat nahled
        if ( ! $this->loaded()) return NULL;
        
        // Pokud nebyl zadan format nahledu, vezme se z jazykoveho souboru
        if ( ! is_string($preview) or empty($preview))
        {
            $preview = __($this->_preview);
        }

        // Rozparsovani retezce s formatem - v $matched bude "pole klicu pro nahrazeni"
        preg_match_all('/@([a-zA-Z_]+)/', $preview, $matched);

        // Projdeme vsechny klice
        foreach ($matched[1] as $i => $key) {
            
            // Pokud se jedna o belongs_to relaci, tak nahradim preview daneho zaznamu
            if (isset($this->_belongs_to[$key]))
            {
                // Klic v preview nahradim Preview daneho relacniho zaznamu
                $replace_with = $this->{$key}->preview();
            } 
            
            // Pokud se jedna o atribut/virtualni atribut
            else {
                // Dostupnost virtualniho atributu nelze overit
                // Proste ho zkusime precist
                try {
                    $replace_with = $this->{$key};
                }
                // Predpokladam ze kdyby zde doslo k jine chybe nez nedostupnost atributu,
                // tak by se na ni urcite narazilo i jinde v behu skriptu, proto ji muzeme ignorovat
                catch (Exception $e) {

                    //neznamy atribut necha v puvodni podobe - muze byt doplnen
                    //v dedici tride
                    continue;
                }
            } 
            $preview = str_replace($matched[0][$i], $replace_with, $preview);
        }
        
        return trim($preview);
    }


    /**
     * Metoda vraci asoc. pole, ktere obsahuje defaultni hodnoty atributu daneho
     * modelu. Hodnoty jsou atributum prirazeny v konstruktoru a to pouze v pripade
     * ze se vytvari novy zaznam (vs. nacitani jiz ulozeneho zaznamu).
     */
    protected function getDefaults()
    {
        //loads table columns definition into $this->_table_columns
        $this->reload_columns();

        $data = array();

        //datum vytvoreni noveho zaznamu
        if (array_key_exists('created', $this->_table_columns))
        {
            $data['created'] = date('Y-m-d H:i:s');
        }
        //ID uzivatele, ktery zaznam vytvari
        if (array_key_exists('userid', $this->_table_columns) && $this->_primary_key != 'userid' && Auth::instance()->get_user() != NULL)
        {
            $data['userid'] = Auth::instance()->get_user()->pk();
        }

        return $data;
    }

    /**
     * Slouzi k dynamickemu pridan validacnich pravidel k atributum objektu.
     * @param <string> $attr Nazev atributu pro ktery se validace pridava
     * @param <array|string> $rule Vycet validacnich pravidel, ktere maji byt pridany
     * nebo nazev validacniho pravidla ve forme retezce.
     */
    public function addValidationRule($attr, $rule)
    {
        //uprava hodnoty $rule aby byla ve spravnem tvaru - tj. pole
        if ( ! is_array($rule))
        {
            $rule = array($rule => NULL);
        }
        
        //priavidlo pro atribut bud nastavim nebo pripojim k tem co jiz existuji
        if (isset($this->_rules[$attr]))
        {
            $this->_rules[$attr] += (array)$rule;
        }
        else
        {
            $this->_rules[$attr] = (array)$rule;
        }
    }

    /**
     * Metoda slouzi k testovani zda je atribut pri validovan jako 'required'.
     * @param <string> $column Nazev testovaneho atributu / sloupce.
     * @return <bool> Vraci TRUE pokud dany atribut je required, FALSE v opacnem
     * pripade.
     */
    public function IsRequired($column)
    {
        $rules = $this->_rules();

        return isset($rules[$column]) && array_key_exists('not_empty', (array)$rules[$column]);
    }

    /**
     * Metoda kontroluje hodnotu atributu $this->update_on_delete <bool> ktera
     * rozhoduje zda maji byt zaznamy fyzicky z tabulky odstraneny anebo pouze
     * aktualizovany (standardne se atribut 'deleted' nastavuje na hodnotu '1').
     */
    public function delete_all()
    {
        //kontrola zda ma uzivatel opravneni 'delete' na tento objekt a popripade
        //zajisti aplikaci modifikatoru
        if ( ! $this->applyUserDeletePermission())
        {
            throw new Exception_UnauthorisedAction('Unauthorised db action "delete" on object "'.$this->_object_name.'".');
        }

        if ($this->update_on_delete)
        {
            $this->_build(Database::UPDATE);

            $this->_db_builder->set(array('deleted' => date('Y-m-d H:i:s')));

            $this->_db_builder->execute($this->_db);
        }
        else
        {
            $this->_build(Database::DELETE);

            $this->_db_builder->execute($this->_db);
        }

        return $this->clear();
    }

    /**
     * Metoda kontroluje hodnotu atributu $this->update_on_delete <bool> ktera
     * rozhoduje zda maji byt zaznamy fyzicky z tabulky odstraneny anebo pouze
     * aktualizovany (standardne se atribut 'deleted' nastavuje na hodnotu '1').
     * 
     * @param <type> $id
     * @param <array> $plan Definuje strukturu relacnich zaznamu, ktere
     * maji byt take rekurzivne odstraneny.
     *
     * @return ORM
     */
    public function delete($id = NULL, array $plan = array())
    {
        //kontrola zda ma uzivatel opravneni 'delete' na tento objekt a popripade
        //zajisti aplikaci modifikatoru
        if ( ! $this->applyUserDeletePermission())
        {
            throw new Exception_UnauthorisedAction('Unauthorised db action "delete" on object "'.$this->_object_name.'".');
        }

        //pokud je definovan plan pro odstraneni relacnich zaznamu, tak se podle
        //nej zaznamy odstrani jeste pred odstranenim tohoto zaznamu
        if ( ! empty($plan))
        {
            foreach ($plan as $rel_object => $subplan)
            {
                //pokud dany relacni zaznam existuje, tak jej odstrani
                if (isset($this->_has_one[$rel_object]) && $this->{$rel_object}->loaded())
                {
                    $this->{$rel_object}->delete(NULL, $subplan);
                }
                else if (isset($this->_has_many[$rel_object]))
                {
                    foreach ($this->{$rel_object}->find_all() as $rel_object_model)
                    {
                        $rel_object_model->delete(NULL, $subplan);
                    }
                }
            }
        }

        if ($id === NULL)
	{
            // Use the the primary key value
            $id = $this->pk();
	}

        // Zalogovani akce, jeste pred vlastnim ulozenim
        LogAction::instance()->runEvent($this, 'delete');

	if ( ! empty($id) OR $id === '0')
	{
            if ($this->update_on_delete)
            {
                DB::update($this->_table_name)->set(array('deleted' => date('Y-m-d H:i:s')))
                                              ->where($this->_primary_key, '=', $id)
                                              ->execute();
            }
            else
            {
        		DB::delete($this->_table_name)
                        ->where($this->_primary_key, '=', $id)
		            	->execute($this->_db);
            }
        }

	return $this;
    }

    /**
     * Metoda provadi nad danym zaznamem 'undelete' - tedy bere zpet akci 'delete'.
     *
     * @param <type> $id
     * @return ORM
     */
    public function undelete($id = NULL)
    {
        if ($id === NULL)
	{
            // Use the the primary key value
            $id = $this->pk();
	}

	if ( ! empty($id) OR $id === '0')
	{
            DB::update($this->_table_name)->set(array('deleted' => NULL))
                                              ->where($this->_primary_key, '=', $id)
                                              ->execute();
        }
        // Zalogovani akce
        LogAction::instance()->runEvent($this, 'undelete');

	return $this;
    }


    /**
     * Vraci vycet validacnich chyb s prekladem podle souboru s nazvem, ktery
     * odpovida nazvu modelu (table_name()).
     * @return <array>
     */
    public function getValidationErrors()
    {
        return $this->validate()->errors('object', TRUE, $this->table_name());
    }

    /**
     * Pomocna metoda, kterou slouzi k testovani zda hodnota nejakeho atributu
     * byla zmenena na jinou specifickou hodnotu.
     * Metoda kontroluje puvodni hodnotu atributu, kterou hleda v $this->log_original_data
     * a pak testuje aktualni hodnotu atributu v $this->object.
     * @param <string> $attr Nazev atribut jehoz hodnota se testuje
     * @param <string> $value Testovana hodnota
     * @return <bool> Vraci TRUE pokud nova hodnota atributu je rovna $value
     * a predtim nebyla. Jinak FALSE.
     */
    public function attrValueChangedTo($attr, $value)
    {
        //mame puvodni hodnotu
        return array_key_exists($attr, $this->log_original_data)
                //ta neni rovna testovane hodnote
                && $this->log_original_data[$attr] != $value
                //nova hodnota je rovna testovane hodnote
                && $this->_object[$attr] == $value;
    }

    /**
     * Vraci celociselny identifikator DB tabulky. V databazi se tato hodnota
     * pouziva ve sloupecku 'reltype'. 
     * @return <int>
     */
    public function reltype()
    {
        // Pokud identifikator neni v configu definovan, vraci NULL.
        return LogNumber::getTableNumber($this->object_name());
    }

    public function relid()
    {
        return $this->pk();
    }

    /**
     * Pomocna metoda pri provadeni validaci.
     *
     * Provadi kontrolu zda v tabulce daneho modelu neexistuje vice stejnych
     * hodnot v danem sloupci. Tedy je mozne kontrolovat napriklad ze ve sloupci
     * 'username' neexistuje hodnota 'admin' u jineho nez aktualniho zaznamu.
     *
     * @param $column <string> Nazev sloupce, ktery se bude kontrolovat
     * @param $value <string> Hodnota ktera muze byt pouze u aktualniho zaznamu
     * a u zadneho jineho.
     */
    public function is_unique_value($column, $value)
    {
        return (bool) ! DB::select(array('COUNT("*")', 'total_count'))
						->from($this->_table_name)
                                                ->where($this->_primary_key, '!=', $this->pk())
						->where($column, '=', $value)
						->execute($this->_db)
						->get('total_count');
    }

    /**
     * Pretezuje bazovou metodu 'find'. Zajistuje aplikaci opravneni
     * 'db_select' nad danym modelem.
     * @param <type> $id
     * @return <type> 
     */
    public function find($id = NULL)
    {
        //aplikuje opravneni 'db_select' (a jeho mozny modifikator)
        if ( ! $this->applyUserSelectPermission())
        {
            throw new Exception_UnauthorisedAction('Unauthorised db action "select" on object "'.$this->_object_name.'".');
        }

        return parent::find($id);
    }

    /**
     * Pretezuje bazovou metodu 'find_all'. Zajistuje aplikaci opravneni
     * 'db_select' nad danym modelem.
     * @return <type>
     */
    public function find_all()
    {
        //aplikuje opravneni 'db_select' (a jeho mozny modifikator)
        if ( ! $this->applyUserSelectPermission())
        {
            throw new Exception_UnauthorisedAction('Unauthorised db action "select (all)" on object "'.$this->_object_name.'".');
        }

        if (array_key_exists('deleted', $this->_object))
        {
            $this->where($this->table_name().'.deleted', 'IS', DB::Expr('NULL'));
        }

        return parent::find_all();
    }

    /**
     * Pretezuje bazovou metodu 'count_all'. Zajistuje aplikaci opravneni
     * 'db_select' nad danym modelem.
     *
     * A dale meni COUNT(*) na COUNT(DISTINCT(_PK_)), protoze
     * pri groupovani COUNT(*) nevraci vysledek, ktery ocekavam.
     * @return <int>
     */
    public function count_all()
    {
        //aplikuje opravneni 'db_select' (a jeho mozny modifikator)
        if ( ! $this->applyUserSelectPermission())
        {
            throw new Exception_UnauthorisedAction('Unauthorised db action "count (all)" on object "'.$this->_object_name.'".');
        }

        if (array_key_exists('deleted', $this->_object))
        {
            $this->where($this->table_name().'.deleted', 'IS', DB::Expr('NULL'));
        }

        $selects = array();

	foreach ($this->_db_pending as $key => $method)
	{
            if ($method['name'] == 'select')
            {
                // Ignore any selected columns for now
		$selects[] = $method;
		unset($this->_db_pending[$key]);
            }
	}

	$this->_build(Database::SELECT);

	$records = (int) $this->_db_builder->from($this->_table_name)
                ->select(array('COUNT(DISTINCT("'.$this->_table_name.'.'.$this->_primary_key.'"))', 'records_found'))
		->execute($this->_db)
		->get('records_found');

        // Add back in selected columns
	$this->_db_pending += $selects;

	$this->reset();

	// Return the total number of records in a table
	return $records;
    }

    /**
     * Metoda vraci nazev objektu pro ktery se ma kontrolovat opravneni.
     * Vraci tedy skutecny nazeb objektu anebo v pripade ze objekt dedi opravneni
     * od jineho objektu tak vraci nazev prave onoho jineho objektu a proti
     * tomu jsou provedeny kontroly opravneni.
     * 
     * @return <type> 
     */
    protected function permissionObjectName()
    {
        return empty($this->_inherit_permission)
                     ? $this->_object_name
                     : $this->_inherit_permission;
    }

    /**
     * Hlavni ucel teto metody je zkontrolovat zda ma uzivatel opravneni
     * pro vlozeni noveho zaznamu a pripadne vyvolat metodu, ktera provede
     * aplikaci modifikatoru opravneni.
     */
    protected function applyUserInsertPermission()
    {
        //ziskam uroven opravneni uzivatele na akci 'db_select' na tomto objektu
        $insert_modificator = Auth::instance()->get_user()->HasPermission($this->permissionObjectName(), 'db_insert');

        //pokud nema uzivatel zakladni select opravneni, tak vracim false
        if ($insert_modificator === FALSE)
        {
            return FALSE;
        }

        //pokud nema plne opravneni tak se bude aplikovat modifikator
        if ($insert_modificator !== TRUE)
        {
            //pocitam s tim ze v update modificatoru - retezci - by mohlo
            //byt vice mezerou oddelenych modifkatoru ktere chci kazdy zvlast
            //testovat
            $inset_modificator_list = explode(' ', $insert_modificator);

            foreach ($inset_modificator_list as $insert_modificator_value)
            {
                $insert_modificator_value = trim($insert_modificator_value);

                if (empty($insert_modificator_value))
                {
                    continue;
                }

                if ( ! $this->applyUserInsertPermissionModificator($insert_modificator_value))
                {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    /**
     * Tato metoda ma za ukol v dedicich modelech aplikovat modifikator 'db_insert'
     * opravneni. Pokud je volana tato "defaultni" implementace tak dojde k zapisu
     * do logu, protoze k tomu by nemelo dochazet a pravdepodobne nekdo
     * zapomel metodu implementovat v dedici tride. Obecne to muze znamenat
     * ze nedochazi ke korektni kontrole opravneni.
     * @param <string> $modificator
     */
    protected function applyUserInsertPermissionModificator($modificator)
    {
        if (Kohana::$environment != Kohana::PRODUCTION)
        {
            //dedici model neimplementuje tuto metodu - tzn. v dedicim modelu neni
            //interpretovan modifikator db_select orpavneni - muze se jednat o chybu
            Kohana::$log->add(Kohana::ERROR, 'In ORM model "'.get_class($this).'" using default applyUserInsertPermissionModificator (empty) implementation. Might not be intended.');

            // Zapise obsah logu na disk
            Kohana::$log->write();
        }
        
        return TRUE;
    }

    /**
     * Hlavni ucel teto metody je zkontrolovat zda ma uzivatel opravneni
     * pro odstranovani zaznamu a pripadne vyvolat metody, ktera provede
     * aplikaci modifikatoru opravneni pro odstranivani (db_delete).
     */
    protected function applyUserUpdatePermission()
    {
        //ziskam uroven opravneni uzivatele na akci 'db_select' na tomto objektu
        $update_modificator = Auth::instance()->get_user()->HasPermission($this->permissionObjectName(), 'db_update');

        //pokud nema uzivatel zakladni select opravneni, tak vracim false
        if ($update_modificator === FALSE)
        {
            return FALSE;
        }

        //pokud nema plne opravneni tak se bude aplikovat modifikator
        if ($update_modificator !== TRUE)
        {
            //pocitam s tim ze v update modificatoru - retezci - by mohlo
            //byt vice mezerou oddelenych modifkatoru ktere chci kazdy zvlast
            //testovat
            $update_modificator_list = explode(' ', $update_modificator);

            foreach ($update_modificator_list as $update_modificator_value)
            {
                $update_modificator_value = trim($update_modificator_value);

                if (empty($update_modificator_value))
                {
                    continue;
                }

                if ( ! $this->applyUserUpdatePermissionModificator($update_modificator_value))
                {
                    return FALSE;
                }
            }
        }

        return TRUE;
    }

    /**
     * Tato metoda ma za ukol v dedicich modelech aplikovat modifikator 'db_update'
     * opravneni. Pokud je volana tato "defaultni" implementace tak dojde k zapisu
     * do logu, protoze k tomu by nemelo dochazet a pravdepodobne nekdo
     * zapomel metodu implementovat v dedici tride. Obecne to muze znamenat
     * ze nedochazi ke korektni kontrole opravneni.
     * @param <string> $modificator
     */
    protected function applyUserUpdatePermissionModificator($modificator)
    {
        if (Kohana::$environment != Kohana::PRODUCTION)
        {
            //dedici model neimplementuje tuto metodu - tzn. v dedicim modelu neni
            //interpretovan modifikator db_select orpavneni - muze se jednat o chybu
            Kohana::$log->add(Kohana::ERROR, 'In ORM model "'.get_class($this).'" using default applyUserUpdatePermissionModificator (empty) implementation. Might not be intended.');

            // Zapise obsah logu na disk
            Kohana::$log->write();
        }

        return TRUE;
    }

    /**
     * Hlavni ucel teto metody je zkontrolovat zda ma uzivatel opravneni
     * pro odstranovani zaznamu a pripadne vyvolat metody, ktera provede
     * aplikaci modifikatoru opravneni pro odstranivani (db_delete).
     */
    protected function applyUserDeletePermission()
    {
        //ziskam uroven opravneni uzivatele na akci 'db_select' na tomto objektu
        $delete_modificator = Auth::instance()->get_user()->HasPermission($this->permissionObjectName(), 'db_delete');

        //pokud nema uzivatel zakladni select opravneni, tak vracim false
        if ($delete_modificator === FALSE)
        {
            return FALSE;
        }

        //pokud nema plne opravneni tak se bude aplikovat modifikator
        if ($delete_modificator !== TRUE)
        {
            //kazdy specificky model tady muze nastavit filtrovaci podminky pro
            //modifikator opravneni 'db_read' - napriklad ':own', apod.
            $this->applyUserDeletePermissionModificator($delete_modificator);
        }

        return TRUE;
    }

    /**
     * Tato metoda ma za ukol v dedicich modelech aplikovat modifikator 'db_delete'
     * opravneni. Pokud je volana tato "defaultni" implementace tak dojde k zapisu
     * do logu, protoze k tomu by nemelo dochazet a pravdepodobne nekdo
     * zapomel metodu implementovat v dedici tride. Obecne to muze znamenat
     * ze nedochazi ke korektni kontrole opravneni.
     * @param <string> $modificator
     */
    protected function applyUserDeletePermissionModificator($modificator)
    {
        if (Kohana::$environment != Kohana::PRODUCTION)
        {
            //dedici model neimplementuje tuto metodu - tzn. v dedicim modelu neni
            //interpretovan modifikator db_select orpavneni - muze se jednat o chybu
            Kohana::$log->add(Kohana::ERROR, 'In ORM model "'.get_class($this).'" using default applyUserDeletePermissionModificator (empty) implementation. Might not be intended.');

            // Zapise obsah logu na disk
            Kohana::$log->write();
        }

        return TRUE;
    }

    /**
     * Tato metoda je volane vzdy pred ctenim z DB tabulky prislusneho modelu
     * a zajistuje nastaveni filtrovacich podminek podle nastaveni opravneni
     * aktualne prihlaseneho uzivatele.
     *
     * Hlavni ucel metody je zkontrolovat zda ma uzivatel vubec opravneni pro
     * cteni na tomto objektu a pripadne vyvolat metodu, ktera zajisti
     * aplikaci modifikatoru opravneni (prida dodatecne filtrovaci podminky).
     */
    protected function applyUserSelectPermission()
    {
        //ziskam uroven opravneni uzivatele na akci 'db_select' na tomto objektu
        $select_modificator = Auth::instance()->get_user()->HasPermission($this->permissionObjectName(), 'db_select');

        //pokud nema uzivatel zakladni select opravneni, tak vracim false
        if ($select_modificator === FALSE)
        {
            return FALSE;
        }

        //pokud nema plne opravneni tak se bude aplikovat modifikator
        if ($select_modificator !== TRUE)
        {
            //kazdy specificky model tady muze nastavit filtrovaci podminky pro
            //modifikator opravneni 'db_read' - napriklad ':own', apod.
            $this->applyUserSelectPermissionModificator($select_modificator);
        }

        return TRUE;
    }

    /**
     * Tato metoda ma za ukol v dedicich modelech aplikovat modifikator 'db_select'
     * opravneni. Pokud je volana tato "defaultni" implementace tak dojde k zapisu
     * do logu, protoze k tomu by nemelo dochazet a pravdepodobne nekdo
     * zapomel metodu implementovat v dedici tride. Obecne to muze znamenat
     * ze nedochazi ke korektni kontrole opravneni.
     * @param <string> $modificator
     */
    protected function applyUserSelectPermissionModificator($modificator)
    {
        if (Kohana::$environment != Kohana::PRODUCTION)
        {
            //dedici model neimplementuje tuto metodu - tzn. v dedicim modelu neni
            //interpretovan modifikator db_select orpavneni - muze se jednat o chybu
            Kohana::$log->add(Kohana::ERROR, 'In ORM model "'.get_class($this).'" using default applyUserSelectModificator (empty) implementation. Might not be intended.');

            // Zapise obsah logu na disk
            Kohana::$log->write();
        }

        return TRUE;
    }

    /**
     * Metoda vraci bool hodnotu, ktera rika zda uzivatel jehoz ORM je predano
     * argumentem $user ma pravneni cist konrketni nacteny zaznam.
     * @param Model_Auth_User $user
     */
    public function testUserSelectPermission(Model_Auth_User $user)
    {
        //ziskam uroven opravneni uzivatele na akci 'db_select' na tomto objektu
        $select_modificator = $user->HasPermission($this->permissionObjectName(), 'db_select');

        //pokud nema uzivatel zakladni select opravneni, tak vracim false
        if ($select_modificator === FALSE)
        {
            return FALSE;
        }

        //pokud nema plne opravneni tak se bude aplikovat modifikator
        if ($select_modificator !== TRUE)
        {
            //kazdy specificky model tady muze nastavit filtrovaci podminky pro
            //modifikator opravneni 'db_read' - napriklad ':own', apod.
            return $this->testUserSelectPermissionModificator($user, $select_modificator);
        }

        return TRUE;
    }

    /**
     * Metoda vraci bool hodnotu, ktera rika zda uzivatel jehoz ORM je predano
     * argumentem $user ma pravneni cist konrketni nacteny zaznam.
     * @param Model_Auth_User $user
     */
    public function testUserDeletePermission(Model_Auth_User $user)
    {
        //ziskam uroven opravneni uzivatele na akci 'db_select' na tomto objektu
        $select_modificator = $user->HasPermission($this->permissionObjectName(), 'db_delete');

        //pokud nema uzivatel zakladni select opravneni, tak vracim false
        if ($select_modificator === FALSE)
        {
            return FALSE;
        }

        //pokud nema plne opravneni tak se bude aplikovat modifikator
        if ($select_modificator !== TRUE)
        {
            //kazdy specificky model tady muze nastavit filtrovaci podminky pro
            //modifikator opravneni 'db_read' - napriklad ':own', apod.
            return $this->testUserDeletePermissionModificator($user, $select_modificator);
        }

        return TRUE;
    }

    /**
     * Dedici modely by mely implementovat tuto metodu, ktera slouzi k otestovani
     * zda dany uzivatel ma select opravneni ke cteni daneho zaznamu.
     *
     * Tato funkcnost se vyuziva naprikald v modulu 'comment' kde je pouzita
     * k otestovani seznamu uzivatelu a odfiltrovani pouze tech, kteri maji
     * opravneni pro cteni daneho zaznamu. A jen na tyto uzivatele muze jit
     * e-mailova notifikace na nove vlozeny komentar.
     *
     * @param Model_Auth_User $user
     * @param <type> $modificator
     */
    protected function testUserDeletePermissionModificator(Model_Auth_User $user, $modificator)
    {
        if (Kohana::$environment != Kohana::PRODUCTION)
        {
            //dedici model neimplementuje tuto metodu - tzn. v dedicim modelu neni
            //interpretovan modifikator db_select orpavneni - muze se jednat o chybu
            Kohana::$log->add(Kohana::ERROR, 'In ORM model "'.get_class($this).'" using default testUserDeletePermissionModificator (empty) implementation. Might not be intended.');

            // Zapise obsah logu na disk
            Kohana::$log->write();
        }

        return TRUE;
    }

    /**
     * Dedici modely by mely implementovat tuto metodu, ktera slouzi k otestovani
     * zda dany uzivatel ma select opravneni ke cteni daneho zaznamu.
     *
     * Tato funkcnost se vyuziva naprikald v modulu 'comment' kde je pouzita
     * k otestovani seznamu uzivatelu a odfiltrovani pouze tech, kteri maji
     * opravneni pro cteni daneho zaznamu. A jen na tyto uzivatele muze jit
     * e-mailova notifikace na nove vlozeny komentar.
     *
     * @param Model_Auth_User $user
     * @param <type> $modificator
     */
    protected function testUserSelectPermissionModificator(Model_Auth_User $user, $modificator)
    {
        if (Kohana::$environment != Kohana::PRODUCTION)
        {
            //dedici model neimplementuje tuto metodu - tzn. v dedicim modelu neni
            //interpretovan modifikator db_select orpavneni - muze se jednat o chybu
            Kohana::$log->add(Kohana::ERROR, 'In ORM model "'.get_class($this).'" using default testUserSelectPermissionModificator (empty) implementation. Might not be intended.');

            // Zapise obsah logu na disk
            Kohana::$log->write();
        }

        return TRUE;
    }

    /**
     * Metoda vraci bool hodnotu, ktera rika zda uzivatel jehoz ORM je predano
     * argumentem $user ma pravneni cist konrketni nacteny zaznam.
     * @param Model_Auth_User $user
     */
    public function testUserUpdatePermission(Model_Auth_User $user)
    {
        //ziskam uroven opravneni uzivatele na akci 'db_select' na tomto objektu
        $update_modificator = $user->HasPermission($this->permissionObjectName(), 'db_update');

        //pokud nema uzivatel zakladni select opravneni, tak vracim false
        if ($update_modificator === FALSE)
        {
            return FALSE;
        }

        //pokud nema plne opravneni tak se bude aplikovat modifikator
        if ($update_modificator !== TRUE)
        {
            //pocitam s tim ze v update modificatoru - retezci - by mohlo
            //byt vice mezerou oddelenych modifkatoru ktere chci kazdy zvlast
            //testovat
            $update_modificator_list = explode(' ', $update_modificator);

            foreach ($update_modificator_list as $update_modificator_value)
            {
                $update_modificator_value = trim($update_modificator_value);

                if (empty($update_modificator_value))
                {
                    continue;
                }

                if ( ! $this->testUserUpdatePermissionModificator($user, $update_modificator_value))
                {
                    return FALSE;
                }
            }

            return TRUE;
        }

        return TRUE;
    }

    /**
     * Dedici modely by mely implementovat tuto metodu, ktera slouzi k otestovani
     * zda dany uzivatel ma select opravneni ke cteni daneho zaznamu.
     *
     * Tato funkcnost se vyuziva naprikald v modulu 'comment' kde je pouzita
     * k otestovani seznamu uzivatelu a odfiltrovani pouze tech, kteri maji
     * opravneni pro cteni daneho zaznamu. A jen na tyto uzivatele muze jit
     * e-mailova notifikace na nove vlozeny komentar.
     *
     * @param Model_Auth_User $user
     * @param <type> $modificator
     */
    protected function testUserInsertPermissionModificator(Model_Auth_User $user, $modificator)
    {
        if (Kohana::$environment != Kohana::PRODUCTION)
        {
            //dedici model neimplementuje tuto metodu - tzn. v dedicim modelu neni
            //interpretovan modifikator db_select orpavneni - muze se jednat o chybu
            Kohana::$log->add(Kohana::ERROR, 'In ORM model "'.get_class($this).'" using default testUserInsertPermissionModificator (empty) implementation. Might not be intended.');

            // Zapise obsah logu na disk
            Kohana::$log->write();
        }

        return TRUE;
    }

    /**
     * Metoda vraci bool hodnotu, ktera rika zda uzivatel jehoz ORM je predano
     * argumentem $user ma pravneni cist konrketni nacteny zaznam.
     * @param Model_Auth_User $user
     */
    public function testUserInsertPermission(Model_Auth_User $user)
    {
        //ziskam uroven opravneni uzivatele na akci 'db_select' na tomto objektu
        $insert_modificator = $user->HasPermission($this->permissionObjectName(), 'db_insert');

        //pokud nema uzivatel zakladni select opravneni, tak vracim false
        if ($insert_modificator === FALSE)
        {
            return FALSE;
        }

        //pokud nema plne opravneni tak se bude aplikovat modifikator
        if ($insert_modificator !== TRUE)
        {
            //pocitam s tim ze v update modificatoru - retezci - by mohlo
            //byt vice mezerou oddelenych modifkatoru ktere chci kazdy zvlast
            //testovat
            $insert_modificator_list = explode(' ', $insert_modificator);

            foreach ($insert_modificator_list as $insert_modificator_value)
            {
                $insert_modificator_value = trim($insert_modificator_value);

                if (empty($insert_modificator_value))
                {
                    continue;
                }

                if ( ! $this->testUserInsertPermissionModificator($user, $insert_modificator_value))
                {
                    return FALSE;
                }
            }

            return TRUE;
        }

        return TRUE;
    }

    /**
     * Dedici modely by mely implementovat tuto metodu, ktera slouzi k otestovani
     * zda dany uzivatel ma select opravneni ke cteni daneho zaznamu.
     *
     * Tato funkcnost se vyuziva naprikald v modulu 'comment' kde je pouzita
     * k otestovani seznamu uzivatelu a odfiltrovani pouze tech, kteri maji
     * opravneni pro cteni daneho zaznamu. A jen na tyto uzivatele muze jit
     * e-mailova notifikace na nove vlozeny komentar.
     *
     * @param Model_Auth_User $user
     * @param <type> $modificator
     */
    protected function testUserUpdatePermissionModificator(Model_Auth_User $user, $modificator)
    {
        return TRUE;
    }

    /**
     * "Pretizeni" metod pro skladani sql dotazu
     * aby se automaticky pridal nazev tabulky, pokud neni specifikovan
     */
    /*
    public function where($column, $op, $val) {
        $column = $this->_addTableName($column);
        return parent::where($column, $op, $val);
    }
    public function or_where($column, $op, $val) {
        $column = $this->_addTableName($column);
        return parent::or_where($column, $op, $val);
    }
    protected function _addTableName($column) {
        // muze to byt Database_expression
        if (is_string($column) and strpos($column, '.') !== FALSE) {
            $column = "$this->_table_name.$column";
        }
        return $column;
    }
    */

    /**
     * Metoda vytvari deep copy daneho zaznamu.
     *
     * @param <array> $plan Definuje strukturu relacnich zaznamu, ktere
     * maji byt take okopirovany.
     * @param <array> $overwrite Asoc. pole ve tvaru atribut => hodnota. Temto
     * atributum budou ve vytvorene kopii prirazeny prislusne hodnoty. Pouziva
     * se napriklad pro predani vazby na "rodicovsky" relacni zaznam.
     */
    public function copy(array $plan, array $overwrite = array())
    {
        //v poli $overwrite jsou nove hodnoty pro specificke atributy, ktere
        //maji byt prirazeny nove vytvorenemu zaznamu
        foreach ($overwrite as $attr => $val)
        {
            $this->{$attr} = $val;
        }

        //pred vytvorenim kopie je potreba ziskat seznam vsech relacnich
        //zaznamu, protoze po vytvoreni kopie bude ztracena vazba
        $rel_objects = array();

        //podle planu projde relacni zaznamy a vytvori jejich kopie
        foreach ($plan as $rel_object => $subplan)
        {
            //"schovam" sem vsechny _has_one relace + jejich subplan
            if (isset($this->_has_one[$rel_object]) && $this->{$rel_object}->loaded())
            {
                $rel_objects[] = array(
                    $this->{$rel_object},
                    $subplan
                );
            }
            //"schovam" sem vsechny _has_many relace + jejich subplan
            else if (isset($this->_has_many[$rel_object]))
            {
                foreach ($this->{$rel_object}->find_all() as $rel_object_model)
                {
                    $rel_objects[] = array(
                        $rel_object_model,
                        $subplan
                    );
                }
            }
        }

        //zrusi se hodnota PK - pri ukladani dojde k insertu
        unset($this->_object[$this->_primary_key],
              $this->_changed[$this->_primary_key]);

        //vytvari se novy zaznam - nastavi se defaultni hodnoty (tohle se dela
        //standardne v konstruktoru)
        $this->_load_values($this->getDefaults($this->getDefaultsModificators()));

        //timto rikam ze byly zmeneny hodnoty vsech atributu. pokud ORM detekuje
        //ze nedoslo ke zmene zadneho atributu, tak by nebyl proveden DB insert
        $this->_changed = array_keys($this->_object);

        //vlastni ulozeni nove kopie do DB
        $this->save();

        //relacnim zaznamum bude prirazena tato hodnota - jedna se o vazbu na
        //tento zaznam
        $rel_overwrite = array(
            $this->_primary_key => $this->pk()
        );

        //vytvoreni kopie jednotlivych relacnich zaznamu s vazbou na tento
        foreach ($rel_objects as $data)
        {
            list($rel_object, $subplan) = $data;

            $rel_object->copy($subplan, $rel_overwrite);
        }

        //vracim nove vytvorenou kopii puvodniho zaznamu
        return $this;
    }

    /**
     * Metoda slouzi k testovani zda doslo ke zmene atributu modelu od jeho nacteni
     * z DB. Pri volani metody save() se priznak resetuje a vrati TRUE az po
     * nasledujicich upravach.
     */
    public function isModified()
    {
        return ! empty($this->_changed);
    }

    protected function _rules()
    {
        return $this->_rules;
    }

    protected function _filters()
    {
        return $this->_filters;
    }

    protected function _callbacks()
    {
        return $this->_callbacks;
    }

    protected function _validate()
    {
        $this->_rules     = $this->_rules();
        $this->_filters   = $this->_filters();
        $this->_callbacks = $this->_callbacks();

        return parent::_validate();
    }

    protected $_explicit_select = FALSE;

    public function setExplicitSelect()
    {
        $this->_explicit_select = TRUE;
    }

	/**
	 * Loads a database result, either as a new object for this model, or as
	 * an iterator for multiple rows.
	 *
	 * @chainable
	 * @param   boolean       return an iterator or load a single row
	 * @return  ORM           for single rows
	 * @return  ORM_Iterator  for multiple rows
	 */
	protected function _load_result($multiple = FALSE)
	{
		$this->_db_builder->from($this->_table_name);

		if ($multiple === FALSE)
		{
			// Only fetch 1 record
			$this->_db_builder->limit(1);
		}

                if ( ! $this->_explicit_select)
                {
                    // Select all columns by default
                    $this->_db_builder->select($this->_table_name.'.*');
                }

		if ( ! isset($this->_db_applied['order_by']) AND ! empty($this->_sorting))
		{
			foreach ($this->_sorting as $column => $direction)
			{
				if (strpos($column, '.') === FALSE)
				{
					// Sorting column for use in JOINs
					$column = $this->_table_name.'.'.$column;
				}

				$this->_db_builder->order_by($column, $direction);
			}
		}

		if ($multiple === TRUE)
		{
			// Return database iterator casting to this object type
			$result = $this->_db_builder->as_object(get_class($this))->execute($this->_db);

			$this->reset();

			return $result;
		}
		else
		{
			// Load the result as an associative array
			$result = $this->_db_builder->as_assoc()->execute($this->_db);

			$this->reset();

			if ($result->count() === 1)
			{
				// Load object values
				$this->_load_values($result->current());
			}
			else
			{
				// Clear the object, nothing was found
				$this->clear();
			}

			return $this;
		}
	}

    /**
     * Basic helper method to load lang attributes.
     *
     * @param $attr
     * @param $locale
     */
    public function getLangAttr($attr, $locale)
    {
        //expecting this has_many rel object to contain the language fields values
        $lang_object = $this->_table_name.'_lang';

        //return its value
        return $this->{$lang_object}->where('field', '=', $attr)
            ->where('locale', '=', $locale)
            ->find()->content;
    }

} // End ORM
