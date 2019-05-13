<?php


namespace ore;


class ORE_Array {

	public $_strict = false;
	public $_add = true;
	protected $_array = array();

	/**
	 * Ore_Array constructor.
	 *
	 * @param $array
	 */
	public function __construct($array) {
		$this->_array = $array;
	}

	/**
	 * 
	 */
	public function getArray() {
		return $this->_array;
	}
	
	/**
	 * @param $key
	 * @return mixed
	 * @throws \Exception
	 */
	public function __get($key) {
		if (array_key_exists($key, $this->_array)) {
			return $this->_array[$key];
		}
		if (TRUE === $this->_strict) {
//            trigger_error ( "key[$key] was not found");
			throw new \Exception("key[$key] was not found");
		}
		return null;
	}

	/**
	 * @param $key
	 * @return mixed
	 * @throws \Exception
	 */
	public function __set($key, $val) {
		if (array_key_exists($key, $this->_array)) {
			$this->_array[$key] = $val;
			return;
		}
		if (TRUE === $this->_add) {
			$this->_array[$key] = $val;
		}
	}

	/**
	 * @param $option
	 * @return arrayay
	 */
	public function mb_convert_kana($option) {
		foreach ($this->_array as & $s) {
			if (string == gettype($s)) {
				$s = mb_convert_kana($s, $option);
			}
		}
		return $this->_array;
	}

	/**
	 * @return arrayay
	 */
	public function strtolower() {
		foreach ($this->_array as & $s) {
			if (string == gettype($s)) {
				$s = strtolower($s);
			}
		}
		return $this->_array;
	}

	/**
	 * @return arrayay
	 */
	public function strtoupper() {
		foreach ($this->_array as & $s) {
			if (string == gettype($s)) {
				$s = strtoupper($s);
			}
		}
		return $this->_array;
	}

	/**
	 * @return array|null
	 */
	public function array_flip() {
		$this->_array = array_flip($this->_array);
		return $this->_array;
	}
}