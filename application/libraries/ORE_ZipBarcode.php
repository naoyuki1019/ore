<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * Class ORE_ZipBarcode
 *
 * @author naoyuki onishi
 */
class ORE_ZipBarcode {

	protected $_debug = false;
	protected $_zip = [];
	protected $_zip_org = null;
	protected $_addr = null;
	protected $_addr_org = null;
	protected $_addr_arr = [];
	protected $_digit = null;
	protected $_digit_sum = null;
	protected $_code_extracted = [];
	protected $_code_cc_20 = [];
	protected $_code_cc_comp = [];
	protected $_code_font = [];
	protected $_sum = [];

	public function code_extracted() {
		return $this->_code_extracted;
	}

	public function code_cc_comp() {
		return $this->_code_cc_comp;
	}

	public function code_font() {
		return $this->_code_font;
	}

	public function set_debug($b) {
		$this->_debug = $b;
	}

	/**
	 * @return array
	 */
	public static function CC() {
		return array(
			'-' => 10,
			'CC1' => 11,
			'CC2' => 12,
			'CC3' => 13,
			'CC4' => 14,
			'CC5' => 15,
			'CC6' => 16,
			'CC7' => 17,
			'CC8' => 18,
		);
	}

	/**
	 * @return array
	 */
	public static function ALFA() {
		return array(
			'A' => array('c' => 'CC1', 'n' => 0),
			'B' => array('c' => 'CC1', 'n' => 1),
			'C' => array('c' => 'CC1', 'n' => 2),
			'D' => array('c' => 'CC1', 'n' => 3),
			'E' => array('c' => 'CC1', 'n' => 4),
			'F' => array('c' => 'CC1', 'n' => 5),
			'G' => array('c' => 'CC1', 'n' => 6),
			'H' => array('c' => 'CC1', 'n' => 7),
			'I' => array('c' => 'CC1', 'n' => 8),
			'J' => array('c' => 'CC1', 'n' => 9),
			'K' => array('c' => 'CC2', 'n' => 0),
			'L' => array('c' => 'CC2', 'n' => 1),
			'M' => array('c' => 'CC2', 'n' => 2),
			'N' => array('c' => 'CC2', 'n' => 3),
			'O' => array('c' => 'CC2', 'n' => 4),
			'P' => array('c' => 'CC2', 'n' => 5),
			'Q' => array('c' => 'CC2', 'n' => 6),
			'R' => array('c' => 'CC2', 'n' => 7),
			'S' => array('c' => 'CC2', 'n' => 8),
			'T' => array('c' => 'CC2', 'n' => 9),
			'U' => array('c' => 'CC3', 'n' => 0),
			'V' => array('c' => 'CC3', 'n' => 1),
			'W' => array('c' => 'CC3', 'n' => 2),
			'X' => array('c' => 'CC3', 'n' => 3),
			'Y' => array('c' => 'CC3', 'n' => 4),
			'Z' => array('c' => 'CC3', 'n' => 5),
		);
	}

	/**
	 * https://www.post.japanpost.jp/zipcode/zipmanual/p24.html
	 * @param $addr
	 */
	public function __construct($zip, $addr, $debug = false) {
		$this->_debug = $debug;
		$this->_zip_org = $zip;
		$this->_addr_org = $addr;
		$code_extracted = $this->_extract($zip, $addr);
		if (FALSE === $code_extracted) {
			return false;
		}
		$code_cc_20 = $this->_cc_20($code_extracted);
		$code_cc_comp = $this->_cc_comp($code_cc_20);
		$code_font = $this->_font($code_cc_comp);
	}

	/**
	 * @param $zip
	 * @param $addr
	 * @return array
	 * @throws \Exception
	 */
	protected function _extract($zip, $addr) {

		if ($this->_debug) {
			echo "■入力値<br>";
			echo "{$zip}{$addr}<br>";
			echo "■バーコード情報の抜き出し<br>";
		}

		$zip = mb_convert_kana($zip, 'a');

		$zip = preg_replace('/[^0-9]/', '', $zip);
		if ('' === $zip) {
			return false;
		}

		$zip_arr = self::mb_split($zip);
		if (7 !== count($zip_arr)) {
			return false;
		}

		$addr = mb_trim($addr);
		if ('' === $addr) {
			return false;
		}

		$addr = str_replace(array('&', '＆', '/', '／', '・', '.', '．'), '', $addr);
		$addr = mb_convert_kana($addr, 'a');
		$addr = static::convert_kanji($addr);
		$addr = preg_replace('/[a-zA-Z]{2,}/', '', $addr);
		$addr = strtoupper($addr);
		$addr = preg_replace('/[^-A-Z0-9]/', ' ', $addr);
		$addr = preg_replace('/F$/', '', $addr);
		$addr = str_replace('F', '-', $addr);
		$addr = mb_trim($addr);
		$addr = preg_replace('/\s+/', '-', $addr);
		$addr = preg_replace('/-{2,}/', '-', $addr);
		$addr = preg_replace('/(^-|-$)/', '', $addr);
		$addr = preg_replace('/([A-Z])-/', '$1', $addr);
		$addr = preg_replace('/-([A-Z])/', '$1', $addr);
		$this->_addr = $addr;
		$this->_addr_arr = $addr = self::mb_split($this->_addr);
		$this->_code_extracted = array_merge($zip_arr, $this->_addr_arr);

		if ($this->_debug) {
			echo implode(' ', $this->_code_extracted)."<br>";
		}

		return $this->_code_extracted;
	}

	/**
	 * https://www.post.japanpost.jp/zipcode/zipmanual/p17.html
	 * > 漢数字が下記の特定文字の前にある場合は抜き出し対象とし、算用数字に変換して抜き出します。
	 *
	 * @param $addr
	 * @return mixed|string|string[]|null
	 */
	public static function convert_kanji($addr) {
		$arr_kansuji = array(
			'0', '０', '〇', '零',
			'1', '１', '一', '壱',
			'2', '２', '二', '弐',
			'3', '３', '三', '参',
			'4', '４', '四', '肆',
			'5', '５', '五', '伍',
			'6', '６', '六', '陸',
			'7', '７', '七', '漆',
			'8', '８', '八', '捌',
			'9', '９', '九', '玖',
			'十', '拾',
			'百', '陌', '佰',
			'千', '阡', '仟',
			'万', '萬',
			'億',
			'兆',
			'京',
			'垓',
			'𥝱', '秭',
			'穣',
			'溝',
			'澗',
			'正',
			'載',
			'極',
		);

		$kansuji = implode('|', $arr_kansuji);

		$arr_suffix = array(
			'番地',
			'番',
			'丁目',
			'丁',
			'号',
			'地割',
			'線',
			'の',
			'ノ',
		);

		$addr = str_replace('◯', '0', $addr); // <- preg_matchでおかしくなるため
		$addr = str_replace('　', '', $addr);
		$addr = preg_replace('/\s+/', '', $addr);
		foreach ($arr_suffix as $suffix) {
			$pattern = "/({$kansuji})+{$suffix}/";
			$matches = [];
			preg_match_all($pattern, $addr, $matches);
			foreach ($matches as $matche) {
				foreach ($matche as $search) {
					$replace = static::jnum2num($search).$suffix;
					$addr = str_replace($search, $replace, $addr);
				}
				break;
			}
		}

		return $addr;
	}

	/**
	 * @param $code_extracted
	 * @return array
	 * @throws \Exception
	 */
	protected function _cc_20($code_extracted) {

		if ($this->_debug) {
			echo "■CC1などのコードへ変換<br>";
		}

		$ALFA = static::ALFA();
		$code_cc_20 = [];
		foreach ($code_extracted as $i => $code) {
			if (array_key_exists($code, $ALFA)) {
				$code_cc_20[] = $ALFA[$code]['c'];
				$code_cc_20[] = $ALFA[$code]['n'];
			}
			else {
				$code_cc_20[] = $code;
			}
		}
		$this->_code_cc_20 = $code_cc_20;

		if ($this->_debug) {
			echo implode(' ', $this->_code_cc_20)."<br>";
			echo "■20文字へ変換<br>";
		}

		if (20 > count($code_cc_20)) {
			$fill = array_fill(0, 20 - count($code_cc_20), 'CC4');
			$code_cc_20 = array_merge($code_cc_20, $fill);
		}
		else if (20 < count($code_cc_20)) {
			$code_cc_20 = array_slice($code_cc_20, 0, 20);
		}
		if (20 !== count($code_cc_20)) {
			throw new \Exception('作り方に問題がある');
		}

		$this->_code_cc_20 = $code_cc_20;

		if ($this->_debug) {
			echo implode(' ', $code_cc_20)."<br>";
		}

		return $code_cc_20;
	}

	/**
	 * @param $code_cc_20
	 * @return array
	 * @throws \Exception
	 */
	protected function _cc_comp($code_cc_20) {

		if ($this->_debug) {
			echo "■最終的なコードの作成<br>";
			echo implode(' ', $code_cc_20)."<br>";
		}

		$digit = $this->_check_digit($code_cc_20);

		// 一番最後にチェックディジットを追加
		$code_cc_comp = $this->_code_cc_20;
		if (10 > $digit) {
			$code_cc_comp[20] = $digit;
		}
		else {
			$CC = static::CC();
			foreach ($CC as $key => $int) {
				if ($digit == $int) {
					$code_cc_comp[20] = $key;
					break;
				}
			}
		}

		// 前後に括弧を追加
		array_unshift($code_cc_comp, 'STC');
		array_push($code_cc_comp, 'SPC');

		if ($this->_debug) {
			echo "■おしりにチェックディジット、前後にSTCとSPCを追加<br>";
			echo implode(' ', $code_cc_comp)."<br>";
		}
		$this->_code_cc_comp = $code_cc_comp;
		return $code_cc_comp;
	}

	/**
	 * チェックディジットの計算
	 *
	 * @param $code_cc_20
	 * @return float|int
	 * @throws \Exception
	 */
	protected function _check_digit($code_cc_20) {

		if ($this->_debug) {
			echo "■チェックディジット用数値へ変換<br>";
			echo implode(' ', $code_cc_20)."<br>";
		}

		$CC = static::CC();
		$ALFA = static::ALFA();
		$digit_arr = [];
		foreach ($code_cc_20 as $i => $code) {
			if (preg_match('/^[0-9]$/', $code)) {
				$digit_arr[] = $code;
			}
			else if (preg_match('/^[A-Z]$/', $code)) {
				if (! array_key_exists($code, $ALFA)) {
					throw new \Exception('$ALFAに定義がされていない文字です ['.$code.']');
				}
				if (! array_key_exists($ALFA[$code]['c'], $CC)) {
					throw new \Exception('$CCに定義がされていない文字です ['.$ALFA[$code]['c'].']');
				}
				$digit_arr[] = $CC[$ALFA[$code]['c']];
				$digit_arr[] = $ALFA[$code]['n'];
			}
			else {
				if (! array_key_exists($code, $CC)) {
					throw new \Exception('$CCに定義がされていない文字です ['.$code.']');
				}
				$digit_arr[] = $CC[$code];
			}
		}

		$this->_digit_arr = $digit_arr;
		if ($this->_debug) {
			echo implode(' ', $digit_arr)."<br>";
			echo "■チェックディジットを計算<br>";
		}

		$digit_sum = array_sum($digit_arr);
		$this->_digit_sum = $digit_sum;
		$float = $digit_sum / 19;
		if (ctype_digit((string)$float)) {
			$digit = 0;
		}
		else {
			$n19 = (floor($float) + 1);
			$digit = (19 * $n19) - $digit_sum;
		}

		if ($this->_debug) {
			echo "(19 * {$n19}) - {$digit_sum}={$digit}<br>";
		}
		$this->_digit = $digit;
		return $digit;
	}

	/**
	 * @param $code_cc_comp
	 * @return mixed
	 * @throws \Exception
	 */
	protected function _font($code_cc_comp) {

		if ($this->_debug) {
			echo "■CC1～CC8 STC SPC などをアルファベットへ変換<br>";
			echo implode(' ', $code_cc_comp)."<br>";
		}

		$font_arr = array(
			'-' => '-',
			'CC1' => 'a',
			'CC2' => 'b',
			'CC3' => 'c',
			'CC4' => 'd',
			'CC5' => 'e',
			'CC6' => 'f',
			'CC7' => 'g',
			'CC8' => 'h',
			'STC' => '(',
			'SPC' => ')',
		);

		foreach ($code_cc_comp as $i => $code) {

			if (preg_match('/^[0-9]$/', $code)) {
				$code_cc_comp[$i] = $code;
			}
			else {
				if (! array_key_exists($code, $font_arr)) {
					throw new \Exception('$font_arrに定義がされていない文字です ['.$code.']');
				}
				$code_cc_comp[$i] = $font_arr[$code];
			}
		}
		if ($this->_debug) {
			echo implode(' ', $code_cc_comp)."<br>";
		}

		$this->_code_font = $code_cc_comp;

		return $code_cc_comp;
	}

	/**
	 * @param $str
	 * @return array
	 */
	public static function mb_split($str) {
		$strlen = mb_strlen($str);
		$arr = [];
		for ($i = 0; $i < $strlen; $i++) {
			$arr[] = mb_substr($str, $i, 1);
		}
		return $arr;
	}

	/**
	 * https://twoterabytes.hatenablog.com/entry/2017/06/09/191958
	 * @param $str
	 * @return string
	 */
	public static function jnum2num($str) {

		$numberlist = array(
			'〇' => '0', '零' => '0',
			'一' => '1', '壱' => '1',
			'二' => '2', '弐' => '2',
			'三' => '3', '参' => '3',
			'四' => '4', '肆' => '4',
			'五' => '5', '伍' => '5',
			'六' => '6', '陸' => '6',
			'七' => '7', '漆' => '7',
			'八' => '8', '捌' => '8',
			'九' => '9', '玖' => '9',
		);
		$prefix_a = array(
			'十' => '1', '拾' => '1',
			'百' => '2', '陌' => '2', '佰' => '2',
			'千' => '3', '阡' => '3', '仟' => '3',
		);
		$prefix_b = array(
			'万' => '4', '萬' => '4',
			'億' => '8',
			'兆' => '12',
			'京' => '16',
			'垓' => '20',
			'𥝱' => '24', '秭' => '24',
			'穣' => '28',
			'溝' => '32',
			'澗' => '36',
			'正' => '40',
			'載' => '44',
			'極' => '48',
		);

		$str = mb_convert_kana($str, 'KVa');
		$str = str_replace(array(',', '、', ' '), '', $str);
		$numstr = preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);

		$mem_a = '0';
		$mem_b = '0';
		$mem_c = '0';
		$nonpower = FALSE;
		foreach ($numstr as $val) {
			if (array_key_exists($val, $prefix_a)) {
				if ($mem_c == '0') $mem_c = '1';
				$mem_b = bcadd($mem_b, bcmul($mem_c, bcpow('10', $prefix_a[$val])));
				$mem_c = '0';
				$nonpower = TRUE;
				continue;
			}

			if (array_key_exists($val, $prefix_b)) {
				$mem_a = bcadd($mem_a, bcmul(bcadd($mem_b, $mem_c), bcpow('10', $prefix_b[$val])));
				$mem_b = '0';
				$mem_c = '0';
				$nonpower = TRUE;
				continue;
			}

			if (array_key_exists($val, $numberlist)) {
				$val = $numberlist[$val];
			}

			if (is_numeric($val)) {
				$mem_c = ($nonpower) ? bcadd($mem_c, $val) : bcadd(bcmul($mem_c, '10'), $val);
				$nonpower = FALSE;
				continue;
			}
			break;

		}
		return bcadd($mem_a, bcadd($mem_b, $mem_c));
	}
}

