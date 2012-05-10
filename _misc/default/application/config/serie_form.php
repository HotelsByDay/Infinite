<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(

    //sablona, ktera bude pouzita pro vykresleni formulare
    'view_name' => 'serie_form',

    //definice formularovych prvku pro jednotlive atributy objektu
    'items' => array(
        'format' => array('type' => 'string',),
        'name'   => array('type' => 'string'),
    ),
);