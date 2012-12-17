<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Helper pro praci s ciselniky napric systemem.
 * Veskere prace s ciselniky by mela probihat prostrednictvim tohoto 
 * helperu, ktery se zaroven stara o cachovani.
 * 
 * @uses Cache
 * @uses Codebook models
 * @uses Kohana Config
 * @uses FormItem helper
 */

class Helper_Codebook
{
    // vychozi hodnoty pro prazdny key a value - pro metodu listing
    protected static $default_prepend_key = '';
    
    // kotva do jazykoveho souboru pro ziskani defaultni hodnoty - nececho jako '-- vse --'
    protected static $default_prepend_value = 'codebook.default_prepend_value';
    
    
    /**
     * Vraci cely ciselnik jako asociativni pole. Pripadne jeho vyfiltrovanou cast
     * podle parametru $filter. Posledni parametr muze byt obecne pole hodnot, ktere
     * se vlozi na zacatek ciselniku - diky tomu budeme moct snadno definovat 
     * klice hodnot jako "-- vse --", "-- nezvoleno --" atd... Zaroven muzeme pred 
     * ciselnik vlozit treba vice polozek, ikdyz to zatim asi na nic nebude.
     * 
     * @param <string> $codebook nazev modelu s ciselnikem
     * @param <mixed> $prepend obecne pole hodnot, ktere se pridaji pred ciselnik 
     *                Array - pole se vlozi na zacatek ciselniku
     *                Bool and true - na zacatek ciselniku se vlozi "-- vyberte --" s prazdnym klicem
     *                String - na zacatek se vlozi $prepend s prazdnym klicem
     * @param <array> $filter asociativni pole s filtrovacimi pravidly
     */
    public static function listing($codebook, $prepend=NULL, $filter=Array()) 
    {
        // Zpracovani posledniho parametru - polozky vkladane na zacatek ciselniku
        if ($prepend !== NULL) {
            if (is_int($prepend) or is_string($prepend)) {
                // Int a string muzeme zobrazit jako hodnotu polozky
                $prepend = Array(self::$default_prepend_key => $prepend);
            } elseif (is_bool($prepend) and $prepend) {
                // Bool rika ze pouzijeme vychozi prazdnou hodnotu
                // Tohle pouzivaji hlavne filtry
                $prepend = Array(self::$default_prepend_key => __(self::$default_prepend_value));
            } elseif ( ! is_array($prepend)) {
                // Pokud to neni ani pole, pak je to nevalidni a zmenime to na prazdne pole
                $prepend = Array();
            }
        }

        //do filtru se automaticky prida podminka 'status'=1 ... coz zajisti
        //ze budou nacteny pouze aktivni polozky ciselniku
        //$filter[] = array('status', '=', '1');

        // Zkusime vytahnout ciselnik z cache
        $values = self::readFromCache($codebook, 'value_list', $filter);
        // Pokud tam nebyl, nacteme z DB a ulozime do cache
        if ($values === NULL) {
            $values = ORM::factory($codebook)->get_codebook($filter);
            self::saveToCache($values, $codebook, 'value_list', $filter);
        }// else { echo "readed from cache - $codebook - <br>"; print_r($values); exit; }
        
        // Vratime ciselnik + pripadne prepend hodnoty
        return (array)$prepend + (array)$values;
    }
    
    
    /**
     * Vrací uzivatelskou hodnotu číselníku na základě jejího ID
     * @param <string> $codebook nazev modelu ciselniku
     * @param <int> $id id zaznamu 
     */
    public static function value($codebook, $id) 
    {
        // Zkusime vytahnout ciselnik z cache
        $values = self::readFromCache($codebook, 'value_list');
        // Pokud tam nebyl, nacteme z DB a ulozime do cache
        if ($values === FALSE) {
            $values = ORM::factory($codebook)->get_codebook();
            self::saveToCache($values, $codebook, 'value_list');
        }
        
        // Vratime nazev pozadovane polozky ciselniku
        return arr::get($values, $id, NULL);
    }
    
    
    /**
     * Vraci ID ciselnikove hodnoty na zaklade jejiho retezcoveho klice
     * pouziti: 
     *      $model->cb_agenda_typeid = codebook::id('cb_agenda_type.type_task');
     * @param <string> $selector retezec obsahujici nazev codebooku a klic zaznamu oddelene teckami 
     */
    public static function id($selector)
    {
        // Rozdelime selector na dve hodnoty podle tecky
        $selector = explode('.', $selector);
        // Pokud selector neni validni, vratime NULL
        if (count($selector) != 2) return NULL;
        // Ulozime casti selectoru do drou ruznych promennych
        list($codebook, $key) = $selector;

        // Zkusime vytahnout ciselnik z cache
        $values = self::readFromCache($codebook, 'key_list');
        // Pokud tam nebyl, nacteme z DB a ulozime do cache
        if ($values === FALSE) {
            $values = ORM::factory($codebook)->get_cb_keys();
            self::saveToCache($values, $codebook, 'key_list');
        }
        
        // Vratime ID s prislusnym klicem - pokud klic existuje
        return arr::get($values, $key, NULL);
    }
    
    
    /**
     * Protoze pro nektere ciselniky muze existovat vazba M:N, nelze k hodnote
     * pristoupit primo pres ORM vazbu belongs_to a metodu Codebook::value(). Pro ziskani
     * pole hodnot slouzi tato metoda, kde druhym parametrem je zakodovany retezec s
     * klici.
     * @param <string> $codebook nazev modelu ciselniku
     * @param <string> $nn_string zakodovana hodnota
     * @return <array> seznam ve stejnem formatu jako get_cb 
     */
    public static function nnValues($codebook, $nn_string)
    {
        // Nacteme cely codebook - vlastni metodou
        $codebook = self::listing($codebook);
        
        // Dekodujeme retezec
        $nn_array = FormItem::NNDecode($nn_string);
        
        // Z hodnot (ID) vytvorime klice
        $nn_array = array_flip($nn_array);
        
        // Spocteme prunik na zaklade klicu
        // Myslim ze to bude rychlejsi nez prochazeni foreachem
        return array_intersect_key($codebook, $nn_array);
    }
    
    
    /**
     * Spocte klic pro ulozeni/precteni ciselniku z cache
     * @param <string> $codebook
     * @param <string> $type typ ciselniku list/keys, kde list pocita klic pro ulozeni
     *   celeho ciselniku v list tvaru a keys pocita klic pro ulozeni pole pro prevod
     *   retezcovych klicu na hodnoty.
     * @param <array> $filter pripadne fitrovaci podminky 
     */
    protected static function countCacheKey($codebook, $type)
    {
        // Vsechny hodnoty definujici ciselnik zahashujeme
        return '__cb-'.$codebook.$type;
    }


    /**
     * Returns value from cache
     * @static
     * @param $codebook
     * @param $type
     * @param $filter
     * @return mixed desired data or NULL
     */
    protected static function readFromCache($codebook, $type, $filter=Array())
    {
        $cache_key = self::countCacheKey($codebook, $type);
        $data = (array)Cache::instance()->get($cache_key, array());
        $filter_key = md5(serialize($filter));
        return arr::get($data, $filter_key);
    }


    /**
     * Saves value into cache where key is based on codebook, type and filter
     * @static
     * @param $value
     * @param $codebook
     * @param $type
     * @param array $filter
     */
    protected static function saveToCache($value, $codebook, $type, $filter=Array())
    {
        // Get codebook cache key
        $cache_key = self::countCacheKey($codebook, $type);
        // Get filter key
        $filter_key = md5(serialize($filter));
        // Read current cb cached data
        $data = (array)Cache::instance()->get($cache_key, array());
        // Store given value on given filters key
        $data[$filter_key] = $value;
        // save new filter key
        Cache::instance()->set($cache_key, $data, Kohana::config('caching')->get('codebook'));
    }


    /**
     * Smaze z cache zaznamy daneho codebooku
     * @static
     * @param $codebook
     */
    public static function invalidateCodebookCache($codebook)
    {
        $cache_key = self::countCacheKey($codebook, 'value_list');
        Cache::instance()->delete($cache_key);
        $cache_key = self::countCacheKey($codebook, 'key_list');
        Cache::instance()->delete($cache_key);
    }

}

?>
