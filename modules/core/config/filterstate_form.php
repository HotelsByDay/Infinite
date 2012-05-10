<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(

    //sablona, ktera bude pouzita pro vykresleni formulare
    'view_name' => 'filterstate_form',
    
    //definice formularovych prvku pro jednotlive atributy objektu
    'items' => array(
        
        'name' => array('type' => 'string', 'label' => __('filterstate_form.name.label')),
        //tento prvek slouzi pouze k udrzeni parametru filtru - je to skryty prvek
        //hodnoty dostava pri prvnim nacteni formulare pres ajax
        'content' => array('type' => 'filtercontent'),
        //obdobne jako filtercontent slouzi i tento prvek k udrezeni hodnoty reltyp
        'reltype' => array('type' => 'filterreltype')
    )
);