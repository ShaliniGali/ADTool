<?php
/**
 * Rhombus_Form_validation Library Class
 *
 * Rule Reference
 * https://codeigniter.com/userguide3/libraries/form_validation.html#rule-reference
 * 
 * required, matches, regex_match, differs, is_unique, min_length, max_length, exact_length,
 * greater_than, greater_than_equal_to, less_than, less_than_equal_to, in_list, 
 * alpha, alpha_numeric, alpha_numeric_spaces, alpha_dash, numeric, integer, decimal,
 * is_natural, is_natural_no_zero, valid_url, valid_email, valid_emails, valid_ip, valid_base64
 *	 
 * Added rules:
 * valid_password, valid_passwords, valid_req_select
 */

defined('BASEPATH') || exit('No direct script access allowed');

/**
 * the Rhombus_Form_validation Library Class
 *
 */
class Rhombus_Form_validation extends CI_Form_validation
{
	public $DEFAULT_FORMAT_LABEL;
	/**
	 * Constructor
	 *
	 */
	public function __construct($rules=array())
	{
		parent::__construct($rules);
		$this->set_message('valid_req_select', 'The {field} is required. Please select an option');
		$this->set_message('valid_password', 'The {field} must contain a valid password.');
		$this->set_message('valid_passwords', 'The {field} field must contain all valid email passwords.');

		// set to false if you dibt want label to be automatically formatted by default
		$this->DEFAULT_FORMAT_LABEL = TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Run Rules
	 *
	 * This function takes an array of 
	 * rules as input, any custom error messages, 
	 * and callbacks for when form validation is success or not
	 *
	 * @param	array	$rules
     * @param	mixed	$true
	 * @param	mixed	$false
	 * @return	mixed
	 */
	public function run_rules($form=array(), $callback_success=NULL, $callback_failure=NULL)
	{	
		$this->all_rules($form);
        return $this->add_run($callback_success, $callback_failure);
	}

	// --------------------------------------------------------------------

	/**
	 * All Rules
	 *
	 * This function takes an array of 
	 * rules as input, any custom error messages
	 * 
	 * @param	array	$rules
	 * @return	Rhombus_form_validation
	 */
	public function all_rules($form=array())
	{	
		foreach($form as $field => $rule)
		{
			$rules = array();
			$errors = array();
			$label = '';
			$format_label = $this->DEFAULT_FORMAT_LABEL;

			if (isset($rule['rules'])) 
			{
				$rules = $rule['rules'];
			}

			if (isset($rule['errors']) )
			{
				$errors = $rule['errors'];
			}

			if (isset($rule['label']))
			{
				$label = $rule['label'];
			} 

			// By default format label will be set by public variable $this->DEFAULT_FORMAT_LABEL
			if (isset($rule['format_label']))
			{
				$format_label = (bool) $rule['format_label'];
			}

			$this->add_rules($field, $label, $rules, $errors, $format_label);
		}

		return $this;
	}

    // --------------------------------------------------------------------

	/**
	 * Add Run
	 *
	 * This function takes function parameters as callbacks to
     * actions done when from runs TRUE or FALSE
	 *
	 * @param	mixed	$true
	 * @param	mixed	$false
	 * @return	mixed
	 */
    public function add_run($true=NULL, $false=NULL)
    {
        if ($this->run() === FALSE)
        {
            if ($false)
            {
				if (is_callable($false)){
					$false();
				} 
				else 
				{

					if ( ! method_exists($this->CI, $false))
					{
						log_message('debug', 'Unable to find callback validation rule: '.$false);
						return FALSE;
					}
					else
					{
						// Run the function and return result
						return $this->CI->$false();
					}
				}
            }
            else
            {
                return validation_errors();
            }
		} 
		else 
		{
            if ($true)
            {
				if (is_callable($true)){
					$true();
				} 
				else 
				{
					if ( ! method_exists($this->CI, $true))
					{
						log_message('debug', 'Unable to find callback validation rule: '.$true);
						return FALSE;
					}
					else
					{
						// Run the function and return result
						return $this->CI->$true();
					}
				}
            }
            else
            {
                return 'success';
            }
		}
    }

	// --------------------------------------------------------------------

	/**
	 * Add Rules
	 *
	 * This function takes an array of field names and validation
	 * rules as input, any custom error messages, validates the info,
	 * and stores it - is a copy from CI_Form_validation with modification
	 *
	 * @param	mixed	$field
	 * @param	string	$label
	 * @param	mixed	$rules
	 * @param	array	$errors
	 * @return	Rhombus_Form_validation
	 */
	public function add_rules($field, $label='', $rules=array(), $errors=array(), $format_label = NULL)
	{
		if ($format_label === NULL)
		{
			$format_label = $this->DEFAULT_FORMAT_LABEL;
		}

		// No reason to set rules if we have no POST data
		// or a validation array has not been specified
		if ($this->CI->input->method() !== 'post' && empty($this->validation_data))
		{
			return $this;
		}

		// If an array was passed via the first parameter instead of individual string
		// values we cycle through it and recursively call this function.
		if (is_array($field))
		{
			foreach ($field as $row)
			{
				// Houston, we have a problem...
				if ( ! isset($row['field'], $row['rules']))
				{
					continue;
				}

				// If the field label wasn't passed we use the field name
				$label = isset($row['label']) ? $row['label']: $row['field'];
				$label = $this->get_formatted_label((string)$label, $format_label);
				
				// Add the custom error message array
				$errors = (isset($row['errors']) && is_array($row['errors'])) ? $row['errors'] : array();

				// Here we go!
				$this->set_rules($row['field'], $label, $row['rules'], $errors);
			}

			return $this;
		}

		// No fields or no rules? Nothing to do...
		if ( ! is_string($field) OR $field === '' OR empty($rules))
		{
			return $this;
		}
		elseif ( ! is_array($rules))
		{
			// BC: Convert pipe-separated rules string to an array
			if ( ! is_string($rules))
			{
				return $this;
			}

			$rules = preg_split('/\|(?![^\[]*\])/', $rules);
		}

		// If the field label wasn't passed we use the field name
		$label = ($label === '') ? $field : $label;
		$label = $this->get_formatted_label((string)$label, $format_label);


		$indexes = array();

		// Is the field name an array? If it is an array, we break it apart
		// into its components so that we can fetch the corresponding POST data later
		if (($is_array = (bool) preg_match_all('/\[(.*?)\]/', $field, $matches)) === TRUE)
		{
			sscanf($field, '%[^[][', $indexes[0]);

			for ($i = 0, $c = count($matches[0]); $i < $c; $i++)
			{
				if ($matches[1][$i] !== '')
				{
					$indexes[] = $matches[1][$i];
				}
			}
		}

		// Adding default messages for required / valid_
		foreach ($rules as $rule)
		{
			if ($rule == 'required')
			{   
				if (!isset($errors['required']))
				{
					$this->add_required_err_msg($errors);
				}
			}
			if (strpos($rule, 'valid_'))
			{
				if (!isset($errors[$rule]))
				{
					$this->add_valid_err_msg($errors, $rule);
				}
			}
		}

		// Build our master array
		$this->_field_data[$field] = array(
			'field'		=> $field,
			'label'		=> $label,
			'rules'		=> $rules,
			'errors'	=> $errors,
			'is_array'	=> $is_array,
			'keys'		=> $indexes,
			'postdata'	=> NULL,
			'error'		=> ''
		);

		return $this;
	}

	// --------------------------------------------------------------------

	public function get_formatted_label($str, $format_label = NULL)
	{
		if ($format_label === NULL)
		{
			$format_label = $this->DEFAULT_FORMAT_LABEL;
		}

		if ($format_label)
		{
			$str = str_replace('_', ' ', $str);
			$str = str_replace('-', ' ', $str);
			$str = ucfirst($str);
		}

		return $str;
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Req Select
	 *
	 * @param	string	$str
	 * @return	bool
	 */
	public function valid_req_select($str)
    {
        return ($str != 'default' && $str != '');
	}

	// --------------------------------------------------------------------

	/**
	 * Valid Password
	 *
	 * @param	string $str
	 * @return	bool
	 */
	public function valid_password($str)
	{
		$password = trim($str);
        $regex_lowercase = '/[a-z]/';
        $regex_uppercase = '/[A-Z]/';
        $regex_number = '/[0-9]/';
		$regex_special = '/[!@#$%^&*()\-_=+{};:,<.>§~]/';

		if (!$this->regex_match($password, $regex_lowercase))
        {
            $this->set_message('valid_password', 'The {field} field must have at least one lowercase letter.');
            return FALSE;
        }
        if (!$this->regex_match($password, $regex_uppercase))
        {
            $this->set_message('valid_password', 'The {field} field must have at least one uppercase letter.');
            return FALSE;
        }
        if (!$this->regex_match($password, $regex_number))
        {
            $this->set_message('valid_password', 'The {field} field must have at least one number.');
            return FALSE;
        }
        if (!$this->regex_match($password, $regex_special))
        {
            $this->set_message('valid_password', 'The {field} field must have at least one special character.' . ' ' . htmlentities('!@#$%^&*()\-_=+{};:,<.>§~'));
            return FALSE;
        }
        if (!$this->min_length($password, 16))
        {
            $this->set_message('valid_password', 'The {field} field must be at least 16 characters in length.');
            return FALSE;
        }
		if (!$this->max_length($password, 32))
        {
            $this->set_message('valid_password', 'The {field} field cannot exceed 32 characters in length.');
            return FALSE;
        }
        return TRUE;
	}

	// --------------------------------------------------------------------

	
	/**
	 * Valid Passwords
	 *
	 * @param	string $str
	 * @return	bool
	 */
	public function valid_passwords($str)
	{
		if (strpos($str, ',') === FALSE)
		{
			return $this->valid_password(trim($str));
		}

		foreach (explode(',', $str) as $password)
		{
			if (trim($password) !== '' && $this->valid_password(trim($password)) === FALSE)
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Add Default Required Error Message
	 *
	 * @param	string
	 * @return	bool
	 */
	public function add_required_err_msg(&$errors, $str='')
	{
		if ($str == '')
		{
			$str = 'You must provide a(n) %s';
		} 
		return $this->_set_object_key_value($errors, 'required', $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Add Default Validator Error Message
	 *
	 * @param	string $str
	 * @return	bool
	 */
	public function add_valid_err_msg(&$errors, $valid_str, $str='')
	{
		if ($str == '')
		{
			$str = 'The %s is not valid';
		} 
		return $this->_set_object_key_value($errors, $valid_str, $str);
	}

	// --------------------------------------------------------------------

	/**
	 * Set object key valie
	 *
	 * @param	string
	 * @return	bool
	 */
	protected function _set_object_key_value(&$object, $key, $value)
	{
		$object[$key] = $value;
		return $object;
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch validation data
	 * 
	 * @return	array
	 */
	public function get_data()
	{
		return $this->validation_data;
	}

	public function set_default_format_label($bool)
	{
		$this->DEFAULT_FORMAT_LABEL = (bool) $bool;
	}
}