<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * Class ORE_DuplicateStringDelete
 *
 * @author naoyuki onishi
 */
class ORE_DuplicateStringDelete {

	public static $threshold_same_string = 8;

	public static function unify_words_same_meaning($str, $groups, $delimiter_arr=[' ']) {

		$arr = [];
		$arr = self::_explode_array_merge($str, $arr, $delimiter_arr, 0, count($delimiter_arr));

		$preg_delimiter = implode(array_map('self::_preg_escape', $delimiter_arr));

		foreach($groups as $main => $group) {
			if (! in_array($main, $arr)) break;
			foreach ($group as $check) {
				if (in_array($check, $arr)) {
					$pattern = "/^{$check}[{$preg_delimiter}]+|[{$preg_delimiter}]{$check}[{$preg_delimiter}]+|[{$preg_delimiter}]+{$check}[{$preg_delimiter}]|[{$preg_delimiter}]+{$check}$|^{$check}$/";
					$str = preg_replace($pattern, ' ', $str);
				}
			}
		}

		return $str;
	}

	private static function _explode_array_merge($str, $arr, $delimiter_arr, $i, $limit) {
		$arr = array_unique(array_merge($arr, explode($delimiter_arr[$i], $str)));

		$i++;
		if ($i < $limit) {
			$new = [];
			foreach ($arr as $x => $a) {
				$new = array_unique(array_merge($new, self::_explode_array_merge($a, $new, $delimiter_arr, $i, $limit)));
			}
			$arr = array_unique(array_merge($arr, $new));
		}

		return $arr;
	}

	private static function _preg_escape($s) {

		// バックスラッシュのエスケープ
		$s = preg_replace('/([^\\\])\\\\/', '${1}\\\\', $s);
		$s = preg_replace('/([^\\\])\\\\/', '${1}\\\\', $s);
		$s = preg_replace('/^\\\\/', '\\\\\\', $s);

		// スラッシュのエスケープ
		$s = preg_replace('/([^\\\])\//', '${1}\\\/', $s);
		$s = preg_replace('/([^\\\])\//', '${1}\\\/', $s);
		$s = preg_replace('/^\//', '\\\/', $s);

		// "(" をエスケープ
		$s = preg_replace('/([^\\\])\(/', '${1}\\\(', $s);
		$s = preg_replace('/([^\\\])\(/', '${1}\\\(', $s);
		$s = preg_replace('/^\(/', '\\\(', $s);

		// ")" をエスケープ
		$s = preg_replace('/([^\\\])\)/', '${1}\\\)', $s);
		$s = preg_replace('/([^\\\])\)/', '${1}\\\)', $s);
		$s = preg_replace('/^\)/', '\\\)', $s);

		// "?" をエスケープ
		$s = preg_replace('/([^\\\])\?/', '${1}\\\?', $s);
		$s = preg_replace('/([^\\\])\?/', '${1}\\\?', $s);
		$s = preg_replace('/^\?/', '\\\?', $s);

		return $s;
	}

	public static function match_by_delimiters($str, $delimiter_arr=[' ']) {
		foreach ($delimiter_arr as $delimiter) {
			$arr = explode($delimiter, $str);
			$arr = self::qqqq($arr);
			$str = implode($delimiter, $arr);
		}
		return $str;
	}

	private static function qqqq($arr) {
		foreach($arr as $i => $vi) {
			foreach($arr as $j => $vj) {

				// 同じなのでcontinue
				if ($i == $j) continue;

				// 小さい文字数は無視する
				if (self::$threshold_same_string > mb_strlen($vi)
					OR self::$threshold_same_string > mb_strlen($vj)) {
					continue;
				}

				// 前方一致で入れ替え
				if (0 === strpos($vj, $vi)) {
					$arr[$j] =  $arr[$i];
					$arr[$i] =  str_replace($vi, '', $vj);;
				}
				else {
					// 後方一致で入れ替え
					if (substr($vj, strpos($vj, $vi)) === $vi) {
						$arr[$j] =  $arr[$i];
						$arr[$i] =  str_replace($vi, '', $vj);;
					}
				}
			}
		}
		return $arr;
	}

	public static function perfect_match_by_delimiters($str, $delimiter_arr=[' ']) {

		// 半角空白一つへ整形
		$str = str_replace('　', ' ', $str);
		$str = preg_replace('/\s+/', ' ', $str);
		$str = trim($str);

		// 分解 重複削除
		$arr = self::ssss($str, $delimiter_arr);

		// 結合
		$str = self::cccc($arr['delimiter'], $arr['array']);

		return $str;
	}

	private static function cccc($delimiter, & $arr) {
		$tmpnm = [];
		foreach ($arr as $a) {
			if (is_array($a)) {
				$tmpnm[] = self::cccc($a['delimiter'], $a['array']);
			}
			else {
				$tmpnm[] = $a;
			}
		}
		$name = implode($delimiter, $tmpnm);
		return $name;
	}

	private static function ssss($str, $delimiter_arr) {
		$arr = [];
		$arr['delimiter'] = array_shift($delimiter_arr);
		$arr['array'] = explode($arr['delimiter'], $str);
		$dup = [];
		foreach ($delimiter_arr as $delimiter) {
			$arr = self::_ssss($delimiter, $arr, $dup);
		}
		foreach ($arr['array'] as $j => $chk) {
			if (! is_array($chk)) {
				if (array_key_exists($chk, $dup)) {
					unset($arr['array'][$j]);
				}
				else {
					$dup[$chk] = null;
				}
			}
		}

		return $arr;
	}

	private static function _ssss($delimiter, &$sss, &$dup) {
		foreach ($sss['array'] as $i => $a) {
			if (is_array($a)) {
				$sss['array'][$i] = self::_ssss($delimiter, $a, $dup);
			}
			else {
				if (FALSE != strpos($a, $delimiter)) {
					$sss['array'][$i] = [];
					$sss['array'][$i]['delimiter'] = $delimiter;
					$sss['array'][$i]['array'] = explode($delimiter, $a);

					foreach ($sss['array'][$i]['array'] as $j => $chk) {
						if (array_key_exists($chk, $dup)) {
							unset($sss['array'][$i]['array'][$j]);
						}
						else {
							$dup[$chk] = null;
						}
					}

				}
			}
		}

		return $sss;
	}
}

