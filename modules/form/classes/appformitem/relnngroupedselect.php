<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vyber M z N pomoci html komponent CHECKBOX
 * 
 */

class AppFormItem_RelNNGroupedSelect extends AppFormItem_RelNNSelect
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/relnngroupedselect';


    // Vychozi config
    protected $config = Array(
        // Vsechny skupiny se zobrazi v jednom sloupci, pro rozdeleni pouzit treba array(3, 4, 2, 1, ...)
        'columns_sizes' => array(1000),
    );


    /**
     * Vrati asociativni pole s vyctem hodnot pro tento prvek.
     * Klic vzdy odpovida hodnote ukladane do DB a hodnota v poli je 
     * zobrazovana uzivateli v GUI.
     */
    protected function getRelItemList()
    {
        // Pokud je definovano source_model v configu, vezmeme z nej data
        // Predpokladame ze source_model je definovan v $this->model v poli $_rel_cb
        if (($source_model = arr::get($this->config, 'rel', FALSE))
            AND ($group_model = arr::get($this->config, 'group_codebook', FALSE))
            ) {
            $filter = arr::get($this->config, 'filter', NULL);
            
            // Naplnime filtr z aktualniho modelu - codebook uz k nemu nema pristup
            foreach ((array)$filter as $column => $value) {
                if ($value == NULL) {
                    $filter[$column] = $this->model->{$column};
                }
            }
            // Tady potrebuji join group_codebook tabulky a order_by klauzuli
            // - to by vyzadovalo pridat do Codebooku metodu modelListing, ktera by misto 
            //   nazvu modelu dostala jiz jeho inicializovanou instanci
            // - Dalsim problemem by bylo to, ze potrebuji data rozdelit do skupin
            # return Codebook::listing($source_model, NULL, $filter);
            
            // Precteme codebook primo pres ORM 
            // - tim prichazime o prepend funkcionalitu, kterou zde nepotrebujeme
            //   a o cachovani, coz je skoda
            $cb = ORM::factory($source_model)
                    // Pripojime k ciselniku jeste ciselnik se skupinami
                    ->join($group_model)
                    ->on($source_model.'.'.$group_model.'id', '=', $group_model.'.'.$group_model.'id')
                    // Seradime primarne podle sekvence skupin
                    ->order_by($group_model.'.'.'sequence', 'ASC')
                    ->order_by($group_model.'.'.'value', 'ASC')
                    // Pro pripad ze sekvence z nejakeho duvodu bude stejna pro vice skupin
                    // musime seradit jeste podle PK skupiny
                    ->order_by($group_model.'id', 'ASC')
                    // Ve skupine seradime podle sekvence zdrojoveho codebooku
                    ->order_by($source_model.'.sequence', 'ASC')
                    ->order_by($source_model.'.value', 'ASC');
            
            // Pridame where podminky
            // - NEmuzeme pouzit metodu ORM_CB::get_codebook, protoze nam nestaci vysledek
            //   ve tvaru assoc. pole (pk=>value), potrebujeme jeste PK skupiny
            foreach ((array)$filter as $cond) {
                $cb->where($cond[0], $cond[1], $cond[2]);
            }
        
            // Poskladame pole tvaru
            // array(
            //      'group_id1' => array(id => value, id2 => value, ...),
            //      'group_id2' => array(id => value, id2 => value, ...),
            //      ...
            // )
            $codebook = array();
            $results = $cb->find_all();
            foreach ($results as $result) {
                $group_id = $result->{$group_model.'id'};
                // Pokud dana skupina zatim nema zadny prvek, pak ji inicializujeme
                if ( ! isset($codebook[$group_id])) $codebook[$group_id] = Array();
                // Zapiseme prvek do skupiny
                $codebook[$group_id][$result->pk()] = $result->preview();
            }
            return $codebook;
        }
        // Jinak vratime prazdne pole - hodnoty si pravdepodobne doplni odvozena trida
        return Array();
    }
    
    
    /**
     * Vrati ciselnik skupin - ten potrebujeme pro vypis jejich nazvu.
     * V nekterych pripadech navic muze byt (v custom sablone) uzitecne vypisovat i skupiny
     * ve kterych nejsou zadne checkboxy.
     * @return array
     */
    protected function getGroups()
    {
       if ($group_model = arr::get($this->config, 'group_codebook', FALSE)) {
           return Codebook::listing($group_model);
       }
       return Array();
    }
    
    
    /**
     * Pretizeni Base Render - pouze predani ciselniku skupin do sablony
     */
    public function Render($render_style=NULL, $error_message=NULL) 
    {
        $view = parent::Render($render_style, $error_message);
        $view->groups = $this->getGroups();
        $view->columns_sizes = (array)$this->config['columns_sizes'];
        return $view;
    }
    
}