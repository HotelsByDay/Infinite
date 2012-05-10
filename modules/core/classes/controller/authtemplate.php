<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Tento kontroler kombinuje funkcionalitu Authentication controleru a
 * automatickeho vlozeni obsaho sablony do stranky.
 *
 * Atributem $base_template je mozne nastavit zakladni sablonu, ktera je vlozena
 * do stranky jako prvni - mela by resit celkovy layout stranky, pozici hlavniho menu, apod.
 * Jako atribut $content je ji predana dalsi sablona, ktera predstavuje obsahovou cast.
 */
abstract class Controller_AuthTemplate extends Controller_Authentication {

	/**
	 * @var  View  page template
	 */
	public $template = 'template';

	/**
	 * @var  boolean  auto render template
	 **/
	public $auto_render = TRUE;

	/**
	 * Loads the template [View] object.
	 */
	public function before()
	{
		if ($this->auto_render === TRUE)
		{
			// Load the template
			$this->template = View::factory($this->template);
		}

		return parent::before();
	}

	/**
	 * Assigns the template [View] as the request response.
	 */
	public function after()
	{
		if ($this->auto_render === TRUE)
		{
			$this->request->response = $this->template;
		}

		return parent::after();
	}
        
} // End Template_Controller