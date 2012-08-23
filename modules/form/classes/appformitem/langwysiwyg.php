<?php defined('SYSPATH') or die('No direct script access.');

class AppFormItem_LangWysiwyg extends AppFormItem_LangString
{
    //Nazev sablony pro tento formularovy prvek
    protected $view_name = 'appformitem/langwysiwyg';

    protected $config = Array(
        'images_upload' => true,
    );

    public function init()
    {
        // Pripojime JS soubor s pluginem
        Web::instance()->addCustomJSFile(View::factory('js/jquery.AppFormItemLangWysiwyg.js'));

        // A jeho inicializaci
        $init_js = View::factory('js/jquery.AppFormItemLangWysiwyg-init.js');

        // Poskladame config prvku
        $config = Array(
            // Predame seznam jazyku do pluginu
            //      'locales' => $this->locales,
            'locales_count' => count($this->locales),
            'mode'          => $this->mode,
        );

        // Pokud je povolen uplaod obrazku tak predame url na upload controller a dalsi parametry
        if ($this->config['images_upload']) {
            $get_params = Array(
                'reltype' => $this->getImageRelType(),
                'relid'   => $this->model->pk(),
            );
            $config['images_upload'] = AppUrl::directupload_file_action('wysiwyg.images_upload', $get_params);
        }


        $init_js->config = $config;

        //prida do sablony identifikator tohoto prvku a zajisti vlozeni do stranky
        parent::addInitJS($init_js);

        $this->assignValue();

        return;
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
            case AppForm::FORM_EVENT_AFTER_SAVE:

                // Data prijata z formulare upravena na asociativni tvar
                // - v parentu je jiz zajisteno jejich ulozeni do DB
                //   zde pouze zajistime preulozeni temp obrazku (prave pridanych pres wysiwyg)
                $form_data = $this->virtual_value;

                // Odkazovane url
                $used_urls = array();

                // Projdeme zaznamy co zustaly ve $form_data najdeme URL vsech obrazku na ktere se odkazuji
                foreach ($form_data as $locale => $content)
                {
                    // Najdeme vsechny SRC obrazku z ukladaneho textu
                    preg_match_all('@<img.*? src="(.*?)"@', $content, $matches);

                //    Kohana::$log->add(Kohana::INFO, 'matches[1] for '.$this->attr.'.'.$locale.': '.json_encode($matches[1]));

                    // Pridame pole nalezenych URL v danem prekladu do celkoveho pole
                    $used_urls = array_merge($used_urls, $matches[1]);
                }

            //    Kohana::$log->add(Kohana::INFO, 'used_urls for '.$this->attr.': '.json_encode($used_urls));

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

                // Projdeme texty z jednotlivych editoru a ulozime temp obrazky a nahradime src
                foreach ($form_data as $locale => $content)
                {
                    // Najdeme vsechny SRC obrazku z ukladaneho textu
                    preg_match_all('@<img.*? src="(.*?)".*? data-tempfileid="(.*?)"@', $content, $matches);

                //    Kohana::$log->add(Kohana::INFO, 'matches[1] for '.$this->attr.'.'.$locale.': '.json_encode($matches[1]));

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
                        $this->virtual_value[$locale] = str_replace($replace_from, $replace_to, $content);
                    }
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