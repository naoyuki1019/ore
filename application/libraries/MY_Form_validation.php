<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */
namespace ore;

/**
 * Class MY_Form_validation
 *
 * @author naoyuki onishi
 */
class MY_Form_validation extends CI_Form_validation {

	/**
	 * @var MY_Controller
	 */
	public $CI = null;

	public $_error_prefix = '<p class="formError">';
	public $_error_suffix = '</p>';

	public $_encoding = 'UTF-8';

	/**
	 *
	 */
	public function __construct($rules = []) {
		//ここで先読みさせてフォームバリデーションオブジェクトの取得関数を定義
		// get_instance()->load->helper('my_form');
		parent::__construct($rules);
	}


	/**
	 *
	 */
	public function initialize() {
		$this->_field_data = [];
		$this->_config_rules = [];
		$this->_error_array = [];
		$this->_error_messages = [];
		$this->error_string = '';
		$this->_safe_form_data = FALSE;
		return $this;
	}


	/**
	 *
	 * @see CI_Form_validation::set_data()
	 */
	public function set_data($data = []) {

		if (is_object($data)) {
			$vars = get_object_vars($data);
			parent::set_data($vars);
			return $this;
		}

		parent::set_data($data);
		return $this;
	}


	/**
	 *
	 */
	public function add_error_message($field, $message) {
		$this->_error_array[$field] = $message;
		$this->_field_data[$field]['error'] = $message;
	}


	/**
	 * Error String
	 *
	 * Returns the error messages as a string, wrapped in the error delimiters
	 *
	 * @param	string
	 * @param	string
	 * @return	string
	 */
	public function error_string($prefix = null, $suffix = null)
	{
		// No errors, validation passes!
		if (count($this->_error_array) === 0)
		{
			return '';
		}

		if (is_null($prefix))
		{
			$prefix = $this->_error_prefix;
		}

		if (is_null($suffix))
		{
			$suffix = $this->_error_suffix;
		}

		// Generate the error string
		$arr = [];
		foreach ($this->_error_array as $val)
		{
			if ($val !== '')
			{
				$arr[] = $prefix.$val.$suffix;
			}
		}

		return implode("\n", $arr);
	}


	/**
	 *
	 */
	public function error_count() {
		return count($this->_error_array);
	}


	/**
	 *
	 */
	public function field_data_keys() {
		return array_keys($this->_field_data);
	}


	/**
	 *
	 */
	public function field_data_keys_flip() {
		return array_flip($this->field_data_keys());
	}

	// --------------------------------------------------------------------

	/**
	 * Executes the Validation routines
	 *
	 * @param	array
	 * @param	array
	 * @param	mixed
	 * @param	int
	 * @return	mixed
	 */
	protected function _execute($row, $rules, $postdata = NULL, $cycles = 0)
	{
		$idx_mb_trim = array_search('mb_trim', $rules);

		// If the $_POST data is an array we will run a recursive call
		if (is_array($postdata))
		{
			foreach ($postdata as $key => $val)
			{
				$this->_execute($row, $rules, $val, $key);
			}

			return;
		}
		else {
			// if ( ! in_array('isset', $rules))
			// {
			// 	if (is_null($postdata)) {
			// 		return ;
			// 	}
			//
			// 	if ( ! in_array('required', $rules) AND $postdata == "") {
			// 		return ;
			// 	}
			// }
		}

		// If the field is blank, but NOT required, no further tests are necessary
		$callback = FALSE;

		if (isset($postdata) AND FALSE !== $idx_mb_trim) {
			unset($rules[$idx_mb_trim]);
			$postdata = $this->mb_trim($postdata);
		}

		// Isset Test. Typically this rule will only apply to checkboxes.
		if (($postdata === NULL OR $postdata === '') && $callback === FALSE)
		{
			$err_isset = false;
			$err_required = false;

			if (in_array('isset', $rules, TRUE))
			{
				if (! isset($postdata)) {
					$err_isset = true;
				}

				if (in_array('required', $rules) AND '' === $postdata) {
					$err_required = true;
				}

				// エラーが発生していない時
				if (! $err_isset AND ! $err_required) {
					return;
				}

			}
			else {
				if (in_array('required', $rules) AND '' === $postdata)
				{
					$err_required = true;
				}
				else {
					return;
				}
			}

			if ($err_isset OR $err_required) {

				// Set the message type
				// $type = in_array('required', $rules) ? 'required' : 'isset';
				$type = ($err_isset) ? 'isset' : 'required';

				if (isset($this->_error_messages[$type]))
				{
					$line = $this->_error_messages[$type];
				}
				elseif (FALSE === ($line = $this->CI->lang->line('form_validation_'.$type))
					// DEPRECATED support for non-prefixed keys
					&& FALSE === ($line = $this->CI->lang->line($type, FALSE)))
				{
					$line = 'The field was not set';
				}

				// Build the error message
				$message = $this->_build_error_msg($line, $this->_translate_fieldname($row['label']));

				// Save the error message
				$this->_field_data[$row['field']]['error'] = $message;

				if ( ! isset($this->_error_array[$row['field']]))
				{
					if ($row['is_array'] === TRUE && is_array($this->_field_data[$row['field']]['postdata']))
					{
						// 配列の添字またはキーが存在しない場合（例：inputname[]）はcycles（index）にする
						$field = str_replace('[]', "[{$cycles}]", $row['field']);
						$this->_error_array[$field] = $message;
					}
					else
					{
						$this->_error_array[$row['field']] = $message;
					}
				}
			}

			return;
		}

		// --------------------------------------------------------------------

		// Cycle through each rule and run it
		foreach ($rules as $rule)
		{
			$_in_array = FALSE;

			// We set the $postdata variable with the current data in our master array so that
			// each cycle of the loop is dealing with the processed data from the last cycle
			if ($row['is_array'] === TRUE && is_array($this->_field_data[$row['field']]['postdata']))
			{
				// We shouldn't need this safety, but just in case there isn't an array index
				// associated with this cycle we'll bail out
				if ( ! isset($this->_field_data[$row['field']]['postdata'][$cycles]))
				{
					continue;
				}

				$postdata = $this->_field_data[$row['field']]['postdata'][$cycles];
				$_in_array = TRUE;
			}
			else
			{
				// If we get an array field, but it's not expected - then it is most likely
				// somebody messing with the form on the client side, so we'll just consider
				// it an empty field
				$postdata = is_array($this->_field_data[$row['field']]['postdata'])
						? NULL
						: $this->_field_data[$row['field']]['postdata'];
			}

			// Is the rule a callback?
			$callback = FALSE;
			if (strpos($rule, 'callback_') === 0)
			{
				$rule = substr($rule, 9);
				$callback = TRUE;
			}

			// Strip the parameter (if exists) from the rule
			// Rules can contain a parameter: max_length[5]
			$param = FALSE;
			if (preg_match('/(.*?)\[(.*)\]/', $rule, $match))
			{
				$rule = $match[1];
				$param = $match[2];
			}

			// Call the function that corresponds to the rule
			if ($callback === TRUE)
			{
				if ( ! method_exists($this->CI, $rule))
				{
					log_message('debug', 'Unable to find callback validation rule: '.$rule);
					$result = FALSE;
				}
				else
				{
					// Run the function and grab the result
					$result = $this->CI->$rule($postdata, $param);
				}

				// Re-assign the result to the master data array
				if ($_in_array === TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = is_bool($result) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = is_bool($result) ? $postdata : $result;
				}

				// If the field isn't required and we just processed a callback we'll move on...
				if ( ! in_array('required', $rules, TRUE) && $result !== FALSE)
				{
					continue;
				}
			}
			elseif ( ! method_exists($this, $rule))
			{
				// If our own wrapper function doesn't exist we see if a native PHP function does.
				// Users can use any native PHP function call that has one param.
				if (function_exists($rule))
				{
					$result = ($param !== FALSE) ? $rule($postdata, $param) : $rule($postdata);

					if ($_in_array === TRUE)
					{
						$this->_field_data[$row['field']]['postdata'][$cycles] = is_bool($result) ? $postdata : $result;
					}
					else
					{
						$this->_field_data[$row['field']]['postdata'] = is_bool($result) ? $postdata : $result;
					}
				}
				else
				{
					if ('isset' === $rule) {
						if (isset($this->_field_data[$row['field']]['postdata'])) {
							$result = TRUE;
						}
						else {
							$result = FALSE;
						}
					}
					else {
						// log_message('debug', 'Unable to find validation rule: '.$rule);
						// $result = FALSE;
						throw new \Exception('Unable to find validation rule: '.$rule);
					}
				}
			}
			else
			{
				$result = $this->$rule($postdata, $param);

				if ($_in_array === TRUE)
				{
					$this->_field_data[$row['field']]['postdata'][$cycles] = is_bool($result) ? $postdata : $result;
				}
				else
				{
					$this->_field_data[$row['field']]['postdata'] = is_bool($result) ? $postdata : $result;
				}
			}

			// Did the rule test negatively? If so, grab the error.
			if ($result === FALSE)
			{
				if ( ! isset($this->_error_messages[$rule]))
				{
					if (FALSE === ($line = $this->CI->lang->line('form_validation_'.$rule))
						// DEPRECATED support for non-prefixed keys
						&& FALSE === ($line = $this->CI->lang->line($rule, FALSE)))
					{
						$line = "Unable to access an error message [{$rule}] corresponding to your field name.";
					}
				}
				else
				{
					$line = $this->_error_messages[$rule];
				}

				// Is the parameter we are inserting into the error message the name
				// of another field? If so we need to grab its "field label"
				$param_multiple_rules = [
					'required_combi'
				];

				if (in_array($rule, $param_multiple_rules)) {
                    $labels = [];
					foreach (explode(',', $param) as $p) {
						if (isset($this->_field_data[$p], $this->_field_data[$p]['label']))
						{
							$labels[] = $this->_translate_fieldname($this->_field_data[$p]['label']);
						}
					}

					// Build the error message
					$message = $this->_build_error_msg($line, $this->_translate_fieldname($row['label']), $labels);
				}
				else {
					if (isset($this->_field_data[$param], $this->_field_data[$param]['label']))
					{
						$param = $this->_translate_fieldname($this->_field_data[$param]['label']);
					}

					// Build the error message
					$message = $this->_build_error_msg($line, $this->_translate_fieldname($row['label']), $param);
				}

				// Save the error message
				$this->_field_data[$row['field']]['error'] = $message;

				if ( ! isset($this->_error_array[$row['field']]))
				{
					if ($row['is_array'] === TRUE && is_array($this->_field_data[$row['field']]['postdata']))
					{
						// 配列の添字またはキーが存在しない場合（例：inputname[]）はcycles（index）にする
						$field = str_replace('[]', "[{$cycles}]", $row['field']);
						$this->_error_array[$field] = $message;
					}
					else
					{
						$this->_error_array[$row['field']] = $message;
					}
				}

				return;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Build an error message using the field and param.
	 *
	 * @param	string	The error message line
	 * @param	string	A field's human name
	 * @param	mixed	A rule's optional parameter
	 * @return	string
	 */
	protected function _build_error_msg($line, $field = '', $param = '')
	{
		// Check for %s in the string for legacy support.
		if (is_array($param)) {
			$param = implode('と', $param);
		}

		return parent::_build_error_msg($line, $field, $param);
	}

	// --------------------------------------------------------------------

	/**
	 * 入力値の変換
	 *
	 * @access public
	 * @param string
	 * @param string
	 * @return string
	 *
	 *
	 * 半角文字列 -> as
	 * 全角文字列 -> ASKV
	 * 全角カタカナ -> KVC
	 * 半角カタカナ -> kh
	 * ひらがな -> HVc
	 */
	public function mb_convert_kana($str, $val) {
		return mb_convert_kana($str, $val, $this->_encoding);
	}


	/**
	 * 入力値の変換
	 *
	 * @access public
	 * @param string
	 * @param string
	 * @return string
	 *
	 */
	public function sprintf($str, $format) {
		return sprintf($format, $str);
	}


	/**
	 * オーバーライド
	 */
	public function required($str) {

		if (is_array($str)) {
			return (! empty($str));
		}

		else {
			return ($this->mb_trim($str) == '') ? FALSE : TRUE;
		}
	}


	/**
	 *
	 */
	public function required_combi($str, $fields) {

		$fields = $this->mb_trim($fields);
		if ('' === $fields) {
			return FALSE;
		}

		if ('' === $this->mb_trim($str)) {
			return TRUE;
		}

		$fields = explode(',', $fields);
		foreach ($fields as $field) {
			if (! array_key_exists($field, $this->_field_data)) {
				return FALSE;
			}

			if (! array_key_exists('postdata', $this->_field_data[$field])) {
				return FALSE;
			}

			if ('' === $this->mb_trim($this->_field_data[$field]['postdata'])) {
				return FALSE;
			}
		}

		return TRUE;
	}


	/**
	 * 最大バイト数
	 */
	public function max_byte($str, $val) {

		if (is_array($str)) {
			return FALSE;
		}
		else {

			$byte = strlen(bin2hex($str)) / 2;
			return ($byte > $val) ? FALSE : TRUE;
		}
	}


	/**
	 * 最小バイト数
	 */
	public function min_byte($str, $val) {

		if (is_array($str)) {
			return FALSE;
		}
		else {

			$byte = strlen(bin2hex($str)) / 2;
			return ($byte > $val) ? TRUE : FALSE;
		}
	}


	/**
	 * 緯度,経度の形式チェック
	 * グーグルマップからのコピペを想定している
	 */
	public function coordinate($str) {

		if (preg_match('/^([1-9]\d*|0)(\.\d+)?,([1-9]\d*|0)(\.\d+)?$/', $str)) {
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * 日付の妥当性チェック
	 *
	 * @param string $date yyyy/mm/dd
	 */
	public function is_date($date, $checkdate = 1) {
		if (preg_match("/^\d{4}\/\d{2}\/\d{2}$/", $date)
			OR preg_match("/^\d{4}\-\d{2}\-\d{2}$/", $date)) {
			if (1 == $checkdate) {
				return checkdate(substr($date, 5, 2), substr($date, 8, 2), substr($date, 0, 4));
			}
			else {
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	 * 日時の妥当性チェック
	 *
	 * @param string $datetime yyyy/mm/dd hh:mm:ss
	 * @param int $checkdate checkdateをする/しない
	 * @return bool
	 */
	public function is_datetime($datetime, $checkdate = 1) {
		if (preg_match("/^\d{4}\/\d{2}\/\d{2}\ ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $datetime)
			OR preg_match("/^\d{4}\-\d{2}\-\d{2}\ ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $datetime)) {
			if (1 == $checkdate) {
				return checkdate(substr($datetime, 5, 2), substr($datetime, 8, 2), substr($datetime, 0, 4));
			}
			else {
				return TRUE;
			}
		}
		return FALSE;
	}


	/**
	 * 時:分の妥当性チェック
	 *
	 * @param string $hhmm hh:mm
	 */
	public function is_hhmm($hhmm) {
		// 形式を調べます
		if (preg_match("/^\d{2}:\d{2}$/", $hhmm)) {

			$t = explode(':', $hhmm);
			$h = $t[0];
			$m = $t[1];

			if (23 < $h) {
				return FALSE;
			}

			if (59 < $m) {
				return FALSE;
			}

			return TRUE;
		}
		return FALSE;
	}


	/**
	 * 半角チェック
	 *
	 * @var string $str
	 * @return bool
	 *
	 */
	public function single($str) {

		if ('' == $str) {
			return TRUE;
		}

		return (strlen($str) != mb_strlen($str)) ? FALSE : TRUE;
	}


	/**
	 * 全角カタカナと全・半角空白のみかチェック
	 *
	 * @access public
	 * @param string
	 * @param string encoding 例) UTF-8
	 * @return bool
	 *
	 */
	public function katakana_blank($str) {
		if ('' == $str) {
			return TRUE;
		}

		if (preg_match("/^[ァ-ヶー 　]+$/u", $str)) {

			return TRUE;
		}
		else {

			return FALSE;
		}
	}


	/**
	 * 全角カタカナ チェック
	 *
	 * @access public
	 * @param string
	 * @param string encoding 例) UTF-8
	 * @return bool
	 *
	 */
	public function katakana($str) {
		if ('' == $str) {
			return TRUE;
		}

		return ( ! preg_match("/^(?:\xE3\x82[\xA1-\xBF]|\xE3\x83[\x80-\xB6]|ー)+$/", $str)) ? FALSE : TRUE;
	}


	/**
	 * 電話番号チェック
	 *
	 * @access public
	 * @param	string
	 * @return bool
	 *
	 */
	public function valid_phone($str) {

		if ('' == $str) {
			return TRUE;
		}

		return ( ! preg_match("/^\d{2,5}[-]?\d{1,4}[-]?\d{1,4}$/", $str)) ? FALSE : TRUE;
		//return ( ! preg_match("/^\d{2,5}\-\d{1,4}\-\d{1,4}$/", $str)) ? FALSE : TRUE;
	}


	/**
	 * 郵便番号チェック
	 *
	 * @access public
	 * @param	string
	 * @return bool
	 *
	 */
	public function valid_zip($str, $no_hyphen) {

		if ('' == $str) {
			return TRUE;
		}

		if (1 == $no_hyphen) {
			return (preg_match("/^\d{7}$/", $str)) ? TRUE : FALSE;
		}
		else {
			return (preg_match("/^\d{3}\-\d{4}$/", $str)) ? TRUE : FALSE;
		}
	}


	/**
	 * @param string $str
	 * @return bool
	 */
	public function valid_url($str) {
		if ('#' === $str) {
			return TRUE;
		}
		return parent::valid_url($str);
	}


	/**
	 * @param $string
	 * @return string
	 */
	public function mb_trim($string) {
		$whitespace = '[\s\0\x0b\p{Zs}\p{Zl}\p{Zp}]';
		$ret = preg_replace(sprintf('/(^%s+|%s+$)/u', $whitespace, $whitespace), '', $string);
		return $ret;
	}


	/**
	 * コンマを削除する…だけ
	 *
	 * @param $number
	 * @return mixed
	 */
	public function rm_comma($number) {
		$ret = str_replace(',', '', $number);
		return $ret;
	}


	/**
	 * スペースを半角一つにする…だけ
	 *
	 * @param $str
	 * @return string|string[]|null
	 */
	public function onespace($str) {
		$str = preg_replace('/[\s\0\x0b\p{Zs}\p{Zl}\p{Zp}]+/u', ' ', $str);
		return $str;
	}


	/**
	 * Alpha-numeric w/ spaces
	 *
	 * @param	string
	 * @return	bool
	 */
	public function alpha_numeric_spaces($str)
	{
		if ('' == $str) {
			return TRUE;
		}

		return (bool) preg_match('/^[A-Z0-9 ]+$/i', $str);
	}


	/**
	 * halfwidth
	 *
	 * @param	string
	 * @return	bool
	 */
	public function halfwidth($str)
	{
		if ('' == $str) {
			return TRUE;
		}

		return (bool) preg_match('/^[!-~ ]+$/i', $str);
	}


	/**
	 * @param $str
	 * @param $field
	 * @return bool
	 */
	public function field_greater_than($str, $field) {
		return (isset($this->_field_data[$field]) && $this->_field_data[$field]['postdata'] < $str);
	}


	/**
	 * DBの文字コードがutf8mb4でしか対応できない文字をお断りする
	 *
	 * @param $str
	 * @return bool
	 */
	public function not_include_utf8mb4($str) {
		$encoding = 'UTF-8';
		$cnt = mb_strlen($str, $encoding);
		for ($i = 0; $i < $cnt; $i++) {
			$s = mb_substr($str, $i, 1, $encoding);
			if (3 < strlen($s)) {
				return false;
			}
		}
		return true;
	}


	/**
	 * UTF-8 の4バイト文字を HTML 数値文字参照に変換する
	 *
	 * @see https://qiita.com/masakielastic/items/ec483b00ff6337a02878
	 * @param $str
	 * @return string|string[]|null
	 */
	public function utf8mb4_encode_numericentity($str) {

		if ('string' !== gettype($str) OR '' === $str) {
			return $str;
		}

		$re = '/[^\x{0}-\x{FFFF}]/u';
		return preg_replace_callback($re, function($m) {
			$char = $m[0];
			$x = ord($char[0]);
			$y = ord($char[1]);
			$z = ord($char[2]);
			$w = ord($char[3]);
			$cp = (($x & 0x7) << 18) | (($y & 0x3F) << 12) | (($z & 0x3F) << 6) | ($w & 0x3F);
			return sprintf("&#x%X;", $cp);
		}, $str);
	}


	/**
	 * @param $str
	 * @return bool
	 */
	public function is_empty($str) {
		return (bool)('' === strval($str));
	}
}
