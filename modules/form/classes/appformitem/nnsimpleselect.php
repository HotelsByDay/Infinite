<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vyber 1 z N pomoci html komponenty SELECT
 * 
 * Config parametry tohoto prvku: (? znaci nepovinne, ! znaci povinne)
 *  !'label' => <string>  ... Label elementu ve formulari
 *  ?'free'  => <bool>    ... Prida polozku '-- nezvoleno --' jako prvni option - zde nema moc vyznam, ale dedi od Select
 * 
 * Parametry zdedene ze SelectDataSource
 *  ?'source_model'      => <string>  ...  Nazev modelu, ze ktereho se ciselnik sestavi. Klicem
 *                                        bude PK a hodnotou atribut "value".
 *  ?'source_codebookid' => <int>     ...  ID ciselniku - ten bude automaticky nacten z tabulky
 *                                        definovane v $this->source_codebook_table. Klicem opet
 *                                        bude PK a hodnotou atribut "value".
 */

class AppFormItem_NNSimpleSelect extends AppFormItem_Select
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/nnsimpleselect';
    
    /**
     * Prevedeni serializovane hodnoty z DB na pole, ktere pak
     * slouzi k snadnemu oznaceni zvolenych checkboxu pomoci fce in_array()
     * V parent tride je vracena hodnota nastavena do $view->value.
     */
    public function getValue()
    {
        return FormItem::NNDecode(parent::getValue());
    }
    
    /** 
     * Pro nastaveni hodnoty vne tridy slouzi metoda assignValue()
     * ve ktere je nacitani z form_data.
     * Tato metoda slouzi pro vlastni zapis do modelu.
     * Zde provadi serializaci N zvolenych hodnot do jednoho retezce
     */
    public function setValue($value)
    {
        if ( ! is_array($value)) {
            throw new Exception_AppFormItemInvalidValue('Value for attribute "'.$this->attr.'" is "'.serialize($this->form_data[$this->attr]).'" - not an array. Unable to assign to ORM attribute.');
        }
        $value = FormItem::NNEncode($value);
        parent::setValue($value);
    }
    
    
}