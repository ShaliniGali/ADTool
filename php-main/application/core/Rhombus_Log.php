<?php 

defined('BASEPATH') || exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Rhombus_Log extends CI_Log
{   

    public function __construct() {
        parent::__construct();
		
		if (P1_FLAG === true && ENVIRONMENT !== 'testing') {
			$this->_log_path = 'php://stdout';
		}


		$this->config =& get_config();
    }

	// --------------------------------------------------------------------

	/**
	 * Write Log File
	 *
	 * Generally this function will be called using the global log_message() function
	 *
	 * @param	string	$level 	The error level: 'error', 'debug' or 'info'
	 * @param	string	$msg 	The error message
	 * @return	bool
	 */
	public function write_log($level, $msg) {
		$result = 0;

		if (P1_FLAG !== true || ENVIRONMENT === 'testing') {
			$result = parent::write_log($level, $msg);
		} else {
			$write = true;
			if ($this->config['log_threshold'] == 0) {
				$write = false;
			}

			$level = strtoupper($level);
			
			if (( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
			&& ! isset($this->_threshold_array[$this->_levels[$level]]))
			{
				$write = false;
			}

			if ($write !== false) {
				$result = $this->_write_log_message($level, $msg);
			}
			
			unset($write);
		}

		return is_int($result);
    }

	private function _write_log_message($level, $msg)
	{
		$message = '';
		$filepath = $this->_log_path;

		if (!$fp = @fopen($filepath, 'ab')) {
			return false;
		}

		flock($fp, LOCK_EX);

		// Instantiating DateTime with microseconds appended to initial date is needed for proper support of this format
		if (strpos($this->_date_fmt, 'u') !== FALSE) {
			$microtime_full = microtime(TRUE);
			$microtime_short = sprintf("%06d", ($microtime_full - floor($microtime_full)) * 1000000);
			$date = new DateTime(date('Y-m-d H:i:s.' . $microtime_short, $microtime_full));
			$date = $date->format($this->_date_fmt);
		} else {
			$date = date($this->_date_fmt);
		}

		$message .= $this->_format_line($level, $date, $msg);

		$result = 0;
		for ($written = 0, $length = self::strlen($message); $written < $length; $written += $result) {
			if (($result = fwrite($fp, self::substr($message, $written))) === FALSE) {
				break;
			}
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		return $result;
	}
    
}