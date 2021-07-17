<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * Class ORE_MemoryUsage
 *
 * @author naoyuki onishi
 */
class ORE_MemoryUsage {

	/**
	 * @var array
	 */
	protected static $_arr = [];

	private static $_ENABLE = NULL;
	private static $_INSTANCE = NULL;

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

		if (defined('DEBUG_MODE') && TRUE === DEBUG_MODE) {
			self::$_ENABLE = TRUE;
		}
		else {
			self::$_ENABLE = FALSE;
		}
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public static function CHECK($name) {
		self::_SET_ENABLE();
		if (TRUE !== self::$_ENABLE) return;
		$o = new ORE_MemoryUsageVolume();
		$o->usage = memory_get_usage();
		$o->name = $name;
		self::$_arr[] = $o;
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
			echo '<div style="background-color:white;margin:20px 0;width:100%;overflow-x:scroll;" class="ore_memoryusage">';
			echo '<div>メモリー使用量</div>';
			$tmp = [];
			foreach (self::$_arr as $o) {
				/** @var ORE_MemoryUsageVolume $o */
				$usage = round($o->usage / pow(1024, 2)).'MB';
				$tmp[] = "<span style='color:red;'>{$o->name}: {$usage}</span>";
			}
			echo implode('<br>', $tmp);
			echo '</div>';
		}
	}
}

/**
 * Class ORE_MemoryUsageVolume
 *
 * @package ore
 */
class ORE_MemoryUsageVolume {
	public $name = 'name';
	public $usage = 0;
}

