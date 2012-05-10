<?php defined('SYSPATH') or die('No direct script access.');

class Request extends Kohana_Request {

    /**
     * Vraci kompletni URL aktualniho hlavniho pozadavku vcetne GET argumentu,
     * kterou bere primo z $_SERVER['REQUEST_URI'].
     *
     * Tato metoda se pouziva pri generovani return_linku - tam potrebuji
     * komplet aktualni URL a nevim kde jinde ji ziskat.
     * 
     * @return <string>
     */
    public function current_url()
    {
        return arr::getifset($_SERVER, 'REQUEST_URI', NULL);
    }

    /**
     * Z parametru pozadavku vytahne data a ty vraci.
     * @return <array>
     */
    public function get_data()
    {
        $requested_data = $this->get_request_data();

        unset($requested_data[appurl::RETLINK_URL_KEY]);

        return (array)$requested_data;
    }

    /**
     * Z parametru pozadavku vytahne navratovy odkaz a ten vraci.
     * @return <string> Vraci navratovy odkaz. Pokud neni definovany tak NULL.
     */
    public function get_retlink()
    {
        $request_data = $this->get_request_data();

        return arr::getifset($request_data, appurl::RETLINK_URL_KEY, NULL);
    }

    /**
     * Z parametru pozadavku vytahne popisek pro navratovy odkaz a ten vraci.
     * @return <string> Vraci navratovy odkaz. Pokud neni definovany tak NULL.
     */
    public function get_retlink_label()
    {
        $request_data = $this->get_request_data();

        return arr::getifset($request_data, appurl::RETLINK_URL_LABEL, NULL);
    }

    /**
     * Vraci aktualni parametry pozadavku.
     * Pokud jsou v zakodovane podobe tak zajisti dekodovani.
     * 
     * @return <array>
     */
    protected function get_request_data()
    {
        //klic na kterem muze byt ulozen kod, ktery reprezentuje data
        $url_data_key = appurl::ENCODED_PACK_KEY;

        //data beru z POST i GET - prioritu ma POST (klic z POST prepise stejny klic v GETu)
        $request_data = array_merge($_GET, $_POST);

        //pokud je v _POST nebo _GET definovany specialni klic,
        //tak obsahuje kod, ktery reprezentuje data, ktera jsou ulozena
        //v DB pomoci tridy Encoder
        if (isset($request_data[$url_data_key]))
        {
            //Encoder mi pro dany kod vrati data
            $decoded_data = Encoder::instance()->decode($request_data[$url_data_key]);

            //request data merguju s daty, ktere jsem ziskal z Encoderu
            $request_data = arr::merge((array)$decoded_data, $request_data);
        }

        return $request_data;
    }



}
