<?php

/**
 *
 * @author onishi naoyuki
 * @since 2008
 *
 */
class SC_Debug {

	private static $_DUMP = array();
	private static $_ENABLE = NULL;
	private static $_INSTANCE = NULL;
	private static $_CLEAN_PATH = NULL;
	private static $_ROOT_PATH = "";

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

		if (defined('DEBUG_MODE') AND TRUE === DEBUG_MODE) {
			self::$_ENABLE = TRUE;
		}
		else if (defined('SC_Debug') AND ('1' == SC_Debug OR 'true' === strtolower(SC_Debug))) {
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
	 * @param string $var_name
	 * @param object $var
	 * @param bool $is_html
	 * @param integer $tracenumber
	 * @return void
	 */
	public static function sfAddVar($var_name, $var = ':undefined:', $is_html = FALSE, $tracenumber = 0) {
		self::_SET_ENABLE();
		if (TRUE !== self::$_ENABLE) return;
		self::$_DUMP[] = self::_make_varinfo($var_name, $var, $is_html, $tracenumber);
	}

	/**
	 * @param string $var_name
	 * @param mixed $var
	 * @return void
	 */
	public static function sfAddVarTop($var_name, $var = ':undefined:', $is_html = FALSE, $tracenumber = 0) {
		self::_SET_ENABLE();
		if (TRUE !== self::$_ENABLE) return;
		array_unshift(self::$_DUMP, self::_make_varinfo($var_name, $var, $is_html, $tracenumber));
	}

	/**
	 * @param $var_name
	 * @param $var
	 * @param $is_html
	 * @param $tracenumber
	 *
	 * @return SC_Debug_VarInfo
	 */
	protected static function _make_varinfo($var_name, $var, $is_html, $tracenumber) {
		$varinfo = new SC_Debug_VarInfo();
		$varinfo->is_html = $is_html;
		if (':undefined:' === $var
			OR (('array' === gettype($var_name) OR 'object' === gettype($var_name))
				AND ('array' !== gettype($var) AND 'object' !== gettype($var) AND '' === strval($var))
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
	 *
	 * @return void
	 */
	public static function sfVarDump($prefix = '<div style="background-color:white;width:100%;overflow-x:auto;">', $sufix = '</div>') {
		self::_SET_ENABLE();
		if (TRUE !== self::$_ENABLE) return;

		if (! empty(self::$_DUMP)) {
			echo $prefix;
			echo '<style>pre{margin:0}</style>';
			foreach (self::$_DUMP as $varinfo) {
				self::_sfVardump($varinfo);
			}
			echo $sufix;
		}
	}

	/**
	 *
	 * @param SC_Debug_VarInfo $varinfo
	 * @return void
	 */
	private static function _sfVardump(SC_Debug_VarInfo $varinfo) {
		$caller = htmlspecialchars($varinfo->caller, ENT_COMPAT);
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
			echo("<div>{$caller}<br>{$var_title}{$varinfo->var}</div>");
		}
		else {
			echo("<div>{$caller}<br>{$var_title}".nl2br(str_replace(" ", "&nbsp;", htmlspecialchars(print_r($varinfo->var, true))).'</div>'));
		}
	}

	/**
	 *
	 * @param string $name
	 */
	public static function sfAddVarTrace($name = "backtrace") {
		$arrTrace = self::getTraceArray();
		$arrTrace = array_reverse($arrTrace);

		self::sfAddVar($name, $arrTrace);
	}

	/**
	 *
	 * @return multitype:
	 */
	public static function getTraceArray() {
		$arrTrace = array();

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
	public static function sfLog($var_name, & $var = null) {
		self::_SET_ENABLE();
		if (TRUE !== self::$_ENABLE) return;

		$arrTrace = self::getTraceArray();

		if (is_null($var)) {
			// EC-CUBE2.1X
//			GC_Utils_Ex::gfPrintLog("\n".$arrTrace['0']."\n".print_r($var_name, true)."\n\n\n");

			// ORE
//			log_message('debug', "\n".$arrTrace['0']."\n".print_r($var_name, true));

			// VALX
			\VALX\logger::debug(print_r($var_name, true), 2);
		}
		else {
			// EC-CUBE2.1X
//			GC_Utils_Ex::gfPrintLog("\n".$arrTrace['0']."\n{$var_name}=".print_r($var, true)."\n\n\n");

			// ORE
//			log_message('debug', "\n".$arrTrace['0']."\n{$var_name}=".print_r($var, true));

			// VALX
			\VALX\logger::debug("{$var_name}=".print_r($var, true), 2);
		}
	}

	/**
	 *
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
}

/**
 * Class SC_Debug_VarInfo
 */
class SC_Debug_VarInfo {
	public $is_html = false;
	public $var_name = "";
	public $var = "";
}
