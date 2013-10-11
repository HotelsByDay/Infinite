<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vlozeni adresy.
 */
class AppFormItem_Gps extends AppFormItem_Base
{

    //tento prvek je virtualni
    protected $virtual = TRUE;

    //nazev sablony pro vykresleni tohoto prvku
    protected $view_name = 'appformitem/gps';

    // default config
    protected $config = Array(
        'latitude_column' => 'latitude',
        'longitude_column' => 'longitude',
        'zoom' => 15,
        'required' => false,
    );

    /**
     * Zajistuje vlozeni potrebnych jquery pluginu do stranky
     */
    public function init()
    {
        // plugin zajistujici funkcionalitu prveku AppFormItemAdvertAddress
        Web::instance()
            ->addCustomJSFile(View::factory('js/jquery.AppFormItemGPS.js'))
        ;

        // provede inicializaci pro tuto specifickou instanci
        $js_file = View::factory('js/jquery.AppFormItemGPS-init.js');
        $config = Array(
            // Predame jquery pluginu informaci o tom, zda prihlaseny uzivatel ma mit cast pravku readonly
            'readonly' => arr::get($this->config, 'readonly', 0),

            // Predame nastaveni zoomu
            'zoom' => $this->config['zoom'],
        );


        $js_file->config = $config;
        //vlozim do stranky
        parent::addInitJS($js_file);

        $retval = parent::init();

        $this->assignValue();

        return $retval;
    }


    public function getHandledErrorMessagesKeys()
    {
        return array($this->config['latitude_column'], $this->config['longitude_column']);
    }



    public function getLatitude()
    {
        $latitude_column = $this->config['latitude_column'];
        return $this->model->{$latitude_column};
    }
    public function getLongitude()
    {
        $longitude_column = $this->config['longitude_column'];
        return $this->model->{$longitude_column};
    }

    /**
     * Metoda priradi hodnotu z formulare do ORM modelu a pokud z formnulare hodnota
     * neprisla (stranka se pouze generuje, nedoslo ke kliknuti na ulozit) tak vezme
     * data z ORM a vlozi je do dat.
     */
    public function assignValue()
    {
        //z formulare neprisly zadna data, tak $this->form_data naplnim podle
        //aktualniho ORM modelu
        if ($this->form_data == NULL)
        {
            $this->form_data['latitude']  = $this->getLatitude();
            $this->form_data['longitude'] = $this->getLongitude();
        }
        else
        {
            //z formulare prisly data - zapisu je do ORM modelu
            foreach ($this->form_data as $attr => $value)
            {
                //do modelu chci vlozit pouze nektere polozky
                if (in_array($attr, array('latitude', 'longitude')))
                {
                    $this->model->{$attr} = trim($value);
                }
            }
        }
        return parent::AssignValue();
    }



    public function  Render($render_style = NULL, $error_message = NULL) {

        $view = parent::Render($render_style, $error_message);
        $lat_label = __('appformitemgps.latitude');
        $lon_label = __('appformitemgps.longitude');

        if ($this->isRequired()) {
            $lat_label .= '<span class="required_label"></span>';
            $lon_label .= '<span class="required_label"></span>';
        }

        $view->lat_label = $lat_label;
        $view->lon_label = $lon_label;
        return $view;
    }
}