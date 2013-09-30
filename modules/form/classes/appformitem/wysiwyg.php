<?php defined('SYSPATH') or die('No direct script access.');

class AppFormItem_Wysiwyg extends AppFormItem_String
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/wysiwyg';

    protected $config = Array(
        'images_upload' => true,
        'formatting_tags' => NULL, // keep editor default setting
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

        if (is_array($this->config['formatting_tags'])) {
            $config['formatting_tags'] = $this->config['formatting_tags'];
        }

        //inicializace pluginu na teto instanci form prvku
        $this->addInitJS(View::factory('js/jquery.AppFormItemWysiwyg-init.js')->set('config', $config));

        return parent::init();
    }


    /**
     * Tato metoda slouzi ke zpracovani formularovych udalosti.
     *
     * @param <int> $type Identifikator typu udalosti. Definovano konstantami
     * AppForm::FORM_EVENT_*.
     *
     * @param <array> $data K obsluze udalosti mohou byt pripojeny data. Napr.
     * k udalosti FORM_EVENT_SAVE_FAILED muze byt pripojena reference na vyjimku,
     *  ktera zpusobyla neuspesne ulozeni zaznamu.
     */
    public function processFormEvent($type, $data)
    {
        switch($type)
        {
            //volano po uspesne ulozeni zaznamu
            case AppForm::FORM_EVENT_BEFORE_SAVE:

                $content = $this->getValue();

                // Najdeme vsechny SRC obrazku z ukladaneho textu
                preg_match_all('@<img.*? src="(.*?)"@', $content, $matches);

                // Pridame pole nalezenych URL v danem prekladu do celkoveho pole
                $used_urls = $matches[1];


                // Kazdy obrazek se maze na model_name.atribut
                $reltype = $this->getImageRelType();
                // A zaroven na jeden zaznam
                $relid = $this->model->pk();
                // Precteme vsechny obrazky pro aktualni zaznam
                $images = ORM::factory('wysiwyg_image')
                    ->where('reltype', '=', $reltype)
                    ->where('relid', '=', $relid)
                    ->find_all();

                // Projdeme je a pokud jejich URL neni v seznamu odkazovanych ($used_urls) pak dany obrazek odstranime
                foreach ($images as $img)
                {
                    if ( ! in_array($img->getUrl(), $used_urls)) {
                        $img->delete();
                    }
                }

                // Projdeme text z editoru a ulozime temp obrazky a nahradime src
                // Najdeme vsechny SRC obrazku z ukladaneho textu, ktere byly prave pridany (maji neprazdne data-tempfileid)
                preg_match_all('@<img[^>]*? src="([^"]*)"[^>]*? data-tempfileid="([^"]*?)"@', $content, $matches);

                // Pridame pole nalezenych (tempfileid => src)
                foreach ($matches[1] as $key => $src) {
                    $tempfileid = $matches[2][$key];

                    // Vytvorime tempfile model
                    $tempfile = ORM::factory('TempFile', $tempfileid);

                    // Vytvorime model obrazku
                    $img = ORM::factory('wysiwyg_image');
                    // Nastavime vazby
                    $img->reltype = $reltype;
                    $img->relid = $relid;
                    // Nacteme z temp modelu
                    $img->initByTempFile($tempfile);
                    // Ulozime
                    $img->save();

                    $replace_from = array(
                        // replace temp src with new one
                        'src="'.$src.'"',
                        // remove tempid
                        'data-tempfileid="'.$tempfileid.'"',
                    );
                    $replace_to = array(
                        'src="'.$img->getUrl().'"',
                        '',
                    );

                    // Nahradime ve virtual_value danou temp src za novou
                    $value = str_replace($replace_from, $replace_to, $content);

                    $this->setValue($value);
                }
                break;
        }

        // Az ted chceme ulozit virtual_value do DB
        parent::processFormEvent($type, $data);
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