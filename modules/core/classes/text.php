<?php defined('SYSPATH') or die('No direct script access.');

class Text extends Kohana_Text
{

    public static function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
    public static function endsWith($haystack, $needle)
    {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    /**
     * Odstrani diakritiku
     *
     * @param string $string vstupni retezec
     * @param bool  $lower priznak zda se ma prevadet na mala pismena
     * @return string retezec bez diakritiky
     */
    public static function toAscii($string, $lower = true)
    {
        if (defined('ICONV_IMPL') && ICONV_IMPL != 'libiconv') {

            /**
             * @author David GRUDL
             * @link http://davidgrudl.cz
             */
            static $table = array(
                "\xc3\xa1" => "a", "\xc3\xa4" => "a", "\xc4\x8d" => "c", "\xc4\x8f" => "d", "\xc3\xa9" => "e",
                "\xc4\x9b" => "e", "\xc3\xad" => "i", "\xc4\xbe" => "l", "\xc4\xba" => "l", "\xc5\x88" => "n",
                "\xc3\xb3" => "o", "\xc3\xb6" => "o", "\xc5\x91" => "o", "\xc3\xb4" => "o", "\xc5\x99" => "r",
                "\xc5\x95" => "r", "\xc5\xa1" => "s", "\xc5\xa5" => "t", "\xc3\xba" => "u", "\xc5\xaf" => "u",
                "\xc3\xbc" => "u", "\xc5\xb1" => "u", "\xc3\xbd" => "y", "\xc5\xbe" => "z", "\xc3\x81" => "A",
                "\xc3\x84" => "A", "\xc4\x8c" => "C", "\xc4\x8e" => "D", "\xc3\x89" => "E", "\xc4\x9a" => "E",
                "\xc3\x8d" => "I", "\xc4\xbd" => "L", "\xc4\xb9" => "L", "\xc5\x87" => "N", "\xc3\x93" => "O",
                "\xc3\x96" => "O", "\xc5\x90" => "O", "\xc3\x94" => "O", "\xc5\x98" => "R", "\xc5\x94" => "R",
                "\xc5\xa0" => "S", "\xc5\xa4" => "T", "\xc3\x9a" => "U", "\xc5\xae" => "U", "\xc3\x9c" => "U",
                "\xc5\xb0" => "U", "\xc3\x9d" => "Y", "\xc5\xbd" => "Z"
            );

            $s = strtr($string, $table);
        } else {
            setlocale(LC_CTYPE, 'cs_CZ.UTF-8'); // en_US ?
            $s = iconv("utf-8", "us-ascii//TRANSLIT", $string);
        }
        $s = str_replace(array('`', "'", '"', '^', '~'), '', $s);

        if ($lower) {
            return strtolower($s);
        }
        return $s;
    }
    
    /**
     * Prevede retezec na SEO URL tvar.
     *
     * Inspirovano z Nette Framework, String::webalize
     *
     * @param string $s vstupni text pro prevod
     * @param string $charlist dodatecne znaky, ktere se maji v puvodnim textu zachovat
     * @param bool $lower - priznak, zda text prevest na mala pismena
     *
     * @return string text pouzitelny do URL adresy
     */
    public static function webalize($s, $charlist = '+', $lower = TRUE)
    {
        $s = strtr($s, '`\'"^~', '-----');
        /*
          if (ICONV_IMPL === 'glibc') {
          setlocale(LC_CTYPE, 'cs_CZ.UTF-8'); // en_US ?
          }

          $s = @iconv('UTF-8', 'ASCII//TRANSLIT', $s); // intentionally @
         */
        $s = self::toAscii($s);
        if ($lower)
            $s = strtolower($s);
        $s = preg_replace('#[^a-z0-9' . preg_quote($charlist, '#') . ']+#i', '-', $s);
        $s = trim($s, '-');
        return $s;
    }

    static public function json_encode($array, $options = JSON_FORCE_OBJECT)
    {

        $replace_keys = array();

        foreach ($array as $key => $value)
        {

            if (is_string($value) && preg_match('#^function\(#', $value) != FALSE)
            {
                $uniq_token = uniqid('', TRUE);

                $array[$key] = $uniq_token;
                $replace_keys[$uniq_token] = $value;
            }
        }

        $json_encoded = json_encode($array, $options);

        foreach ($replace_keys as $key => $value)
        {
            $json_encoded = str_replace('"'.$key.'"', $value, $json_encoded);
        }

        return $json_encoded;
    }

    /**
     * Tato metoda nahrazuje funkci http_build_str, ktera neni soucastni
     * zakladniho PHP.
     * @param <array> $url_components Ocekava pole ve tvaru, ktery vraci funkce parse_url
     * @param <bool> $relative Definuje zda ma byt vracena URL relativni nebo absolutni
     * @return <string> Kompletni url vytvorenou podle parametru $url_components
     *
     */
    static public function http_build_str(array $url_components, $relative = TRUE)
    {
        $url = '';

        if ( ! $relative)
        {
            if (($scheme = arr::get($url_components, 'scheme')) != NULL)
            {
                $url .= $scheme.'://';
            }

            if (($host = arr::get($url_components, 'host')) != NULL)
            {
                $url .= $host;
            }
        }

        if (($path = arr::get($url_components, 'path')) != NULL)
        {
            $url .= $path;
        }

        if (($query = arr::get($url_components, 'query')) != NULL)
        {
            $url .= '?'.$query;
        }

        if (($fragment = arr::get($url_components, 'fragment')) != NULL)
        {
            $url .= '#'.$fragment;
        }

        return $url;
    }

}
