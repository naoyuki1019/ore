<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */
namespace ore;

/**
 * Class ORE_Validation
 *
 * @author naoyuki onishi
 */
class ORE_Validation {


	////////////////////////////////////////////////////////////////////////////////////////////
	//	Form_validation.php
	////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Performs a Regular Expression match test.
	 *
	 * @param	string
	 * @param	string	regex
	 * @return	bool
	 */
	public static function regex_match($str, $regex)
	{
		return (bool) preg_match($regex, $str);
	}

// --------------------------------------------------------------------

	/**
	 * Minimum Length
	 *
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	public static function min_length($str, $val)
	{
		if ( ! is_numeric($val))
		{
			return FALSE;
		}
		else
		{
			$val = (int) $val;
		}

		return (MB_ENABLED === TRUE)
			? ($val <= mb_strlen($str))
			: ($val <= strlen($str));
	}

// --------------------------------------------------------------------

	/**
	 * Max Length
	 *
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	public static function max_length($str, $val)
	{
		if ( ! is_numeric($val))
		{
			return FALSE;
		}
		else
		{
			$val = (int) $val;
		}

		return (MB_ENABLED === TRUE)
			? ($val >= mb_strlen($str))
			: ($val >= strlen($str));
	}

// --------------------------------------------------------------------

	/**
	 * Exact Length
	 *
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	public static function exact_length($str, $val)
	{
		if ( ! is_numeric($val))
		{
			return FALSE;
		}
		else
		{
			$val = (int) $val;
		}

		return (MB_ENABLED === TRUE)
			? (mb_strlen($str) === $val)
			: (strlen($str) === $val);
	}

// --------------------------------------------------------------------

	/**
	 * Valid URL
	 *
	 * @param	string	$str
	 * @return	bool
	 */
	public static function valid_url($str)
	{
		$matches = array();
		if (empty($str))
		{
			return FALSE;
		}
		elseif (preg_match('/^(?:([^:]*)\:)?\/\/(.+)$/', $str, $matches))
		{
			if (empty($matches[2]))
			{
				return FALSE;
			}
			elseif ( ! in_array($matches[1], array('http', 'https'), TRUE))
			{
				return FALSE;
			}

			$str = $matches[2];
		}

		$str = 'http://'.$str;

// There's a bug affecting PHP 5.2.13, 5.3.2 that considers the
// underscore to be a valid hostname character instead of a dash.
// Reference: https://bugs.php.net/bug.php?id=51192
		if (version_compare(PHP_VERSION, '5.2.13', '==') === 0 OR version_compare(PHP_VERSION, '5.3.2', '==') === 0)
		{
			sscanf($str, 'http://%[^/]', $host);
			$str = substr_replace($str, strtr($host, array('_' => '-', '-' => '_')), 7, strlen($host));
		}

		return (filter_var($str, FILTER_VALIDATE_URL) !== FALSE);
	}

// --------------------------------------------------------------------

	/**
	 * Valid Email
	 *
	 * @param	string
	 * @return	bool
	 */
	public static function valid_email($str)
	{
		return (bool) filter_var($str, FILTER_VALIDATE_EMAIL);
	}

// --------------------------------------------------------------------

	/**
	 * Valid Emails
	 *
	 * @param	string
	 * @return	bool
	 */
	public static function valid_emails($str)
	{
		if (strpos($str, ',') === FALSE)
		{
			return self::valid_email(trim($str));
		}

		foreach (explode(',', $str) as $email)
		{
			if (trim($email) !== '' && self::valid_email(trim($email)) === FALSE)
			{
				return FALSE;
			}
		}

		return TRUE;
	}

// --------------------------------------------------------------------

	/**
	 * Alpha
	 *
	 * @param	string
	 * @return	bool
	 */
	public static function alpha($str)
	{
		return ctype_alpha($str);
	}

// --------------------------------------------------------------------

	/**
	 * Alpha-numeric
	 *
	 * @param	string
	 * @return	bool
	 */
	public static function alpha_numeric($str)
	{
		return ctype_alnum((string) $str);
	}

// --------------------------------------------------------------------

	/**
	 * Alpha-numeric w/ spaces
	 *
	 * @param	string
	 * @return	bool
	 */
	public static function alpha_numeric_spaces($str)
	{
		return (bool) preg_match('/^[A-Z0-9 ]+$/i', $str);
	}

// --------------------------------------------------------------------

	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @param	string
	 * @return	bool
	 */
	public static function alpha_dash($str)
	{
		return (bool) preg_match('/^[a-z0-9_-]+$/i', $str);
	}

// --------------------------------------------------------------------

	/**
	 * Numeric
	 *
	 * @param	string
	 * @return	bool
	 */
	public static function numeric($str)
	{
		return (bool) preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $str);

	}

// --------------------------------------------------------------------

	/**
	 * Integer
	 *
	 * @param	string
	 * @return	bool
	 */
	public static function integer($str)
	{
		return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
	}

// --------------------------------------------------------------------

	/**
	 * Decimal number
	 *
	 * @param	string
	 * @return	bool
	 */
	public static function decimal($str)
	{
		return (bool) preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
	}

// --------------------------------------------------------------------

	/**
	 * Greater than
	 *
	 * @param	string
	 * @param	int
	 * @return	bool
	 */
	public static function greater_than($str, $min)
	{
		return is_numeric($str) ? ($str > $min) : FALSE;
	}

// --------------------------------------------------------------------

	/**
	 * Equal to or Greater than
	 *
	 * @param	string
	 * @param	int
	 * @return	bool
	 */
	public static function greater_than_equal_to($str, $min)
	{
		return is_numeric($str) ? ($str >= $min) : FALSE;
	}

// --------------------------------------------------------------------

	/**
	 * Less than
	 *
	 * @param	string
	 * @param	int
	 * @return	bool
	 */
	public static function less_than($str, $max)
	{
		return is_numeric($str) ? ($str < $max) : FALSE;
	}

// --------------------------------------------------------------------

	/**
	 * Equal to or Less than
	 *
	 * @param	string
	 * @param	int
	 * @return	bool
	 */
	public static function less_than_equal_to($str, $max)
	{
		return is_numeric($str) ? ($str <= $max) : FALSE;
	}

// --------------------------------------------------------------------

	/**
	 * Is a Natural number	(0,1,2,3, etc.)
	 *
	 * @param	string
	 * @return	bool
	 */
	public static function is_natural($str)
	{
		return ctype_digit((string) $str);
	}

// --------------------------------------------------------------------

	/**
	 * Is a Natural number, but not a zero	(1,2,3, etc.)
	 *
	 * @param	string
	 * @return	bool
	 */
	public static function is_natural_no_zero($str)
	{
		return ($str != 0 && ctype_digit((string) $str));
	}

// --------------------------------------------------------------------

	/**
	 * Valid Base64
	 *
	 * Tests a string for characters outside of the Base64 alphabet
	 * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
	 *
	 * @param	string
	 * @return	bool
	 */
	public static function valid_base64($str)
	{
		return (base64_encode(base64_decode($str)) === $str);
	}

// --------------------------------------------------------------------

	/**
	 * Prep URL
	 *
	 * @param	string
	 * @return	string
	 */
	public static function prep_url($str = '')
	{
		if ($str === 'http://' OR $str === '')
		{
			return '';
		}

		if (strpos($str, 'http://') !== 0 && strpos($str, 'https://') !== 0)
		{
			return 'http://'.$str;
		}

		return $str;
	}

// --------------------------------------------------------------------

	/**
	 * Convert PHP tags to entities
	 *
	 * @param	string
	 * @return	string
	 */
	public static function encode_php_tags($str)
	{
		return str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $str);
	}

	////////////////////////////////////////////////////////////////////////////////////////////
	//	MY_Form_validation.php
	////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * オーバーライド
	 */
	public static function required($str) {

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
	public static function max_byte($str, $val) {

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
	public static function min_byte($str, $val) {

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
	public static function coordinate($str) {

		if (preg_match('/^([1-9]\d*|0)(\.\d+)?,([1-9]\d*|0)(\.\d+)?$/', $str)) {
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * 日付の妥当性チェック
	 * @param string $date yyyy/mm/dd
	 */
	public static function is_date($date) {
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
	public static function is_datetime($datetime) {

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
	public static function single($str) {

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
	public static function katakana_blank($str) {
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
	public static function katakana($str) {
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
	public static function valid_phone($str) {

		if ('' == $str) {

			return TRUE;
		}
		return ( ! preg_match("/^\d{2,5}[-]?\d{1,4}[-]?\d{1,4}$/", $str)) ? FALSE : TRUE;
		//return ( ! preg_match("/^\d{2,5}\-\d{1,4}\-\d{1,4}$/", $str)) ? FALSE : TRUE;
	}

}
