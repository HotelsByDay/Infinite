<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Ucelem tohoto ORM modelu je aby z nej dedily systemove ORM modely u kterych
 * neni zadouci aby dochazelo ke kontrole uzivatelskeho opravneni. Jedna se
 * napriklad o ORM model 'email_queue' apod.
 *
 * Na ORM model dedici z tohoto je aplikovan akontrola opravneni, ale tento model
 * zajisti ze vsechny kontroly skonci uspechem (uzivatel ma opravneni).
 */
abstract class ORM_Authorized extends ORM
{
   /**
     * Hlavni ucel teto metody je zkontrolovat zda ma uzivatel opravneni
     * pro vlozeni noveho zaznamu a pripadne vyvolat metodu, ktera provede
     * aplikaci modifikatoru opravneni.
     */
    protected function applyUserInsertPermission()
    {
        return TRUE;
    }


    /**
     * Hlavni ucel teto metody je zkontrolovat zda ma uzivatel opravneni
     * pro odstranovani zaznamu a pripadne vyvolat metody, ktera provede
     * aplikaci modifikatoru opravneni pro odstranivani (db_delete).
     */
    protected function applyUserUpdatePermission()
    {
        return TRUE;
    }

    /**
     * Hlavni ucel teto metody je zkontrolovat zda ma uzivatel opravneni
     * pro odstranovani zaznamu a pripadne vyvolat metody, ktera provede
     * aplikaci modifikatoru opravneni pro odstranivani (db_delete).
     */
    protected function applyUserDeletePermission()
    {
        return TRUE;
    }

    /**
     * Tato metoda je volane vzdy pred ctenim z DB tabulky prislusneho modelu
     * a zajistuje nastaveni filtrovacich podminek podle nastaveni opravneni
     * aktualne prihlaseneho uzivatele.
     *
     * Hlavni ucel metody je zkontrolovat zda ma uzivatel vubec opravneni pro
     * cteni na tomto objektu a pripadne vyvolat metodu, ktera zajisti
     * aplikaci modifikatoru opravneni (prida dodatecne filtrovaci podminky).
     */
    protected function applyUserSelectPermission()
    {
        return TRUE;
    }

}