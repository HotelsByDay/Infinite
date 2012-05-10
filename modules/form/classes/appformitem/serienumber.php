<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro zobrazeni cisla serie
 * Config parametry tohoto prvku: (? znaci nepovinne, ! znaci povinne)
 */
class AppFormItem_SerieNumber extends AppFormItem_Base
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/serienumber';
    
    /**
     * Generuje HTML kod formularoveho prvku
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
        // necham base tridu nacist sablonu
        $view = parent::Render($render_style, $error_message);
        
        // Pokud je value prazdna a model neni nacten (formular pro pridani)
        if ($this->getValue() == '' and ! $this->model->loaded()) {
            $view->msg = __('appformitem_serienumber.number_not_generated_yet');
        }
        //vracim inicializovanou sablonu
        return $view;
    }
    
    
    
}