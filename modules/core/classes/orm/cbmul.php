<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Bazove ORM pro ciselniky - vychazi z cisteho Kohana_ORM (!)
 * Jelikoz vice ciselnikovych modelu muze pracovat nad stejnou tabulkou
 * (vice ciselniku muze byt v jedne tabulce) je treba rozlisit jednotlive
 * mnoziny zaznamu. K tomu slouzi DB atribut "codebookid" a atribut tohoto
 * bazoveho ORM "$_codebookid". 
 * 
 * Struktura databazove tabulky musi byt nasledujici:
 * table_nameid(PK) | [codebookid] | value 
 * 
 * (!) Asociativni pole s ciselnikem je sestaveno VZDY z primarniho klice zaznamu
 *     a z atributu 'value'. Pokud je v modelu definovan atribut $_codebookid
 *     je nutne aby tabulka obsahovala sloupec "codebookid".
 *     Nyni lze atribut 'value' zmenit pomoci atributu tridy $_value_key.
 */
class ORM_CBMUL extends Kohana_ORM {

    
    /**
     * Muze byt definovano v odvozenych tridach spolu s _table_name
     * @var <int> ID ciselnikove rady v prislusne tabulce
     */
    protected $_codebookid = NULL;
    
    /**
     * @var <string> nazev sloupce ve kterem je v tabulce uzivatelska hodnota ciselniku
     */
    protected $_value_key = 'value';
    
    
    /**
     * Toto je volano ve find() i ve find_all() metodach pro nacteni dat
     * Zde pridame podminku na codebookid - aby nebylo mozna cist z ciselniku
     * hodnoty, ktere patri do jine rady */    
    protected function _load_result($multiple=false) {
        
        if ($this->_codebookid != NULL) {
            $this->_db_builder->where('codebookid', '=', $this->_codebookid);
        }
        // Dalsi zpracovani nechame na rodicovske tride
        return parent::_load_result($multiple);
    }

    
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
        foreach ((array)$filter as $column=>$value) {
            $this->where($column, '=', $value);
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
    public function preview()
    {
        return $this->{$this->_value_key};
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
            array_key_exists($column, $this->_object) OR
            array_key_exists($column, $this->_related) OR
            array_key_exists($column, $this->_has_one) OR
            array_key_exists($column, $this->_belongs_to) OR
            array_key_exists($column, $this->_has_many)
	);
    }

    protected function getDefaults()
    {
        return array();
    }
    
} // End ORM
