<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Tato trida provadi fulltextove vyhledavani pro kontroler Fulltext.
 *
 * Trida implementuje Factory design pattern. Metoda factory predava konstruktoru
 * nazev objektu a konfiguraci pro vyhledavani. Konfigurace pro vyhledavani
 * obsahuje pozadovany pocet vysledku a retezec podle ktereho filtrovat.
 *
 * Vysledkem hledani je indexovane pole (parametry lze na strane volajiciho prijmout
 * pomoci konstrukce list(...)) kde jako prvni je pole ORM modelu, ktere predstavuji
 * vysledek hledani a druhou polozkou je celkovy pocet vysledku, ktery odpovida filtru.
 *
 * Ucel tridy:
 * Ucel tridy je zapouzdreni logiky vlastniho fulltextoveho hledani. Ta spociva
 * ve vytvoreni instance Filter tridy nad danym objektem. Toto chovani je mozne
 * customizovat pomoci pretizeni tridy.
 *
 */
class Core_Fulltext
{
    /**
     * Konfigurace pro tuto tridu.
     * @var <array> 
     */
    protected $config = array();

    /**
     * Factory design pattern.
     */
    protected function __clone()
    {

    }

    /**
     * Factory design pattern.
     * 
     * @param <string> $object_name Nazev objektu nad kterym se provadi fulltextove vyhledavani.
     * @param <array> $config Konfigurace pro fulltextove vyhledavani nad danym objektem.
     */
    protected function __construct($object_name, $config)
    {
        $this->object_name = $object_name;
        
        $this->config = $config;
    }

    /**
     * Factory design pattern.
     * @param <type> $object_name
     * @param <type> $config
     * @return Fulltext
     */
    static public function factory($object_name, $config)
    {
        return new Fulltext($object_name, $config);
    }

    /**
     * Tato metoda provadi fulltextoveho vyhledavani a vraci vysledek.
     *
     * Metoda vytvari instanci filtru nad danym objektem a tomuto filtru predava
     * parametry pro vyvolani fulltextoveho vyhledavani. 
     *
     * @param <string> $query Retezec, ktery ma byt pouzit pro fulltextove vyhledavani.
     * @return <type>
     */
    public function Search($query)
    {
        //toto pole predstavuje parametry vyhledavani
        $params = array(
            //retezec pro fulltext vyhledavani
            Filter_Base::FULLTEXT_QUERY_KEY => $query,
            
            //pocet zobrazenych vysledku
            Filter_Base::PAGE_SIZE_KEY      => arr::get($this->config, 'size', NULL),

            //Filter_Base akceptuje pole jako atribut pro razeni kde je mozne specifikovat
            //vicero sloupcu pro razeni
            Filter_Base::ORDERBY_KEY        => arr::get($this->config, 'orderby', NULL)
        );

        //nazev tridy ktera implementuje filtrovani standardne sestavim
        $class_name = 'Filter_'.$this->object_name;

        //vytvorim si instanci se kterou budu dale pracovat
        $filter_instance = new $class_name($this->object_name, $this->object_name, $params, Auth::instance()->get_user()->pk(), array());

        //Vraci ORM_Iterator predstavujici vysledky vyhledavani
        list($results, $filter_state_id, $filter_state_stat) = $filter_instance->getResults();

        //celkovy pocet nalezenych vysledku
        $total_count = $filter_instance->getResultsTotalCount();

        //vracim pole s vysledky vyhledavani ve forme pole objektu ORM + celkovy pocet vysledku
        return array($results, $total_count);
    }
}