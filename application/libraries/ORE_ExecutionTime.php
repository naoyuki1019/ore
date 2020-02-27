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
	protected static $_arr = array();
	public static $threshold = 0.1;

	private static $_ENABLE = NULL;
	private static $_INSTANCE = NULL;
	private static $_DESTRUCT_DUMP = FALSE;

	/**
	 *
	 */
	public static function ENABLE() {
		self::$_ENABLE = TRUE;
	}

	/**
	 *
	 */
	public static function DISABLE() {
		self::$_ENABLE = FALSE;
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

		if (defined('DEBUG_MODE') AND TRUE === DEBUG_MODE) {
			self::$_ENABLE = TRUE;
		}
		else {
			self::$_ENABLE = FALSE;
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
		if (TRUE !== self::$_ENABLE) return;

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
		if (TRUE !== self::$_ENABLE) return;

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
	public static function DUMP() {
		self::_SET_ENABLE();
		if (TRUE !== self::$_ENABLE) return;

		if (! empty(self::$_arr)) {
			echo '<div style="background-color:white;margin:20px 0;width:100%;overflow-x:scroll;" class="ore_executiontime">';

			foreach (self::$_arr as $name => $o) {
				/** @var ORE_ExecutionTimeVolume $o */
				$str = "{$name}={$o->time}";
				if (self::$threshold < $o->time) {
					$str = "<span style='color:red;'>{$str}</span>";
				}
				echo "{$str}<br>";
			}
			echo '</div>';
		}
	}

	/**
	 *
	 * @return void
	 */
	public static function LOG() {
		self::_SET_ENABLE();
		if (TRUE !== self::$_ENABLE) return;

		if (! empty(self::$_arr)) {

			$arr = [];
			foreach (self::$_arr as $name => $o) {
				/** @var ORE_ExecutionTimeVolume $o */
				$str = "{$name}={$o->time}";
				if (self::$threshold < $o->time) {}
				$arr[] = $str;
			}
			\VALX\logger::info(implode("\n", $arr));
		}
	}

	/**
	 *
	 */
	function __destruct() {
		if (true === self::$_DESTRUCT_DUMP) {
			self::sfVardump();
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
