<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Zajistuje autentizaci uzivatele. Slouzi jako bazovy kontroler pro kontrolery
 * ktere jsou pristupne pouze prihlasenemu uzivateli.
 *
 * Kontroler kontroluje zda je uzivatel prihlasny a take muze kontrolovat zda
 * ma potrebne uzivatelske role. Pokud uzivatel neni prihlaseny tak dojde
 * k presmerovani na prihlasovaci obrazovku. Pokud uzivatel nema jednu z povinnych
 * uzivatelskych roli tak je presmerovan chod pozadavku ($this->request->action) na
 * akci tohoto kontroleru - action_unauthorized_access_detected, ktera zajisti
 * zobrazeni prislusne zpravy. Nedojde tedy k puvodnimu (pozadovanemu) zpracovani
 * pozadavku (napr. zobrazeni nejakeho zaznamu).
 *
 * @author: Jiri Melichar
 */
abstract class Controller_Authentication extends Controller_Base_Authentication {



}