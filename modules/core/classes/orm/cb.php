<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Bazove ORM pro standardni ciselniky, ktere jsou ulozeny v samostatnych tabulkach.
 * Model rozsiruje standardni Kohana_ORM tridu o funkce, ktere umozni k ciselniku
 * snadno pristupovat pomoci tridy Codebook.
 *
 * 
 * (!) Asociativni pole s ciselnikem je sestaveno VZDY z primarniho klice zaznamu
 *     a z atributu 'value'. Pokud je v modelu definovan atribut $_codebookid
 *     je nutne aby tabulka obsahovala sloupec "codebookid".
 *     Nyni lze atribut 'value' zmenit pomoci atributu tridy $_value_key.
 */
class ORM_CB extends ORM {


    /**
     * Nazvy tabulek mame v jednotnem cisle.
     * @var <string>
     */
    protected $_table_names_plural = FALSE;

    /**
     * @var <string> nazev sloupce ve kterem je v tabulce uzivatelska hodnota ciselniku
     */
    protected $_value_key = 'value';

    /**
     * Default format 'preview' zaznamu.
     * @var string
     */
    protected $_preview = '@value';

    /**
     * Defaultni zpusob razeni codebooku je podle sequence hodnoty.
     * @var <type>
     */
    protected $_sorting = array(
        'sequence' => 'asc'
    );
     
    public function __construct($id=NULL) 
    {
        // Tohle by idealne melo byt implementovano mezi (ORM_cb, ORM) a Kohana_ORM
        // Nastaveni nazvu primarniho klice - pouzivame "neKohani" konvenci
        $this->_primary_key = ! empty($this->_table_name)
                                ? $this->_table_name.'id'
                                : strtolower(substr(get_class($this), 6)).'id';
        
        parent::__construct($id);
    }

    public function __set($column, $value)
    {
        if (isset($this->_object_name)) {
            switch ($column) {
                case 'value': {
                if (empty($this->code)) {
                    $this->code = $value; // Text::webalize($value);
                }
                break;
                }
            }
        }
        return parent::__set($column, $value);
    }


    protected function getDefaults()
    {
        $data = parent::getDefaults();
        $data['sequence'] = 100;
        return $data;
    }


    public function delete($id = NULL, array $plan = array())
    {
        $res = parent::delete($id, $plan);
        Codebook::invalidateCodebookCache($this->object_name());
        return $res;
    }


    public function save()
    {
        $changed = $this->_changed;
        $res = parent::save();
        if ($changed) {
            Codebook::invalidateCodebookCache($this->object_name());
        }
        return $res;
    }


    /**
     * Vraci asociativni pole s ciselnikem.
     *   - jako klice jsou pouzity hodnoty sloupce ktery je PK v dane tabulce
     *   - hodnoty jsou cteny ze sloupce "value"
     */
    public function get_codebook($filter=Array())
    {
        foreach ((array)$filter as $cond) {
            $this->where($cond[0], $cond[1], $cond[2]);
        }
        //vyvolam spusteni SELECT dotazu
        $results = $this->find_all();
        //vytvorim asoc. pole v pozadovanem tvaru
        $codebook = array();
        foreach ($results as $result) {
            $codebook[$result->pk()] = $result->{$this->_value_key};
        }
        return $codebook;
    }
    
    
    /**
     * Vraci asociativni pole pro prevod klice na ID zaznamu
     * @return <array>
     */
    public function get_cb_keys() {
        $results = $this->find_all();
        $codebook = array();
        foreach ($results as $result) {
            $codebook[$result->code] = $result->pk();
        }
        return $codebook;
    }

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
     * Protoze pro nektere ciselniky muze existovat vazba M:N, nelze k hodnote
     * pristoupit primo pres ORM vazbu belongs_to. Pro ziskani
     * pole hodnot slouzi tato metoda, kde parametrem je zakodovany retezec s
     * klici.
     * @param <string> $nn_string zakodovana hodnota
     * @return <array> seznam ve stejnem formatu jako get_cb 
     */
    /*
    public function get_nn_values($nn_string)
    {
        $nn_array = FormItem::NNDecode($nn_string);
        return empty($nn_array) ? array() : $this->where($this->_primary_key, 'IN', $nn_array)->get_cb();
    } */


    /**
     * Vraci preview dane ciselnikove hodnoty.
     * Nedochazi k zadnemu prekladu ale vraci pouze hodnotu atributu
     * $this->_value_key.
     * @param <type> $preview
     * @return <type>
     */
    public function preview($preview = NULL)
    {
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

//    /**
//     * Metoda slouzi k testovani zda objekt ma dany atribut.
//     * Kontroluje i relacni atributy.
//     * @param <string> $column
//     * @return <bool>
//     */
//    public function hasAttr($column)
//    {
//        $this->_load();
//
//	return
//	(
//            array_key_exists($column, $this->_object) OR
//            array_key_exists($column, $this->_related) OR
//            array_key_exists($column, $this->_has_one) OR
//            array_key_exists($column, $this->_belongs_to) OR
//            array_key_exists($column, $this->_has_many)
//	);
//    }
//
//    protected function getDefaults()
//    {
//        return array();
//    }
} // End ORM
