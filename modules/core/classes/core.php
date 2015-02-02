<?php

/**
 * Tato trida zapouzdruje instanciovani filter trid pro objekty a pozdeji mozna i dalsi podobne operace
 * Class Core
 */
class Core {


    /**
     * @param $object
     * @param null $controller
     * @param null $filter_params
     * @return Filter_Base
     * @throws FilterClassNotFoundException
     */
    public static function getFilterInstanceForObject($object, $controller=NULL, $filter_params=array())
    {
        if ($object instanceof ORM) {
            $object = $object->object_name();
        }
        $class_name = 'Filter_'.$object;
        if ( ! class_exists($class_name)) {
            throw new FilterClassNotFoundException('Filter class "'.$class_name.'" is missing.');
        }
        return new $class_name($object, $controller, $filter_params, NULL, NULL);
    }

}