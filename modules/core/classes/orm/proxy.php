<?php defined('SYSPATH') or die('No direct script access.');

/**
 * ORM proxy se pouziva pro nasledujici specificky pripad:
 *
 * V kofiguraci formularovych prvku je moznost <bool> 'original_value', ktera zajisti
 * ze hodnota prvku vzdy odpovida hodnote, ktera je aktualne v DB - tzn. pokud
 * dojde k pri ukladani zaznamu k validacni chybe, tak prvek nesmi vzit hodnotu
 * z "aktualniho" orm modelu, ale ma k dispozici "loaded_model", ktery nebyl po
 * nacteni z DB modifikovan.
 * Reference na tento "loaded_model" je prvkum predana v pres konstruktor spolecne
 * s referenci na "standardni" model do ktereho se vkladaji nove hodnoty, provadi
 * se validace atd.
 * Pokud ale dojde k uspesnemu ulozeni nebo odstraneni zaznamu, tak je potreba
 * zmenit i "loaded_model" ktery nyni musi zacit ukazovat na prave ulozeny nebo
 * prave odstraneny zaznam. Pres PHP reference se mi tohoto chovani nepodarilo
 * dosahnout, takze "loaded_model" posilam do konstruktoru form itemu zabaleny
 * touto Proxy tridou. A pri uspesem ulozenim nebo odstraneni modelu, zmenim
 * vnitrni referenci Proxy tridy na ten prave ulozeny nebo odstraneny model.
 */
class ORM_Proxy
{
    /**
     * ORM class that is being proxied.
     * @var <ORM>
     */
    protected $orm = NULL;

    protected function __construct(Kohana_ORM $orm)
    {
        $this->orm = $orm;
    }

    /**
     * Factory design pattern.
     * @param ORM $orm Reference to class that is being proxied.
     * @return ORM_Proxy Returns a new instance of a ORM_Proxy class with
     * passed $orm class reference inside - the passed class is being proxied.
     */
    static public function factory(Kohana_ORM $orm)
    {
        return new ORM_Proxy($orm);
    }

    public function setORM(Kohana_ORM $orm)
    {
        $this->orm = $orm;
    }

    /**
     * Veskere volani metod preposilam na ORM model.
     * @param <type> $method
     * @param <type> $arguments
     * @return <type>
     */
    public function __call($method, $arguments)
    {
        return call_user_func_array(array($this->orm, $method), $aguments);
    }

    /**
     * Vsechny atributy posilam zapisuji do ORM tridy.
     * @param <type> $attr
     * @param <type> $value
     * @return <type>
     */
    public function __set($attr, $value)
    {
        return $this->orm->{$attr} = $value;
    }

    /**
     * Vsechny atributy ctu z ORM tridy.
     * @param <type> $attr
     * @return <type>
     */
    public function __get($attr)
    {
        return $this->orm->{$attr};
    }
}