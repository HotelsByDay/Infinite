<?php defined('SYSPATH') OR die('No direct access allowed.');

return array(

    'set' => array(
        //Set JS pro formular
        'form' => array(
            //explicitne nebudu definovat zadne soubory
            //vkladat nazvy sablon (napr. "js/file.js")
            'list'   => array('js/FileUploader.js', 'js/jquery.fancybox.js', 'js/jquery.objectForm.js', 'js/jquery.qtip.min.js', 'js/jquery.redactor.js', 'js/jquery.miniColors.js'),
            //Definuje regularni vyraz, ktery bude pouzit pro nalezeni JS souboru,
            //ktere patri do tohoto setu
            'filter' => '#AppFormItem[^-]+\.#'
        ),
        'table' => array(
            'list' => array(
                'js/jquery.objectFilter.js',
                'js/jquery.objectItemAction.js'
            ),
            'filter' => NULL
        )
    )
);
