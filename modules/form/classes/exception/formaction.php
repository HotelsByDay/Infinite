<?php defined('SYSPATH') or die('No direct access allowed.');


abstract class Exception_FormAction extends Kohana_Exception
{
    //uzivatelska zprava - ta bude zobrazena na formulari
    protected $user_message = '';

    /**
     * Zajisti ulozeni uzivatelske zpravy, ktera je urcena k zobrazeni
     * v sablone kterou vraci metoda getView (ta by mela byt zobrazena na
     * formulari).
     * 
     * @param <string> $user_message Zprava popisujici chybu pro uzivatele
     * @param <array> $variables Promenne do $user_message - preklada se metodou __
     * @param <string> $message Tato zprava pujde do logu.
     * @param <bool> $log Slouzi k zapnuti/vypnuti automatickeho logovani vyjimky.
     * Defaultni hodnota je FALSE, protoze potomci teto vyjimky slouzi k oznameni
     * uzivatel ze doslo k validacni chybe apod. - tyto veci nejsou urceny k logovani.
     * @return <type>
     */
    public function __construct($user_message, array $variables = NULL, $message = NULL, $log = FALSE)
    {
        //ulozim uzivatelskou zpravu
        $this->user_message = __($user_message, $variables);

        //zavolam kontruktor rodice - posledni argument zajisti ovlada automaticke logovani
        return parent::__construct($message, array(), 0, $log);
    }

    public function getUserMessage()
    {
        return $this->user_message;
    }

    /**
     * Vraci sablonu, ktera je urcena k zobrazeni na formulari k informovani
     * uzivatele o vznikle chybe.
     * 
     * @param <string> $view_name Nazev sablony, ktera se ma pouzit.
     * @return <type>
     */
    public function getView($view_name)
    {
        //nactu sablonu
        $view = View::factory($view_name);

        //vlozim uzivatelskou zpravu
        $view->user_message = $this->user_message;

        //vracim pripravenou sablonu
        return $view;
    }

}