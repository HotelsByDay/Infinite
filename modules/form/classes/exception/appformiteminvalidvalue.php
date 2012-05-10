<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Tato vyjimka je vyhozena v metode AppFormItemBase->assignValue v pripade ze
 * hodnota daneho formularoveho prvku neni retezcovou hodnotou (ale napriklad
 * array nebo jiny ne-retezcovy typ) a neni tedy mozne tuto hodnotu korektne
 * vlozit do atributu ORM modelu.
 */
class Exception_AppFormItemInvalidValue extends Kohana_Exception
{
    
}
