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
	public $echo = 0;


	protected function _exec($statement) {}

	public function begin($option = null) {}

	public function commit($option = null) {}

	public function rollback($option = null) {}

	/**
	 * @param $str
	 */
	protected function echo_flush($str) {
		if (0 < ob_get_level() AND $this->echo) {
			echo $str;
			ob_flush();
			flush();
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
			throw new \Exception('$this->id_columnがセットされていません');
		}

		foreach ($targets as $i => $target) {
			if ($target === $this->id_column)  unset($targets[$i]);
		}

		if (! is_array($targets) OR empty($targets)) {
			throw new \Exception('$targetsの値が不正です');
		}

		$this->_targets = $targets;
		$this->init_list();
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
	 * @param $keyval
	 * @throws \Exception
	 */
	public function add($id, $keyval) {

		if (array_key_exists($this->id_column, $keyval)) unset($keyval[$this->id_column]);

		if (! isset($id) OR is_null($id) OR '' === strval($id)) {
			throw new \Exception('$idがセットされていません');
		}

		if (empty($this->_targets)) {
			throw new \Exception('$this->_targetsがセットされていません');
		}

		$this->update_cnt++;
		$this->total_cnt++;

		foreach ($this->_targets as $target) {
			if (! array_key_exists($target, $keyval)) {
				throw new \Exception("{$target}がセットされていません");
			}
			$this->_list[$target][$id] = $keyval[$target];
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
		$this->echo_flush('<div>'.nl2br(str_replace(" ", "&nbsp;", htmlspecialchars(print_r($statement, true)))).'</div>');
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
			throw new \Exception('$this->tableがセットされていません');
		}

		if ('' === strval($this->id_column)) {
			throw new \Exception('$this->id_columnがセットされていません');
		}

		if (empty($this->_targets)) {
			throw new \Exception('$this->_targetsがセットされていません');
		}

		$t1[] = "UPDATE {$this->table} SET";

		foreach ($this->_targets as $target) {
			$t2 = [];
			$t2[] = "  {$target} = CASE {$this->id_column}";

			if (! is_array($this->_list[$target]) OR empty($this->_list[$target])) {
				throw new \Exception("\$this->_list[{$target}]がセットされていません");
			}

			foreach ($this->_list[$target] as $keyval => $val) {
				if (is_null($val)) {
					$valstr = 'NULL';
				}
				else {
					$valstr = "'".$this->_escape_string($val)."'";
				}
				$t2[] = "    WHEN '{$keyval}' THEN {$valstr}";
			}
			$t2[] = "  END";
			$t3[] = implode("\n", $t2);
		}

		$t1[] = implode(",\n", $t3);

		$where = '';
		foreach ($this->_targets as $target) {
			$ids = array_keys($this->_list[$target]);
			$where = "'".implode("'={$this->id_column} OR '", $ids)."'={$this->id_column}";
			break;
		}

		$t1[] = "WHERE {$where}";

		$statement = implode("\n", $t1);

		return $statement;
	}
}

