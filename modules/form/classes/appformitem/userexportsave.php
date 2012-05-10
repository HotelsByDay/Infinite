<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek, ktery se pouziva specialne na formulari pro ulozeni
 * uzivatelskeho exportu.
 * Prvek uzivateli umozni zvolit zda chce export ulozit a pod jakym jmenem.
 */
class AppFormItem_UserExportSave extends AppFormItem_Base
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/userexportsave';

    //Prvek je virtualni
    protected $virtual = TRUE;
}