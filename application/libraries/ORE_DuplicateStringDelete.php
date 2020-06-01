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

	public static function exec($str) {

		// 半角空白一つへ整形
		$str = str_replace('　', ' ', $str);
		$str = preg_replace('/\s+/', ' ', $str);
		$str = trim($str);

		// 分解 重複削除
		$arr = self::ssss($str);

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

	private static function ssss($str) {
		$arr = [];
		$arr['delimiter'] = ' ';
		$arr['array'] = explode($arr['delimiter'], $str);
		$dup = [];
		foreach (['/', ','] as $delimiter) {
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

