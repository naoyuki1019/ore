<?php

/**
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

	/**
	 * @var array
	 */
	protected $_array = [];

	/**
	 * @var string
	 */
	protected $__option1 = null;

	/**
	 * @var string
	 */
	protected $__option2 = null;

	/**
	 * @var bool
	 */
	protected $__recursive = false;

	/**
	 * @var mixed
	 */
	protected $__function = null;

	/**
	 * @var string
	 */
	protected $__search = '';

	/**
	 * @var string
	 */
	protected $__replace = '';

	/**
	 * __setにて存在しないkey値を追加する、しない
	 *
	 * @var bool
	 */
	protected $_add = true;

	/**
	 * @param bool $add
	 */
	public function set_add($add) {
		$this->_add = $add;
	}

	/**
	 * __getにて存在しないkey値の呼び出し時に例外とする、しない
	 *
	 * @var bool
	 */
	protected $_strict = false;

	/**
	 * @param bool $strict
	 */
	public function set_strict($strict) {
		$this->_strict = $strict;
	}

	/**
	 * Ore_Array constructor.
	 *
	 * @param mixed $array
	 */
	public function __construct($array) {
		$type = gettype($array);
		if ('array' === $type) {
			$this->_array = $array;
		}
		else if ('object' === $type) {
			$this->_array = (array)$array;
		}
	}

	/**
	 * @return array
	 */
	public function get_array() {
		return $this->_array;
	}

	/**
	 * @param string $key
	 * @return mixed|null
	 * @throws \Exception
	 */
	public function __get($key) {
		if (array_key_exists($key, $this->_array)) {
			return $this->_array[$key];
		}
		if (true == $this->_strict) {
			throw new \Exception("key[{$key}] was not found");
		}
		return null;
	}

	/**
	 * @param string $key
	 * @param mixed $val
	 * @return $this
	 */
	public function __set($key, $val) {
		if (array_key_exists($key, $this->_array)) {
			$this->_array[$key] = $val;
		}
		else {
			if (true == $this->_add) {
				$this->_array[$key] = $val;
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

	/**
	 * @param string $function 'mb_convert_kana', 'strtolower', 'strtoupper'
	 * @param bool $recursive
	 * @param string $option1
	 * @param string $option2
	 * @return $this
	 */
	public function func($function, $recursive = true, $option1 = null, $option2 = null) {
		$this->__function = $function;
		$this->__option1 = $option1;
		$this->__option2 = $option2;
		$this->__recursive = $recursive;
		$this->_recursive_function($this->_array);
		return $this;
	}

	/**
	 * @param $val
	 * @return array|string
	 */
	protected function _recursive_function(&$val) {
		$type = gettype($val);
		if ('array' === $type || 'object' === $type) {
			if (! $this->__recursive) {
				return;
			}
			foreach ($val as & $v) {
				$this->_recursive_function($v);
			}
			return;
		}

		$function = $this->__function;

		if (! is_null($this->__option2)) {
			$val = $function($val, $this->__option1, $this->__option2);
			return;
		}

		if (! is_null($this->__option1)) {
			$val = $function($val, $this->__option1);
			return;
		}

		$val = $function($val);
	}

	/**
	 * @param string $to_enc
	 * @param string $from_enc
	 * @return $this
	 */
	public function mb_convert_variables($to_enc, $from_enc) {
		$this->_array = mb_convert_variables($to_enc, $from_enc, $this->_array);
		return $this;
	}

	/**
	 * @param string|array $search
	 * @param string|array $replace
	 * @param bool $recursive
	 * @return $this
	 */
	public function str_replace($search, $replace, $recursive = true) {
		$this->__search = $search;
		$this->__replace = $replace;
		if (true === $recursive) {
			array_walk_recursive($this->_array, function(&$val, $key) {
				$val = str_replace($this->__search, $this->__replace, $val);
			});
		}
		else {
			$this->_array = str_replace($this->__search, $this->__replace, $this->_array);
		}
		return $this;
	}
}
