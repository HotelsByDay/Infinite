<?php defined('SYSPATH') or die('No direct script access.');
 
/**
 * Rozsiruje standardni obecnou funkcnost tridy Cache.
 *
 * Upraveno je chovani metody 'set' a to tak aby bylo mozne misto tretiho
 * argumentu $lifetime specifikovat textovy retezec, ktery v konfiguracnim
 * souboru 'caching' definuje dobu platnosti. V praxi by to vypadalo takto:
 *
 * Cache::instance()->set('js', $js, 'js_resources');
 *
 * //a v caching.php (konfigu):
 *
 * 'js_resources' => 3600,
 *
 * Zamerem je mit v jednom miste definovane platnosti ruznych cachovanych zdroju
 * aby bylo mozne snadno platnosti menit a optimalizovat.
 *
 * @TODO: Zvazit zda nedefinovat v 'caching' konfiguraku i caching driver pro
 * jednotive zdroje. Bylo by tedy mozne snadno nastavit ze cachovane JS chci
 * mit v Memcache na 1h, ale DB vysledky na disku na 10minut. Zvazit zda to bude
 * k necemu ?
 *
 */
class Cache_File extends Kohana_Cache_File {

    /**
     * Rozsiruje zakladni metody set o moznost specifikovat misto tretiho argumentu
     * $lifetime textovy retezec, ktery je definovan jako klic v konfiguracnim
     * souboru 'caching' kde je specifikovana pozadovana doba platnosti. Ucelem
     * je mit na jednom miste vsechny pouzivane doby platnosti, coz usnadni jejich
     * ladeni a nastavovani.
     *
     * @param <type> $id
     * @param <type> $data
     * @param <type> $lifetime
     * @return <type>
     */
    public function set($id, $data, $lifetime = 3600)
    {
        if ( ! is_numeric($lifetime)) {
            $lifetime = Kohana::config('caching.' . $lifetime);
            //pokud neni pozadovany klic definovany, tak udelam zapis do logu
            if ($lifetime === NULL) {
                kohana::log('error', 'Trying to cache resources with undefined lifetime key "'.$lifetime.'". Add this key to "caching" config file.');
                //defaultni hodnota
                $lifetime = Kohana::config('caching.default', 3600);
            }
        }

        return parent::set($id, $data, $lifetime);
    }

}