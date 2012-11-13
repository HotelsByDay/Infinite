<?php defined('SYSPATH') OR die('No direct access allowed.');


return array(
    //obecne akce, ktere lze nad zaznamem vyvolat (bude z /table vypisu nebo z /overview stranky)
    'actions' => array(
        'delete' => array(
            'label' => __('object.delete_action'),
            'message_ok'         => __('object.action.delete.message_ok'),
            'message_error'      => __('object.action.delete.message_error'),
            'undo_message_ok'    => __('object.action.delete.undo_message_ok'),
            'undo_message_error' => __('object.action.delete.undo_message_error'),
            'do'    => function($model) {
                $model->delete();
            },
            'undo'  => function($model) {
                $model->undelete();
            }
        )
    )
);