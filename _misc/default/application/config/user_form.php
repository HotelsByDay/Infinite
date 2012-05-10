<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(

    //sablona, ktera bude pouzita pro vykresleni formulare
    'view_name' => 'user_form',

    //definice formularovych prvku pro jednotlive atributy objektu
    'items' => array(
        'active' => array(
            'type' => 'boolset',
        ),
        'username'  => array('type' => 'insertstring'),
        'password'  => array('type' => 'password'),
        'email'     => array('type' => 'string'),
        'role'      => array(
            'type'  => 'relnnselect',
            'rel'   => 'role',
            'filter' => array(
                array('public', '=', '1')
            )
        )
    ),

);