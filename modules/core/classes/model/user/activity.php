<?php defined('SYSPATH') or die('No direct access allowed.');

class Model_User_Activity extends ORM
{

    /**
     * Definuje relace typu belongs_to
     * @var <array>
     */
    protected $_belongs_to = Array(
        'user_ipaddress' => Array('model' => 'user_ipaddress', 'foreign_key' => 'user_ipaddressid'),
    );


    public function __get($column)
    {
        switch ($column)
        {
            case '_to':
                return date('j.n.Y H:i', strtotime(parent::__get('to')));
            break;

            case '_useragent':
                return parent::__get('useragent');
            break;

            case '_locality':
                return utf8::ucfirst(mb_strtolower($this->user_ipaddress->city)).', '.$this->user_ipaddress->country;
            break;

            default:
                return parent::__get($column);
        }
    }
}