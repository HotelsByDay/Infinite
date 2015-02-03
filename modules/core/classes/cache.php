<?php defined('SYSPATH') OR die('No direct access allowed.');

/** 
 * Hierarchie dedicnosti u Cache trid je v kohane nasledujici
 * (od base tridy smerem k potomkum)
 * Kohana_Cache --> Abstract Cache(empty) --> Kohana_Cache_File --> Cache_File(empty)
 *                                        --> Kohana_Cache_Apc  --> Cache_Apc(empty)
 *                                        --> ....
 * 
 * Tato trida musi byt abstraktni, protoze neimplementuje abstraknti metody get() a set() 
 * z bazove tridy Kohana_Cache. 
 * Trida se musi jmenovat Cache, aby jeji funkcionalita byla dostupna ve vsech cache driverech (dedi z Cache)
 * 
 * Ucel teto tridy:
 *   - Definuje metody pro cachovani binarnich dat   
 */        

abstract class Cache extends Kohana_Cache {

    // Pouziva Kohana_Cache
    public static $default = 'file';


    /**
     * Deletes all given keys from cache
     * @param array $keys
     */
    public function deleteKeys(array $keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }

    
  /**
   * Ulozi binarni soubor do cache v base64 kodovani
   * @param key - klic pod kterym se data ulozi
   * @param data - binarni data
   * @param lifetime - doba expirace
   * @return bool - zda se ulozeni povedlo      
   */  
    public function setBinary($key, $data, $lifetime=NULL) {
        return $this->set($key, base64_encode($data), $lifetime);
    }
    
    /**
     * Precte zakodovana binarni data z cache a vrati je */    
    public function getBinary($key, $default=NULL) {
        // Zkusime precist zakodovana data
        $data = $this->get($key, FALSE);
        // Data v cache nenalezena, vratime default hodnotu
        if ($data === FALSE) return $default;        
        // Data nalezena, vratime dekodovana data
        return base64_decode($data);
    }
    
    
    
    /**
     * Ulozi binarni soubor do cache v base64 kodovani
     * @param key - klic pod kterym se data ulozi
     * @param file - nazev souboru
     * @param lifetime - doba expirace
     * @return bool - zda se ulozeni povedlo      
     */                                  
    public function setBinaryFile($key, $file, $lifetime=NULL, $delete=FALSE) {
        if ( ! file_exists($file)) {
            /* // Mozna vyhodit vyjimku a nejak to zalogovat... 
            throw new System_Exception('File does not exists: :file', array(
				':file' => $file,
			));
			*/
			return FALSE;
        }
        $handle = fopen($file, "rb");
        if ( ! is_resource($handle)) {
            /* // Opet vyhozeni vyjimky
            throw new System_Exception('Unable to open file: :file', array(
				':file' => $file,
			));
			*/
			return FALSE;
        }                            
        $data = fread($handle, filesize($file));
        fclose($handle);
        
        if ($this->setBinary($key, $data, $lifetime)) {
            // Pokud se podarilo ulozit do cache, a ma se smazat docasny soubor
            if ($delete === TRUE) unlink($file);
            return TRUE;
        }        
        return FALSE;
    }
                   
    /** !!! PREIMPLEMENTOVAT AZ TO BUDE POTREBA */
    /* 
    public function tryToCache($app_cache_key_info, $data, $tags = NULL) {

        if (is_array($app_cache_key_info)) {
            $app_cache_key_type = $app_cache_key_info[0];
            $app_cache_key = $app_cache_key_info[1];
        } else {
            $app_cache_key = $app_cache_key_type = $app_cache_key_info;
        }
        if (($cache_lifetime = kohana::config('caching.'.$app_cache_key_type)) !== NULL) {
            //data zacachuju na urcity cas
            $this->set($app_cache_key,
                        $data,
                        $tags,
                        $cache_lifetime
            );
            return TRUE;
        }
        return FALSE;
    }
    */



}