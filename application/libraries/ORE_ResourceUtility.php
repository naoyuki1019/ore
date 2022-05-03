<?php

/**
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 *
 */
class ORE_ResourceUtility {

	public $opendir_errors = [];
	public $dirnames = [];
	public $fileperms = [];
	public $fileperms_errors = [];

	/**
	 *
	 */
	public function __construct() {
		$this->initialize();
	}

	/**
	 *
	 */
	public function initialize() {
		$this->opendir_errors = [];
		$this->dirnames = [];
		$this->fileperms = [];
		$this->fileperms_errors = [];
	}

	/**
	 * @param String $dir
	 * @return array
	 */
	function get_directory_names($dir) {

		$this->initialize();

		$this->dirnames = [];

		$realdir = realpath($dir);
		if ("" == $realdir) {
			$this->opendir_errors[] = $dir." NOT FOUND";
			return $this->dirnames;
		}

		$handle = opendir($dir);
		if ($handle === FALSE) {
			$this->opendir_errors[] = $dir." OPEN ERROR";
			return $this->dirnames;
		}

		$dir = $realdir.'/';

		while (FALSE !== ($entry = readdir($handle))) {

			if (str_starts_with($entry, ".")) {
				continue;
			}

			$fullpath = $dir.$entry;

			if (TRUE === is_dir($fullpath)) {
				$this->dirnames[] = $entry;
			}
		}

		closedir($handle);

		return $this->dirnames;
	}

	/**
	 * @param string $dir
	 * @param string $recursive_call
	 * @return array
	 */
	public function get_permissions($dir, $recursive_call = FALSE) {

		if (FALSE === $recursive_call) {
			$this->initialize();
		}

		$realdir = realpath($dir);
		if ("" == $realdir) {
			$this->opendir_errors[] = $dir." NOT FOUND";
			return $this->fileperms;
		}

		$handle = opendir($dir);
		if ($handle === FALSE) {
			$this->opendir_errors[] = $dir." OPEN ERROR";
			return $this->fileperms;
		}

		$dir = $realdir.'/';

		while (FALSE !== ($entry = readdir($handle))) {

			if ($entry == "." || $entry == "..") {
				continue;
			}

			$fullpath = $dir.$entry;

			$permission = fileperms($fullpath);
			if ($permission !== FALSE) {
				$this->fileperms[$fullpath] = substr(sprintf('%o', $permission), -4);
			}
			else {
				echo $fullpath." GET PERMISSION ERROR\n";
				$this->fileperms_errors[] = $fullpath." GET PERMISSION ERROR";
			}

			// 再帰
			if (is_dir($fullpath)) {
				$this->get_permissions($fullpath, TRUE);
			}
		}

		closedir($handle);

		return $this->fileperms;
	}

	/**
	 * @return void
	 */
	public function dump() {

		//FTPバッチを作成のための一覧
		foreach ($this->dirnames as $dirname) {
			echo $dirname."\n";
		}

		//FTPバッチを作成のための一覧
		foreach ($this->fileperms as $path => $permssion) {
			echo "quote site chmod $permssion $path<br />";
		}

		//opendirでhandle取得に失敗した一覧
		if (! empty($this->opendir_errors)) {

			echo "============== opendir_errors ====================<br /><br />";

			foreach ($this->opendir_errors as $dir) {

				echo "$dir<br />";
			}
			echo "<br />";

			echo "==================================================<br /><br />";
		}

		//filepermsでpermssion取得に失敗した一覧
		if (! empty($this->fileperms_errors)) {

			echo "============== fileperms_errors ==================<br /><br />";

			foreach ($this->fileperms_errors as $path) {

				echo "$path<br />";
			}

			echo "<br />";
			echo "==================================================<br /><br />";
		}
	}
}
