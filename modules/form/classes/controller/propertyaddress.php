<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Tento kontroler funguje jako specialni backend pro naseptavac adresy na
 * formularovem prvku AppFormItemPropertyAddress. Mozna by bylo vhodnejsi
 * ho pojmenovat Controller_AppFormItem_PropertyAddress, aby bylo jasne ze patri
 * primo k formularovemu prvku, ale nejsem si jist ze je tato logika spravna.
 *
 * Adresy se berou z Google sluzeb a v pripade potreby se prevedou tak aby
 * odpovidali UIR-ADDR strukture se kterou alikace pracuje.
 *
 *
 */
class Controller_PropertyAddress extends Controller_Authentication {

    /**
     * Tato akce slouzi jako backend pro naseptavac adresy.
     *
     * Ocekava argument 'q' v poli _POST ktery pouziva jako argument dotazu
     * pro google api place autocomplete.
     *
     * Na vystup tiskne seznam polozek v podobe JSON kde 'id' obsahuje polozku
     * 'reference' (od google, slouzi jako jednoznacny identifikator lokality)
     * a 'value' obsahuje nazev polozky.
     * 
     */
    public function action_autocomplete()
    {
        //retezec, podle ktereho se bude vyhledavat

        $city = arr::get($_REQUEST, 'city', '');
        $postalcode = arr::get($_REQUEST, 'postalcode', '');
        $address = arr::get($_REQUEST, 'address', '');

        $q = trim("$address $postalcode $city");

        //pokud neprisel vyhledavaci retezec, tak se nebude vyhledavat
        if (empty($q))
        {
            return;
        }

        // jazyk precteme z configu
        $lang = i18n::$lang;
        //sestavim adresu pro dotaz
        $address = 'http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($q).'&sensor=false&language='.$lang;

        //stahnu obsah dane URL
        $page = cURL::get_contents($address);

        //odpoved je ve formaru JSON, druhy argument zajisti ze bude dekodovano do asoc. pole (namisto objektu)
        $response = json_decode($page, TRUE);

        // Odpoved, kterou zasleme prohlizeci
        $result = Array();

        if ($response['status'] == 'OK') {
            $result['success'] = 1;

            // Pokud je status OK, pak urcite mame alespon jeden vysledek
            $first_item = $response['results'][0];

            /*
            //zaklad polozky tvori gps vazby
            $gps_address = new GPSAddress($first_item['address_components']);

            //pri parovani Google adresy na UIR-ADDR muze dojit k chybe pokud
            //neni jednoznacne mozne urcite UIR-ADDR ekvivalent adresy
            try
            {
                $item = $gps_address->Resolve();
            }
            catch (Kohana_Exception $e)
            {
                kohana::$log->add(Kohana::ERROR, 'Unable to resolve UIR-ADDR address from Google address due to exception "'.$e->getMessage().'".');
                continue;
            }
            catch (Exception $e)
            {
                kohana::$log->add(Kohana::ERROR, 'Unable to resolve UIR-ADDR address from Google address due to error "'.$e->getMessage().'".');
                continue;
            }
            */

            //pridam nazev adresy
            // $result['value'] = $first_item['formatted_address'];

            //pridam i GPS souradnice
            $result['latitude']  = $first_item['geometry']['location']['lat'];
            $result['longitude'] = $first_item['geometry']['location']['lng'];

        } else {
            $result['success'] = 0;
        }

        // Odesleme vysledek
        $this->request->headers['Content-Type'] = 'application/json';
        $this->request->response = json_encode($result);
    }


    /**
     * Tato akce slouzi k ziskani detailni adresy lokality, ktera je definovana
     * hodnotou 'reference' (tohle Google pouziva jako jednoznacny identifikator
     * lokality/mista)
     * @return <type>
     */
    public function action_getplacedetails()
    {
        //retezec, podle ktereho se bude vyhledavat
        $reference = trim(arr::get(array_merge($_GET, $_POST), 'r', ''));

        //pokud neprisel vyhledavaci retezec, tak se nebude vyhledavat
        if (empty($reference))
        {
            return;
        }

        //pozadavek Place Autocomplete Request sestavim primo v kontroleru

        $key = AppConfig::instance()->get('maps_api_key', 'application');

        //Pozadovana adresa
        $address = "https://maps.googleapis.com/maps/api/place/details/json?reference=".urlencode($reference)."&sensor=false&language=cs&key=".$key;

        //stahnu obsah dane URL
        $page = cURL::get_contents($address);

        //odpoved je ve formaru JSON, druhy argument zajisti ze bude dekodovano do asoc. pole (namisto objektu)
        $response = json_decode($page, TRUE);

        //sablona predtavuje JSON objekt, ktery obsahuje potrebne polozky adresy
        $view = View::factory('address/place');

        //zaklad polozky tvori gps vazby
        $gps_address = new GPSAddress($response['result']['address_components']);

        //pri parovani Google adresy na UIR-ADDR muze dojit k chybe pokud
        //neni jednoznacne mozne urcite UIR-ADDR ekvivalent adresy
        try
        {

            $gps_item = $gps_address->Resolve();
        }
        catch (Kohana_Exception $e)
        {
            kohana::$log->add(Kohana::ERROR, 'Unable to resolve UIR-ADDR address from Google address due to exception "'.$e->getMessage().'".');
            return;
        }
        catch (Exception $e)
        {
            kohana::$log->add(Kohana::ERROR, 'Unable to resolve UIR-ADDR address from Google address due to error "'.$e->getMessage.'".');
            return;
        }

        //do odpovedi jeste pridam souradnice
        $gps_item['locality_latitude']  = $response['result']['geometry']['location']['lat'];
        $gps_item['locality_longitude'] = $response['result']['geometry']['location']['lng'];

        //pridam hodnotu 'reference'
        $gps_item['reference'] = $reference;

        //polozku vlozim do view
        $view->item = $gps_item;

        //tisknu na vystup
        echo $view;
    }
}