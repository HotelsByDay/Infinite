<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * ORM Auth driver.
 *
 * @package    Kohana/Auth
 * @author     Kohana Team
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaframework.org/license
 */
class Kohana_Auth_ORM extends Auth {

        protected $_login_in_progress = FALSE;

	/**
	 * Checks if a session is active.
	 *
	 * @param   mixed    role name string, role ORM object, or array with role names
	 * @param   boolean  check user for every role applied (TRUE, by default) or if any?
	 * @return  boolean
	 */
	public function logged_in($role = NULL, $all_required = TRUE, $ignore_roles = array())
	{
		$status = FALSE;

                if ($this->_login_in_progress)
                {
                    return FALSE;
                }

		// Get the user from the session
		$user = $this->get_user();


                //uzivatele, kteryu maji alespon jednu z roli, ktera je dana 3. argumentem
                //jsou ignorovani - pokud tedy uzivatel ma jednu z danych roli, tak
                //jej pri teto kontrole ignorujeme a je jakoby neprihlaseny.
                if ( ! empty($ignore_roles))
                {
                    foreach ($ignore_roles as $ignore_role)
                    {
                        if ($user->HasRole($ignore_role))
                        {
                            return FALSE;
                        }
                    }
                }

		if (is_object($user) AND $user instanceof Model_User AND $user->loaded())
		{
			// Everything is okay so far
			$status = TRUE;

			if ( ! empty($role))
			{
				// Multiple roles to check
				if (is_array($role))
				{
					// set initial status
					$status = (bool) $all_required;

					// Check each role
					foreach ($role as $_role)
					{
						if ( ! is_object($_role))
						{
							$_role = ORM::factory('role', array('name' => $_role));
						}

						// If the user doesn't have the role
						if ( ! $user->has('role', $_role))
						{
							// Set the status false and get outta here
							$status = FALSE;
							if ($all_required)
							{
								break;
							}
						}
					   elseif ( ! $all_required )
					   {
						   $status = TRUE;
						   break;
					   }
					}
				}
				// Single role to check
				else
				{
					if ( ! is_object($role))
					{
						// Load the role
						$role = ORM::factory('role', array('name' => $role));
					}

					// Check that the user has the given role
					$status = $user->has('role', $role);
				}
			}
		}

		return $status;
	}

	/**
	 * Logs a user in.
	 *
	 * @param   string   username
	 * @param   string   password
	 * @param   boolean  enable autologin
	 * @return  boolean
	 */
	protected function _login($user, $password, $remember)
	{

            $this->_login_in_progress = TRUE;

		if ( ! is_object($user))
		{
			$username = $user;

			// Load the user
            $user = $this->findUserToLogin($username);
		}

		// If the passwords match, perform a login
		if ($user->password === $password)
		{
			if ($remember === TRUE)
			{
				$this->remember($user);
			}

			// Finish the login
			$this->complete_login($user);

                        $this->_login_in_progress = FALSE;

			return TRUE;
		}

                        $this->_login_in_progress = FALSE;

		// Login failed
		return FALSE;
	}

    protected function findUserToLogin($username)
    {
        return ORM::factory('user')->findUserToLogin($username);
    }

	/**
	 * Forces a user to be logged in, without specifying a password.
	 *
	 * @param   mixed    username string, or user ORM object
	 * @param   boolean  mark the session as forced
	 * @return  boolean
	 */
	public function force_login($user, $mark_session_as_forced = FALSE)
	{
		if ( ! is_object($user))
		{
			$username = $user;

			// Load the user
			$user = ORM::factory('user');
			$user->where($user->unique_key($username), '=', $username)->find();
		}

		if ($mark_session_as_forced === TRUE)
		{
			// Mark the session as forced, to prevent users from changing account information
			$this->_session->set($this->_config['forced_key'], TRUE);
		}

		// Run the standard completion
		$this->complete_login($user);
	}

	/**
	 * Logs a user in, based on the authautologin cookie.
	 *
	 * @return  mixed
	 */
	public function auto_login()
	{
            $this->_login_in_progress = TRUE;
            
		if ($token = Cookie::get($this->_config['autologin_key']))
		{
			// Load the token and user
			$token = ORM::factory('user_token', array('token' => $token));

			if ($token->loaded() AND $token->user->loaded())
			{
				if ($token->user_agent === sha1(Request::$user_agent))
				{
					// Save the token to create a new unique token
					$token->save();

					// Set the new token
					Cookie::set($this->_config['autologin_key'], $token->token, (!is_numeric($token->expires) ? strtotime($token->expires) : $token->expires) - time());

					// Complete the login with the found data
					$this->complete_login($token->user);

                                        $this->_login_in_progress = FALSE;

					// Automatic login was successful
					return $token->user;
				}

				// Token is invalid
				$token->delete();
			}
		}

                $this->_login_in_progress = FALSE;

		return FALSE;
	}

	/**
	 * Gets the currently logged in user from the session (with auto_login check).
	 * Returns FALSE if no user is currently logged in.
	 *
	 * @return  mixed
	 */
	public function get_user()
	{
		$user = parent::get_user();

		if ($user === FALSE)
		{
			// check for "remembered" login
			$user = $this->auto_login();
		}

		return $user;
	}

	/**
	 * Log a user out and remove any autologin cookies.
	 *
	 * @param   boolean  completely destroy the session
	 * @param	boolean  remove all tokens for user
	 * @return  boolean
	 */
	public function logout($destroy = FALSE, $logout_all = FALSE)
	{
		// Set by force_login()
		$this->_session->delete($this->_config['forced_key']);

		if ($token = Cookie::get($this->_config['autologin_key']))
		{
			// Delete the autologin cookie to prevent re-login
			Cookie::delete($this->_config['autologin_key']);

			// Clear the autologin token from the database
			$token = ORM::factory('user_token', array('token' => $token));

			if ($token->loaded() AND $logout_all)
			{
				ORM::factory('user_token')->where('user_id', '=', $token->user_id)->delete_all();
			}
			elseif ($token->loaded())
			{
				$token->delete();
			}
		}

		return parent::logout($destroy);
	}

	/**
	 * Get the stored password for a username.
	 *
	 * @param   mixed   username string, or user ORM object
	 * @return  string
	 */
	public function password($user)
	{
		if ( ! is_object($user))
		{
			$username = $user;

			// Load the user
			$user = ORM::factory('user');
			$user->where($user->unique_key($username), '=', $username)->find();
		}

		return $user->password;
	}

	/**
	 * Complete the login for a user by incrementing the logins and setting
	 * session data: user_id, username, roles.
	 *
	 * @param   object  user ORM object
	 * @return  void
	 */
	protected function complete_login($user)
	{
		$user->complete_login();

		return parent::complete_login($user);
	}

	/**
	 * Compare password with original (hashed). Works for current (logged in) user
	 *
	 * @param   string  $password
	 * @return  boolean
	 */
	public function check_password($password)
	{
		$user = $this->get_user();

		if ($user === FALSE)
		{
			// nothing to compare
			return FALSE;
		}

		$hash = $this->hash_password($password, $this->find_salt($user->password));

		return $hash == $user->password;
	}

	/**
	 * Remember user (create token and save it in cookie)
	 *
	 * @param  Model_User  $user
	 * @return boolean
	 */
	public function remember($user = NULL)
	{
		if (is_null($user))
		{
			$user = $this->get_user();
		}
		if ( ! $user)
		{
			return FALSE;
		}

		// Create a new autologin token
		$token = ORM::factory('user_token');

		// Set token data
		$token->userid  = $user->pk();
		$token->expires = time() + $this->_config['lifetime'];
		$token->save();

		// Set the autologin cookie
		Cookie::set($this->_config['autologin_key'], $token->token, $this->_config['lifetime']);

		return TRUE;
	}

} // End Auth ORM
