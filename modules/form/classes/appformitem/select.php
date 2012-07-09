<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vyber 1 z N pomoci html komponenty SELECT
 * 
 * Config parametry tohoto prvku: (? znaci nepovinne, ! znaci povinne)
 *  !'label' => <string>  ... Label elementu ve formulari
 *  ?'free'  => <bool>    ... Prida polozku '-- nezvoleno --' jako prvni option
 * Parametry zdedene ze SelectDataSource
 *  ?'codebook'      => <string>  ...  Nazev modelu, ze ktereho se ciselnik sestavi. Klicem
 *                                        bude PK a hodnotou atribut "value".
 *  ?'source_codebookid' => <int>     ...  ID ciselniku - ten bude automaticky nacten z tabulky
 *                                        definovane v $this->source_codebook_table. Klicem opet
 *                                        bude PK a hodnotou atribut "value".
 */
class AppFormItem_Select extends AppFormItem_SelectDataSource
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/select';
    
    
    // Klic, pro hodnotu '-- nezvoleno --', pokud je pouzita varianta freeSelect
    //  - ta se aktivuje v configu nastavenim 'free' => '1',
    protected $empty_key = '';
    
    // Text reprezentujici prazdnou hodnotu - v odvozenych tridach bude
    // uzitecne mit moznost ho zmenit 
    protected $empty_value = '-- nezvoleno --';
    
    
   
    /**
     * Vrati asociativni pole s vyctem hodnot pro tento prvek.
     * Zde neobsahuje zadnou funkcionalitu - slouzi k pretezovani v 
     * odvozenych tridach.
     * 
     * Pole bude ve tvarku Array(KEY => VAL, ...), pricemz 
     * pri generovani  HTML komponenty bude kazda polozka mit nasledujici tvar:
     * <option value="KEY">VAL</option>
     */
    protected function getValues() 
    {
        $values = parent::getValues();
        // Pridame '-- nezvoleno --', pokud je to vyzadovano configem
        if (arr::get($this->config, 'free', FALSE)) {
            $values = $this->addFreeValue($values);
        }
        return $values;
    }
    
    
     /**
     * Prida na zacatek ciselniku hodnotu '-- nezvoleno --',
     * ktera bude pod klicem $this->empty_key
     */
    protected function addFreeValue($values=array()) 
    {
        $res_values[$this->empty_key] = __('codebook.default_prepend_value');
        
        // Nevim jak lepe zajistit pridani hodnoty na zacatek asociativniho pole
        // Razeni podle klicu nepripada v uvahu - bude to casto serazeno podle hodnot
        // array_merge nepracuje s assoc poli a jinou funkci jsem nenasel
        foreach ($values as $key=>$val) {
            $res_values[$key] = $val;
        }
        
        // Nakonec nechame pole zpracovat rodicovskou tridou
        return $res_values;
    }

    /**
     * Na formulari je misto hodnoty NULL (=nevybrano) uveden prazdna hodnota ('').
     * Tu je ted potreba prelozit zpatky na NULL aby byla takto zapsana do DB.
     * @param string $value
     * @return <type>
     */
    public function setValue($value)
    {
        if ($value === '')
        {
            $value = NULL;
        }

        return parent::setValue($value);
    }
    
    /**
     * Pretizeni Base Render - pouze predani seznamu hodnot do sablony
     */
    public function Render($render_style=NULL, $error_message=NULL) 
    {
        $view = parent::Render($render_style, $error_message);
        $view->values = $this->getValues();
        return $view;
    }
    
    
}