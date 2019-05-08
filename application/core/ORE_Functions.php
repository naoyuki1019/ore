<?php

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
	 * @param $val
	 * @param null $key
	 * @return bool
	 */
	function isNULL($val, $key = null) {

		if (! isset($val) OR is_null($val)) {
			return true;
		}

		$type = gettype($val);

		if (is_null($key)) {
			if ('array' === $type AND 0 === count($val)) {
				return true;
			}
			if ('string' === $type AND '' === $val) {
				return true;
			}
			return false;
		}

		if ('object' === $type) {
			if (property_exists($val, $key)) {
				return isNULL($val->{$key});
			}
			if (method_exists($val, '__get')) {
//            try {
				return isNULL($val->{$key});
//            }
//            catch (\Exception $e) {
//                return true;
//            }
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

		if (isNULL($vo, 'tree_key')) $vo->tree_key = 'tree_id';
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
		$last = array();
		foreach ($vo->entries as & $r) {
			if (true === $is_array) {
				$r = (object)$r;
			}
			$depth = $r->{$vo->tree_depth};
			$parent_id = $r->{$vo->tree_parent_id};

			$last[$parent_id] = $r->{$vo->tree_key};

			if (is_null($min_depth)) {
				$min_depth = $depth;
			}

			if ($min_depth > $depth) {
				$min_depth = $depth;
			}
			if (true === $is_array) {
				$r = (array)$r;
			}
		}

		$prefix = array();
		foreach ($vo->entries as & $r) {
			if (true === $is_array) {
				$r = (object)$r;
			}
			$depth = $r->{$vo->tree_depth};
			$parent_id = $r->parent_id;

			// 子供が存在する場合で且つ最小のdepthではない
			if (isset($last[$parent_id]) AND $min_depth != $depth) {
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

			$tree_nm = $tree_nm.$r->{$vo->tree_label};
			$r->tree_nm = $tree_nm;

			if (true === $is_array) {
				$r = (array)$r;
			}
		}
	}

}