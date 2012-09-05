<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Tato trida slouzi k specialnimu ucelu, zatim je vyuzivana tridou Core_AppForm
 * v metode itemAttr k tomuto:
 * 
 * Standardne je nazev atributu jednotlivych prvku definovan 'staticky' pri 
 * vytvoreni prvku a dale se uz nemeni. V pripade ze je formular pouzit jako
 * polozka v AppFormItemAdvancedItemlist, tak nazev atributu kazdeho prvku
 * musi obsahovat nazev atributu AppFormItemAdvancedItemlist a dale pak hodnotu
 * PK modelu nad kterym formular stoji, tak aby fungovalo spravne odesilani dat
 * POST-em (AppFormItemAdvancedItemList) bez nutnosti pouziti JS apod.
 * V pripade ze se vytvari novy zaznam, je misto PK pouzita nahodna hodnota.
 * Ve chvili kdy jsou na server odeslana data + prikaz k akci ulozeni, dojde k
 * vytvoreni koplet formulare se vsemi prvky. V tuto chvili uz nektere prvky
 * potrebuji znat nazev 'sveho' atributu aby ji mohli vlozit do JS souboru, ktere
 * vkladaji do stranky jiz v konstruktoru - tedy pred ulozeni hlavniho modelu.
 * Problem nastava v tom ze atribut obsahuje, jiz zminenou, hodnotu PK, ktera jeste
 * neni znama, protoze k pokusu o ulozeni zaznamu dojde az po vytoreni komplet
 * formulare.
 * Je tedy nutne aby prvky vyzadovali nazev 'sveho atributu' nejdrive po ulozeni
 * hlavniho zaznamu, coz by vyzadovali komplikovanejsi konstrukci form prvku.
 *
 * Nahradnim resenim je ze si kazdy prvek vyzada hodnotu sveho atributu 'kdykoliv'
 * pomoci metody $form->itemAttr a Core_AppForm trida mu vrati instanci tridy
 * helper_delayedstring, ktera az ve chvili volani __toString skutecne vola
 * $form->itemAttr - to by melo byt az pri renderovani, coz je vzdy po pokusu
 * o ulozeni zaznamu.
 *
 * Pokud dojde kdykoliv pred ulozenim hlavniho zaznamu o pretypovani
 * instance teto tridy na (string) tak metoda itemAttr vyhodi vyjimku, protoze
 * by pak doslo k neocekavanemu chovani na formulari.
 *
 */
class helper_delayedstring {

    protected $_callback = NULL;

    public function __construct($callback)
    {
        $this->_callback = $callback;
    }

    public function __toString()
    {
    	try
    	{
    		$retval = call_user_func($this->_callback);
    	}
    	catch (Exception $e)
    	{
    		die($e->getMessage());
    	}
    	
        return $retval;
    }

}

