<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tento kontroler zajistuje prihlasovani a odhlasovani uzivatele v systemu.
 *
 *
 *
 * @author: Jiri Melichar
 */
class Controller_Base_Login extends Controller_Template {

    /**
     * Sablona, ktera bude nactena pro vygenerovani login stranky
     * @var <string>
     */
    public $template = 'login_page';

    /**
     * Kontroluje stav prihlaseni uzivatele.
     *
     */
    public function before()
    {
        parent::before();
        //Pokud je uzivatel prihlaseny, tak ani nevolam konstruktor rodice,
        //ale rovnou presmeruju na defaultni stranku systemu.
        // @todo - tohle je spatne - pokud je uzivatel prihlasen ale nema pristup na adresu kam smeruje sendUserAlong tak ho to hodi zpet na login
        // @todo   a nepodari se mu prihlasit dokud nezada rucne adresu /logout - coz neni idealni chovani.
        if (Auth::instance()->logged_in()) {
           //presmerovani uzivatele do systemu
           $this->sendUserAlong();
        }
    }

    /**
     * Tato akce slouzi k prihlaseni uzivatele. Prihlasovaci formular musi smerovat
     * prave na tuto akci.
     *
     * Ocekava v POST poli atribut 'username' a 'password'.
     */
    public function action_index()
    {
        //pokud jsou v POSTu prihlasovaci udaje uzivatele, tak
        if (isset($_POST['username']) && isset($_POST['password'])) {
            //vytahnu si prihlasovaci udaje
            $login    = $_POST['username'];
            $password = $_POST['password'];
            //dlouhodobe prihlaseni ?
            $remember = (bool)arr::getifset($_POST, 'remember', FALSE);

            if (Auth::instance()->login($login, $password, $remember)) {

                //prihlaseni uspesne
                //pokud je v session ulozena stranka na kterou se uzivatel snazil
                //dostat pres prihlasenim tak jej na tuto stranku presmeruji
                if (($requested_url = Session::instance()->get_once('requested_url', FALSE)) !== FALSE) {
                    $this->request->redirect($requested_url);
                    return;
                }

                //odstrani se klic ze session, ktery rika ze uzivatel provedl
                //alespon jeden neuspesny pokud o prihlaseni a je mu dovoleno
                //pristoupit na stranku pro resetovani hesla
                Session::instance()->delete('show_reset_password_option');
                //presmerovani uzivatele do systemu
                $this->sendUserAlong();

            } else {
                $this->loginFailed($remember);
            }
        }
        $this->template->flash_msg = Session::instance()->get_once('flash_msg', null);
    }


    protected function loginFailed($remember)
    { //Login se nezdaril, vypisu chybove hlaseni a zobrazi se
        //standardne prihlasovaci obrazovka
        $this->template->err_msg = __('invalid_login_or_password');

        //predam hodnotu parametru 'remember'
        $this->template->remember = $remember;

        //bude zobrazen odkaz pro pristup na stranku k resetovani hesla
        //a stranka bude uzivateli pristupna
        Session::instance()->set('show_reset_password_option', '1');
    }



    /**
     * Metoda presmeruje uzivatele 'do systemu'. Ucel metody spociva v tom
     * ze je volana z nekolika mist a pouze zde je definovana adresa na kterou
     * je standardne uzivatel presmerovan.
     */
    protected function sendUserAlong()
    {
        return $this->request->redirect(appurl::homepage());
    }

}

?>