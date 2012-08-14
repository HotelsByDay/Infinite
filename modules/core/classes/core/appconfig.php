<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 */
class Core_AppConfig {

    /**
     * V teto promenne je ulozeny obsah hlavniho konfiguracniho souboru
     * nacteny z INI souboru.
     */
    protected $config = NULL;


	private function __construct()
	{

	}

    /**
     * Zamezi vytvoreni instance pomoci kopirovani.
     */
	private function __clone()
	{
        
    }

    /**
     * Vraci referenci na jedinou instance teto tridy.
     * @return <type>
     */
	static public function instance()
	{
        static $instance;
        // Load the Auth instance
        empty($instance) and $instance = new AppConfig;

		return $instance;
	}



    /**
     *
     * @param <string> $filename
     *
     * @throws SystemException Pokud neni mozne nacist hlavni konfiguracni soubor
     * ktery je specifikovan prvnim argumentem.
     */
    public function init($filename)
    {
        //pokusim se nacist hlavni konfiguracni sobuor
        try {
            // Zusime precist config z cache
            $cache_key = '_config_file_'.$filename.'.'.filemtime($filename);
            $this->config = Cache::instance()->get($cache_key);

            // Pokud tam nebyl
            if ( ! $this->config) {
                // Parsujeme ini soubor
                $this->config = parse_ini_file($filename, TRUE);
                // A ulozime do cache
                Cache::instance()->set($cache_key, $this->config);
            }
        } catch (Exception $e) {
            //vyhodim systemovou vyjimku, ktera zajisti zalogovani chyby
            throw new AppException('Unable to load main config file "'.$filename.'" due to exception: "'.$e->getMessage().'".');
        }
    }

    /**
     * Metoda slouzi k ziskani hodnoty z hlavniho konfiguracniho souboru.
     * @param <string> $key Klic jehoz hodnotu vraci.
     * @param <string> $section Sekce kde ma byt klic hledan.
     * @param <midex> $default Hodnota, kterou metoda vrati pokud nenajde pozadovany
     * klic.
     *
     */
    public function get($key, $section, $default = NULL)
    {
        //vytahnu si pozadovnou sekci, a z ni vracim daany klic
        $section = arr::getifset($this->config, $section, array());
        return arr::getifset($section, $key, $default);
    }
    
    
    
    /**
     * Vraci celou sekci konfiguracniho souboru
     *  - pouzitelne pro nacteni cele konfigurace tridy pri jejim vytvareni
     * @param <string> $section
     * @param <mixed> $default 
     * @author Jiri Dajc
     */
    public function getSection($section, $default=Array()) {
        return arr::getifset($this->config, $section, $default);
    }

	/**
	 * Definuje zda je aktivni debugovaci mod.
	 * Debugovaci mod, muze byt aktivni pouze pro vybrane IP adresy.
	 *
	 * @return <bool>
	 */
	public function debugMode()
	{
		$debug = $this->get('debug', 'system');
		//pokud se jedna o bool hodnotu, tak to je globalni nastaveni
		if ($debug == '1' || $debug == '0') {
			return (bool)$debug;
		}
		//pokud se jedna o retezec, tak zpracuji jako carkou oddeleny seznam IP 
		//adres pro ktere je debug rezim zapnuty
		$hosts = explode(',', $debug);
		return in_array($_SERVER['REMOTE_ADDR'], $hosts);
	}


    /**
     * Metoda slouzi k detekci zda system bezi na localhostu.
     */
    public function RunningOnLocalhost()
    {
        return true;
    }

}

?>
