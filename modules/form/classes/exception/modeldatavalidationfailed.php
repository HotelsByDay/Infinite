<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Tato vyjimka je vyhozena v pripade ze doslo k neuspesne validaci formularovych
 * dat ORM modelem.
 *
 * Vyjimka definuje sablonu, ktera bude pouzita k zobrazeni chybove hlasky.
 * U kazdeho prvku ktery ma nevalidni hodnotu bude take zobrazena chybova hlaska,
 * ale to uz je zodpovednost tridy AppForm.
 */
class Exception_ModelDataValidationFailed extends Exception_FormAction
{
    /**
     * Definuje ktera sablona bude pouzita pro zobrazeni vyjimky na formulari.
     * @return <View>
     */
    public function getView($view_name = NULL)
    {
        return parent::getView('formaction/failed');
    }
}