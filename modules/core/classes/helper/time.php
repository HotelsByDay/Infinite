<?php defined('SYSPATH') or die('No direct script access.');

class Helper_Time
{
    /**
     * Metoda prevede vstupni cas do casove zony daneho uzivatele a vraci
     * v pozadovanem formatu. Z tohoto objektu se ziska casova zona uzivatele.
     * @param <Model_User> $user Reference na objekt, ktery reprezentuje
     * uzivatele.
     * @param <string> $format Pozadovany vystupni format datumu
     * (format musi odpovidat tomu co akceptuje funkce date())
     * @param <string> $datetime Vstupni cas v casove zone systemu, ktery
     * bude preveden do casove zony uzivatele.
     * @return <string>
     */
    static public function toUserTZ($timezone, $format, $datetime = NULL)
    {
        //pokud neni datum a cas specifikovan tak se doplni aktualni
        //(v systemove casove zony)
        if ($datetime === NULL)
        {
            $datetime = date('Y-m-d H:i:s');
        }

        //vlozim datum, ktere je v defaultni casove zone systemu
        $new_date = new DateTime($datetime, new DateTimeZone(date_default_timezone_get()));

        //pokud je casova zona definovana chybne, tak bude vyhozena vyjimka
        //a pak se pouzije defaultni casova zona - tedy nebude se nic ve skutecnosti
        //prevadet
        try
        {
            //z oznaceni casove zony uzivatele se vytvori DateTimeZone objekt
            $user_time_zone_class = new DateTimeZone($timezone);
        }
        catch (Exception $e)
        {
            return date($format, strtotime($datetime));
        }

        //datum chci prevest do teto casove zony
        $new_date->setTimezone($user_time_zone_class);
        
        //vracim cas v pozadovanem formatu, ktery je v casove zone uzivatele
        return $new_date->format($format);
    }

    /**
     * Metoda prevede vstupni cas z casove zony uzivatele do casove zony systemu.
     * @param <Model_User> $user Reference na objekt, ktery reprezentuje
     * uzivatele. Z tohoto objektu se ziska casova zona uzivatele.
     * @param <string> $format Pozadovany vystupni format datumu
     * (format musi odpovidat tomu co akceptuje funkce date())
     * @param <string> $datetime Vstupni cas v casove zone systemu, ktery
     * bude preveden do casove zony uzivatele.
     * @return <string>
     */
    static public function toSystemTZ($timezone, $format, $datetime = NULL)
    {
        //pokud neni explicitne definovana hodnota $datetime, tak se bude vracet
        //aktualni systemovy cas - neni treba provade prevody mezi casovymy
        //zonamy
        if ($datetime === NULL)
        {
            return date($format);
        }

        //z oznaceni casove zony uzivatele se vytvori DateTimeZone objekt
        $user_time_zone_class = new DateTimeZone($timezone);

        //datum je v casove zone uzivatele
        $new_date = new DateTime($datetime, $user_time_zone_class);

        //prevede se do systemove casove zony
        $new_date->setTimezone(new DateTimeZone(date_default_timezone_get()));

        //vraci se v pozadovanem formatu
        return $new_date->format($format);
    }
}
