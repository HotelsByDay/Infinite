<?php defined('SYSPATH') or die('No direct script access.');

class AppFormItem_Wysiwyg extends AppFormItem_String
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/wysiwyg';

    protected $config = Array(
        'images_upload' => true,
    );

    /**
     * Inicializace objektu - volano v konstruktoru AppFormItem_Base
     */
    public function init()
    {
        // Poskladame config prvku
        $config = Array();

        // Pokud je povolen uplaod obrazku tak predame url na upload controller a dalsi parametry
        if ($this->config['images_upload']) {
            $get_params = Array(
                'reltype' => $this->getImageRelType(),
                'relid'   => $this->model->pk(),
            );
            $config['images_upload'] = AppUrl::directupload_file_action('wysiwyg.images_upload', $get_params);
        }

        //inicializace pluginu na teto instanci form prvku
        $this->addInitJS(View::factory('js/jquery.AppFormItemWysiwyg-init.js')->set('config', $config));

        return parent::init();
    }


    /**
     * Vrati reltype pro dane locale
     * (!) pozor, tento vypocet je implementovan i v jQuery pluginu prvku a pri zmene zde je nutne ho zmenit i tam (!)
     * @param $locale
     * @return string
     */
    protected function getImageRelType($locale=null)
    {
        return $this->model->object_name().'.'.$this->attr;
    }



}