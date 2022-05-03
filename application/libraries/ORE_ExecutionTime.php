<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * Class ORE_ExecutionTime
 *
 * @package ore
 */
class ORE_ExecutionTime {

	/**
	 * @var array
	 */
	protected static $_arr = [];
	public static $threshold = 0.1;

	private static $_ENABLE = NULL;
	private static $_INSTANCE = NULL;

	public static $DEFAULT_PREFIX = '<div style="background-color:white;margin:20px 0;width:100%;overflow-x:auto;" class="ore_executiontime">';
	public static $DEFAULT_SUFFIX = '</div>';
	public static $DEFAULT_OUTPUT_FORMAT = true;

	/**
	 *
	 */
	public static function ENABLE() {
		self::$_ENABLE = true;
	}

	/**
	 *
	 */
	public static function DISABLE() {
		self::$_ENABLE = false;
	}

	/**
	 * @param boolean $enable
	 */
	public static function SET_ENABLE($enable) {
		if (is_bool($enable)) {
			self::$_ENABLE = $enable;
		}
	}

	/**
	 * 未設定時のみDEBUG_MODEに応じて切り替える
	 */
	private static function _SET_ENABLE() {

		if (is_null(self::$_INSTANCE)) {
			self::$_INSTANCE = new static();
		}

		if (! is_null(self::$_ENABLE)) {
			return;
		}

		if (defined('DEBUG_MODE') && true === DEBUG_MODE) {
			self::$_ENABLE = true;
		}
		else {
			self::$_ENABLE = false;
		}
	}

	/**
	 * @return mixed
	 */
	protected static function _time() {
		return microtime(true);
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public static function START($name) {
		self::_SET_ENABLE();
		if (true !== self::$_ENABLE) return;

		if (! array_key_exists($name, self::$_arr)) {
			$o = new ORE_ExecutionTimeVolume();
			$o->name = $name;
			$o->start = self::_time();
			self::$_arr[$name] = $o;
		}
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public static function END($name) {
		self::_SET_ENABLE();
		if (true !== self::$_ENABLE) return;

		/** @var ORE_ExecutionTimeVolume $o */
		if (array_key_exists($name, self::$_arr)) {
			$o = self::$_arr[$name];
			$o->end = self::_time();
			$o->time = $o->end - $o->start;
		}
	}

	/**
	 * @return array
	 */
	public static function get() {
		return self::$_arr;
	}

	/**
	 *
	 * @return void
	 */
	public static function sfDump($is_html = null, $prefix = null, $suffix = null) {
		self::_SET_ENABLE();
		if (true !== self::$_ENABLE) return;

		if ($is_html) {
			if (is_null($prefix)) {
				$prefix = static::$DEFAULT_PREFIX;
			}
			if (is_null($suffix)) {
				$suffix = static::$DEFAULT_SUFFIX;
			}
		}
		else if (is_null($is_html)) {
			$is_html = static::$DEFAULT_OUTPUT_FORMAT;
		}

		if (! empty(self::$_arr)) {
			echo $prefix;
			foreach (self::$_arr as $name => $o) {
				if (0 === $o->end) {
					static::END($name);
				}
				/** @var ORE_ExecutionTimeVolume $o */
				$str = "{$name}={$o->time}";
				if (self::$threshold < $o->time) {
					if ($is_html) {
						$str = '<span style="color:red;">'.$str.'</span>';
					}
					else {
						$str = $str.' *';
					}
				}
				$lf = ($is_html) ? '<br>' : "\n";
				echo $str.$lf;
			}
			echo $suffix;
		}
	}

	/**
	 *
	 * @return void
	 */
	public static function sfGetDump($is_html = true, $prefix = null, $suffix = null) {
		ob_start();
		self::sfDump($is_html, $prefix, $suffix);
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}

	/**
	 *
	 * @return void
	 */
	public static function sfLog() {
		self::_SET_ENABLE();
		if (true !== self::$_ENABLE) return;

		if (! empty(self::$_arr)) {

			$arr = [];
			foreach (self::$_arr as $name => $o) {
				/** @var ORE_ExecutionTimeVolume $o */
				$str = "{$name}={$o->time}";
				if (self::$threshold < $o->time) {
					$str = $str.' *';
				}
				$arr[] = $str;
			}
			\VALX\libs\logger::info(implode("\n", $arr));
		}
	}
}

/**
 * Class ORE_ExecutionTimeVolume
 *
 * @package ore
 */
class ORE_ExecutionTimeVolume {
	public $name = 'name';
	public $start = 0;
	public $end = 0;
	public $time = 0;
}
