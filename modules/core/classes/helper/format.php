<?php defined('SYSPATH') OR die('No direct access allowed.');

class Helper_Format {


    /**
     * @static
     * @param $fname
     * @param $width
     * @param $height
     * @return string
     */
    static public function imageExactResizeVariantName($fname, $width, $height)
    {
        $filename = pathinfo($fname, PATHINFO_FILENAME);
        $ext = pathinfo($fname, PATHINFO_EXTENSION);
        return $filename. '_' .$width. 'x' .$height. '.' .$ext;
    }



    static public function absoluteUrl($url)
    {
        return (strpos($url, 'http') !== 0) ? 'http://'.$url : $url;
    }

    /**
     * Nahradi alfanumericke znaky za hvezdicky
     * @static
     * @param $string
     */
    static public function starOut($string)
    {
        return preg_replace('/[^_ @#?!-]/', '*', $string);
    }

    /**
     * Formatuje cislo jako telefoni cislo ve formatu platnem pro CR.
     * @param <string> $number Cislo k naformatovani
     * @return <string> Naformatovane cislo. Pokud cislo nema delku 9 nebo 13 znaku
     * tak je vraceno bez formatovani.
     */
    static public function phone($number)
    {
        //uschovam si original abych ho mohl vratit pokud nebudu formatovat
        $orig_number = $number;
        
        $number = str_replace(' ', '', $number);

        //osetrim znak '+' na zacatku
        $number = trim($number, '+');

        //podle delky cisla zvolim formatovani
        if (strlen($number) == 9)
        {
            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{3})/", "$1 $2 $3", $number);
        }
        else if (strlen($number) == 12)
        {
            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{3})/", "+$1 $2 $3 $4", $number);
        }

        return $orig_number;
    }

    static public function phone_us($number)
    {
        //return $number;
        $number = preg_replace('/[^0-9]/', '', $number);
        $number = preg_replace('/^(.*?)(.{1,3})(.{3})(.{4})$/', '$1 ($2) $3-$4', $number);
        return $number;
    }
    
    
    /**
     * Vraci naformatovany seznam cisel jako jeden retezec
     * na kazde cislo je volana funkce phone()
     * cisla jsou ve vyslednem retezci oddelena carkou
     * @param <array> $numbers seznam cisel
     * @return <string> formatovany retezec vsech cisel 
     */
    static public function phoneNumbers($numbers) 
    {
        foreach ($numbers as $i => $number)
        {
            $numbers[$i] = format::phone($number);
        }

        return implode(", ", $numbers);
    }

    
    /**
     * Formatuje zip kod podle formatu, ktery je platny pro CR.
     * @param <string> $zip
     * @return <string> Pokud cislo nema delku 5 znaku, tak vraci zadanou hodnotu
     * bez formatovani.
     */
    static public function zipcode($zip)
    {
        //originalni hodnotu si necham pro pripat ze nebudu formatovat
        $orig_zip = $zip;
        
        $zip = str_replace(' ', '', $zip);
        //podle delky cisla zvolim formatovani
        if (strlen($zip) == 5)
        {
            return preg_replace("/([0-9]{3})([0-9]{2})/", "$1 $2", $zip);
        }
        return $orig_zip;
    }

    /**
     * Metoda bere datum v ceskem formatu a vraci v MySQL formatu.
     * @param <string> $date
     * @return <string>
     */
    static public function mysqlDate($date)
    {
        if (preg_match('/([0-9]{1,2})\. ?([0-9]{1,2})\. ?([0-9]{4})/', $date, $match))
        {
            if (count($match) == 4)
            {
                return date('Y-m-d', strtotime($match[3].'-'.$match[2].'-'.$match[1]));
            }
        }
        //pokud se datum nechytlo na regular, tak to je pro me neznamy format a
        //vracim v puvodnim tvaru
        return $date;
    }
    
    
    /**
     * Metoda bere datum v ceskem formatu a vraci v MySQL formatu.
     * @param <string> $date
     * @return <bool|string>
     */
    static public function mysqlDateTime($datetime)
    {
        $datetime = trim($datetime);

        if (preg_match('/^([0-9]{1,2})\. ?([0-9]{1,2})\. ?([0-9]{4})$/', $datetime, $match))
        {
            return date('Y-m-d', strtotime($match[3].'-'.$match[2].'-'.$match[1]));
        }
        else if (preg_match('/^([0-9]{1,2})\. ?([0-9]{1,2})\. ?([0-9]{4}) ?(([0]{1,2}:[0]{1,2})|((2[0-3]|1[0-9]|(0|)[1-9]|0|00):([0-5][0-9]|60)(:[0-5][0-9]|60)?))$/', $datetime, $match))
        {
            if (count($match) >= 4)
            {
                return date('Y-m-d H:i:s', strtotime($match[3].'-'.$match[2].'-'.$match[1].' '.$match[4]));
            }

        }

        return FALSE;
    }

    /**
     * Metoda bere datum v MySQL formatu a vraci v ceskem formatu.
     * @param <string> $date Vstupni datetime v MySQL formatu
     * @param <string|bool> $strip_time Pokud je TRUE, tak je vracen cesky format
     * casu i s presnou hodinou, minutou a sekundou, pokud je FALSE tak pouze
     * datum bez casu. Pokud je retezec, tak tento retezec je pouzit jako format
     * pro vystupni datum.
     * @return <string>
     */
    static public function czechDate($date, $strip_time = FALSE)
    {
        //pokud je $strip_time retezec, tak to je format ve kterem ma byt datum
        //vraceno
        if (is_string($strip_time))
        {
           return date($strip_time, strtotime($date));
        }
        
        if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})(.*)?/', $date, $match)) {
            if (count($match) == 5) {
                return date('j.n.Y', strtotime($match[3].'-'.$match[2].'-'.$match[1])). ($strip_time ? '' : $match[4]);
            }
        }
        //pokud se datum nechytlo na regular, tak to je pro me neznamy format a
        //vracim v puvodnim tvaru
        return $date;
    }

    /**
     * Metoda slouzi k naformatovani cisla jako ceny.
     * @param <string> $price cena
     * @param <int> $cb_currency_type mena
     * @return <string>
     */
    static public function price($price)
    {
        return number_format($price, 2, '.', ' ');
    }
    
    /**
     * Metoda slouzi k naformatovani intervalu ceny (od - do)
     * @param type $price_from
     * @param type $price_to
     * @param type $cb_currency_type
     * @return type 
     */
    static public function priceInterval($price_from, $price_to, $cb_currency_type)
    {
        return number_format($price_from, 2, '.', ' ').' - '.number_format($price_to, 2, '.', ' ').__('cb_currency_type_'.$cb_currency_type);
    }
    
    /**
     * Zkrati zadany retezec na zadanou delku a pokud doslo ke zkraceni
     * doplni ho na konci znakem '…' (v metode limit_chars)
     * @param <string> $string zkracovany retezec
     * @param <int> $max_length maximalni delka
     * @return <string> Zkraceny retezec
     */
    static public function short($string, $max_length=50) {
        return text::limit_chars($string, $max_length);
    }
    
    /**
     * Vraci typ a subtyp nabidky, ke ktere se vaze poptavka.
     * 
     * @param type $type
     * @param type $subtypes 
     */
    static public function demandAdvertType($type, $subtypes) 
    {
        if (empty($subtypes) or ! is_array($subtypes)) return $type;
        else return "$type / ".implode(', ', $subtypes);
    }

    /**
     * Metoda pro přepočet IP adresy na binární číslo
     *
     * @param <type> $ip
     * @return <type>
     */
    static public function ip2bin($ip)
    {
        $ipbin = "";
        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false)
            return base_convert(ip2long($ip),10,2);
        if(filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)
            return false;
        if(($ip_n = inet_pton($ip)) === false) return false;
        $bits = 15; // 16 x 8 bit = 128bit (ipv6)
        while ($bits >= 0)
        {
            $bin = sprintf("%08b",(ord($ip_n[$bits])));
            $ipbin = $bin.$ipbin;
            $bits--;
        }
        return $ipbin;
    }

    /**
     * Metoda pro výepočet IP adresy z binárního čísla
     * 
     * @param <type> $bin
     * @return <type>
     */
    static public function bin2ip($bin)
    {
        $ipv6 = "";
        if(strlen($bin) <= 32) // 32bits (ipv4)
            return long2ip(base_convert($bin,2,10));
        if(strlen($bin) != 128)
            return false;
        $pad = 128 - strlen($bin);
        for ($i = 1; $i <= $pad; $i++)
        {
            $bin = "0".$bin;
        }
        $bits = 0;
        while ($bits <= 7)
        {
            $bin_part = substr($bin,($bits*16),16);
            $ipv6 .= dechex(bindec($bin_part)).":";
            $bits++;
        }
        return inet_ntop(inet_pton(substr($ipv6,0,-1)));
    }

    /**
     * Predany argument pretypuje na bool hodnotu a tu pak vraci ve forme
     * retezce - 'ano' nebo 'ne'.
     * 
     * @param <type> $value
     * @return <string>
     */
    static public function boolWord($value)
    {
        return (bool)$value
                    ? __('general.bool_yes')
                    : __('general.bool_no');
    }

    /**
     * Ocekva hodnotu ve tvatu "|134|43|33|" pro kterou vraci inedxovane pole, ktere
     * obsahuje tri ciselne polozky z tohoto retezce.
     * @param <string> $value
     * @return <array>
     */
    static public function multiValue($value)
    {
        //odstranim dvojte roury a roury na zacatku a na konci
        //jsou to znaky, ktere by pak delali bordel po volani explode
        $value = str_replace('||', '', $value);
        $value = trim($value, '|');

        return explode('|', $value);
    }

    /**
     * 
     * @param <type> $date
     * @param <type> $locale
     * @return <type> 
     */
    static public function datetimeToLocalDate($date, $locale)
    {
        return date('j M Y H:i:s', strtotime($date));
    }
}