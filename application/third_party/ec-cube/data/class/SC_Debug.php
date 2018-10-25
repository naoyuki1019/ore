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
	private static $_CLEAN_PATH= NULL;
	private static $_ROOT_PATH= "";

	public static function ENABLE() {
		self::$_ENABLE = TRUE;
	}

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

		if (! is_null(self::$_ENABLE)) {
			return;
		}

		if (defined('DEBUG_MODE') AND TRUE === DEBUG_MODE) {
			self::$_ENABLE = TRUE;
		}
		else {
			self::$_ENABLE = FALSE;
		}
	}


	/**
	 * 記録するファイルパスが相対パスなのかフルパスなのか設定
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
	 * @return void
	 */
	public static function sfAddVar($var_name, & $var, $is_html = FALSE) {

		self::_SET_ENABLE();

		if (TRUE !== self::$_ENABLE) return;

		$varinfo = new SC_Debug_VarInfo();
		$varinfo->is_html = $is_html;
		$varinfo->var_name = $var_name;
		$varinfo->var = $var;

		$arrTrace = self::getTraceArray();
		$varinfo->caller = $arrTrace['0'];

		self::$_DUMP[] = $varinfo;
	}


	/**
	 * @param string $var_name
	 * @param resource $var
	 * @return void
	 */
	public static function sfAddVarTop($var_name, & $var, $is_html = FALSE) {

		self::_SET_ENABLE();

		if (TRUE !== self::$_ENABLE) return;

		$varinfo = new SC_Debug_VarInfo();
		$varinfo->is_html = $is_html;
		$varinfo->var_name = $var_name;
		$varinfo->var = $var;

		$arrTrace = self::getTraceArray();
		$varinfo->caller = $arrTrace['0'];

		array_unshift(self::$_DUMP, $varinfo);
	}


	/**
	 *
	 * @return void
	 */
	public static function sfVarDump() {

		self::_SET_ENABLE();

		if (TRUE !== self::$_ENABLE) return;

		if (! empty(self::$_DUMP)) {
			echo '<div style="background-color:white;margin:40px 0;" class="sc_debug">';
			foreach (self::$_DUMP as $varinfo) {
				self::_sfVardump($varinfo);
			}
			echo '</div>';
		}
	}


	/**
	 *
	 * @param SC_Debug_VarInfo $varinfo
	 * @return void
	 */
	private static function _sfVardump(SC_Debug_VarInfo $varinfo) {

		$caller = htmlspecialchars($varinfo->caller);
		$var_name = htmlspecialchars($varinfo->var_name);

		if ($varinfo->is_html) {
			echo ("<div>{$caller}<br>{$var_name}=>{$varinfo->var}</div>");
		}
		else {
			$br = '';
			if (is_array($varinfo->var) OR is_object($varinfo->var)) {
				$br = '<br>';
			}
			echo ("<div>{$caller}<br>{$br}{$var_name}=>" . nl2br(str_replace(" ", "&nbsp;", htmlspecialchars(print_r($varinfo->var, true))) . '</div>'));
		}
	}


	/**
	 *
	 * @param string $name
	 */
	public static function sfAddVarTrace($name="backtrace") {

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

		for ($i=1; $i<$limit; $i++) {

			$line = '';

			if (isset($debug_backtrace[$i]['file'])) {
				$line .= self::cleanPath($debug_backtrace[$i]['file']);
			}

			if (isset($debug_backtrace[$i]['line'])) {
				$line .= "(" . $debug_backtrace[$i]['line'] . "): ";
			}

			if (isset($debug_backtrace[$i+1])) {
				$line .= $debug_backtrace[$i+1]['function'];
			}

			$arrTrace[] = $line;
		}

		return $arrTrace;
	}


	/**
	 * @param string $var_name
	 * @param resource $var
	 * @return void
	 */
	public static function sfLog($var_name, & $var) {

		self::_SET_ENABLE();

		if (TRUE !== self::$_ENABLE) return;

		$arrTrace = self::getTraceArray();

		// EC-CUBE2.1X
//		GC_Utils_Ex::gfPrintLog("\n" . $arrTrace['0'] . "\n{$var_name}=" . print_r($var, true) . "\n\n\n" );

		// ORE
//		log_message('debug', "\n" . $arrTrace['0'] . "\n{$var_name}=" . print_r($var, true));

		// VALX
		VALX\logger::debug($arrTrace['0'] . "\n{$var_name}=" . print_r($var, true) . "\n");
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
 *
 * @author naoyuki
 *
 */
class SC_Debug_VarInfo {
	public $is_html = TRUE;
	public $var_name = "";
	public $var = "";
}
