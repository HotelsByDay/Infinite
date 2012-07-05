<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vyber obrazku
 * konfigurace prvku obsahuje klice 'relobject' pro ktery musi platit nasledujici:
 *  - aktualni model ma definovanou vazbu na relobject model
 *  - relobject model ma definovanou vazbu na "<relobject>_image" model
 * Tyto konvence znacne zjednodusuji konfiguraci prvku
 */

class AppFormItem_ObjectImageSelector extends AppFormItem_Base
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/objectimageselector';

    // Object for which we will find images
    protected $relobject = null;

    /**
     * Nacteme config
     */
    public function __construct($attr, $config, ORM $model, ORM_Proxy $loaded_model, $form_data, $form)
    {
        //zakladni zpracovani konfigurace
        parent::__construct($attr, $config, $model, $loaded_model, $form_data, $form);

        // Ulozime si relobject do local atributu
        $this->relobject = arr::get($this->config, 'relobject');
    }

    /**
     * Inicializace objektu - volano v konstruktoru AppFormItem_Base
     */
    public function init()
    {
        parent::addInitJS(View::factory('js/jquery.AppFormItemObjectImageSelector-init.js'));

        return parent::init();
    }

    /**
     * Precteme ID zvoleneho obrazku a to nechame zpracovat rodice
     * @param $value
     */
    public function setValue($value)
    {
        $value = arr::get($value, 'id');
        return parent::setValue($value);
    }

    /**
     * Vraci obrazky prectene z DB
     */
    public function getImages()
    {
        $resize_variant = arr::get($this->config, 'image_resize_variant');
        // precteme obrazky pres relace modelu
        $images = $this->model->{$this->relobject}->{$this->relobject.'_image'}->find_all();
        // Projdeme obrazky a ulozime si potrebne info do pole
        $result = Array();
        foreach ($images as $image) {
            $result[] = Array(
                'url'     => $image->getUrl($resize_variant),
                'preview' => $image->preview(),
                'id'      => $image->pk(),
            );
        }
        return $result;
    }


    
    public function Render($render_style = NULL, $error_messages = NULL)
    {
        $view = parent::Render($render_style, $error_messages);
        $view->images = $this->getImages();
        return $view;
    }

}