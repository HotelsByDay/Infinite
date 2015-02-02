<?php defined('SYSPATH') or die('No direct access allowed.');

class Model_User_IPAddress extends ORM
{
    public function __get($column)
    {
        switch ($column)
        {
            case '_ip':
                return parent::__get('ip');
            break;

            default:
                return parent::__get($column);
        }
    }
}