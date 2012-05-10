<?php

class Filter_Serie extends Filter_Base {


    /**
     * Defaultni konfigurace pro /table vypis dat tohoto objektu.
     * Zde se vypina moznost ukladani uzivatelskych filtru.
     * @var <type>
     */
    protected $config = array(
        //povolit funkci pro ulozeni stavu filtru ?
        'save_filtere_state' => FALSE,
    );

    protected function applyFilter($orm)
    {
        // Nazev
        if (($value = arr::get($this->filter_params, 'name', '')) != '') {
            $orm->like('name', $value);
        }
        return $this;
    }
    
    
    protected function applyFulltextFilter($orm, $query)
    {
        return $this;
    }
}
