<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Zajistuje autentizaci uzivatele. Slouzi jako bazovy kontroler pro kontrolery
 * ktere jsou pristupne pouze prihlasenemu uzivateli.
 *
 * Kontroler kontroluje zda je uzivatel prihlasny a take muze kontrolovat zda
 * ma potrebne uzivatelske role. Pokud uzivatel neni prihlaseny tak dojde
 * k presmerovani na prihlasovaci obrazovku. Pokud uzivatel nema jednu z povinnych
 * uzivatelskych roli tak je presmerovan chod pozadavku ($this->request->action) na
 * akci tohoto kontroleru - action_unauthorized_access_detected, ktera zajisti
 * zobrazeni prislusne zpravy. Nedojde tedy k puvodnimu (pozadovanemu) zpracovani
 * pozadavku (napr. zobrazeni nejakeho zaznamu).
 *
 * @author: Jiri Melichar
 */
abstract class Controller_Base_Authentication extends Controller {

    /**
     * Vycet nazvu roli, ktere musi prihlaseny uzivateli mit prirazeny.
     * Pokud nema vsechny z techto roli tak jeho opravneni je povazovano za
     * nedostatecne.
     */
    protected $auth_required_role = array();



    /**
     * Zajistuje zavolani parent konstruktoru
     */
    public function before()
    {
        //Provede kontrolu zda je uzivatel prihlaseny
        if ( ! Auth::instance()->logged_in()) {
            return $this->redirectToLoginPage();
        }
        //kontrola zda ma uzivatel povoleni na tento kontroler
        if ( ! $this->IsAuthorized()) {
            $this->request->action = 'unauthorized_access_detected';
        }
        return parent::before();
    }


    /**
     * Presmeruje neautorizovane uzivatele na login page
     */
    protected function redirectToLoginPage()
    {
        //do sessny si ulozim adresu na kterou se snazil pristoupit
        //abych ho mohl po uspesnem prihlaseni na tuto adresu presmerovat
        //toto je zajisteno v login controlleru
        // Pokud jde o ajaxovy pozadavek, tak ulozime referrer
        if (Request::$is_ajax) {
            // @todo - overit ze toto bude vzdy fungovat spravne
            $url = Request::$referrer;
        } else {
            // Jinak ulozime pozadovanou url
            $url = Request::detect_uri();
        }
        Session::instance()->set('requested_url', $url);
        Session::instance()->set('flash_msg', __('system.automatic_logout'));
        //dochazi k presmerovani na login stranku
        $this->request->redirect(Appurl::login_page());
    }

    /**
     * Metoda provadi kontrolu podle standardnich pravidel zda ma uzivatel
     * opravneni pro pristup na tento kontroler.
     * @deprecated
     * @return <bool>
     */
    protected function IsAuthorized()
    {
        //pokud je na nejakem z dedicich kontroleru definovan atribut
        //$auth_required_role, ktery ma obsahovat nazvy uzivatelskych roli
        //tak zkontroluji zda je vsechny uzivatel ma
        if ( ! empty($this->auth_required_role)) {
            //ziskam referenci na uzivatele
            $user = Auth::instance()->get_user();
            //pokud uzivatel nema jednu z pozadovanych roli, tak presmeruju na stranku
            //
            foreach ($this->auth_required_role as $role_name) {
                if ( ! $user->HasRole($role_name)) {
                    //tato metoda zajisti presmerovani uzivatele pryc nebo vypsani
                    //ze nema pristup
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Metoda je vyvolana ve chvili kdy se neautorizovany uzivatel snazi dostat
     * do kontroleru ke kteremu nema podle svych uzivatelskych roli pristup.
     *
     * Do $this->request->response vlozi hlaseni, ktere uzivatele informuje o
     * nedostatecnem opravneni.
     *
     */
    protected function action_unauthorized_access_detected()
    {
        $this->template = 'unauthorized access';
    }
}