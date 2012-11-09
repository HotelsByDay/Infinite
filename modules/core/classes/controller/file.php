<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tento kontroler zajistuje upload souboru na server.
 *
 * @author: Jiri Melichar
 */
class Controller_File extends Controller_Authentication
{
    /**
     * Tato akce slouzi k nahrani souboru na server a na vystup vraci
     * preview daneho souboru ve formatu JSON.
     *
     * Pokud je ve vystupu definovan klic 'error', tak doslo k chybe a tato
     * na danem klici je zprava pro uzivatele.
     *
     * Pokud je na vysptupu definovan klic 'out', tak doslo k uspesnemu
     * nahrani souboru a hodnotou klice 'out' je preview daneho souboru (sablona)
     *
     * @param <string> $config_key
     */
    public function action_upload($config_key)
    {
        //podle vstupnich dat nahraju driver pro praci s docasnym souborem
        if (isset($_GET['file']))
        {
            $file = new Upload_Driver_Xhr();
        }
        elseif (isset($_FILES['file']))
        {
            $file = new Upload_Driver_Form('file');
        }
        else
        {
            throw new Kohana_Exception('Unexpected data received - no driver available. [:ip][:user_agent]', array(
                ':ip' => arr::get($_SERVER, 'REMOTE_ADDR'),
                ':user_agent' => arr::get($_SERVER, 'HTTP_USER_AGENT')
            ));
        }

        //$config_key smeruje na konfiguraci formularoveho prvku, ktery zajistuje
        //odeslani souboru na tento kontroler. V jeho konfiguraci najdu potrebne
        //udaje pro dalsi zpracovani souboru
        $config = kohana::config($config_key);

        //spatny konfiguracni klic
        if ($config === FALSE)
        {
            throw new Kohana_Exception('Undefined config key. Unable to process uploaded files.');
        }

        //cilovy model pro soubor - obsahuje definici povolenych Mime-Typu, max velikost, apod.
        $target_model = $config['model'];

        //nazev atributu je retezec za poslednim znakem '.'
        $attr = substr(strrchr($config_key, '.'), 1);

        //View, ktere ma byt pouzito pro preview souboru
        $file_view_name = $config['file_view_name'];

        //nactu si nazev adresare pro docasne uploadovane soubory
        $temp_dir = DOCROOT.AppConfig::instance()->get('temp_dir', 'system');

        //vytvorim si instanci ciloveho modelu pro soubor
        $target_file = ORM::factory($target_model);

        //trida, ktera zajisti zapis souboru do temp adresare a zaroven i validaci
        $uploader = new FileUploader($file,
                                     $target_file::$allowed_mime_types,
                                     $target_file::$allowed_max_filesize,
                                     $target_file::$allowed_image_dimension);

        //do teto promenne budu vkladat vystup
        $out = array();

        //zapis souboru do temp adresare
        try
        {
            //pri uspesnem nahrani souboru vrati ORM model TempFile
            $temp_file = $uploader->saveUploadedFile($temp_dir);

            //zaznam pro docasny soubor je vytvoren - ted vytvorim instanci
            //ciloveho modelu a ten inicializuju docasnym souborem
            $target_file->initByTempFile($temp_file);
            
            //ted je potreba na vystup vlozit pozadovanou reprezentaci souboru
            $view = View::factory($file_view_name);
            
            //do sablony predam ORM, ktery reprezentuje soubor
            $view->file = $target_file;
            
            //nazev atributu - potreba aby po vlozeni do stranky a odeslani formulare
            //prisly data na spravny formularovy prvek
            $view->attr = $attr;

            // Sablona se muze pouzivat pro editaci vicejazycnych atributu - musime predat dalsi promenne
            // - seznam aktivnich locales - zde predame prazdne
            $view->active_locales = (array)arr::get($this->request->get_data(), 'active_locales', array());
            // Seznam prekladu vsech vicejazycnych atributu daneho souboru - zatim zadne hodnoty nemame
            $view->lang = array();

            //vlozim na vystup
            $out['file_preview'] = (string)$view;
        }
        //uzivatelska chyba - prekrocen max limit na velikost souboru, 
        //nepovoleny mime typ nebo neco podobneho
        catch (Upload_Exception_UserError $e)
        {
            $out['error'] = __('upload.error_message', array(
                                    ':message'  => $e->getMessage(),
                                    ':filename' => $file->getName()
                            ));
        }
        //interni chyba - neco se posralo pri nahravani souboru - nelze nahrat
        //duvod pro uzivatele neznamy
        catch (Upload_Exception_InternalError $e)
        {
            $out['error'] = __('upload.error_message', array(
                                    ':message'  => $e->getMessage(),
                                    ':filename' => $file->getName()
                            ));
        }

        //vystup
        $this->request->response = json_encode($out, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP );
    }


    public function action_direct_upload($config_key)
    {
        //podle vstupnich dat nahraju driver pro praci s docasnym souborem
        if (isset($_GET['file']))
        {
            $file = new Upload_Driver_Xhr();
        }
        elseif (isset($_FILES['file']))
        {
            $file = new Upload_Driver_Form('file');
        }
        else
        {
            throw new Kohana_Exception('Unexpected data received - no driver available. [:ip][:user_agent]', array(
                ':ip' => arr::get($_SERVER, 'REMOTE_ADDR'),
                ':user_agent' => arr::get($_SERVER, 'HTTP_USER_AGENT')
            ));
        }

        //$config_key smeruje na konfiguraci formularoveho prvku, ktery zajistuje
        //odeslani souboru na tento kontroler. V jeho konfiguraci najdu potrebne
        //udaje pro dalsi zpracovani souboru
        $config = kohana::config($config_key);

        //spatny konfiguracni klic
        if ($config === FALSE)
        {
            throw new Kohana_Exception('Undefined config key. Unable to process uploaded files.');
        }

        //cilovy model pro soubor - obsahuje definici povolenych Mime-Typu, max velikost, apod.
        $target_model = $config['model'];

        //nazev atributu je retezec za poslednim znakem '.'
//        $attr = substr(strrchr($config_key, '.'), 1);

        //View, ktere ma byt pouzito pro preview souboru
//        $file_view_name = $config['file_view_name'];

        //nactu si nazev adresare pro docasne uploadovane soubory
        $temp_dir = DOCROOT.AppConfig::instance()->get('temp_dir', 'system');

        //vytvorim si instanci ciloveho modelu pro soubor
        $target_file = ORM::factory($target_model);

        // Nactu request parametry - reltype/relid
        $target_file->values($this->request->get_data());

        //trida, ktera zajisti zapis souboru do temp adresare a zaroven i validaci
        $uploader = new FileUploader($file,
            $target_file::$allowed_mime_types,
            $target_file::$allowed_max_filesize,
            $target_file::$allowed_image_dimension);

        //do teto promenne budu vkladat vystup
        $out = array();

        //zapis souboru do temp adresare
        try
        {
            //pri uspesnem nahrani souboru vrati ORM model TempFile
            // - provadi validace a vyhazuje vyjimky
            $temp_file = $uploader->saveUploadedFile($temp_dir);

            //zaznam pro docasny soubor je vytvoren - ted vytvorim instanci
            //ciloveho modelu a ten inicializuju docasnym souborem
            $target_file->initByTempFile($temp_file);

            // A zaroven cilovy model ulozim
        //    $target_file->save();

            /*
            //ted je potreba na vystup vlozit pozadovanou reprezentaci souboru
            $view = View::factory($file_view_name);

            //do sablony predam ORM, ktery reprezentuje soubor
            $view->file = $target_file;

            //nazev atributu - potreba aby po vlozeni do stranky a odeslani formulare
            //prisly data na spravny formularovy prvek
            $view->attr = $attr;

            // Sablona se muze pouzivat pro editaci vicejazycnych atributu - musime predat dalsi promenne
            // - seznam aktivnich locales - zde predame prazdne
            $view->active_locales = (array)arr::get($this->request->get_data(), 'active_locales', array());
            // Seznam prekladu vsech vicejazycnych atributu daneho souboru - zatim zadne hodnoty nemame
            $view->lang = array();

            //vlozim na vystup
            $out['file_preview'] = (string)$view;
            */
            $out['filelink'] = $target_file->getUrl();
            $out['tempfileid'] = $temp_file->pk();

        }
            //uzivatelska chyba - prekrocen max limit na velikost souboru,
            //nepovoleny mime typ nebo neco podobneho
        catch (Upload_Exception_UserError $e)
        {
            $out['error'] = __('upload.error_message', array(
                ':message'  => $e->getMessage(),
                ':filename' => $file->getName()
            ));
        }
            //interni chyba - neco se posralo pri nahravani souboru - nelze nahrat
            //duvod pro uzivatele neznamy
        catch (Upload_Exception_InternalError $e)
        {
            $out['error'] = __('upload.error_message', array(
                ':message'  => $e->getMessage(),
                ':filename' => $file->getName(),
            ));
        }

        //vystup
        // @todo - nejak zahadne se mi na vystup pred vlastni json objekt dostaval znak '<' coz zpusobovalo
        //         nefunkcnost javascriptu (divne je ze jen pri drag & drop uploadu)
        //         ob_end_clean to vyresilo, ale ten znak muze delat problemy i jinde a chtelo by ho to odstranit
        ob_end_clean();
        $this->request->response = json_encode($out, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP );
    }




    /**
     * Tato akce slouzi k odstraneni libovolneho zaznamu v systemu.
     */
    public function action_delete()
    {
        //ziskam potrebne parametry pro identifikaci souboru z parametru pozadavku
        $object_name = Request::instance()->param('object_name');
        $item_id     = arr::get($_GET, 'id');

        //do teto promenne vlozim vystupni hodnotu - ta bude 'echovana' na vystup
        //ve forme JSONu
        $out = array();

        //pokusim se nacit dany soubor
        $model = ORM::factory($object_name, $item_id);

        try
        {
            //pokud neni zaznam nalezen, tak vracim chybu
            if ( ! $model->loaded())
            {
                throw new Kohana_Exception(__('delete.file.record_not_found'));
            }

            //pokud
            $model->delete();
        }
        catch (Exception $e)
        {
            $out['error'] = $e->getMessage();
        }

        //vystup
        $this->request->response = json_encode($out, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP );
    }
}