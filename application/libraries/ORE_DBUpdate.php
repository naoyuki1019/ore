<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * Class ORE_DBInsert
 *
 * @author naoyuki onishi
 */
class ORE_DBUpdate {
	public $table = null;
	public $id_column = null;
	protected $_targets = null;
	protected $_list = [];

	public $update_cnt = 0;
	public $total_cnt = 0;
	public $threshold = 1000;
	public $dry_run = 0;
	public $echo = 0;
	public $debug = 0;
	public $sc_debug = 0;

	/**
	 * @param $statement
	 */
	protected function _exec($statement) {
		if ($this->debug) {
			$this->echo_flush('<h3>'.__FILE__.'('.__LINE__.')'.' '.__METHOD__.'</h3>');

			// PHPUnit
			if (isset($_SERVER) && array_key_exists('argv', $_SERVER) && 0 < count($_SERVER['argv']) && 'phpunit' === substr($_SERVER['argv'][0], -7)) {
				$this->echo_flush(\SqlFormatter::format($statement, false));
			}
			else {
				$this->echo_flush(\SqlFormatter::format($statement, true));
			}
		}

		if ($this->sc_debug) {
			// PHPUnit
			if (isset($_SERVER) && array_key_exists('argv', $_SERVER) && 0 < count($_SERVER['argv']) && 'phpunit' === substr($_SERVER['argv'][0], -7)) {
				\SC_Debug::sfAddVar(\SqlFormatter::format($statement, false));
			}
			else {
				\SC_Debug::sfAddVar(\SqlFormatter::format($statement, false));
			}
		}

		if ($this->dry_run) {
			return;
		}
	}

	/**
	 * @param null $option
	 */
	public function begin($option = null) {
		if ($this->debug) {
			$this->echo_flush('<h3>'.__FILE__.'('.__LINE__.')'.' '.__METHOD__.'</h3>');
		}
		if ($this->dry_run) {
			return;
		}
	}

	/**
	 * @param null $option
	 */
	public function commit($option = null) {
		if ($this->debug) {
			$this->echo_flush('<h3>'.__FILE__.'('.__LINE__.')'.' '.__METHOD__.'</h3>');
		}
		if ($this->dry_run) {
			return;
		}
	}

	/**
	 * @param null $option
	 */
	public function rollback($option = null) {
		if ($this->debug) {
			$this->echo_flush('<h3>'.__FILE__.'('.__LINE__.')'.' '.__METHOD__.'</h3>');
		}
		if ($this->dry_run) {
			return;
		}
	}

	/**
	 * @param $str
	 */
	protected function echo_flush($str) {
		if (0 < ob_get_level() && $this->echo) {
			// PHPUnit
			if (isset($_SERVER) && array_key_exists('argv', $_SERVER) && 0 < count($_SERVER['argv']) && 'phpunit' === substr($_SERVER['argv'][0], -7)) {
				\SC_Debug::sfAddVar('$str', $str);
			}
			else {
				echo $str;
				ob_flush();
				flush();
			}
		}
	}

	/**
	 * @see https://www.php.net/manual/en/function.mysql-real-escape-string.php
	 * @param $str
	 * @return array|string|string[]
	 */
	protected function _escape_string($str) {
		return str_replace(['\\', "\0", "\n", "\r", "'", '"', "\x1a"], ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'], $str);
	}

	/**
	 * @param $targets
	 * @throws \Exception
	 */
	public function set_targets($targets) {

		if ('' === strval($this->id_column)) {
			throw new \Exception('$this->id_columnがセットされていません。');
		}

		foreach ($targets as $i => $target) {
			if ($target === $this->id_column) unset($targets[$i]);
		}

		if (! is_array($targets) || empty($targets)) {
			throw new \Exception('$targetsの値が不正です');
		}

		$this->_targets = $targets;
		$this->init_list();
	}

	/**
	 * @param $target
	 * @return bool
	 */
	public function has_target($target) {
		return (boolean)(in_array($target, $this->_targets));
	}

	/**
	 *
	 */
	protected function init_list() {
		$this->_list = [];
		foreach ($this->_targets as $target) {
			$this->_list[$target] = [];
		}
	}

	/**
	 * @param $id
	 * @param $arg_cols
	 * @throws \Exception
	 */
	public function add($id, $arg_cols) {

		$type = strtolower(gettype($arg_cols));
		if ('array' !== $type && 'object' !== $type) {
			throw new \Exception('$arg_colsが配列でもオブジェクトでもありません。');
		}

		if ('object' === $type) {
			$cols = get_object_vars($arg_cols);
		}
		else {
			$cols = $arg_cols;
		}

		if (array_key_exists($this->id_column, $cols)) unset($cols[$this->id_column]);

		if (! isset($id) || is_null($id) || '' === strval($id)) {
			throw new \Exception('$idがセットされていません。');
		}

		if (empty($this->_targets)) {
			throw new \Exception('$this->_targetsがセットされていません。');
		}

		$this->update_cnt++;
		$this->total_cnt++;

		foreach ($this->_targets as $target) {
			if (! array_key_exists($target, $cols)) {
				throw new \Exception("{$target}がセットされていません。");
			}
			$this->_list[$target][$id] = $cols[$target];
			unset($cols[$target]);
		}

		if (0 < count($cols)) {
			$msg = 'Error: 余った -> '.implode(', ', array_keys($cols));
			$this->echo_flush($msg.'<br>');
			throw new \Exception($msg);
		}

		if ($this->update_cnt >= $this->threshold) {
			$this->update_all();
		}
	}

	/**
	 * @throws \Exception
	 */
	public function update_all() {
		if (1 > $this->update_cnt) return;
		$statement = $this->makeStatement();
		$this->_exec($statement);
		$this->init_list();
		$this->update_cnt = 0;
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function makeStatement() {

		if ('' === strval($this->table)) {
			throw new \Exception('$this->tableがセットされていません。');
		}

		if ('' === strval($this->id_column)) {
			throw new \Exception('$this->id_columnがセットされていません。');
		}

		if (empty($this->_targets)) {
			throw new \Exception('$this->_targetsがセットされていません。');
		}

		$t1[] = "UPDATE {$this->table} SET";

		foreach ($this->_targets as $target) {
			$t2 = [];
			$t2[] = "  {$target} = CASE {$this->id_column}";

			if (! is_array($this->_list[$target]) || empty($this->_list[$target])) {
				throw new \Exception("\$this->_list[{$target}]がセットされていません。");
			}

			foreach ($this->_list[$target] as $key => $val) {
				if (is_null($val)) {
					$valstr = 'NULL';
				}
				else {
					$valstr = "'".$this->_escape_string($val)."'";
				}
				$t2[] = "    WHEN '{$key}' THEN {$valstr}";
			}
			$t2[] = "  END";
			$t3[] = implode("\n", $t2);
		}

		$t1[] = implode(",\n", $t3);

		$where = '';
		foreach ($this->_targets as $target) {
			$ids = array_keys($this->_list[$target]);
			$where = "'".implode("'={$this->id_column} || '", $ids)."'={$this->id_column}";
			break;
		}

		$t1[] = "WHERE {$where}";

		$statement = implode("\n", $t1);

		return $statement;
	}
}

