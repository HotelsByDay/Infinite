<?php

/**
 * Trida zajistujici kompilovani externich JS souboru - kompiluje soubory az v cachi na zaklade
 * zaznamu v databazi. Do databaze zaroven loguje pripadne chyby.
 * @uses Model_Sys_js_compile
 * @uses Model_Sys_js_error
 * @uses Cache
 * @uses Kohana::$log
 */
class Core_Compiler {
    
    protected $compiler_url = 'http://closure-compiler.appspot.com/compile';
        
    /**
     * Prida soubor pro zkompilovani
     * @param string $key - klic do cache
     */
    public function addForCompile($key)
    {
        $item = ORM::factory('Sys_js_compile');
        $item->key = $key;
        $item->save();
    }
    
    
    /**
     * Metoda zkompiluje soubory nalezene v cache pres klice v sys_js_compile tabulce.
     * @param int limit - maximalni pocet souboru, ktery se bude kompilovat, 0=nekonecno
     */
    public function compile($limit=0) 
    {
        $items = ORM::factory('Sys_js_compile');
        if ($limit) $items->limit($limit);
        $items = $items->find_all();
        // Projdeme soubory a zkompilujeme
        foreach ($items as $item) {
            $file = Cache::instance()->get($item->key, false);
            $content = arr::get((array)$file, 'content', false);
            if ( ! $file) {
                Kohana::$log->add(Kohana::ERROR, "JS Compiler - nepodarilo se precist soubor s cache ($item->key).");
                $item->delete();
                continue;
            }
            
            // Jinak soubor zkusime zkompilovat
            $content = $this->compileOne($content);
             
            // Pokud se kompilace zdarila, nahradime soubor v cache
            if ($content) {
                //vlozim novy obsah polozky do cache
                $file['content'] = $content;
                //priznak ze je soubor zkompilocan
                $file['compiled'] = TRUE;
                //vlozim zpatky do cache
                Cache::instance()->set($item->key, $file, 'resource_js');
            }
            
            // Smazeme zaznam - at se podarilo nebo ne - novy pokus jiz nepomuze
            $item->delete();
            continue;
        }
    }
    
    
    public function compileOne($file)
    {
        // Pripravime si request data
        $post_data = Array(
            'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
            'output_format' => 'json', 
            'output_info' => 'compiled_code',
            'js_code' => urlencode($file),
        );
        // Pokud soubor presahne 1000kB, pak ho compiler odmitne
        $length = strlen($post_data['js_code']);
        if ($length > 1024000) {
            Kohana::$log->add(Kohana::ERROR, 'JS Compiler - Soubor je příliš velký ('.$length.'B)');
            // return;
        }
        
        // Zkusime ziskat odpoved
        $result = $this->getResponse($post_data);
            
        // Prevedeme na pole
        $result = json_decode($result);
            
        // Ziskame vlastni kod
        $output = isset($result->compiledCode) ? $result->compiledCode : '';
            
        // Pokud kod neni prazdny, pak se to asi povedlo a vratime ho
        if ( ! empty($output)) return $output;
            
        // V outputu muze byt i klic serverErrors - v takovem pripade ho ulozime
        // a nepokracujeme - snizeni levelu compilace to vetsinou myslim nevyresi
        $server_errors = isset($result->serverErrors) ? $result->serverErrors : Array();
        if ( ! empty($server_errors)) {
            $error = ORM::factory('Sys_js_error');
            $error->input = $file;
            $error->server_errors = json_encode((array)$server_errors);
            $error->save();
            return false;
        }
            
        // Jinak zkusime ziskat chybove hlasky a ulozit je do DB
        $post_data['output_info'] = 'errors';
        $result = $this->getResponse($post_data);
            
        $error = ORM::factory('Sys_js_error');
        $error->input = $file;
        $error->errors = $result;
        $error->save();    
    }
    
    
    
    protected function buildQuery($post_data)
    {
        $query = Array();
        foreach ((array)$post_data as $key => $val) {
            $query[] = "$key=".$val;
        }
        return implode('&', $query);
        
    }
    
    
    
    /**
     * Vlastni odeslani pozadavku a ziskani odpovedi
     * @param type $post_data
     * @return type 
     */
    protected function getResponse($post_data)
    {
        // Poskladame query string
        $query = $this->buildQuery($post_data);  
        // Posleme request
        // Nastavime parametry pripojeni
        $curl = curl_init($this->compiler_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        // Pridame data
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        // Odesleme pozadavek
        $result = curl_exec($curl);
        // Zavreme spojeni
        curl_close($curl);
        // Vratime odpoved
        return $result;
    }
    
    
    
    /**
     * Singleton navrhovy vzor.
     */
    private function __construct()
    {
        // Nacte configuraci doby po jakou se maji js soubory cachovat
        $config = kohana::config('caching');
        $this->caching_time = arr::get($config, 'resource_js');
    }

    /**
     * Singleton navrhovy vzor.
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

        $instance == NULL && $instance = new Compiler;

        return $instance;
    }

    
}

?>
