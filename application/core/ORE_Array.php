<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * Class ORE_Array
 *
 * @package ore
 */
class ORE_Array {

	protected $_array = array();

	/**
	 * @var bool __setにて存在しないkey値を追加するかどうか
	 */
	protected $_add = true;
	public function set_add($add) {
		$this->_add = $add;
	}

	/**
	 * @var bool __getにて存在しないkey値呼び出し時などにどうするか
	 */
	protected $_strict = false;
	public function set_strict($strict) {
		$this->_strict = $strict;
	}

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
	public function get_array() {
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
		if (TRUE == $this->_strict) {
			// trigger_error ( "key[{$key}] was not found");
			throw new \Exception("key[{$key}] was not found");
		}
		return null;
	}

	/**
	 * @param $key
	 * @return $this
	 */
	public function __set($key, $val) {
		if (array_key_exists($key, $this->_array)) {
			$this->_array[$key] = $val;
		}
		else {
			if (TRUE == $this->_add) {
				$this->_array[$key] = $val;
			}
		}
		return $this;
	}

	/**
	 * @param $to_enc
	 * @param $from_enc
	 * @return $this
	 */
	public function mb_convert_variables($to_enc, $from_enc) {
		$this->_array = mb_convert_variables('UTF-8', 'SJIS-win', $this->_array);
		return $this;
	}

	protected $__option = '';
	protected $__recursive = false;

	/**
	 * @param $option
	 * @return $this
	 */
	public function mb_convert_kana($option, $recursive = true) {
		$this->__option = $option;
		$this->__recursive = $recursive;
        array_map(array($this,'_mb_convert_kana'), $this->_array);
		return $this;
	}

	/**
	 * @param $val
	 * @return array|string
	 */
	protected function _mb_convert_kana(& $val) {
		$type = gettype($val);
		if ('object' !== $type) {
			if ('array' === $type) {
				if (TRUE == $this->__recursive) {
					$val = array_map(array($this,'_mb_convert_kana'), $val);
				}
			}
			else {
				$val = mb_convert_kana($val, $this->__option);
			}
		}
		return $val;
	}

	/**
	 * @return $this
	 */
	public function strtolower($recursive = true) {

		if (TRUE === $recursive) {
			array_walk_recursive($this->_array, function(&$val, $key) {
				$val = strtolower($val);
			});
		}
		else {
			foreach ($this->_array as & $val) {
				if ('string' == gettype($val)) {
					$val = strtolower($val);
				}
			}
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function strtoupper($recursive = true) {

		if (TRUE === $recursive) {
			array_walk_recursive($this->_array, function(&$val, $key) {
				$val = strtoupper($val);
			});
		}
		else {
			foreach ($this->_array as & $val) {
				if ('string' == gettype($val)) {
					$val = strtoupper($val);
				}
			}
		}

		return $this;
	}

	/**
	 * @return $this
	 */
	public function array_flip() {
		$this->_array = array_flip($this->_array);
		return $this;
	}

	protected $__search = '';
	protected $__replace = '';
	/**
	 * @param $search Array|String|Number
	 * @param $replace
	 * @param bool $recursive
	 * @return $this
	 */
	public function str_replace($search, $replace, $recursive = true) {
		$this->__search = $search;
		$this->__replace = $replace;
		if (TRUE === $recursive) {
			array_walk_recursive($this->_array, function(&$val, $key) {
				$val = str_replace($this->__search, $this->__replace, $val);
			});
		}
		else {
			$this->_array = str_replace($this->__search, $this->__replace, $this->_array);
		}
		return $this;
	}

	/**
	 * @param $callback
	 * @return $this
	 */
	public function array_map($callback) {
		$this->_array = array_map($callback, $this->_array);
		return $this;
	}

}