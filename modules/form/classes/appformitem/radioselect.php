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
class AppFormItem_RadioSelect extends AppFormItem_SelectDataSource
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/radioselect';


    /**
     * Pretizeni Base Render - pouze predani seznamu hodnot do sablony
     */
    public function Render($render_style=NULL, $error_message=NULL)
    {
        $view = parent::Render($render_style, $error_message);

        //get standard set of values
        $values = $this->getValues();

        //configuration may define translation for specific values
        $translated_values = $this->translateValues($values);

        $view->values = $translated_values;

        return $view;
    }



}