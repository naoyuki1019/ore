<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */
namespace ore;

/**
 * Class ORE_Data_validation
 *
 * @author naoyuki onishi
 */
class ORE_Data_validation extends MY_Form_validation {

	private $_data_array = [];

	/**
	 *
	 */
	public function set_data($arr = '') {
		$this->_data_array = $arr;
		return $this;
	}

	/**
	 *
	 */
	public function data() {
		return $this->_data_array;
	}

	/**
	 * @param $key
	 * @return mixed|null
	 */
	public function get($key) {
		if ($this->array_key_exists($key)) {
			return $this->_data_array[$key];
		}
		return null;
	}

	/**
	 * @param $key
	 * @return bool
	 */
	public function array_key_exists($key) {
		return array_key_exists($key, $this->_data_array);
	}

	// --------------------------------------------------------------------

	/**
	 * Set Rules
	 *
	 * This function takes an array of field names and validation
	 * rules as input, validates the info, and stores it
	 *
	 * @param	mixed	$field
	 * @param	string	$label
	 * @param	mixed	$rules
	 * @return ORE_Data_validation
	 */
	public function set_rules($field, $label = '', $rules = '')
	{
		// // No reason to set rules if we have no POST data
		// // or a validation array has not been specified
		// if ($this->CI->input->method() !== 'post' && empty($this->validation_data))
		// {
		// 	return $this;
		// }

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
				$label = isset($row['label']) ? $row['label'] : $row['field'];

				// Here we go!
				$this->set_rules($row['field'], $label, $row['rules']);
			}

			return $this;
		}

		// Convert an array of rules to a string
		if (is_array($rules))
		{
			$rules = implode('|', $rules);
		}

		// No fields? Nothing to do...
		if ( ! is_string($field) || ! is_string($rules) || $field === '')
		{
			return $this;
		}

		// If the field label wasn't passed we use the field name
		$label = ($label === '') ? $field : $label;

		// Is the field name an array? If it is an array, we break it apart
		// into its components so that we can fetch the corresponding POST data later
		$indexes = [];
		if (preg_match_all('/\[(.*?)\]/', $field, $matches))
		{
			sscanf($field, '%[^[][', $indexes[0]);

			for ($i = 0, $c = count($matches[0]); $i < $c; $i++)
			{
				if ($matches[1][$i] !== '')
				{
					$indexes[] = $matches[1][$i];
				}
			}

			$is_array = TRUE;
		}
		else
		{
			$is_array	= FALSE;
		}

		// Build our master array
		$this->_field_data[$field] = array(
			'field'		=> $field,
			'label'		=> $label,
			'rules'		=> $rules,
			'is_array'	=> $is_array,
			'keys'		=> $indexes,
			'postdata'	=> NULL,
			'key_exists' => NULL,
			'error'		=> ''
		);

		return $this;
	}


	// --------------------------------------------------------------------

	/**
	 * Run the Validator
	 *
	 * This function does all the work.
	 *
	 * @param	string	$group
	 * @return	bool
	 */
	public function run($group = '')
	{
		// // Do we even have any data to process?  Mm?
		// if (count($this->_data_array) === 0)
		// {
		// 	return FALSE;
		// }

		// Does the _field_data array containing the validation rules exist?
		// If not, we look to see if they were assigned via a config file
		if (count($this->_field_data) === 0)
		{
			// No validation rules?  We're done...
			if (count($this->_config_rules) === 0)
			{
				return FALSE;
			}

			// // Is there a validation rule for the particular URI being accessed?
			// $uri = ($group === '') ? trim($this->CI->uri->ruri_string(), '/') : $group;
			//
			// if ($uri !== '' && isset($this->_config_rules[$uri]))
			// {
			// 	$this->set_rules($this->_config_rules[$uri]);
			// }
			// else
			// {
				$this->set_rules($this->_config_rules);
			// }

			// Were we able to set the rules correctly?
			if (count($this->_field_data) === 0)
			{
				log_message('debug', 'Unable to find validation rules');
				return FALSE;
			}
		}

		// Load the language file containing error messages
		$this->CI->lang->load('validation', $this->setted_lang);

		// Cycle through the rules for each field and match the corresponding $validation_data item
		foreach ($this->_field_data as $field => $row)
		{
			// Fetch the data from the validation_data array item and cache it in the _field_data array.
			// Depending on whether the field name is an array or a string will determine where we get it from.
			if ($row['is_array'] === TRUE)
			{
				$this->_field_data[$field]['postdata'] = $this->_reduce_array($this->_data_array, $row['keys']);
				$this->_field_data[$field]['key_exists'] = 1;

				if (array_key_exists(0, $row['keys']))
				{
					$this->_field_data[$field]['key_exists'] = (array_key_exists($row['keys'][0], $this->_data_array)) ? 1 : 0;
				}
				else
				{
					$this->_field_data[$field]['key_exists'] = 0;
				}

			}
			else {
				if (array_key_exists($field, $this->_data_array))
				{
					$this->_field_data[$field]['postdata'] = $this->_data_array[$field];
					$this->_field_data[$field]['key_exists'] = 1;
				}
				else {
					$this->_field_data[$field]['postdata'] = NULL;
					$this->_field_data[$field]['key_exists'] = 0;
				}
			}
		}

		// Execute validation rules
		// Note: A second foreach (for now) is required in order to avoid false-positives
		//	 for rules like 'matches', which correlate to other validation fields.
		foreach ($this->_field_data as $field => $row)
		{
			// Don't try to validate if we have no rules set
			if (empty($row['rules']))
			{
				continue;
			}

			$this->_execute($row, explode('|', $row['rules']), $this->_field_data[$field]['postdata']);
		}

		// Did we end up with any errors?
		$total_errors = count($this->_error_array);
		if ($total_errors > 0)
		{
			$this->_safe_form_data = TRUE;
		}

		// Now we need to re-set the POST data with the new, processed data
		$this->_reset_post_array();

		return ($total_errors === 0);
	}


	// --------------------------------------------------------------------

	/**
	 * Re-populate the _POST array with our finalized and processed data
	 *
	 * @return	void
	 */
	protected function _reset_post_array()
	{
		foreach ($this->_field_data as $field => $row)
		{
			if ($row['postdata'] !== NULL)
			{
				if ($row['is_array'] === FALSE)
				{
					if (isset($this->_data_array[$row['field']]))
					{
						$this->_data_array[$row['field']] = $row['postdata'];
					}
				}
				else
				{
					// start with a reference
					$post_ref =& $this->_data_array;

					// before we assign values, make a reference to the right POST key
					if (count($row['keys']) === 1)
					{
						$post_ref =& $post_ref[current($row['keys'])];
					}
					else
					{
						foreach ($row['keys'] as $val)
						{
							$post_ref =& $post_ref[$val];
						}
					}

					if (is_array($row['postdata']))
					{
						$array = [];
						foreach ($row['postdata'] as $k => $v)
						{
							$array[$k] = $v;
						}

						$post_ref = $array;
					}
					else
					{
						$post_ref = $row['postdata'];
					}
				}
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Match one field to another
	 *
	 * @access	public
	 * @param	string
	 * @param	field
	 * @return	bool
	 */
	function matches($str, $field)
	{
		if ( ! isset($this->_data_array[$field]))
		{
			return FALSE;
		}

		$field = $this->_data_array[$field];

		return ($str !== $field) ? FALSE : TRUE;
	}

}
