<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tento kontroler slouzi k ziskani obsahu TIP HELP napovedy. Tato napoveda
 * je zobrazena formou otazniku u ruznych prvku ve strance a pri najeti mysi
 * je zbrazen qTip do ktereho je prace pomoci akce 'tip' nahran text prislusne
 * napovedy.
 * 
 * @author: Jiri Melichar
 */
class Controller_Help extends Controller_Authentication {

    /**
     * Vraci text pozadovaneho tematu napovedy. Pozadovane tema napovedy
     * je definovano hodnotou atributu 'helpid' v _GET poli.
     */
    public function action_tip()
    {
        //ID tematu napovedy
        $helpid = arr::get($_GET, 'helpid', NULL);

        //do stranky vraci jako cisty text
        echo __('tip_help.topic_'.$helpid);
    }
}