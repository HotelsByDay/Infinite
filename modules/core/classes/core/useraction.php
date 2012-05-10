<?php

/**
 * Třída implementující logovani aktivity uzivatelů
 *
 */
class Core_UserAction {
    /**
     * Definuje klic v SESSION, na kterem bude ulozen timestamp ktery definuje
     * posledni kontrolu aktivity uzivatele. Nasledujici kontrola bude nejdrive
     * provedena az po uplynuti definovaneho intervalu. Timto setrime zatez
     * serveru.
     */
    const SESSION_LAST_CHECK_KEY = 'last_check';

    const SESSION_CUR_ACTIVITY_ID_KEY = 'current_activity';

    /**
     * Defaultni hodnoty konfigurace.
     * @var <array>
     */
    protected $config = array();

    /**
     * Singleton design pattern
     */
    protected function __clone() {

    }

    /**
     * Konstruktor třídy
     */
    public function __construct() {
        //nacte konfiguraci pro tuto knihovnu
        $this->config = kohana::config('useraction');
    }

    /**
     * Provedení logování aktivity, zápis do DB.
     */
    public function process()
    {
        //aktualni cas
        $now = time();

        //kdy byla zapsana posledni aktivita
        $last_activity = Session::instance()->get(USERACTION_LASTACTIVITY_KEY);

        //USERID aktualniho uzivatele
        $user_id = Auth::instance()->get_user()->pk();
        $session_id = Session::instance()->id();

        //pokud neni v SESSION zadna aktivita, nebo uz ubehl dany casovy internal
        //tak se bude vytvaret nova aktivita
        if (empty($last_activity) || $now - $last_activity > $this->config['new_interval'])
        {
            //vytvoreni nove aktivity
            $activity = $this->newActivity($user_id, $session_id);

            //do session zapisu novy activity_id - je to pouze z duvodu aby bylo
            //snadnejsi vyhledani aktivity v DB - nebudu hledat nejnovejsi ale
            //primo podle PK
            Session::instance()->set(self::SESSION_CUR_ACTIVITY_ID_KEY, $activity->pk());

            //do session se vlozi aktualni cas - dalsi kontrola aktivity uzivatele
            //a tedy DB dotazy budou nejdrive za ACTIVITY_UPDATE_LIMIT sekund
            Session::instance()->set(self::SESSION_LAST_CHECK_KEY, $now);
        }
        //pokud uz ubehl interval pro minimalni casovou mezeru mezi kontrolami aktivity
        else if ($now - $last_activity >  $this->config['update_interval'])
        {
            //ID aktivity se kterou se aktualne pracuje
            $activity_id = Session::instance()->get(self::SESSION_CUR_ACTIVITY_ID_KEY);

            //najdu si posledni aktivitu pro daneho uzivatele v teto SESSION
            $activity = ORM::factory('user_activity', $activity_id)->find();

            //SESSION je aktivni - podle hodnoty 'activity_id' doslo k zapisu activity
            //zaznamu ale ten nebyl nalezen v DB - doslo k nezname chybe
            if ( ! $activity->loaded())
            {
                //provedu zapis do logu
                kohana::$log->write(Kohana::ERROR, 'Last activity record was not found in an active ongoing session.');

                //a vytvorim novou aktivitu
                $activity = $this->newActivity($user_id, $session_id);

                //do session zapisu novy activity_id - je to pouze z duvodu aby bylo
                //snadnejsi vyhledani aktivity v DB
                Session::instance()->set(self::SESSION_CUR_ACTIVITY_ID_KEY, $activity->pk());
            }

            //aktualizace doby trvani aktivity
            $activity->to = $now;
            $activity->save();

            //do session se vlozi aktualni cas - dalsi kontrola aktivity uzivatele
            //a tedy DB dotazy budou nejdrive za ACTIVITY_UPDATE_LIMIT sekund
            Session::instance()->set(self::SESSION_LAST_CHECK_KEY, $now);
        }






        // příprava proměnných
        $userid = Auth::instance()->get_user()->pk();
        $ipaddr = arr::get($_SERVER, 'REMOTE_ADDR');
        $ipbin = Format::ip2bin($ipaddr);
        $useragent = arr::get($_SERVER, 'HTTP_USER_AGENT');
        $timenow = time();
        $mintime = $timenow - $this->config["time"];

        // data ze session
        $session_last_update = Session::instance()->get_once("last_update");
        $user_activityid = Session::instance()->get("user_activityid");

        // proměnné pro podmínky
        $session_new_session = $user_activityid == NULL;
        $save_new_activity = false;

        // žádné id aktivity -> první aktivita v rámci session
        if ($session_new_session)
            $session_last_update = $timenow;

        // uložení aktuálního času do session
        Session::instance()->set("last_update", $timenow);
        // test na vypršení timeoutu pro session
        $session_last_update_ok = ($timenow - $this->config["session_time"]) <= $session_last_update;

        // nové přihlášení, zkontrolujeme IP, zda je uložena v BD
        $ip = "";
        if ($session_new_session) {
            // ověření zda je stávající IP již uložena v DB pro daného uživatele
            $ip = ORM::factory('user_ipaddress')
                            ->where('userid', '=', $userid)
                            ->where('ip', '=', $ipbin)
                            ->find();

            // ip v db již existuje
            if ($ip->loaded()) {
                $ipid = $ip->pk();
            }

            // ip není v DB, uložíme nový záznam
            /**
             * Pozn.:
             * Jelikož je toto uložení nové IP imlementováno zde, po kontole nové session,
             * pokud během testování během aktivní session smažeme IP adresu z DB, nebude kód fungovat
             * jak má. Proto při testování po smazání IP je třeba se znovu přihlásit.
             */ else {
                // instance IPinfoDB pro zjisteni lokace IP
                $objip = IPinfoDB::factory($ipaddr);

                // uložení nové IP do DB
                $newip = ORM::factory('user_ipaddress');
                $newip->userid = $userid;
                $newip->ip = $ipbin;
                $newip->status = $this->config["statuses"]["new"];
                // vlastnosti získané z ipinfo db
                $newip->country = $objip->getCountryCode();
                $newip->city = $objip->getCityName();
                // uložení
                $newip->save();
                // id uložené ip, zároveň ip pro uložení nové aktivity
                $ipid = $newip->pk();
            }

            // budeme ukladat novou aktivitu - viz níže
            $save_new_activity = true;
        } // if($session_new_session)

        /**
         * Session není nové, ale
         * doba mezi jednotlivými událostmi je větší, než limit
         * - kontrola v session, teď musíme ověřit v DB
         */ elseif (!$session_last_update_ok) {

            // vyfiltrování poslední aktivity, která není staší než určená doba a shoduje se useragent a user_activityid
            $a = ORM::factory('user_activity')
                            ->where('userid', '=', $userid)
                            ->where('useragent', '=', $useragent)
                            ->where('user_activityid', '=', $user_activityid)
                            ->where('to', '>=', date('Y-m-d H:i:s', $mintime))
                            ->order_by('to', 'desc')
                            ->find();

            // Odpovídající aktivita nalezena, upraví se čas
            if ($a->loaded()) {
                $a->to = $timenow;
                $a->save();
            }
            // Žádná aktivita neodpovídá, uložíme novou
            else {
                // budeme ukladat novou aktivitu - viz níže
                $save_new_activity = true;

                $ip = ORM::factory('user_ipaddress')
                                ->where('userid', '=', $userid)
                                ->where('ip', '=', $ipbin)
                                ->find();

                $ipid = $ip->user_ipaddressid;
            }
        } // if( ! $session_last_update_ok)
        // je třeba uložit novou aktivitu
        if ($save_new_activity) {
            // uložení nové aktivity
            $a = ORM::factory('user_activity');
            $a->userid = $userid;
            $a->user_ipaddressid = $ipid;
            $a->to = date('Y-m-d H:i:s', $timenow);
            $a->useragent = $useragent;
            $a->save();

            // zápis nového id aktivity do session
            Session::instance()->set("user_activityid", $a->pk());
        } // if($save_new_activity)
    }

// process()
}