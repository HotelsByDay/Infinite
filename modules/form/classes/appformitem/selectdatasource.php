<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Toto je bazova trida pro formularove prvky pracujici s mnozinou zaznamu
 * tj. prvky pro vyber 1 z N nebo N z N hodnot s ruznymi GUI.
 * Vsechny odvozene tridy dedi tuto konfiguraci:
 * ?'source_model'      => <string>  ...  Nazev modelu, ze ktereho se ciselnik sestavi. Klicem
 *                                        bude PK a hodnotou atribut "value".
 * ?'filter'            => <array>   ...  Asociativni pole, ktere bude pouzito jako filtr ??? 
 */
class AppFormItem_SelectDataSource extends AppFormItem_Base
{
  
    
    /**
     * Vrati asociativni pole s vyctem hodnot pro tento prvek.
     * Klic vzdy odpovida hodnote ukladane do DB a hodnota v poli je 
     * zobrazovana uzivateli v GUI.
     */
    protected function getValues()
    {
        // Pokud je definovano source_model v configu, vezmeme z nej data
        // Predpokladame ze source_model je definovan v $this->model v poli $_rel_cb
        if (($source_model = arr::get($this->config, 'codebook', FALSE))) {
            $filter = arr::get($this->config, 'filter', NULL);
            
            // Naplnime filtr z aktualniho modelu - codebook uz k nemu nema pristup
            foreach ((array)$filter as $column => $value) {
                if ($value == NULL) {
                    $filter[$column] = $this->model->{$column};
                }
            }
            return Codebook::listing($source_model, NULL, $filter);
        }
        // Jinak vratime prazdne pole - hodnoty si pravdepodobne doplni odvozena trida
        return Array();
    }
    
    
    
    /*
     *  protected function getValues() 
    {
        // Pokud je definovano source_model v configu, vezmeme z nej data
        // Predpokladame ze source_model je definovan v $this->model v poli $_rel_cb
        if (($source_model = arr::get($this->config, 'codebook', FALSE))) {
            $filter = arr::get($this->config, 'filter', NULL);
            
            // Naplnime filtr z aktualniho modelu
            foreach ((array)$filter as $column => $value) {
                if ($value == NULL) {
                    $filter[$column] = $this->model->{$column};
                }
            }
            // Filtr by se musel naplnit uz tady - to by nemel byt problem
            // Tady by se pak volalo Codebook::list($source_model, $filter);
            return Codebook::listing($source_model, NULL, $filter);
        }
        // Jinak vratime prazdne pole - hodnoty si pravdepodobne doplni odvozena trida
        return Array();
    }
     */
    
    
}