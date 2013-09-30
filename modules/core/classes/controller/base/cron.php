<?php defined('SYSPATH') OR die('No direct access allowed.');

class Controller_Base_Cron extends Controller
{
    /**
     * Provadi odeslani e-mailu, ktere jsou ve fronte.
     */
    public function action_email()
    {
        //kvuli priloham musim prihlasit uzivatele, ktery ma opravneni je precist
        Auth::instance()->force_login('root');

        Emailq::factory()->send_emails();
    }

    /**
     * Provadi kompilaci JS souboru v cache pomoci Google Closure Compiler.
     */
    public function action_js()
    {
        Compiler::instance()->compile();
    }

    /**
     * Provadi procisteni temp adresare.
     */
    public function action_cleantmp()
    {
        //procisteni temp adresare
        file::cleanTempDir();
        
        //procisti temp DB tabulku
        ORM::factory('tempfile')->cleanTempTable();
    }
}