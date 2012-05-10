<?php defined('SYSPATH') or die('No direct script access.');

class AppFormItem_UserExportFormatType extends AppFormItem_Select
{
    /**
     * Vrati asociativni pole s vyctem hodnot pro tento prvek.
     * Klic vzdy odpovida hodnote ukladane do DB a hodnota v poli je
     * zobrazovana uzivateli v GUI.
     */
    protected function getValues()
    {
        return array(
            'csv'   => __('user_export.format_type_csv'),
        );
    }
}