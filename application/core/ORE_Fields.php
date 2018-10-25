<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * Class ORE_Fields
 * @package ore
 */
class ORE_Fields extends ORE_Params {

	/**
	 * @param array $params
	 */
	public function set ($params = array()) {

		if ('array' === gettype($params) OR 'object' === gettype ($params)) {

			$public_vars = get_class_public_vars(get_class($this));

			foreach ($params as $key => $val) {
				// Publicな項目のみ 先頭アンダーバーはセットしない
				if ('_' !== substr($key, 0, 1) AND TRUE === array_key_exists($key, $public_vars)) {
					// 数値項目でブランクはnullとする
					if ($this->is_int($key) AND '' === strval($val)) {
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
	protected $_int_fields = array();


	/**
	 * @param $filed_nm
	 * @return bool
	 */
	public function is_int($filed_nm) {
		return (in_array($filed_nm, $this->_int_fields));
	}
}

