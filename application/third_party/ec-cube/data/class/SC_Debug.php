<?php

/**
 * @author naoyuki onishi
 * @since 2008
 */
class SC_Debug {

	const undefined = ':undefined:';

	private static $_DUMP = [];
	private static $_ENABLE = NULL;
	private static $_INSTANCE = NULL;
	private static $_DESTRUCT_DUMP = FALSE;
	private static $_CLEAN_PATH = NULL;
	private static $_ROOT_PATH = "";
	private static $_IS_CLI = FALSE;

	/**
	 *
	 */
	public static function CLI() {
		self::$_IS_CLI = TRUE;
	}

	/**
	 *
	 */
	public static function NOT_CLI() {
		self::$_IS_CLI = FALSE;
	}

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
	 * @return null|boolean
	 */
	public static function isENABLE() {
		return self::$_ENABLE;
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
		else if (defined('SC_Debug') && ('1' == SC_Debug || 'true' === strtolower(SC_Debug))) {
			self::$_ENABLE = TRUE;
		}
		else {
			self::$_ENABLE = FALSE;
		}
	}

	/**
	 * 記録するファイルパスが相対パスなのかフルパスなのか設定
	 *
	 * @param boolean $enable
	 */
	public static function SET_CLEAN_PATH($enable, $root_path) {
		if (! is_null(self::$_CLEAN_PATH)) {
			return;
		}

		if (is_bool($enable)) {
			self::$_CLEAN_PATH = $enable;
		}

		self::$_ROOT_PATH = strval($root_path);
	}

	/**
	 * @param bool $is_html
	 * @return bool
	 */
	protected static function _get_is_html($is_html) {
		// if (TRUE === self::$_IS_CLI) {
		// 	return false;
		// }
		if (! is_null($is_html)) {
			return $is_html;
		}
		return false;
	}

	/**
	 * @param string $strings
	 * @param bool $is_html
	 * @return void
	 */
	public static function sfAddStr($strings, $is_html = null) {
		$is_html = static::_get_is_html($is_html);
		self::_SET_ENABLE();
		if (TRUE !== self::$_ENABLE) return;
		$varinfo = new SC_Debug_VarInfo();
		$varinfo->is_html = $is_html;
		$varinfo->var_name = '';
		$varinfo->var = $strings;
		self::$_DUMP[] = $varinfo;
	}

	/**
	 * @param string $var_name
	 * @param object $var
	 * @param bool $is_html
	 * @param integer $tracenumber
	 * @return void
	 */
	public static function sfAddVar($var_name, $var = SC_Debug::undefined, $is_html = null, $tracenumber = 0) {
		$is_html = static::_get_is_html($is_html);
		self::_SET_ENABLE();
		if (TRUE !== self::$_ENABLE) return;
		self::$_DUMP[] = self::_make_varinfo($var_name, $var, $is_html, $tracenumber);
	}

	/**
	 * @param string $var_name
	 * @param mixed $var
	 * @return void
	 */
	public static function sfAddVarTop($var_name, $var = SC_Debug::undefined, $is_html = null, $tracenumber = 0) {
		$is_html = static::_get_is_html($is_html);
		self::_SET_ENABLE();
		if (TRUE !== self::$_ENABLE) return;
		array_unshift(self::$_DUMP, self::_make_varinfo($var_name, $var, $is_html, $tracenumber));
	}

	/**
	 * @param $var_name
	 * @param $var
	 * @param $is_html
	 * @param $tracenumber
	 * @return SC_Debug_VarInfo
	 */
	protected static function _make_varinfo($var_name, $var, $is_html, $tracenumber) {
		$varinfo = new SC_Debug_VarInfo();
		$varinfo->is_html = $is_html;
		if (SC_Debug::undefined === $var
			OR (('array' === gettype($var_name) || 'object' === gettype($var_name))
				AND ('array' !== gettype($var) && 'object' !== gettype($var) && '' === strval($var))
			)
		) {
			$varinfo->var_name = '';
			$varinfo->var = $var_name;
		}
		else {
			$varinfo->var_name = $var_name;
			$varinfo->var = $var;
		}
		$arrTrace = self::getTraceArray();
		$varinfo->caller = $arrTrace[$tracenumber + 1];
		return $varinfo;
	}

	/**
	 * @param string $prefix
	 * @param string $suffix
	 */
	public static function sfVarDump($prefix = '<div style="background-color:white;width:100%;overflow-x:auto;" class="sc_debug">', $suffix = '</div>') {
		self::_SET_ENABLE();
		if (TRUE !== self::$_ENABLE) return;

		if (! empty(self::$_DUMP)) {

			if (TRUE !== self::$_IS_CLI) {
				echo $prefix;
				echo '<style>pre{margin:0}</style>';
			}

			foreach (self::$_DUMP as $varinfo) {
				self::_sfVarDump($varinfo);
			}

			if (TRUE !== self::$_IS_CLI) {
				echo $suffix;
			}
		}
	}

	/**
	 * @param string $prefix
	 * @param string $suffix
	 * @return false|string
	 */
	public static function sfGetVarDump($prefix = '<div style="background-color:white;width:100%;overflow-x:auto;" class="sc_debug">', $suffix = '</div>') {
		if (empty(self::$_DUMP)) {
			return '';
		}
		ob_start();
		self::sfVarDump($prefix, $suffix);
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}

	public static function sfReset() {
		self::$_DUMP = [];
	}

	/**
	 * @return string|true
	 */
	public static function sfGetDump() {
		return self::$_DUMP;
	}

	/**
	 * @param SC_Debug_VarInfo $varinfo
	 * @return void
	 */
	private static function _sfVarDump(SC_Debug_VarInfo $varinfo) {

		if (! is_null($varinfo->caller)) {
			$caller = htmlspecialchars($varinfo->caller, ENT_COMPAT).'<br>';
		}
		else {
			$caller = '';
		}

		if ('' !== strval($varinfo->var_name)) {
			$var_name = htmlspecialchars($varinfo->var_name);
			$var_title = "{$var_name}=>";
		}
		else {
			$var_title = '';
		}

		if (is_null($varinfo->var)) {
			$varinfo->var = 'null';
		}

		if ($varinfo->is_html) {
			echo("<div>{$caller}{$var_title}".print_r($varinfo->var, true)."</div>");
		}
		else {
			echo("<div>{$caller}{$var_title}".nl2br(str_replace(" ", "&nbsp;", htmlspecialchars(print_r($varinfo->var, true)))).'</div>');
		}
	}

	/**
	 * @param string $name
	 */
	public static function sfAddVarTrace($name = "backtrace") {
		$arrTrace = self::getTraceArray();
		$arrTrace = array_reverse($arrTrace);

		self::sfAddVar($name, $arrTrace);
	}

	/**
	 * @return multitype:
	 */
	public static function getTraceArray() {
		$arrTrace = [];

		$debug_backtrace = debug_backtrace();

		$limit = count($debug_backtrace);

		for ($i = 1; $i < $limit; $i++) {

			$line = '';

			if (isset($debug_backtrace[$i]['file'])) {
				$line .= self::cleanPath($debug_backtrace[$i]['file']);
			}

			if (isset($debug_backtrace[$i]['line'])) {
				$line .= "(".$debug_backtrace[$i]['line']."): ";
			}

			if (isset($debug_backtrace[$i + 1])) {
				$line .= $debug_backtrace[$i + 1]['function'];
			}

			$arrTrace[] = $line;
		}

		return $arrTrace;
	}

	/**
	 * @param string $var_name
	 * @param mixed $var
	 * @return void
	 */
	public static function sfLog($var_name, &$var = null) {
		self::_SET_ENABLE();
		if (TRUE !== self::$_ENABLE) return;

		$arrTrace = self::getTraceArray();

		if (is_null($var)) {
			// EC-CUBE2.1X
			// GC_Utils_Ex::gfPrintLog("\n".$arrTrace[0]."\n".print_r($var_name, true)."\n\n\n");

			// ORE
			// log_message('debug', "\n".$arrTrace[0]."\n".print_r($var_name, true));

			// VALX
			\VALX\libs\logger::debug(print_r($var_name, true), 2);
		}
		else {
			// EC-CUBE2.1X
			// GC_Utils_Ex::gfPrintLog("\n".$arrTrace[0]."\n{$var_name}=".print_r($var, true)."\n\n\n");

			// ORE
			// log_message('debug', "\n".$arrTrace[0]."\n{$var_name}=".print_r($var, true));

			// VALX
			\VALX\libs\logger::debug("{$var_name}=".print_r($var, true), 2);
		}
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public static function cleanPath($path) {
		$tmp = $path;
		$tmp = realpath($tmp);
		if (! $tmp) {
			return $path;
		}

		if (TRUE === self::$_CLEAN_PATH) {
			//$tmp = str_replace(DIR_DATA_ROOT, DIR_DATA_NAME, $tmp);
			//$tmp = str_replace(DIR_HTML_ROOT, DIR_HTML_NAME, $tmp);
			$tmp = str_replace(self::$_ROOT_PATH, "", $tmp);
		}

		return $tmp;
	}

	/**
	 *
	 */
	function __destruct() {
		if (TRUE === self::$_DESTRUCT_DUMP) {
			self::sfVardump();
		}
	}
}

/**
 * Class SC_Debug_VarInfo
 */
class SC_Debug_VarInfo {
	public $is_html = false;
	public $var_name = null;
	public $var = null;
	public $caller = null;
}
