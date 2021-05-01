<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * Class ORE_Const
 *
 * @package ore
 */
class ORE_Const {

	protected $_array = [];

	/**
	 * @var bool __getにて存在しないkey値呼び出し時などにどうするか
	 */
	protected $_strict = false;
	public function set_strict($strict) {
		$this->_strict = $strict;
	}

	/**
	 * ORE_Const constructor.
	 *
	 * @param $array
	 */
	public function __construct($array=null) {
		$type = gettype($array);
		if ('array' === $type) {
			$this->_array = $array;
		}
		else if ('object' === $type) {
			$this->_array = (array)$array;
		}
	}

	/**
	 *
	 */
	public function get_array() {
		return $this->_array;
	}

	/**
	 * @param $key
	 * @return mixed|null
	 * @throws \Exception
	 */
	public function __get($key) {
		if (array_key_exists($key, $this->_array)) {
			return $this->_array[$key];
		}
		if (TRUE == $this->_strict) {
			// trigger_error ( "key[{$key}] was not found");
			throw new \Exception("key[{$key}] was not found");
		}
		return null;
	}

	/**
	 * @param $key
	 * @param $val
	 * @return $this
	 */
	public function __set($key, $val) {
		if (array_key_exists($key, $this->_array)) {
			throw new \Exception("key[{$key}] is already set");
		}
		else {
			$this->_array[$key] = $val;
		}
		return $this;
	}
}
