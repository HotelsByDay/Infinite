<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Tato trida slouzi k zakodovani dat. Princip pouziti je nasledujici - tride se
 * preda PHP objekt, pole, retezec, aj. Dany PHP element bude serializovan a ulozen
 * do DB. Trida vraci identifikator tohoto ulozeneho zaznamu.
 * Trida dale poskytuje metody, ktera vrati puvodni PHP element pro N-mistny
 * alfanumericky retezec.
 *
 * Tento princip se pouziva v pripade kdy potrebujeme pres URL predet parametry
 * a neni zadouci aby uzivatel videl obsah dat. Jedna se o tyto pripady:
 *  - predani navratove adresy
 *  - predani parametru filtru
 *  - predani vysledku operace
 *  - a dalsi...
 *
 * @author: Jiri Melichar
 */
class Core_Encoder
{
    /**
     * Defaultni hodnoty konfigurace.
     * @var <array>
     */
    protected $config = array(
        //Nazev tabulky do ktere se data ukladaji
        'table_name' => 'encoder'
    );

    /**
     * Singleton design pattern
     */
    protected function __clone()
    {

    }

    /**
     * Singleton design pattern
     */
    protected function __construct()
    {

    }

    /**
     * Singleton design pattern
     */
    static public function instance()
    {
        static $instance;

        $instance == NULL AND $instance = new Encoder;

        return $instance;
    }

    /**
     * Metoda koduje data do unikatniho retezce.
     * @param <mixed> $hash
     * @return <string>
     */
    public function encode($data)
    {
        //retezec si zakoduju do base64
        $encoded_string = base64_encode(serialize($data));

        //pripravim data pro insert
        $data = array(
            'data' => $encoded_string,
            //tabulka dale obsahuje sloupec ts (timestamp), ten ale mysql doplnuje
            //automaticky
        );

        //pokud by doslo pri ukladani zaznam k vyjimce, tak vracim NULL
        try{
            list($id, $total_rows_affected) = DB::insert(arr::getifset($this->config, 'table_name', 'encoder'), array_keys($data))
                                                    ->values(array_values($data))
                                                    ->execute();
        } catch (Exception $e) {
            //logovani vyjimky neni potreba - to je zarizeno v objektech ze kterych
            //vyjimka dedi
            return NULL;
        }
        return $id;
    }

    /**
     * Metoda vraci data, ktera byla zakodovana do daneho klice.
     * @param <string> $hash
     * @return <mixed>
     */
    public function decode($id)
    {
        $data = DB::select('data')->from(arr::getifset($this->config, 'table_name', 'encoder'))
                                  ->where('encoderid', '=', $id)
                                  ->execute()->as_array();

        if (isset($data[0])) {
            return unserialize(base64_decode((string)$data[0]['data']));
        }

        return NULL;
    }

    /**
     * Tato metoda je urcena ke spusteni v ramci cron udalosti 'cron.1d' a slouzi
     * k procisteni tabulky 'encoder'. Metoda promazava vsechny zaznamy starsi
     * nez definovana doba.
     */
    static public function cron_event()
    {
        //nactu si hodnotu, ktera definuje minimalni stari zaznamu, ktere
        //budou z tabulky 'encoder' odstraneny. Fallback hodnota je 30dnu.
        $clean_old = kohana::config('encoder.clean_old', 86400*30);
        //provede odstraneni starych zaznamu
        $q=DB::delete('encoderX')
                ->where('ts', '<', time() - $clean_old)
                ->execute();
    }
}

?>
