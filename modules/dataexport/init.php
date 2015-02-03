<?php defined('SYSPATH') or die('No direct access allowed.');

Route::set('generate_table_data_export', '<controller>/<action>/<table_config>/<export_config>',
    array(
        'controller'    => '[a-z0-9_-]+',
        'action'        => 'table_data_export',
        'table_config'  => '[a-z0-9_]+',
        'export_config' => '[a-z0-9_]+'
    ));