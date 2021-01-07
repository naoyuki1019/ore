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
class ORE_DBInsert {

	public $table = null;
	public $cols_header = [];
	public $values = [];
	public $values_cnt = 0;
	public $insert_cnt = 0;
	public $threshold = 3000;
	public $dry_run = 0;
	public $echo = 0;
	public $debug = 0;

	/**
	 * @param $statement
	 */
	protected function _exec($statement) {
		if ($this->debug) {
			$this->echo_flush('<h3>'.__FILE__.'('.__LINE__.')'.' '.__METHOD__.'</h3>');
			$this->echo_flush(\SqlFormatter::format($statement, true));
		}
		if (0 !== $this->dry_run) {
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
		if (0 !== $this->dry_run) {
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
		if (0 !== $this->dry_run) {
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
		if (0 !== $this->dry_run) {
			return;
		}
	}

	/**
	 * 記号の前にバックスラッシュを付ける
	 *
	 * @param $str
	 * @param $symbol
	 * @return string|string[]|null
	 */
	protected function _escape_symbol($str, $symbol) {
		$str = preg_replace('/([^\\\])'.$symbol.'/', '${1}\\'.$symbol, $str);
		$str = preg_replace('/([^\\\])'.$symbol.'/', '${1}\\'.$symbol, $str);
		$str = preg_replace('/^'.$symbol.'/', '\\'.$symbol, $str);
		return $str;
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
	 * @return array
	 */
	public function dataFormat() {
		$data = [];
		foreach($this->cols_header as $col_nm) {
			$data[$col_nm] = '';
		}
		return $data;
	}

	/**
	 * @param $cols
	 */
	public function insertAdd($cols) {
		if (is_object($cols)) {
			$cols = get_object_vars($cols);
		}
		$this->insert_cnt++;
		$this->values_cnt++;
		$arr = [];
		foreach ($this->cols_header as $col_nm) {
			if (TRUE !== array_key_exists($col_nm, $cols)) {
				$msg = "Error: col_nm[{$col_nm}]が設定されていない";
				$this->echo_flush($msg.'<br>');
				throw new \Exception($msg);
			}
			$val = $cols[$col_nm];
			if (! is_null($val)) {
				if (! is_null($this->tsv_option)) {
					$val = $this->_escape_symbol($val, '"');
				}
				else {
					$val = $this->_escape_string($val);
				}
			}
			$arr[] = $val;
			unset($cols[$col_nm]);
		}

		if (0 < count($cols)) {
			$msg = 'Error: 余った -> '.implode(', ', array_keys($cols));
			$this->echo_flush($msg.'<br>');
			throw new \Exception($msg);
		}

		if (! is_null($this->tsv_option)) {
			$row = '"'.implode('"'."\t".'"', $arr).'"';
			@fputs($this->tsv_option->handle, $row."\n");
		}
		else {
			foreach ($arr as $i => $val) {
				if (is_null($val)) {
					$arr[$i] = 'NULL';
				}
				else {
					$arr[$i] = "'".$val."'";
				}
			}
			$str = '('.implode(',', $arr).')';
			$this->values[] = $str;
			if ($this->threshold <= $this->values_cnt) {
				$this->insertAll();
			}
		}
	}

	/**
	 *
	 */
	public function insertAll() {

		if (! is_null($this->tsv_option)) {
			return;
		}

		if ('' === strval($this->table)) {
			$msg = 'Error: tableが入ってない';
			$this->echo_flush($msg.'<br>');
			throw new \Exception($msg);
		}

		if (! is_array($this->cols_header) OR 0 === count($this->cols_header)) {
			$msg = 'Error: cols_headerが入ってない';
			$this->echo_flush($msg.'<br>');
			throw new \Exception($msg);
		}

		if (0 < $this->values_cnt) {
			$table = $this->_escape_string($this->table);
			$statement = "INSERT INTO {$table} (".implode(', ', $this->cols_header).') VALUES ';
			$statement .= implode(', ', $this->values).';';
			$this->_exec($statement);
			$this->values = [];
			$this->values_cnt = 0;
			$this->echo_flush("処理済み:{$this->insert_cnt}件…<br>");
		}
	}

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

	public $tsv_handle = null;
	public $tsv_option = null;

	/**
	 * @param ORE_DBInsert_TSV_Option $option
	 * @throws \Exception
	 */
	public function tsvOutputSetOption(ORE_DBInsert_TSV_Option $option) {
		$this->tsv_option = $option;
		$this->tsv_option->handle = @fopen($option->base_dir.$option->dir_uri.$option->file_nm, 'w');
		if (! $this->tsv_option->handle) {
			throw new \Exception('open file handle error');
		}

		if (! is_array($this->cols_header) OR 0 === count($this->cols_header)) {
			$msg = 'Error: cols_headerが入ってない';
			$this->echo_flush($msg.'<br>');
			throw new \Exception($msg);
		}

		$arr = $this->_escape_symbol($this->cols_header, '"');
		$row = '"'.implode('"'."\t".'"', $arr).'"';
		@fputs($this->tsv_option->handle, $row."\n");
	}

	/**
	 *
	 */
	public function tsvOutput() {
		if (! is_null($this->tsv_option)) {
			$uri = $this->tsv_option->dir_uri.$this->tsv_option->file_nm;
			@fclose($this->tsv_option->handle);
			$this->echo_flush("出力件数:{$this->insert_cnt}件<br>");
			if (0 < $this->insert_cnt) {
				$this->echo_flush("<a class='download' href='{$uri}' download='{$this->tsv_option->file_nm}'>TSVリンク</a><br>");
			}
		}
	}

	public function finish() {
		if (! is_null($this->tsv_option)) {
			$this->tsvOutput();
		}
		else {
			$this->insertAll();
		}
	}
}

class ORE_DBInsert_TSV_Option {
	public $handle = null;
	public $file_nm = 'db_insert_tsv.tsv';
	public $dir_uri = "/files/users/214/tmp/";
	public $base_dir = 'WEB_DIR';
}
