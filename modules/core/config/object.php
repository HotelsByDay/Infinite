<?php defined('SYSPATH') OR die('No direct access allowed.');


return array(
    //obecne akce, ktere lze nad zaznamem vyvolat (bude z /table vypisu nebo z /overview stranky)
    'actions' => array(
        'delete' => array(
            'label' => 'Odstranit',
            'message_ok'         => ':count záznamů bylo úspěšně odstraněno.',
            'message_error'      => 'Při odstraňování těchto záznamů došlo k chybě:',
            'undo_message_ok'    => ':count záznaml bylo úspěšně obnoveno.',
            'undo_message_error' => 'Při obnovení :count záznamů došlo k chybě:',
            'do'    => function($model) {
                $model->delete();
            },
            'undo'  => function($model) {
                $model->undelete();
            }
        )
    )
);