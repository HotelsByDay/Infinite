<?php

/**
 * Poskytuje rozhrani pro praci s ciselnymi radami
 * 
 * @author Jiri Dajc
 */
class Core_Serie {
    
    /**
     * Vychozi konfigurace
     * @var <array>
     */
    protected $config = Array(
        /**
         * Vychozi nastaveni maximalniho poctu cekacich cyklu
         * na odemceni pocitadla. Po vyprseni poctu je generoana vyjimka.
         */
        'max_unlock_wait_cycle_count' => 50,
        
        /**
         * Cas cekani na odemknuti serie v jedne iteraci cyklu
         * v mikro-sekundach
         */
        'cycle_wait_time' => 20000, // 20 ms
    );
    /**
     * Nazev databazove tabulky nad kterou trida pracuje
     */
    protected $table_name = 'serie';
        
    // Odpovida serie.estateagencyid
    // predavano konstruktoru tridy
    protected $type = NULL;
    
    /**
     * Rika zda tato instance uzamnknula serii 
     * a tedy zda ma pravo z ni cist a navysovat hodnotu pocitadla
     * @var <bool>
     */
    protected $has_locked = FALSE;
 
    /**
     * Po vytvoreni bude instance objektu celou dobu pracovat s jednou konkretni ciselnou radou
     * @param <int> $type typ rady se kterou chceme pracovat
     * @param <ind> $estateagencyid Estate Agency ID - nutne pro presnou identifikaci rady
     */
    public function __construct($type) 
    {
        $this->type = (int)$type;
        $this->config = Kohana::config('serie');
    }
    
    
    /**
     * Vraci castecne formatovanou hodnotu pocitadla
     * pokousi se pocitadlo uzamknout, pripadne ceka na jeho uvolneni (odemceni jinym procesem)
     * formatovani resi volanim metody formatSerie()
     * v pripade ze je pocitadlo uzamceno, ceka na jeho uvolneni
     * @throws SerieException pokud dojde k prekroceni maximalniho poctu cekacich cyklu
     */
    public function getNextValue() 
    {
        $count = 0;
        while ( ! $this->lock()) {
            $count++;
            if ($count > $this->config['max_unlock_wait_cycle_count']) {
                throw new SerieException('Max wait cycle count ("' . ($this->config['max_unlock_wait_cycle_count']) . '") exceeded for serie type "' . $this->type . '"');
            
            }
            // cekani v mikrosekundach
            usleep($this->config['cycle_wait_time']);
        }
        // Nyni jsme uspesne uzamkli serii a muzeme precist, naformatovat a vratit hodnotu pocitadla
        return $this->formatedSerie();
    }
    
    /**
     * Vraci castecne formatovanou hodnotu pocitadla
     * Co se parsuje:
     * {x-n} - x reprexentuje hodnotu pocitadla, n udava pocet mist na kolik ma byt cislo zleva doplneno nulami
     *         {x-4} s value=123 --> 0123  
     * {Y}   - kompletni rok (4 cifry)
     * {y}   - dvouciferny rok (2 posledni cifry)
     * {m}   - cislo mesice
     * {M}   - cislo mesice s uvodni nulou
     * {d}   - cislo dne v mesici
     * {D}   - cislo dne v mesici s uvodni nulou
     * ------- ostatni znaky jsou ponechany a mohou byt parsovany v metode formatSerie()
     *         bazoveho ORM nebo jednotlivych modelu.
     *         Toho se da vyuzit treba pro zahrnuti prvniho znaku typu nabidky atp.
     * Komplexni priklad:
     *  format, value --> return
     *  "#advert_type.first_char#-{Y}{M}{D}-{x-6}, 17 --> "#advert_type.first_char#-20110623-000017
     *  pricemz #advert_type.first_char# bude nahrazeno v metode formatSerie prislusnemo modelu
     */
    protected function formatedSerie() 
    {
        // ziskame zaznam pocitadla z DB tabulky
        $serie = DB::select()->from($this->table_name)
                ->where('type', '=', $this->type)
                ->execute();
        $serie = $serie[0];
        
        // Format serie, ktery se bude parsovat
        $format = $serie['format'];
        // Hodnota pocitadla serie - pro parsovani
        $value = $serie['next_value'];
        
        // Nahrazeni roku
        $format = str_replace('{y}', date('y'), $format);
        $format = str_replace('{Y}', date('Y'), $format);
        // Nahrazeni mesice
        $format = str_replace('{m}', date('n'), $format);
        $format = str_replace('{M}', date('m'), $format);
        // Nahrazeni dne
        $format = str_replace('{d}', date('j'), $format);
        $format = str_replace('{D}', date('d'), $format);
        
        // Najde {x-n}, kde n je cislo udavajici pocet cislic
        if (preg_match('#{x-([0-9])}#', $format, $matched)) {
            // Padding cisla pokud bude potreba
            $len = $matched[1];
            if (strlen($value) < $len) {
                $value = str_pad($value, $len, '0', STR_PAD_LEFT);
            }
            // Nahradim {x-n} za dane cislo
            $format = str_replace($matched[0], $value, $format);
        }
        return $format;
    }
    
    
    /**
     * Vytvori serii pokud zatim neexistuje - pri prvnim pokusu o nacteni hodnoty
     */
    protected function create() 
    {
        // !!! Nevim jestli je vhodne to resit tady 
        // nebo jinde - treba pri pridavani maklere !!!
    }
    
    
    /**
     * Pokusi se zamknout odemcenou serii. Pokud serie jiz byla zamcena, 
     * vraci FALSE (je jiz obsazena a nemuzeme z ni cist)
     * Pokud se serii podari uzamknout, nastavi $this->has_locked na TRUE, coz
     * je vyuzivano ke kontrole pri volani dalsich metod.
     * @return bool zda serie byla odemcena
     */
    protected function lock() 
    {
        $locked = DB::update($this->table_name)->set(Array('locked'=>1))
                ->where('type', '=', $this->type)
                ->where('locked', '=', '0')->execute();
        
        if ($locked == 1) {
            // Serie prave byla uzamknuta
            $this->has_locked = TRUE;
            return TRUE;
        }
        // Serie jiz byla uzamcena
        return FALSE;
    }
    
    
    
    /**
     * Navysi pocitadlo serie a zapise zmeny do DB
     * Kontroluje zda ma instance pravo pro pristup k dane serii
     * @return <Serie> this
     */
    public function generateNextValue() 
    {
        if ($this->has_locked) {
            DB::update($this->table_name)->set(Array('next_value'=>DB::expr('next_value+1')))
                ->where('type', '=', $this->type)
                ->execute();
        }
        return $this;
    }
    
    /**
     * Odemkne serii nastavenim hodnoty locked na 0
     * a odebere instanci opravneni k odemknuti a navysovani
     * hodnoty pocitadla. 
     * @return <Serie> this
     */
    public function unlock() 
    {
        if ($this->has_locked) {
            DB::update($this->table_name)->set(Array('locked'=>0))
                    ->where('type', '=', $this->type)
                    ->execute();
            $this->has_locked = FALSE;
        }    
        return $this;
    }
    
    
    
    
    
}

?>
