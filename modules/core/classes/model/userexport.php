<?php defined('SYSPATH') or die('No direct access allowed.');


/**
 *
 */
class Model_UserExport extends ORM {

    /**
     * Nazev DB tabulky nad kterou stoji tento model.
     * @var <string>
     */
    protected $_table_name = 'user_export';

    //defaultni zpusob razeni - podle vytvoreni od nejnovejsich
    protected $_sorting = array('name' => 'asc');

    /**
     * Validacni pravidla
     */
    protected $_rules = array
    (
        //nazev nabidky nesmi byt prazdny
        'name'              => array
        (
            'not_empty'     => NULL,
        ),
    );

    /**
     * Metoda aplikuje modifikator opravneni 'db_select'.
     * @param <string> $modificator
     */
    function applyUserSelectModificator($modificator)
    {
        return true;
    }

    public function __get($column)
    {
        switch ($column)
        {
            case '_columns':
                return FormItem::NNDecode(parent::__get('columns'));
            break;

            default:
                return parent::__get($column);
        }
    }

    public function __set($column, $value)
    {
        switch ($column)
        {
            //pokud uzivatel nastavuje hodnotu 'reltype' a zaznam neni jeste ulozen,
            //tak se podle toho automaticky doplni i atribut 'name' - tedy
            //defaultni nazev exportu
            case 'reltype':

                //ziskam nazev objektu - podle toho vytvorim jazykovou kotvu
                $object_name = LogNumber::getTableName($value);

                //defaultni nazev souboru
                $this->name = ucfirst(text::webalize(__($object_name.'.menu_name'))).'_'.date('j_n_Y', time());

            default:
                return parent::__set($column, $value);
        }
    }
}
