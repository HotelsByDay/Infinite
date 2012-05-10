<?php

/*
 * Konfiguracni soubor s ciselniky tabulek(sloupcu), slouzicimi pro efektivni 
 * ukladani historie zmen v urcitych tabulkach(sloupcich).
 * Soubor se musi vlozit rucne, nejlepe pomoci Kohana::find_file metody.
 * POZOR - Kohana::find_file vraci pro config adresar vzdy Array.
 * @author Jiri Dajc
 */


return  Array(
    // Ciselnik pro prevod tabulek na cisla (pouziva se i pro prevod obracenym smerem)
    // Indexem je nazev MODELU dane tabulky (v pripade ze neodpovida jejimu nazvu).
    // Pomoci inverzniho slovniku pak nalezneme na zaklade cisla ihned prislusny model, 
    // jehoz instanci muzeme vytvorit.
    'all_tables' => Array(
        'agenda' => 1,
        'logaction' => 2,
        'user'      => 3,
        'comment'   => 4,
    ),  
);
