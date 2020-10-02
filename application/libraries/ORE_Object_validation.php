<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */
namespace ore;

/**
 * Class ORE_Object_validation
 *
 * @author naoyuki onishi
 */
class ORE_Object_validation extends MY_Form_validation {

	private $_object = null;

	/**
	 *
	 * @see CI_Form_validation::set_data()
	 */
	public function set_data($data = []) {
		throw new \Exception('do not use set_data');
	}


	/**
	 *
	 */
	public function set_object(& $object) {
		if (! isset($object) OR ! is_object($object)) {
			throw new \Exception('$object is not object');
		}
		$this->_object = $object;
		return $this;
	}

	/**
	 *
	 */
	public function object(& $object) {
		return $this->_object;
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
	 * @return	CI_Form_validation
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
		if ( ! is_string($field) OR ! is_string($rules) OR $field === '')
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
		// Do we even have any data to process?  Mm?
		if (! is_object($this->_object)) {
			return FALSE;
		}

		// Does the _field_data array containing the validation rules exist?
		// If not, we look to see if they were assigned via a config file
		if (count($this->_field_data) === 0)
		{
			// No validation rules?  We're done...
			if (count($this->_config_rules) === 0)
			{
				return FALSE;
			}

//			// Is there a validation rule for the particular URI being accessed?
//			$uri = ($group === '') ? trim($this->CI->uri->ruri_string(), '/') : $group;
//
//			if ($uri !== '' && isset($this->_config_rules[$uri]))
//			{
//				$this->set_rules($this->_config_rules[$uri]);
//			}
//			else
//			{
				$this->set_rules($this->_config_rules);
//			}

			// Were we able to set the rules correctly?
			if (count($this->_field_data) === 0)
			{
				log_message('debug', 'Unable to find validation rules');
				return FALSE;
			}
		}

		// Load the language file containing error messages
		$this->CI->lang->load('validation');

		// Cycle through the rules for each field and match the corresponding $validation_data item
		foreach ($this->_field_data as $field => $row)
		{
			// Fetch the data from the validation_data array item and cache it in the _field_data array.
			// Depending on whether the field name is an array or a string will determine where we get it from.
			if ($row['is_array'] === TRUE)
			{
				$this->_field_data[$field]['postdata'] = $this->_reduce_array($this->_object, $row['keys']);
			}
			elseif (property_exists($this->_object, $field))
			{
				$this->_field_data[$field]['postdata'] = $this->_object->{$field};
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
	 * Traverse a multidimensional $this->_object index until the data is found
	 *
	 * @param	object
	 * @param	array
	 * @param	int
	 * @return	mixed
	 */
	protected function _reduce_array($obj, $keys, $i = 0)
	{
		if (is_object($obj) && isset($keys[$i]))
		{
			$k = $keys[$i];
			return isset($obj->{$k}) ? $this->_reduce_array($obj->{$k}, $keys, ($i+1)) : NULL;
		}

		// NULL must be returned for empty fields
		return ($obj === '') ? NULL : $obj;
	}

	// --------------------------------------------------------------------

	/**
	 * Re-populate the $this->_object with our finalized and processed data
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
					if (isset($this->_object->{$row['field']}))
					{
						$this->_object->{$row['field']} = $row['postdata'];
					}
				}
				else
				{
					// start with a reference
					$post_ref =$this->_object;

					// before we assign values, make a reference to the right POST key
					if (count($row['keys']) === 1)
					{
					}
					else
					{
						foreach ($row['keys'] as $val)
						{
							$post_ref =& $post_ref->{$val};
						}
					}

					if (is_array($row['postdata']))
					{
						$array = [];
						foreach ($row['postdata'] as $k => $v)
						{
							$array[$k] = $v;
						}
						$f = preg_replace('/\[[^\]]*\]/', '', $row['field']);
						$post_ref->{$f} = $array;
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
	public function matches($str, $field)
	{
		if ( ! isset($this->_object->{$field}))
		{
			return FALSE;
		}

		$field = $this->_object->{$field};

		return ($str !== $field) ? FALSE : TRUE;
	}

}
