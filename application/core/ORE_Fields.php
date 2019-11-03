<?php

/**
 *
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
	 * @param array $params
	 */
	public function set($params = array()) {

		if ('array' === gettype($params)) {
			if(0 === count($params)) return;
			$params = (object)$params;
		}

		if ('object' === gettype($params)) {
			$public_vars = get_class_public_vars(get_class($this));
			foreach ($public_vars as $key => $default) {
				if (TRUE === property_exists($params, $key)) {
					$val = $params->{$key};
					if ($this->is_btnf($key) AND '' === strval($val)) {
						$val = NULL;
					}
					$this->$key = $val;
				}
			}
		}
	}

	/**
	 * @var array
	 */
	protected $_blank_to_null_fields = array();

	/**
	 * @param $filed_nm
	 * @return bool
	 */
	public function is_btnf($filed_nm) {
		return (in_array($filed_nm, $this->_blank_to_null_fields));
	}

	/**
	 * @param $obj
	 */
	public function blank_to_null(& $obj) {
		foreach ($obj as $filed_nm => & $d) {
			if ($this->is_btnf($filed_nm) AND '' === strval($d)) {
				$d = NULL;
			}
		}
	}

	/**
	 * @param $params
	 * @param string $type
	 * @param string $key_prefix
	 * @return array|object|void
	 */
	public function filter($params, $type = 'object', $param_key_prefix = '') {

		if ('array' === gettype($params)) {
			if(0 === count($params)) return;
			$params = (object)$params;
		}

		$data = array();
		$public_vars = get_class_public_vars(get_class($this));
		foreach ($public_vars as $key => $default) {
			$param_key = $param_key_prefix.$key;
			if (TRUE === property_exists($params, $param_key)) {
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

