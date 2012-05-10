<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro upload jednoho nebo vice souboru.
 */
class AppFormItem_SellerPhoto extends AppFormItem_File
{
    /**
     * Nazev modelu se kterym prvek pracuje - model reprezentuje soubor v DB.
     * @var <string>
     */
    protected $model_name = 'sellerphoto';
}