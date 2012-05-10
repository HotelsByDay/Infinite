<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(

    'set' => array(
        //do JS FileSetu 'table' pridam plugin pro objectDataPanel, ktery je potreba
        //kvuli zobrazeni prehledu komentaru ke konkretnimu zaznamu
        'table' => array(
            //Definuje regularni vyraz, ktery bude pouzit pro nalezeni JS souboru,
            //ktere patri do tohoto setu
            'filter' => '#AppFormItem[^-]+\.#',

            'list' => array(
                'js/FileUploader.js',
                'js/jquery.objectDataPanel.js',
                'js/jquery.objectForm.js',
            ),
        ),
        //do JS FileSetu 'table' pridam plugin pro objectDataPanel, ktery je potreba
        //kvuli zobrazeni prehledu komentaru ke konkretnimu zaznamu
        'overview' => array(
            //Definuje regularni vyraz, ktery bude pouzit pro nalezeni JS souboru,
            //ktere patri do tohoto setu
            'filter' => '#AppFormItem[^-]+\.#',
            'list' => array(
                'js/jquery.objectForm.js',
            ),
        )
    )
);
