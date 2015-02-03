<?php defined('SYSPATH') or die('No direct access allowed.');


/**
 * Model implementuje virtualni atribut '_filter_params' pro cteni i zapis.
 * Pri zapisu ocekava pole, ktere predstavuje parametry filtrovani - to je
 * zakodovano do atributu 'content'. Pri cteni jsou parametry filtru z atributu
 * 'content' dekodovany a vraceny.
 *
 */
class Model_FilterState extends ORM {

    /**
     * Nazev DB tabulky nad kterou stoji tento model.
     * @var <string>
     */
    protected $_table_name = 'filter_state';

    //defaultni zpusob razeni - podle vytvoreni od nejnovejsich
    protected $_sorting = array('created' => 'desc');

    

    //slouzi jako virtualni atribut, ktery slouzi k docasnemu ulozeni
    //rozdilu hodnoty 'size' proti nove nastavene hodnote 'size'
    public $delta = 0;

    /**
     * Metoda nastavuje hodnotu atributu 'size' a zaroven explicitne resetuje
     * hodnotu 'delta', ktery se automaticky po nastaveni 'size' aktualizuje.
     * @param <int> $size
     */
    public function setSizeAndClearDelta($size)
    {
        $this->size = $size;
        $this->delta = 0;
    }

    /**
     * Pri nastaveni hodnoty atributu 'size' se provadi prepocet hodnoty delta
     * podle puvodni hodnoty 'size'.
     *
     * Dale implementuje virtualni atribut '_filter_params', ktery slouzi ke
     * snadnemu nastaveni filtrovacich parametru.
     *
     */
    public function __set($column, $value)
    {
        switch ($column)
        {
            //aktualizace velikosti filtru - pocet zaznamu ktere spadaji do parametru filtru.
            //k tomuto dochazi pri standardnim prepoctu statistik filtru, coz se deje po nacteni
            //prislusne ___/table stranky - v tomto pripade nedochazi k volani metody save, takze 
            //aktualizovana hodnota se pouze pouzije v sablone pri vykreslovani statistik filtru.
            //Dale pak dochazi k aktualizaci atributu 'size' pri filtrovani dat po kliknuti na dany
            //filtr - v tomto pripade dochazi k pouziti filtru a aktualizuje se tedy datum a cas, spolecne
            //s aktualni size hodnotou. Trida filtr pri filtrovani dat podle filterstate
            //zaznamu vola metodu save() takze dojde k ulozeni techto hodnot.
            case 'size':
                //kontrola nutna jinak vyhazuje Kohana vyjimku - neco s construct metodou - nerozumim tomu presne
                if (isset($this->size))
                {
                    //aktualizace hodnoty 'delta' - je to rozdil proti minule hodnote 'size'
                    $this->delta = ($value - $this->size);
                    //cas pouziti
                    $this->lastused = date('Y-m-d H:i:s');
                }
                //standardni ulozeni hodnoty 'size'
                parent::__set($column, $value);

            break;

            //slouzi pri snadnejsi zapis filtrovacich parametru
            case '_filter_params':
                
                //provedu aktualizaci atributu content
                $this->content = base64_encode(serialize($value));

            break;

            default:
                return parent::__set($column, $value);
        }
    }

    /**
     * Pretezuje a vytvari nektere nove virtualni atributy modelu.
     * _filter_params - vraci parametry daneho filtru ve forme asoc. pole
     * 
     * @param <string> $column
     * @return <type>
     */
    public function __get($column)
    {
        switch ($column)
        {
            //slouzi pro snadnejsi cteni parametru filtru
            case '_filter_params':

                return empty($this->content)
                        ? array()
                        : unserialize(base64_decode($this->content));

                break;

            default:
                return parent::__get($column);
        }
    }

    /**
     * Metoda aplikuje modifikator opravneni 'db_select'.
     * @param <string> $modificator
     */
    protected function applyUserSelectPermissionModificator($modificator)
    {
        switch ($modificator)
        {
            case 'own':
                $this->where('userid', '=', Auth::instance()->get_user()->pk());
            break;
        }
    }
    
}
