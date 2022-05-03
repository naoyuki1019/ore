<?php

if (! function_exists('mb_trim')) {
	/**
	 * UTF-8の文字列の両端の半角空白、全角空白、タブを削除する
	 *
	 * @param $string
	 * @return string
	 */
	function mb_trim($string) {

		if (is_null($string)) {
			return null;
		}

		if (in_array(gettype($string), ['integer', 'double', 'float'])) {
			return $string;
		}

		// TODO 再帰処理
		if (gettype($string) !== 'string') {
			return $string;
		}

		$whitespace = '[\s\0\x0b\p{Zs}\p{Zl}\p{Zp}]';
		$ret = preg_replace(sprintf('/(^%s+|%s+$)/u', $whitespace, $whitespace), '', $string);
		return $ret;
	}
}

if (! function_exists('get_class_public_vars')) {
	/**
	 * @param $class_name
	 * @return array
	 */
	function get_class_public_vars($class_name) {
		return get_class_vars($class_name);
	}
}

if (! function_exists('isNULL')) {
	/**
	 * @param mixed $val
	 * @param string $key
	 * @return bool
	 */
	function isNULL($val, $key = null) {

		if (! isset($val)) {
			return true;
		}

		$type = gettype($val);

		if (is_null($key)) {
			if ('array' === $type && 0 === count($val)) {
				return true;
			}
			if ('string' === $type && '' === $val) {
				return true;
			}
			return false;
		}

		if ('object' === $type) {
			if (property_exists($val, $key)) {
				return isNULL($val->{$key});
			}
			if (method_exists($val, '__get')) {
				return isNULL($val->{$key});
			}
			return true;
		}

		if ('array' === $type) {
			if (! array_key_exists($key, $val)) {
				return true;
			}
			return isNULL($val[$key]);
		}

		return true;
	}
}

if (! function_exists('generateTreeName')) {
	/**
	 * @param \ore\ORE_TreeVolume $vo
	 */
	function generateTreeName(\ore\ORE_TreeVolume $vo) {

		if (1 != $vo->tree_name_generate) return;

		if (isNULL($vo, 'tree_key')) $vo->tree_key = 'id';
		if (isNULL($vo, 'tree_parent_id')) $vo->tree_parent_id = 'parent_id';
		if (isNULL($vo, 'tree_label')) $vo->tree_label = 'label';
		if (isNULL($vo, 'tree_depth')) $vo->tree_depth = 'depth';

		$is_array = false;
		foreach ($vo->entries as & $r) {
			if (is_array($r)) {
				$is_array = true;
			}
			break;
		}

		$min_depth = null;
		$last = [];
		foreach ($vo->entries as & $r) {
			if (true === $is_array) {
				$r = (object)$r;
			}

			if (! isNULL($r, $vo->tree_depth) && ! isNULL($r, $vo->tree_parent_id)) {
				$depth = $r->{$vo->tree_depth};
				$parent_id = $r->{$vo->tree_parent_id};

				$last[$parent_id] = $r->{$vo->tree_key};

				if (is_null($min_depth)) {
					$min_depth = $depth;
				}

				if ($min_depth > $depth) {
					$min_depth = $depth;
				}
			}

			if (true === $is_array) {
				$r = (array)$r;
			}
		}

		$prefix = [];
		foreach ($vo->entries as & $r) {
			if (true === $is_array) {
				$r = (object)$r;
			}

			if (! isNULL($r, $vo->tree_depth) && ! isNULL($r, $vo->tree_parent_id)) {
				$depth = $r->{$vo->tree_depth};
				$parent_id = $r->{$vo->tree_parent_id};

				// 子供が存在する場合で且つ最小のdepthではない
				if (isset($last[$parent_id]) && $min_depth != $depth) {
					// 最後の場合
					if ($last[$parent_id] === $r->{$vo->tree_key}) {
						$prefix[$depth] = '└─';
					}
					else {
						$prefix[$depth] = '├─';
					}
				}

				$tree_nm = '';

				for ($i = 0; $i <= $depth; $i++) {
					if (array_key_exists($i, $prefix)) {
						$tree_nm .= $prefix[$i];
					}
				}

				if (array_key_exists($depth, $prefix)) {
					// 一回限りで変更
					if ($prefix[$depth] == '├─') {
						$prefix[$depth] = '│　';
					}

					// 一回限りで変更
					if ($prefix[$depth] === '└─') {
						$prefix[$depth] = '　　';
					}
				}

				$r->tree_nm = $tree_nm.$r->{$vo->tree_label};
			}

			if (true === $is_array) {
				$r = (array)$r;
			}
		}
	}

}
