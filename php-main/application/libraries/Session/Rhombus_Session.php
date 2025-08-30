<?php 

defined('BASEPATH') || exit('No direct script access allowed');

#[AllowDynamicProperties]
class Rhombus_Session extends CI_Session
{
	private const APP_DESTROY = '__app_destroy';
	private const APP_ACESSED = '__app_accessed';
	private const SESSION_LIFETIME = 3600;

	public function __construct(array $params = array()) {
		// No sessions under CLI
		if (is_cli())
		{
			log_message('debug', 'Session: Initialization under CLI aborted.');
			return;
		}
		elseif ((bool) ini_get('session.auto_start'))
		{
			log_message('error', 'Session: session.auto_start is enabled in php.ini. Aborting.');
			return;
		}
		elseif ( ! empty($params['driver']))
		{
			$this->_driver = $params['driver'];
			unset($params['driver']);
		}
		elseif ($driver = config_item('sess_driver'))
		{
			$this->_driver = $driver;
		}
		// Note: BC workaround
		elseif (config_item('sess_use_database'))
		{
			log_message('debug', 'Session: "sess_driver" is empty; using BC fallback to "sess_use_database".');
			$this->_driver = 'database';
		}

		$class = $this->_ci_load_classes($this->_driver);

		// Configuration ...
		$this->_configure($params);
		$this->_config['_sid_regexp'] = $this->_sid_regexp;

		$class = new $class($this->_config);
		if ($class instanceof SessionHandlerInterface)
		{
			if (is_php('5.4'))
			{
				session_set_save_handler($class, TRUE);
			}
			else
			{
				session_set_save_handler(
					array($class, 'open'),
					array($class, 'close'),
					array($class, 'read'),
					array($class, 'write'),
					array($class, 'destroy'),
					array($class, 'gc')
				);

				register_shutdown_function('session_write_close');
			}
		}
		else
		{
			log_message('error', "Session: Driver '".$this->_driver."' doesn't implement SessionHandlerInterface. Aborting.");
			return;
		}

		// Sanitize the cookie, because apparently PHP doesn't do that for userspace handlers
		if (isset($_COOKIE[$this->_config['cookie_name']])
			&& (
				! is_string($_COOKIE[$this->_config['cookie_name']])
				OR ! preg_match('#\A'.$this->_sid_regexp.'\z#', $_COOKIE[$this->_config['cookie_name']])
			)
		)
		{
			unset($_COOKIE[$this->_config['cookie_name']]);
		}

		session_start();

		$regenerate_time = config_item('sess_time_to_update');
		// log the user out if they have not accessed their session
		// in over 10 minutes
		if (
			isset($_SESSION[self::APP_ACESSED]) &&
			$_SESSION[self::APP_ACESSED] < (time() - self::SESSION_LIFETIME)
		) {
			log_message(
				'error',
				sprintf(
					'New session that has not been used '.
					'longer than the cookie expiration of %s has been logged out',
					self::SESSION_LIFETIME
				)
			);
			
			$this->log_out_user();
		} elseif (isset($_SESSION[self::APP_DESTROY])) {
			$appDestroy = $_SESSION[self::APP_DESTROY];

			log_message('error', 'Attempted to use old session');

			$this->sess_regenerate(false);

			// Remove all session data for this session if it is destroyed and more
			// old than the regenerate time.  Additionally log the user out.
			if ($appDestroy <= time() - 5) {
				log_message('error', 'An old session logged out');

				$this->log_out_user();
			}

			unset($appDestroy);
		}
		// Is session ID auto-regeneration configured? (ignoring ajax requests)
		elseif (
			(
				empty($_SERVER['HTTP_X_REQUESTED_WITH']) ||
				strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest'
			)
			&& $regenerate_time > 0
		)
		{
			if ( ! isset($_SESSION['__ci_last_regenerate']))
			{
				$_SESSION['__ci_last_regenerate'] = time();
			}
			elseif ($_SESSION['__ci_last_regenerate'] < (time() - $regenerate_time))
			{
				$this->sess_regenerate(false);
			}
		}
		// Another work-around ... PHP doesn't seem to send the session cookie
		// unless it is being currently created or regenerated
		elseif (
			isset($_COOKIE[$this->_config['cookie_name']]) &&
			$_COOKIE[$this->_config['cookie_name']] === session_id()
		)
		{
			setcookie(
				$this->_config['cookie_name'],
				session_id(),
				(empty($this->_config['cookie_lifetime']) ? 0 : time() + $this->_config['cookie_lifetime']),
				$this->_config['cookie_path'],
				$this->_config['cookie_domain'],
				$this->_config['cookie_secure'],
				TRUE
			);
		}

		// set last accessed time to log out old and lost sessions
		$_SESSION[self::APP_ACESSED] = time();

		$this->_ci_init_vars();

		log_message('info', "Session: class initialized using '".$this->_driver."' driver.");
	}

	/**
	 * Session regenerate
	 *
	 * Legacy CI_Session compatibility method
	 *
	 * @param	bool	$destroy	Destroy old session data flag
	 * @return	void
	 */
	public function sess_regenerate($destroy = false)
	{
		
		$_SESSION[self::APP_DESTROY] = time();

		parent::sess_regenerate(false);

		// New session does not need them
		unset($_SESSION[self::APP_DESTROY]);
	}

	public function log_out_user()
	{
		if (
			!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
		) {
			return false;
		}

		$_SESSION = null;

		session_write_close();

		setcookie(
			$this->_config['cookie_name'],
			'',
			(empty($this->_config['cookie_lifetime']) ? 0 : time() + $this->_config['cookie_lifetime']),
			$this->_config['cookie_path'],
			$this->_config['cookie_domain'],
			$this->_config['cookie_secure'],
			TRUE
		);

		redirect('login/logout');
		exit();
	}
}
