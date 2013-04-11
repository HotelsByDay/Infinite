<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vkladani retezcove hodnoty.
 * Config parametry tohoto prvku: (? znaci nepovinne, ! znaci povinne)
 *  ?'label'          => <string>  ... Label elementu ve formulari
 *  ?'placeholder'    => <string>  ... Polaceholder daneho inputu - pokud prohlicec nepodporuje html5 pak se pouzije JS
 *  ?'min_length'     => <int>     ... Minimalni delka textu - mozna bude osetreno v jQuery
 *  ?'max_length'     => <int>     ... Maximalni delka textu - atribut maxlength je akceptovan prohlizecem
 *  ?'popover'        => <array>   ... Bootstrap popover plugin configuration
 *  ?'field_prefix'   => <string>  ... Bootstrap add-on prefix content
 *  ?'field_suffix'   => <string>  ... Bootstrap add-on suffix content
 *  ?'input_class'   => <string>   ... Class for the <input> field
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
        $has_placeholder = (isset($this->config['placeholder']) and ! empty($this->config['placeholder']));
        $has_popover = (isset($this->config['popover']) and ! empty($this->config['popover']));
        // If item has placeholder defined - add JS to ensure that placeholder will work in html4 browsers
        if ($has_placeholder or $has_popover) {
            Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemString.js'));
            $init_js = View::factory('js/jquery.AppFormItemString-init.js');

            $config = array();
            if ($has_popover) {
                $config['popover'] = $this->config['popover'];
            }
            $init_js->config = $config;
            parent::addInitJS($init_js);
        }
        return parent::init();
    }
    
    /**
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

        // If placeholder is defined - add it into view
        if (isset($this->config['placeholder']) and ! empty($this->config['placeholder'])) {
            $view->placeholder = $this->config['placeholder'];
        }

        // If field_prefix is defined
        if (isset($this->config['field_prefix'])) {
            $view->field_prefix = $this->config['field_prefix'];
        }
        // If field_suffix is defined
        if (isset($this->config['field_suffix'])) {
            $view->field_suffix = $this->config['field_suffix'];
        }
        // If input_class is defined
        if (isset($this->config['input_class'])) {
            $view->input_class = $this->config['input_class'];
        }

        // Vratime $view
        return $view;
    }
    
    
}