<?php defined('SYSPATH') or die('No direct access allowed.');

return array(
    'objects' => array(
        'advert' => array(
            //zpusob razeni polozek nalezenych v tomto objektu
            'orderby' => array(
                'created' => 'desc'
            ),
        ),
        'demand' => array(
            'orderby' => array(
                'name'  => 'desc',
                'code'  => 'desc'
            ),
        ),
        'interest' => array(
            'order' => array(
                'created' => 'desc',
            ),
        ),
        'client' => array(
            'order' => array(
                'created' => 'desc',
            ),
        ),
    )
);