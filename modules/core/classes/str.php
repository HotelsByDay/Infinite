<?php
/**
 * String helper.
 */
class Str
{

    protected $string = NULL;

    public static function create($string)
    {
        return new Str($string);
    }

    public function __construct($string='')
    {
        $this->string = $string;
    }

    /**
     * Pokud je $string neprazdny prida ho do retezce
     * - vcetne prefixu a suffixu
     * @param $prefix
     * @param $string
     * @param null $suffix
     */
    public function add($prefix, $string, $suffix='')
    {
        if (empty($string)) {
            return $this;
        }
        if (empty($this->string)) {
            $this->string = $string.$suffix;
        } else {
            $this->string .= $prefix.$string.$suffix;
        }
        return $this;
    }

    /**
     * Pokud je $string neprazdny prida ho do retezce
     * - vcetne prefixu a suffixu
     * @param $prefix
     * @param $string
     * @param null $suffix
     */
    public function append($prefix, $string, $suffix='')
    {
        $this->add($prefix, $string, $suffix);
    }


    public function prepend($string, $suffix)
    {
        if (empty($string)) {
            return;
        }
        $this->string = $string.$suffix.$this->string;
        return $this;
    }


    /**
     * Pokud nekdo objekt pouzije jako string, pak bude pracovat s hodnotou atributu string.
     * @return null|string
     */
    function __toString()
    {
        return (string)$this->string;
    }


}
