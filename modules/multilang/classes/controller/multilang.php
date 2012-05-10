<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Kontroler slouzi k nastaveni aktivniho jazyka pro prihlaseneho uzivatele.
 */
class Controller_Multilang extends Controller_Authentication
{
    /**
     * Akce zapise do nastaveni uzivatele aktivni jazyk a provede presmerovani
     * na stranku, ktera je definovana na klici 'r' v GET nebo POST parametrech.
     *
     */
    public function action_set()
    {
        //pozadovany jazyk ulozim do nastaveni uzivatele pouze v pripade
        //ze takovy jazyk je definovan v konfiguraci
        $lang_list = kohana::config('multilang.lang_list');

        //parametrey pozadavku akceptuji v GET i POST
        $request_params = array_merge($_GET, $_POST);

        //kod pozadovaneho jazyka
        $lang_code = arr::get($request_params, 'l', NULL);

        //kontrola zda je jazyk s danym kodem definovan
        if ( ! isset($lang_list[$lang_code]))
        {
            Kohana::$log->add(Kohana::ERROR, 'Requested lang "'.$lang_code.'" is not defined in multilang.lang_list. It will not be saved to user settings.');
        }
        else
        {
            //ulozim do nastaveni uzivatele
            Auth::instance()->get_user()->setSetting('application.lang', $lang_code)->save();
        }

        //pokud je v parametrech definovana URL pro presmerovani uzivatele, tak
        //jej tam presmeruju, jinak na homepage
        $redirect_to = arr::get($request_params, 'r', appurl::homepage());

        //odesilam uzivatele na danou url
        Request::instance()->redirect($redirect_to);
    }
}