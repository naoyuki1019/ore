<?php

/**
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * Class ORE_Fields
 *
 * @package ore
 */
class ORE_Fields extends ORE_Params {

	/**
	 * @param mixed $params
	 * @param mixed $value
	 */
	public function set($params = [], $value = null) {

		if ('array' === gettype($params)) {
			if (0 === count($params)) return;
			$params = (object)$params;
		}

		$public_vars = get_class_public_vars(get_class($this));

		if ('object' === gettype($params)) {
			foreach ($public_vars as $key => $default) {
				if (true === property_exists($params, $key)) {
					$val = $params->{$key};
					if ($this->is_btnf($key) && '' === strval($val)) {
						$val = NULL;
					}
					$this->{$key} = $val;
				}
			}
		}
		else {
			if (true === array_key_exists($params, $public_vars)) {
				$this->{$params} = $value;
			}
		}
	}

	/**
	 * @var array
	 */
	protected $_blank_to_null_fields = [];

	/**
	 * @param string $filed_nm
	 * @return bool
	 */
	public function is_btnf($filed_nm) {
		return (in_array($filed_nm, $this->_blank_to_null_fields));
	}

	/**
	 * @param mixed $obj
	 */
	public function blank_to_null(&$obj) {
		foreach ($obj as $filed_nm => & $d) {
			if ($this->is_btnf($filed_nm) && '' === strval($d)) {
				$d = NULL;
			}
		}
	}

	/**
	 * @param mixed $params
	 * @param string $type
	 * @param string $key_prefix
	 * @return array|object|void
	 */
	public function filter($params, $type = 'object', $param_key_prefix = '') {

		if ('array' === gettype($params)) {
			if (0 === count($params)) return;
			$params = (object)$params;
		}

		$data = [];
		$public_vars = get_class_public_vars(get_class($this));
		foreach ($public_vars as $key => $default) {
			$param_key = $param_key_prefix.$key;
			if (true === property_exists($params, $param_key)) {
				$data[$key] = $params->{$param_key};
			}
		}

		if ('object' === $type) {
			return (object)$data;
		}
		else {
			return $data;
		}
	}
}
