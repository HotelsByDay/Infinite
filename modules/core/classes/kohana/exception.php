<?php defined('SYSPATH') or die('No direct access');
/**
 * Kohana exception class. Translates exceptions using the [I18n] class.
 *
 * @package    Kohana
 * @category   Exceptions
 * @author     Kohana Team
 * @copyright  (c) 2008-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Exception extends Exception {

	/**
	 * Creates a new translated exception.
	 *
	 *     throw new Kohana_Exception('Something went terrible wrong, :user',
	 *         array(':user' => $user));
	 *
	 * @param   string          error message
	 * @param   array           translation variables
	 * @param   integer|string  the exception code
	 * @return  void
	 */
	public function __construct($message, array $variables = NULL, $code = 0, $log = TRUE)
	{
		// Save the unmodified code
		// @link http://bugs.php.net/39615
		$this->code = $code;

		// Set the message
		$message = __($message, $variables);

		// Pass the message and integer code to the parent
		parent::__construct($message, (int) $code);

                //argument $log definuje zda ma dojit k automatickemu logovani
                //logovani vyjimek v konstruktoru se provadu pouze pokud nejme
                //v produkcnim rezimu
                if ($log && Kohana::$environment != Kohana::PRODUCTION)
                {
                    // Do logu pridam tuto vyjimku - pridam text aby bylo rozeznatelne
                    // ze zapis do logu se provadi v konstruktoru
                    Kohana::$log->add(Kohana::ERROR, 'Constructing Exception - ['.$code.']'.Kohana::exception_text($this));

                    // Zapise obsah logu na disk
                    Kohana::$log->write();
                }
	}

	/**
	 * Magic object-to-string method.
	 *
	 *     echo $exception;
	 *
	 * @uses    Kohana::exception_text
	 * @return  string
	 */
	public function __toString()
	{
		return Kohana::exception_text($this);
	}

} // End Kohana_Exception
