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
        return $this->{$this->_value_key};
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
