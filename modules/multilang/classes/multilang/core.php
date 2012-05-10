<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Description of core
 *
 * @author jirkamelichar
 */
class Multilang_Core
{
    /**
     * Metoda je prichycena na systemovou udalost system.ready a zajistuje
     * aktivovani jazykove mutace dle nastaveni uzivatele. Pokud uzivatel
     * nema explicitne nastaven pozadovany jazyk, tak se bere nastaveni
     * z config.ini.
     */
    static public function set_active_lang()
    {
        //nastaveni aktivniho jazyka
        if (Auth::instance()->logged_in())
        {
            I18n::lang(Auth::instance()->get_user()->getSetting('application.lang', AppConfig::instance()->get('lang', 'application')));
        }
    }

    /**
     * Zajistuje zobrazeni jazykoveho panelu ve strance.
     *
     * Metoda je prichycena na systemovou udalost system.layout_after_post.
     *
     * @param <array> $parameters Ocekava parametry spojene se systemovou
     * udalosti system.layout_after_post.
     */
    static public function attach_lang_panel($event_name, $parameters = array())
    {
        //na klici 'controller' je ocekavana reference na kontroler
        //zpracovavajici pozadavek
        if ( ! isset($parameters['controller']))
        {
            Kohana::$log->add(Kohana::ERROR, 'Unable to attach lang panel, because key "controller" is not defined in parameters.');
            return;
        }

        //vytahnu si referenci na kontroler, aby se s tim lepe pracovalo
        $controller = $parameters['controller'];

        //pokud neni definovana sablona do ktere se bude lang panel vkladat tak
        //metoda konci
        if ( ! isset($controller->template)
                || ! isset($controller->template->top_navigation))
        {
            return;
        }

        //nactu si z konfigurace prehled jazyku do nabidky
        $lang_list = kohana::config('multilang.lang_list');

        //aktivni jazyk - beru dle nastaveni uzivatele, jinak z nataveni v config.ini
        $lang = self::get_active_lang();

        //pripravim jazykovy panel (zvolim aktivni jazyk)
        $lang_panel = View::factory('multilang/lang_panel', array(
            'active_lang_code' => $lang,
            'lang_list'        => $lang_list,
        ));
        
        //sahnu do sablony a na systemovou kotvu doplnim jazykovy panel
        $controller->template->top_navigation->anchor_1 = $lang_panel;
    }

    /**
     * Metoda slouzi k ziskani kodu aktivniho jazyka pro daneho uzivatele.
     *
     * Pokud neni uzivatel prihlasen anebo nema explicitne jazyk vybran, tak
     * se vezme aktivni jazyk podle globalniho nastaveni v config.ini.
     *
     * @return <string> Vraci kod aktivniho jazyka
     */
    static protected function get_active_lang()
    {
        //globalne nastaveny defaultni jazyk systemu
        $fallback_lang = AppConfig::instance()->get('lang', 'application');

        //pokud je nejaky uzivatel prihlaseny
        if (Auth::instance()->logged_in())
        {
            //tak aktivni je jazyk dle jeho nastaveni, pokud nastaveni pro jazyk
            //neexistuje tak se bere defaultni 
            return Auth::instance()->get_user()->getSetting('application.lang', $fallback_lang);
        }

        return $fallback_lang;
    }
}
?>
