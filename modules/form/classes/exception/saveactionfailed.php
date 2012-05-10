<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Tato vyjimka je vyhozena v pripade ze dojde k neuspesnemu ulozeni modelu, nad
 * kterym stoji formular.
 * Neuspesne ulozeni modelu formulare nastava v pripade ze je zachycena vyjimka
 * pri ukladani a nasledne je zjisteno ze zaznam nebyl ulozen.
 *
 * Tato vyjimka definuje sablonu, ktera bude pouzita k zobrazeni chybove hlasky.
 * Text hlasky je implicitne definovan metodou AppForm->getActionResult na zaklade
 * typu (ulozeni, odstraneni, atd.) a vysledku akce(neuspech).
 */
class Exception_SaveActionFailed extends Exception_FormAction
{
    /**
     * Definuje ktera sablona bude pouzita pro zobrazeni vyjimky na formulari.
     * @return <View>
     */
    public function getView($view_name=NULL)
    {
        return parent::getView('formaction/failed');
    }
}