<?php

/**
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * Class ORE_Volume
 *
 * @package ore
 */
class ORE_Volume extends ORE_Params {

	const msg_default_value = '----___----';

	/**
	 * @var string|array
	 */
	public $find_fileds = '*';

	/**
	 * @var string
	 */
	public $count_filed = '*';

	/**
	 * This property is deprecated. Use $rs instead.
	 *
	 * @var array
	 */
	public $entries = [];

	/**
	 * This property is deprecated. Use $r instead.
	 *
	 * @var null
	 */
	public $entry = null;

	/**
	 * record set
	 *
	 * @var array
	 */
	public $rs = [];

	/**
	 * @var null
	 */
	public $r = null;

	/**
	 * @var int
	 */
	protected $_page = 1;

	/**
	 * @var int
	 */
	protected $_limit = null;

	/**
	 * @var int
	 */
	protected $_default_limit = 20;

	/**
	 * @var int
	 */
	protected $_max_limit = 10000;

	/**
	 * @var int
	 */
	protected $_total = 0;

	/**
	 * 総件数不要フラグ
	 *
	 * @var int
	 */
	public $flg_no_total = 0;

	/**
	 * @var string
	 */
	protected $_sort_ud = 'asc';

	/**
	 * @var int
	 */
	protected $_sort_key = 1;

	/**
	 * @var \string[][]
	 */
	protected $_sort_key_allows = [
		1 => [
			'label' => 'registration date',
			'query' => 'created {sort_ud}',
		],
		2 => [
			'label' => 'update date',
			'query' => 'modified {sort_ud}',
		],
	];

	/**
	 * @var null
	 */
	protected $_result = null;

	/**
	 * @var array
	 */
	protected $_messages = [];

	/**
	 * @var array
	 */
	protected $_errors = [];

	/**
	 * ORE_Volume constructor.
	 *
	 * @param array $params
	 */
	public function __construct($params = []) {
		$this->_limit = $this->_default_limit;
		parent::__construct($params);
	}

	/**
	 * @param mixed $params
	 * @param mixed $value
	 * @return $this
	 */
	public function set($params = [], $value = null) {

		$type = strtolower(gettype($params));
		if ('array' === $type || 'object' === $type) {
			foreach ($params as $key => $val) {

				if ('_result' === $key || '_total' === $key || '_sort_key_allows' === $key) {
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

				$this->{$key} = $val;
			}
		}
		else {
			$this->{$params} = $value;
		}

		return $this;
	}

	/**
	 * @param $find_fileds
	 * @return $this
	 */
	public function set_find_fileds($find_fileds) {
		$this->find_fileds = $find_fileds;
		return $this;
	}

	/**
	 * @return string
	 */
	public function find_fileds() {
		return $this->find_fileds;
	}

	/**
	 * @return string
	 */
	public function count_filed() {
		return $this->count_filed;
	}

	/**
	 * @param mixed $result
	 * @return $this
	 */
	public function set_result($result) {
		$this->_result = $result;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function result() {
		return $this->_result;
	}

	/**
	 * @param int $page 明細ページング処理のページ番号
	 * @return $this
	 */
	public function set_page($page) {
		$page = mb_convert_kana(mb_trim((string)$page), 'as');
		if (preg_match('/^[1-9]\d*$/', $page)) {
			$this->_page = (int)$page;
		}
		else if (0 > $page) {
			$this->_page = 1;
		}
		return $this;
	}

	/**
	 * @return int
	 */
	public function page() {
		return $this->_page;
	}

	/**
	 * @param int $limit 明細ページング処理の明細数
	 * @return $this
	 */
	public function set_limit($limit) {
		$limit = mb_convert_kana(mb_trim((string)$limit), 'as');
		if (preg_match('/^[1-9]\d*$/', $limit)) {
			$this->_limit = min([(int)$limit, $this->_max_limit]);
		}
		return $this;
	}

	/**
	 * @return int
	 */
	public function limit() {
		return $this->_limit;
	}

	/**
	 * @param int $limit 明細ページング処理の明細数
	 * @return $this
	 */
	public function set_max_limit($max_limit) {
		$max_limit = mb_convert_kana(mb_trim((string)$max_limit), 'as');
		if (preg_match('/^[1-9]\d*$/', $max_limit)) {
			$this->_max_limit = (int)$max_limit;
			$this->_limit = min([(int)$this->_limit, $this->_max_limit]);
		}
		return $this;
	}

	/**
	 * @param int $total 総件数
	 * @return $this
	 */
	public function set_total($total) {
		$total = mb_convert_kana(mb_trim((string)$total), 'as');
		if (preg_match('/^[1-9]\d*$/', $total)) {
			$this->_total = (int)$total;
		}
		else {
			$this->_total = 0;
		}
		return $this;
	}

	/**
	 * @return int
	 */
	public function total() {
		return $this->_total;
	}

	/**
	 * @return int
	 */
	public function offset() {
		$offset = ($this->_page - 1) * $this->_limit;
		if ($this->_total <= $offset) {
			$this->set_page(ceil($this->_total / $this->_limit));
		}
		return ($this->_page - 1) * $this->_limit;
	}

	/**
	 * @return int
	 */
	public function last_page() {
		return ceil($this->_total / $this->_limit);
	}

	/**
	 * @param mixed $allows
	 * @return $this
	 */
	public function set_sort_key_allows($allows) {
		if ('ALL' === $allows) {
			$this->_sort_key_allows = 'ALL';
		}
		else if (is_array($allows)) {
			$this->_sort_key_allows = $allows;
		}
		return $this;
	}

	/**
	 * @return \string[][]
	 */
	public function sort_key_allows() {
		return $this->_sort_key_allows;
	}

	/**
	 * @param string $sort_key
	 * @return bool
	 */
	public function is_allowed_key($sort_key) {

		$type = gettype($sort_key);
		if ('object' === $type || 'array' === $type || '' === strval($sort_key)) {
			return false;
		}

		if ('string' === gettype($this->_sort_key_allows)) {
			if ('ALL' === strtoupper($this->_sort_key_allows)) {
				return true;
			}
			else {
				if ($sort_key === $this->_sort_key_allows) {
					return true;
				}
			}
			return false;
		}

		if (is_array($this->_sort_key_allows)) {
			if (true === array_key_exists($sort_key, $this->_sort_key_allows)) {
				return true;
			}
			else if (true === in_array($sort_key, $this->_sort_key_allows, true)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $sort_key
	 * @param string $sort_ud 'asc' OR 'desc'
	 * @return mixed
	 */
	public function sort_query($sort_key, $sort_ud = 'asc') {
		if (true !== ctype_digit((string)$sort_key)) {
			return null;
		}
		if (! is_array($this->_sort_key_allows)) {
			return null;
		}
		if (! array_key_exists($sort_key, $this->_sort_key_allows)) {
			return null;
		}
		if (! is_array($this->_sort_key_allows[$sort_key])) {
			return $this->_sort_key_allows[$sort_key];
		}
		if (! array_key_exists('query', $this->_sort_key_allows[$sort_key])) {
			return null;
		}
		$query = $this->_sort_key_allows[$sort_key]['query'];
		$query = str_replace('{sort_ud}', $this->_get_sort_ud($sort_ud), $query);
		return $query;
	}

	/**
	 * @param string $sort_key
	 * @param string $sort_ud 'asc' OR 'desc'
	 * @return $this
	 */
	public function set_sort_key($sort_key, $sort_ud = null) {
		if (! isset($sort_key) || '' === $sort_key) return;
		$bk = $this->_sort_key;
		$this->_sort_key = [];
		$this->add_sort_key($sort_key, $sort_ud);
		if ((true !== is_object($this->_sort_key) && true !== is_array($this->_sort_key) && '' !== strval($this->_sort_key))
			OR (true === is_array($this->_sort_key) && 0 < count($this->_sort_key))) {
			return $this;
		}
		$this->_sort_key = $bk;
		return $this;
	}

	/**
	 * @return string
	 */
	public function sort_key() {
		return $this->_sort_key;
	}

	/**
	 * @param string $sort_ud 'asc' OR 'desc'
	 * @return $this
	 */
	public function set_sort_ud($sort_ud) {
		if (is_null($sort_ud) || '' === $sort_ud) return;
		$this->_sort_ud = $this->_get_sort_ud($sort_ud);
		return $this;
	}

	/**
	 * @return string
	 */
	public function sort_ud() {
		return $this->_sort_ud;
	}

	/**
	 * @param mixed $sort_key
	 * @param string $sort_ud 'asc' OR 'desc'
	 * @return $this
	 */
	public function add_sort_key($sort_key, $sort_ud = null) {
		if (! isset($sort_key) || '' === $sort_key) return;
		if (is_array($sort_key)) {
			foreach ($sort_key as $tmp => $sort_ud) {
				$this->_add_sort_key($tmp, $sort_ud);
			}
			return $this;
		}
		$this->_add_sort_key($sort_key, $sort_ud);
		return $this;
	}

	/**
	 * @param string $sort_key
	 * @param string $sort_ud 'asc' OR 'desc'
	 * @return $this
	 */
	private function _add_sort_key($sort_key, $sort_ud) {
		if (! isset($sort_key) || '' === $sort_key) {
			return $this;
		}

		if (! $this->is_allowed_key($sort_key)) {
			return $this;
		}

		if (is_null($sort_ud)) {
			$this->_sort_key = $sort_key;
			return $this;
		}

		if (! is_array($this->_sort_key)) {
			$this->_sort_key = [];
		}

		// 削除して一番最後に入れる
		unset($this->_sort_key[$sort_key]);
		$this->_sort_key[$sort_key] = $this->_get_sort_ud($sort_ud);
		return $this;
	}

	private function _get_sort_ud($sort_ud) {
		if (! isset($sort_ud) || '' === $sort_ud) {
			return 'asc';
		}

		if ('{$sort_ud}' === $sort_ud) {
			return $this->_sort_ud;
		}

		$sort_ud = strtolower($sort_ud);
		if ('desc' !== $sort_ud && 'asc' !== $sort_ud) {
			$sort_ud = 'asc';
		}
		return $sort_ud;
	}

	/**
	 * @param mixed $key
	 * @param mixed $msg
	 */
	public function add_error($key, $msg = self::msg_default_value) {
		if (is_array($key)) {
			foreach ($key as $key2 => $msg2) {
				$this->add_error($key2, $msg2);
			}
			return;
		}

		if (is_array($msg)) {
			if (! empty($msg)) {
				if (! array_key_exists($key, $this->_errors)) {
					$this->_errors[$key] = [];
				}
				foreach ($msg as $m) {
					if (! in_array($m, $this->_errors[$key])) {
						$this->_errors[$key][] = $m;
					}
				}
			}
			return;
		}

		if (self::msg_default_value === $msg) {
			$msg = $key;
			$key = 'error';
		}

		if (! array_key_exists($key, $this->_errors)) {
			$this->_errors[$key] = [];
		}

		if (! in_array($msg, $this->_errors[$key])) {
			$this->_errors[$key][] = $msg;
		}
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
	 * @params array $errors
	 * @return $this
	 */
	public function set_errors($errors) {
		$this->_errors = $errors;
		return $this;
	}

	/**
	 * @param mixed $keys
	 * @return mixed
	 */
	public function errors($keys = null) {
		if (is_null($keys)) {
			return $this->_errors;
		}

		$errors = [];

		$type = strtolower(gettype($keys));
		if ('array' === $type || 'object' === $type) {
			foreach ($keys as $key) {
				if (array_key_exists($key, $this->_errors)) {
					$errors[$key] = $this->_errors[$key];
				}
			}
			return $errors;
		}

		if (array_key_exists($keys, $this->_errors)) {
			$errors = $this->_errors[$keys];
		}
		return $errors;
	}

	/**
	 * @param mixed $key
	 * @param mixed $msg
	 */
	public function add_message($key, $msg = self::msg_default_value) {
		if (is_array($key)) {
			foreach ($key as $key2 => $msg2) {
				$this->add_message($key2, $msg2);
			}
			return;
		}

		if (is_array($msg)) {
			if (! empty($msg)) {
				if (! array_key_exists($key, $this->_messages)) {
					$this->_messages[$key] = [];
				}
				foreach ($msg as $m) {
					if (! in_array($m, $this->_messages[$key])) {
						$this->_messages[$key][] = $m;
					}
				}
			}
			return;
		}

		if (self::msg_default_value === $msg) {
			$msg = $key;
			$key = 'message';
		}

		if (! array_key_exists($key, $this->_messages)) {
			$this->_messages[$key] = [];
		}

		if (! in_array($msg, $this->_messages[$key])) {
			$this->_messages[$key][] = $msg;
		}
	}

	/**
	 * @return int
	 */
	public function message_count() {
		return count($this->_messages);
	}

	/**
	 * @return bool
	 */
	public function has_message() {
		return (bool)(0 < $this->message_count());
	}

	/**
	 * @params array $messages
	 * @return $this
	 */
	public function set_messages($messages) {
		$this->_messages = $messages;
		return $this;
	}

	/**
	 * @return array
	 */
	public function messages() {
		return $this->_messages;
	}

	/**
	 * @param string $from
	 * @param string $to
	 */
	public function error_copy($from, $to) {
		$errors = $this->errors();
		if (array_key_exists($from, $errors) && ! array_key_exists($to, $errors)) {
			$this->add_error($to, $errors[$from]);
		}
	}

	/**
	 * @param string $glue
	 * @param string $open
	 * @param string $close
	 * @return string
	 * @throws \Exception
	 */
	public function message_string($glue = "\n", $open = '', $close = '') {
		return $open.$this->__string('messages', $glue).$close;
	}

	/**
	 * @param string $glue
	 * @param string $open
	 * @param string $close
	 * @return string
	 * @throws \Exception
	 */
	public function error_string($glue = "\n", $open = '', $close = '') {
		return $open.$this->__string('errors', $glue).$close;
	}

	/**
	 * @param string $type
	 * @param string $glue
	 * @return string
	 * @throws \Exception
	 */
	public function __string($type, $glue) {

		$key = '_'.$type;

		if (! property_exists($this, $key)) {
			throw new \Exception('key not found');
		}

		$arr = [];
		foreach ($this->{$key} as $msg) {
			if (is_array($msg)) {
				foreach ($msg as $m) {
					$arr[] = $m;
				}
			}
			else {
				$arr[] = $msg;
			}
		}
		return implode($glue, $arr);
	}
}
