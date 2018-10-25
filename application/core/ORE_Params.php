<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;


/**
 * Class ORE_Params
 * @package ore
 */
class ORE_Params {


	/**
	 * ORE_Params constructor.
	 * @param array $params
	 */
	public function __construct($params = array()) {

		$this->set($params);
	}


	/**
	 * @param array $params
	 */
	public function set ($params = array()) {

		if ('array' === gettype($params) OR 'object' === gettype ($params)) {

			foreach ($params as $key => $val) {

				$this->$key = $val;
			}
		}
	}


	/**
	 * @return array
	 */
	public function to_array () {

		$tmp = array();
		$array = get_object_vars($this);
		$this->_to_array($tmp, $array);
		return $tmp;
	}

	/**
	 * @return array
	 */
	public function to_public_array () {

		$tmp = array();
		$array = get_object_public_vars($this);
		$this->_to_array($tmp, $array);
		return $tmp;
	}

	/**
	 * @param $tmp
	 * @param $array
	 */
	private function _to_array(& $tmp, & $array) {

		if (is_array($array) OR is_object($array)) {

			foreach ($array as $key => $val) {

				if (is_array($val)) {

					$tmp[$key] = array();
					$this->_to_array($tmp[$key], $val);
				}

				else if (is_object($val)) {

					$val = get_object_vars($val);
					$tmp[$key] = array();
					$this->_to_array($tmp[$key], $val);
				}

				else {
					$tmp[$key] = $val;
				}

			}
		}
	}


	/**
	 * @param array $remove_keys
	 * @return string
	 */
	public function to_uri ($remove_keys = array()) {

		if (! is_array($remove_keys)) {
			$remove_keys = array($remove_keys);
		}

		$keys = array();
		$tmp = array();
		$array = $this->to_array();

		foreach ($array as $key => $val) {
			if (in_array($key, $remove_keys)) {
				unset($array[$key]);
			}
		}

		$this->_to_uri($keys, $tmp, $array);

		if (0 < count($tmp)) {
			return implode('/', $tmp);
		}
		else {
			return "";
		}
	}


	/**
	 * @param $keys
	 * @param $tmp
	 * @param $array
	 */
	private function _to_uri(& $keys, & $tmp, & $array) {

		foreach ($array as $key => $val) {

			array_push($keys, $key);

			if (is_array($val)) {

				if (0 < count($val)) {
					$this->_to_uri($keys, $tmp, $val);
				}
			}

			else if (is_object($val)) {

				$val = get_object_vars($val);

				if (0 < count($val)) {
					$this->_to_uri($keys, $tmp, $val);
				}
			}

			else {
				if ("" != $val OR 0 === $val) {

					$uri_keys = $keys;

					$uri_key = array_shift($uri_keys);

					foreach ($uri_keys as $uri_key2) {
						$uri_key .= "[" . urlencode($uri_key2) . "]";
					}

					$tmp[] = $uri_key;

					$tmp[] = urlencode($val);
				}
			}

			array_pop($keys);
		}
	}


	/**
	 * @param array $pointing_keys
	 * @return string
	 */
	public function to_pointing_uri ($pointing_keys = array()) {

		if (! is_array($pointing_keys)) {
			$pointing_keys = array($pointing_keys);
		}

		$array = $this->to_array();


		$keys = array();
		$tmp = array();
		$array = $this->to_array();
		foreach ($array as $key => $val) {
			if (! in_array($key, $pointing_keys)) {
				unset($array[$key]);
			}
		}

		$this->_to_uri($keys, $tmp, $array);

		if (0 < count($tmp)) {
			return implode('/', $tmp);
		}
		else {
			return "";
		}
	}
}
