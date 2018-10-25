<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation {

	/**
	 * @var MY_Controller
	 */
	public $CI = null;

	public $_error_prefix = '<p class="form-error">';
	public $_error_suffix = '</p>';

	/**
	 *
	 */
	public function __construct($rules = array()) {
		//ここで先読みさせてフォームバリデーションオブジェクトの取得関数を定義
		get_instance()->load->helper('my_form');
		parent::__construct($rules);
	}


	/**
	 *
	 */
	public function initialize() {
		$this->_field_data = array();
		$this->_config_rules = array();
		$this->_error_array = array();
		$this->_error_messages = array();
		$this->error_string = '';
		$this->_safe_form_data = FALSE;
		return $this;
	}


	/**
	 *
	 * @see CI_Form_validation::set_data()
	 */
	public function set_data($data=array()) {

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
	 *
	 */
	public function error_array() {
		return $this->_error_array;
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
	* 入力値の変換
	*
	* @access public
	* @param string
	* @param string
	* @return string
	*
	*
	* 半角文字列 -> ras
	* 全角文字列 -> ASKV
	* 全角カタカナ -> KVC
	* 半角カタカナ -> kh
	* ひらがな -> HVc
	*/
	public function mb_convert_kana($str, $val) {
		return mb_convert_kana($str, $val);
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
			return ( ! empty($str));
		}

		else {
			return (mb_trim($str) == '') ? FALSE : TRUE;
		}
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
	 * @param string $date yyyy/mm/dd
	 */
	public function is_date($date) {
		if (preg_match("/^\d{4}\/\d{2}\/\d{2}$/", $date)
		 OR preg_match("/^\d{4}\-\d{2}\-\d{2}$/", $date)) {
			return checkdate(substr($date ,5 ,2), substr($date ,8 ,2), substr($date ,0 ,4));
		}
		return FALSE;
	}


	/**
	 * 日時の妥当性チェック
	 * @param string $date yyyy/mm/dd hh:mm:ss
	 */
	public function is_datetime($datetime) {

		if (preg_match("/^\d{4}\/\d{2}\/\d{2}\ ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $datetime)
		 OR preg_match("/^\d{4}\-\d{2}\-\d{2}\ ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $datetime)) {
			return checkdate(substr($datetime ,5 ,2), substr($datetime ,8 ,2), substr($datetime ,0 ,4));
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
	 * @param   string
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
}
