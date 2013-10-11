<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Formularovy prvek pro vlozeni adresy.
 */
class AppFormItem_PropertyAddress extends AppFormItem_Base
{

    //tento prvek je virtualni
    protected $virtual = TRUE;

    //nazev sablony pro vykresleni tohoto prvku
    protected $view_name = 'appformitem/propertyaddress';

    // Zde je mozne snadno zmenit property location na jiny objekt
    // ale musi mit stejne atributy a autocomplete cb item objektu musi mit
    // pro spravnou funkcnost stejne polozky
    protected $location_object = 'property_location';

    // default config
    protected $config = Array(
        'required' => false,
        'address_zoom' => 17,
        'location_zoom' => 15,
    );

    /**
     * Zajistuje vlozeni potrebnych jquery pluginu do stranky
     */
    public function init()
    {
        // plugin zajistujici funkcionalitu prveku AppFormItemAdvertAddress
        Web::instance()
            ->addCustomJSFile(View::factory('js/jquery.AppFormItemPropertyAddress.js'))
            ->addCustomJSFile(View::factory('js/jquery.AppFormItemRelSelect.js'))
        ;

        // provede inicializaci pro tuto specifickou instanci
        $js_file = View::factory('js/jquery.AppFormItemPropertyAddress-init.js');
        $config = Array(
            //vlozum URL na akci ktera zajistuje Autocomplete a akci ktera zajistuje
            //zjisteni informaci o miste
            'property_address_url' => url::base().'propertyaddress/autocomplete',

            // Autocomplete pro property_location
            'property_location_url' => AppUrl::object_cb_data($this->location_object),

            // Predame jquery pluginu informaci o tom, zda prihlaseny uzivatel ma mit cast pravku readonly
            'readonly' => $this->isReadonly(),

            // Predame nastaveni zoomu
            'location_zoom' => $this->config['location_zoom'],
            'address_zoom' => $this->config['address_zoom'],
            'gps_ok'       => (int)$this->model->gps_ok,
        );


        $js_file->config = $config;
        //vlozim do stranky
        parent::addInitJS($js_file);

        $retval = parent::init();

        $this->assignValue();

        return $retval;
    }


    /**
     * Validace hodnot prvku
     * @return string
     */
    public function check()
    {
        Kohana::$log->add(Kohana::INFO, 'check called in propertyAddress ');
        if ($this->config['required']) {
            Kohana::$log->add(Kohana::INFO, 'item is required propertyAddress ');
            if ($this->isReadonly()) {
                // V readonly rezimu kontrolujeme jen adresu
                $key = 'address';
                $value = arr::get($this->form_data, $key, NULL);
                if (empty($value)) {
                    return __($this->model->table_name().'.'.$key.'.validation.required');
                }
            }
            else {

                Kohana::$log->add(Kohana::INFO, 'check else in propertyAddress ');
                // Jinak kontrolujeme vice prvku
                foreach (array('value') as $key) {
                    $value = arr::get($this->form_data, $key, NULL);
                    if (empty($value)) {
                        if ($key == 'value') $key = 'property_locationid';
                        Kohana::$log->add(Kohana::INFO, 'validation_error in propertyAddress on '.$key);
                        return __($this->model->table_name().'.'.$key.'.validation.required');
                    }
                }
            }
        }
        return NULL;
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
            $this->form_data['address'] = $this->model->address;
            $this->form_data['latitude']  = $this->model->latitude;
            $this->form_data['longitude'] = $this->model->longitude;
            $this->form_data['google_latitude'] = $this->model->google_latitude;
            $this->form_data['google_longitude'] = $this->model->google_longitude;
            $this->form_data['arn_latitude'] = $this->model->arn_latitude;
            $this->form_data['arn_longitude'] = $this->model->arn_longitude;
            $this->form_data['gps_ok'] = $this->model->gps_ok;
            // $this->form_data['city'] = $this->model->city;
            $this->form_data['postal_code'] = $this->model->postal_code;

            // Konvence nazvu podle RelSelect prvku
            $this->form_data['value'] = $this->model->{$this->location_object.'id'};
            $this->form_data['name'] = $this->model->{$this->location_object}->preview();

            // Location indexed CSC, ktere se pouzije v dotazu na google api
            $this->form_data['property_location_indexed_csc'] = $this->model->{$this->location_object}->indexed_csc;
        }
        else
        {
            //z formulare prisly data - zapisu je do ORM modelu
            foreach ($this->form_data as $attr => $value)
            {
                //do modelu chci vlozit pouze nektere polozky
                if (in_array($attr, array('address', 'latitude', 'longitude', 'postal_code', 'gps_ok')))
                {
                    $this->model->{$attr} = trim($value);
                }
            }
            // value zapiseme do property_locationid
            $locationid = arr::get($this->form_data, 'value');
            $this->model->{$this->location_object.'id'} = empty($locationid) ? NULL : $locationid;
        }

        return parent::AssignValue();
    }



    /**
     * @return bool Zda ma byt prvek pro aktualniho uzivatele zobrazen v readonly rezimu (castecne).
     */
    protected function isReadonly()
    {
        return ! Auth::instance()->get_user()->IsAdmin() && Auth::instance()->get_user()->HasRole('accountmanager');
    }



    public function  Render($render_style = NULL, $error_message = NULL) {

        $view = parent::Render($render_style, $error_message);
        $view->readonly = $this->isReadonly();
        return $view;
    }
}