<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * Class ORE_Volume
 * @package ore
 */
class ORE_Volume extends ORE_Params {

	protected $_result = null;

	public $find_fileds = "*";
	public $entries = array();
	public $entry = null;

	protected $_page = 1;
	protected $_limit = 20;
	protected $_total = 0;
	protected $_sort_ud = "asc";
	protected $_sort_key = "created";
	protected $_sort_key_allows = array("created", "modified");

	protected $_errors = array();
	protected $_messages = array();

	/**
	 * ORE_Volume constructor.
	 * @param array $params
	 */
	public function __construct($params = array()) {
		parent::__construct($params);
	}


	/**
	 * @param array $params
	 */
	public function set($params = array()) {

		if (is_array($params) OR is_object($params)) {

			foreach ($params as $key => $val) {

				if ('_result' === $key OR '_total' === $key OR '_sort_key_allows' === $key) {
					continue;
				}

				if ('_page' === $key) {
					$this->set_page($val);
					continue;
				}

				if ('_limit' === $key) {
					$this->set_limit($val);
					continue;
				}

				if ('_sort_ud' === $key) {
					$this->set_sort_ud($val);
					continue;
				}

				if ('_sort_key' === $key) {
					$this->set_sort_key($val);
					continue;
				}

				$this->$key = $val;
			}
		}
	}


	/**
	 * @return string
	 */
	public function find_fileds() {

		if (TRUE === is_array($this->find_fileds)) {
			if (! empty($this->find_fileds)) {
				return implode(", ", $this->find_fileds);
			}
			return "*";
		}

		if ("" != $this->find_fileds) {
			return $this->find_fileds;
		}

		return "*";
	}


	/**
	 *
	 */
	public function set_result($result) {
		$this->_result = $result;
	}


	/**
	 *
	 */
	public function result() {
		return $this->_result;
	}


	/**
	 *
	 * @param integer $page 明細ページング処理のページ番号
	 */
	public function set_page($page) {
		if (preg_match('/^[1-9]\d*$/', $page)) $this->_page = $page;
	}


	/**
	 *
	 */
	public function page() {
		return $this->_page;
	}


	/**
	 *
	 * @param integer $limit 明細ページング処理の明細数
	 */
	public function set_limit($limit) {
		if (preg_match('/^[1-9]\d*$/', $limit)) $this->_limit = $limit;
	}


	/**
	 *
	 */
	public function limit() {
		return $this->_limit;
	}


	/**
	 * 入力値規制
	 */
	public function set_total($total) {
		$this->_total = (preg_match('/^[1-9]\d*$/', $total)) ? $total : 0 ;
	}


	/**
	 *
	 */
	public function total() {
		return $this->_total;
	}


	/**
	 * 入力値規制
	 */
	public function set_sort_ud($sort_ud) {

		$sort_ud = strtolower($sort_ud);
		if ($sort_ud == "desc" OR $sort_ud == "asc" ) {
			$this->_sort_ud = $sort_ud;
		}
	}


	/**
	 *
	 */
	public function sort_ud() {
		return $this->_sort_ud;
	}


	/**
	 * 入力値規制
	 * @param $sort_key
	 */
	public function set_sort_key($sort_key) {

		if ('ALL' === $this->_sort_key_allows) {
			$this->_sort_key = $sort_key;
			return;
		}

		if (TRUE === in_array($sort_key, $this->_sort_key_allows)) {
			$this->_sort_key = $sort_key;
			return;
		}

		throw new \Exception("sort key value error: '{$sort_key}' not allowed");
	}


	/**
	 * @return string
	 */
	public function sort_key() {
		return $this->_sort_key;
	}


	/**
	 * @return float|int
	 */
	public function offset() {

		// ページがリンクに存在しないページ番号の時は最終ページとする
		$offset = ($this->_page -1) * $this->_limit;
		if ($this->_total <= $offset) {
			$this->set_page(ceil($this->_total / $this->_limit));
		}

		return ($this->_page -1) * $this->_limit;
	}


	/**
	 * @return float
	 */
	public function lastpage() {
		return (ceil($this->_total / $this->_limit));
	}

	/**
	 * @param $key
	 * @param $msg
	 */
	public function add_error($key, $msg) {
		if (! array_key_exists($key, $this->_errors)) {
			$this->_errors[$key] = array();
		}
		$this->_errors[$key][] = $msg;
	}

	/**
	 * @return int
	 */
	public function error_count() {
		return count($this->_errors);
	}

	/**
	 * @return bool
	 */
	public function has_error() {
		return (bool)(0 < $this->error_count());
	}

	/**
	 * @param $key
	 * @param $msg
	 */
	public function add_message($key, $msg) {
		if (! array_key_exists($key, $this->_messages)) {
			$this->_messages[$key] = array();
		}
		$this->_messages[$key][] = $msg;
	}
}
