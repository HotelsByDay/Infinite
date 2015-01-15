<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tento kontroler se stara o spusteni cron udalosti.
 *
 */
class Controller_Cron extends Controller {

    /**
     * Provadi kontrolu IP adresy hosta, ktery psristupuje k tomuto kontroleru.
     * Pokud nema host jednu z povolenych IP adres, tak je proveden zapis
     * do logu, kde bude ulozena IP adresa hosta a beh aplikace je ukoncen
     * volanim funkce exit().
     */
    public function before()
    {
        //nactu si seznam IP adres ze kterych je mozne tento cron spustit
        $allowed_ip_list = kohana::config('cron.allowed_ip');

        //pokud seznam povolenych IP adres je prazdny, tak je pristup zakazan
        //pro vsechny
        if (empty($allowed_ip_list) || ! in_array($_SERVER['REMOTE_ADDR'], $allowed_ip_list)) {
            //zaloguju IP adresu hosta
            Kohana::$log->add(Kohana::ALERT, 'Controller_Cron - unauthorized access from "' . $_SERVER['REMOTE_ADDR'] . '". IP address check failed.');
            exit;
        }

        return parent::before();
    }
    /**
     * Spousti CRON udalosti na zaklade hodnoty jejich lastran atributu a
     * definovaneho intervalu.
     */
    public function action_index($type = NULL)
    {
        //nactu si seznam udalosti - jsou tam definovany intervaly jejich spusteni
        $events = kohana::config('cron.events');
        
        //pokud je explicitne definovan nazev cronu, ktery ma byt spusten
        //tak z konfigu vezmu jen prave ten
        if ( ! empty($type)) {
            $events = array_intersect_key($events, array_flip(array($type)));
        }

        //pokud z nejakeho duvodu nejsou definovane udalosti, tak nebude nic provedeno
        if (empty($events)) {
            Kohana::$log->add(Kohana::ERROR, 'No events to be ran. Check config file "cron" and key "events".');
            return;
        }
        //metoda najde vsechny crony, ktere je mozne spustit z daneho seznamu
        //$events
        $event_records = $this->selectRunnableCrons($events);

        //z DB mam vytazene pouze ty eventy, co se maji spoustet
        foreach ($event_records as $record) {
            //nazev cronu ('1d', '1h') - nazev udalosti je pak 'cron.1d',
            $cron_name = $record->name;
            //doplni korektni nazev udalosti, ktera ma byt spoustena
            $event_name = $this->getCronEventName($cron_name);

            //pokud cron neni mozne zamknout, tak jej nebudu provadet
            if ( ! $this->lockCron($cron_name)) {
                continue;
            }

            //zapisu si cas spusteni cronu
            $cron_start_time = Date('Y-m-d H:i:s');

            //zapisu do logu, ktera udalost ted bude spustena
            Kohana::$log->add(Kohana::INFO, 'Cron running event "'.$event_name.'". Current time: '.date('Y-m-d H:i:s', time()).'.');

            //pokud dojde k chybe tak se prerusi vykonavani udalosti a zaloguju
            //text vyjimky
            try{
                $data = NULL;
                //vyvola globalni udalost
                Dispatcher::instance()->trigger_event($event_name, Dispatcher::event());
            } catch (Exception $e) {
                Kohana::$log->add(Kohana::ERROR, 'Cron event "'.$event_name.'" stopped with exception: "'.$e->getMessage().'"');
            }
            //info zapis do logu, cron dobehl
            Kohana::$log->add(Kohana::INFO, 'Cron event "'.$event_name.'" finished. Current time: '.date('Y-m-d H:i:s', time()).'.');
            //cron odemykam, zaroven nastavim cas posledniho spusteni, ktere dobehlo
            //do konce
            $ret = $this->unlockCron($cron_name, $cron_start_time);
            //info zapis do logu
            Kohana::$log->add(Kohana::INFO, 'Cron event "'.$event_name.'" unlocked with retval: '.$ret.'. Current time: '.date('Y-m-d H:i:s', time()).'.');
        }
    }

    /**
     * Podle nazvu cronu generuje nazev udalosti na kterou jsou registrovany
     * callbacky.
     *
     * @param <string> $interval
     * @return <string>
     * @author Jiří Melichar
     */
    protected function getCronEventName($interval)
    {
        return 'cron.'.$interval;
    }

    /**
     * Provadi aktualizacei taublky cron na zaklade definice cron udalosti
     * v konfiguracnim souboru.
     * @return <type>
     */
    public function action_setup()
    {
        //projdu vsechny definovane udalosti a zalozim pro ne zaznamy pokud neexistuji
        $events = kohana::config('cron.events');
        if (empty($events)) {
            echo 'No events defined. Check config file "cron" and key "events".';
            return;
        }
        //mam eventy, pudu je zkontrolovat
        $db = Database::instance();
        //pocitadlo cron eventu, ktere jsem vlozil
        $count = 0;
        foreach ($events as $name => $interval) {
            //pokud neexistuje, zalozim
            $found = DB::select_count_records()->from('cron')
                                                       ->where('name', '=', $name)
                                                       ->execute()
                                                       ->count_records_value();

            if ($found == 0) {
                DB::insert('cron', array('name', 'lastran'))
                                  ->values(array($name, 0))
                                  ->execute();
                $count ++;
            }
        }
        //ted smazu ty eventy co nejsou v poli $events
        $deleted = DB::delete('cron')->where('name', 'NOT IN', array_keys($events))->execute();
        echo "Setup complete. $count events inserted into and $deleted deleted from rh_cron.";
        return;
    }

    /**
     * Metoda nastavuje priznak uzamknuti daneho cron jobu.
     * @param <string> $name Nazev cron (napriklad.: '1d', '1hh', ...)
     * @return <bool> Vraci true pokud doslo k prepnuti priznaku uzamknuti daneho cron.
     *                  Pokud byl priznak uz zapnuty, tak vraci false.
     */
    protected function lockCron($name) {
        $rows_effected = DB::update('cron')
                            ->set(array('locked' => '1'))
                            ->where('name', '=', $name)
                            ->where('locked', '=', '0')
                            ->execute();
        
        return (bool)$rows_effected;
    }

    /**
     * Metoda z DB vybere crony, ktere podle sveho intervalu a casu posledniho
     * spusteni by mely byt spusteny znovu. Kontroluje jen ty crony, ktere jsou
     * definovany v prvni argumentu ve tvaru
     * array(
     *      '1d' => 86400
     * )
     * @param <array> $cron_types
     * @return <array>
     */
    protected function selectRunnableCrons($cron_types)
    {
        //z tabulky cron si vytahnu vsechny crony, ktere byly naposledy spustene
        //pred minimalne definovanym intervalem
        $q = DB::Select()->from('cron');
        //z DB vytahnu pouze ty crony, ktere by mely byt v tuto chvili spusteny
        foreach ($cron_types as $name => $interval) {
            $q->or_where_open()
                    ->where(DB::expr('UNIX_TIMESTAMP(`lastran`)'), '<', (time() - $interval))
                    ->where('name', '=', $name)
              ->where_close();
        }
        //SQL dotaz spustim
        $results = $q->as_object()->execute();
        //a vysledek vracim
        return $results;
    }

    /**
     * Metoda resetuje priznak uzamknuti cronu definovaneho jeho nazvem.
     * @param <string> $name Nazev cron (napriklad.: '1d', '1hh', ...)
     * @param <string> $datetime Datum a cas posledniho spusteni cronu, zapise se
     * pouze pokud je definovano.
     * @return <bool> True pokud bylo odemknuto, jinak false.
     */
    protected function unlockCron($name, $datetime = NULL)
    {
        //pripravim data pro update
        $data = array('locked' => '0');
        //pokud je definovan cas posledniho spusteni tak ho zapisu
        if ( ! empty($datetime)) {
            $data['lastran'] = $datetime;
        }
        
        //provedu upravu, vracim true pokud bylo odemknuto, jinak false
        return (bool)DB::update('cron')
                    ->set($data)
                    ->where('name', '=', $name)
                    ->execute();
    }

} // End Cron Controller