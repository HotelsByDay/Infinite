<?php defined('SYSPATH') or die('No direct script access.');
/**
 * The Encrypt library provides two-way encryption of text and binary strings
 * using the [Mcrypt](http://php.net/mcrypt) extension, which consists of three
 * parts: the key, the cipher, and the mode.
 *
 * The Key
 * :  A secret passphrase that is used for encoding and decoding
 *
 * The Cipher
 * :  A [cipher](http://php.net/mcrypt.ciphers) determines how the encryption
 *    is mathematically calculated. By default, the "rijndael-128" cipher
 *    is used. This is commonly known as "AES-128" and is an industry standard.
 *
 * The Mode
 * :  The [mode](http://php.net/mcrypt.constants) determines how the encrypted
 *    data is written in binary form. By default, the "nofb" mode is used,
 *    which produces short output with high entropy.
 *
 * @package    Kohana
 * @category   Security
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @copyright  (c) 2016-2018 Koseven Team
 * @copyright  (c) 2023 HotelsByDay
 * @license    http://kohanaframework.org/license
 */
class Kohana_Encrypt {

	/**
	 * @var  string  default instance name
	 */
	public static $default = 'default';

	/**
	 * @var  array  Encrypt class instances
	 */
	public static $instances = array();

	/**
	 * @var  string  OS-dependent RAND type to use
	 */
	protected static $_rand;

	/**
	 * Returns a singleton instance of Encrypt. An encryption key must be
	 * provided in your "encrypt" configuration file.
	 *
	 *     $encrypt = Encrypt::instance();
	 *
	 * @param   string  configuration group name
	 * @return  Encrypt
	 */
	public static function instance($name = NULL)
	{
		if ($name === NULL)
		{
			// Use the default instance name
			$name = Encrypt::$default;
		}

		if ( ! isset(Encrypt::$instances[$name]))
		{
			// Load the configuration data
			$config = Kohana::config('encrypt')->$name;

			if ( ! isset($config['key']))
			{
				// No default encryption key is provided!
				throw new Kohana_Exception('No encryption key is defined in the encryption configuration group: :group',
					array(':group' => $name));
			}

      // Openssl doesn't use a mode as a separate argument
      $config['mode'] = NULL;

			if ( ! isset($config['cipher']))
			{
				// Add the default cipher
				$config['cipher'] = 'AES-256-CBC';
			}

			// Create a new instance
			Encrypt::$instances[$name] = new Encrypt($config);
		}

		return Encrypt::$instances[$name];
	}

	/**
	 * Creates a new openssl wrapper
	 *
	 * @param   string   key_config
	 * @param   string   encryption mode (ignored)
	 * @param   string   openssl cipher
	 */
	public function __construct($key_config, $mode = NULL, $cipher = NULL)
	{
    if (is_array($key_config))
    {
      if (isset($key_config['key']))
      {
        $this->_key = $key_config['key'];
      }
      else
      {
        // No default encryption key is provided!
        throw new Kohana_Exception('No encryption key is defined in the encryption configuration');
      }

      if (isset($key_config['mode']))
      {
        $this->_mode = $key_config['mode'];
      }
      // Mode not specified in config array, use argument
      else if ($mode !== NULL)
      {
        $this->_mode = $mode;
      }

      if (isset($key_config['cipher']))
      {
        $this->_cipher = $key_config['cipher'];
      }
      // Cipher not specified in config array, use argument
      else if ($cipher !== NULL)
      {
        $this->_cipher = $cipher;
      }
    }
    else if (is_string($key_config))
    {
      // Store the key, mode, and cipher
      $this->_key = $key_config;
      $this->_mode = $mode;
      $this->_cipher = $cipher;
    }
    else
    {
      // No default encryption key is provided!
      throw new Kohana_Exception('No encryption key is defined in the encryption configuration');
    }

    if($this->_cipher === NULL)
    {
      // Force sane config as last resort
      $this->_cipher = 'AES-256-CBC';
    }

		// Store the IV size
    $this->_iv_size = openssl_cipher_iv_length($this->_cipher);
    $length = mb_strlen($this->_key, '8bit');

    // Validate configuration
    if ($this->_cipher === 'AES-128-CBC')
    {
      if ($length !== 16)
      {
        // No valid encryption key is provided!
        throw new Kohana_Exception('No valid encryption key is defined in the encryption configuration: length should be 16 for AES-128-CBC');
      }
    }

    elseif ($this->_cipher === 'AES-256-CBC')
    {
      if ($length !== 32)
      {
        // No valid encryption key is provided!
        throw new Kohana_Exception('No valid encryption key is defined in the encryption configuration: length should be 32 for AES-256-CBC');
      }
    }

    else
    {
      // No valid encryption cipher is provided!
      throw new Kohana_Exception('No valid encryption cipher is defined in the encryption configuration. Use "AES-128-CBC" or "AES-256-CBC"');
    }
	}

	/**
	 * Encrypts a string and returns an encrypted string that can be decoded.
	 *
	 *     $data = $encrypt->encode($data);
	 *
	 * The encrypted binary data is encoded using [base64](http://php.net/base64_encode)
	 * to convert it to a string. This string can be stored in a database,
	 * displayed, and passed using most other means without corruption.
	 *
	 * @param   string  data to be encrypted
	 * @return  string
	 */
	public function encode($data)
	{
    // Get an initialization vector
    $iv = $this->create_iv();

    // Encrypt the value using OpenSSL. After this is encrypted we
    // will proceed to calculating a MAC for the encrypted value so that this
    // value can be verified later as not having been changed by the users.
    $value = \openssl_encrypt($data, $this->_cipher, $this->_key, 0, $iv);

    if ($value === FALSE)
    {
      // Encryption failed
      return FALSE;
    }

    // Once we have the encrypted value we will go ahead base64_encode the input
    // vector and create the MAC for the encrypted value so we can verify its
    // authenticity. Then, we'll JSON encode the data in a "payload" array.
    $mac = $this->hash($iv = base64_encode($iv), $value);

    $json = json_encode(compact('iv', 'value', 'mac'));

    if (! is_string($json))
    {
      // Encryption failed
      return FALSE;
    }

    return base64_encode($json);
	}

	/**
	 * Decrypts an encoded string back to its original value.
	 *
	 *     $data = $encrypt->decode($data);
	 *
	 * @param   string  encoded string to be decrypted
	 * @return  FALSE   if decryption fails
	 * @return  string
	 */
	public function decode($data)
	{
    // Convert the data back to binary
    $data = json_decode(base64_decode($data), TRUE);

    // If the payload is not valid JSON or does not have the proper keys set we will
    // assume it is invalid and bail out of the routine since we will not be able
    // to decrypt the given value. We'll also check the MAC for this encryption.
    if ( ! $this->valid_payload($data))
    {
      // Decryption failed
      return FALSE;
    }

    if ( ! $this->valid_mac($data))
    {
      // Decryption failed
      return FALSE;
    }

    $iv = base64_decode($data['iv']);
    if ( ! $iv)
    {
      // Invalid base64 data
      return FALSE;
    }

    // Here we will decrypt the value. If we are able to successfully decrypt it
    // we will then unserialize it and return it out to the caller. If we are
    // unable to decrypt this value we will throw out an exception message.
    $decrypted = \openssl_decrypt($data['value'], $this->_cipher, $this->_key, 0, $iv);

    if ($decrypted === FALSE)
    {
      return FALSE;
    }

    return $decrypted;
	}

  /**
   * Create a MAC for the given value.
   *
   * @param  string  $iv
   * @param  mixed  $value
   * @return string
   */
  protected function hash($iv, $value)
  {
    return hash_hmac('sha256', $iv.$value, $this->_key);
  }

  /**
   * Verify that the encryption payload is valid.
   *
   * @param  mixed  $payload
   * @return bool
   */
  protected function valid_payload($payload)
  {
    return is_array($payload) AND
        isset($payload['iv'], $payload['value'], $payload['mac']) AND
        strlen(base64_decode($payload['iv'], TRUE)) === $this->_iv_size;
  }

  /**
   * Determine if the MAC for the given payload is valid.
   *
   * @param  array  $payload
   * @return bool
   */
  protected function valid_mac(array $payload)
  {
    $bytes = $this->create_iv($this->_iv_size);
    $calculated = hash_hmac('sha256', $this->hash($payload['iv'], $payload['value']), $bytes, TRUE);

    return hash_equals(hash_hmac('sha256', $payload['mac'], $bytes, TRUE), $calculated);
  }

  /**
   * Proxy for the random_bytes function - to allow mocking and testing against KAT vectors
   *
   * @return string the initialization vector or FALSE on error
   */
  protected function create_iv()
  {
    if (function_exists('random_bytes'))
    {
      return random_bytes($this->_iv_size);
    }

    throw new Kohana_Exception('Could not create initialization vector.');
  }

} // End Encrypt
