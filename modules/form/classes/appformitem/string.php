<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vkladani retezcove hodnoty.
 * Config parametry tohoto prvku: (? znaci nepovinne, ! znaci povinne)
 *  !'label'      => <string>  ... Label elementu ve formulari
 *  ?'min_length' => <int>     ... Minimalni delka textu - mozna bude osetreno v jQuery
 *  ?'max_length' => <int>     ... Maximalni delka textu - atribut maxlength je akceptovan prohlizecem
 */
class AppFormItem_String extends AppFormItem_Base
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/string';

    /**
     * 
     */
    public function init()
    {
        return parent::init();
    }
    
    /**
     * 
     * Generuje HTML kod formularoveho prvku
     * navic predava do sablony atributy min_length a max_length
     *
     * @param <const> $render_style Definuje zpusob zobrazeni formularoveho prvku.
     * Ocekava jednu z konstant AppForm::RENDER_STYLE_*.
     *
     * @param <string> $error_message Definuje validacni chybu, ktera ma byt
     * u prvku zobrazena.
     * 
     * @return <View>
     */
    public function Render($render_style = NULL, $error_message = NULL)
    {
        // Zavolame base Render, ktera vytvori pohled a preda zakladni atributy
        $view = parent::Render($render_style, $error_message);
        
        // Vratime $view
        return $view;
    }
    
    
}