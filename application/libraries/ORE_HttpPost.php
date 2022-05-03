<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

/**
 * Class ORE_HttpPost
 *
 * @author naoyuki onishi
 */
class ORE_HttpPost {

	/**
	 *
	 * @var string
	 */
	protected $_url = "";

	/**
	 *
	 * @var string
	 */
	protected $_boundary = "";

	/**
	 *
	 * @var float
	 */
	protected $_default_socket_timeout = "";

	/**
	 *
	 * @var float
	 */
	protected $_socket_timeout = "";

	/**
	 *
	 * @var array
	 */
	protected $_header_list = [];

	/**
	 *
	 * @var array
	 */
	protected $_file_list = [];

	/**
	 *
	 * @var array
	 */
	protected $_text_list = [];

	/**
	 *
	 * @var array
	 */
	protected $_errors = [];

	/**
	 *
	 */
	public function __construct() {
		$this->initialize();
	}

	/**
	 */
	public function initialize() {

		$this->_url = "";
		$this->_boundary = "---------------------".substr(md5(rand(0, 32000)), 0, 15);
		$this->_default_socket_timeout = ini_get('default_socket_timeout');
		$this->_socket_timeout = $this->_default_socket_timeout;
		$this->_header_list = [];
		$this->_file_list = [];
		$this->_text_list = [];
		$this->_errors = [];
	}

	/**
	 * @param string $url
	 */
	public function set_url($url) {
		//TODO validation
		$this->_url = $url;
	}

	/**
	 *
	 * @param string $boundary
	 */
	public function set_boundary($boundary) {
		//TODO validation
		$this->_boundary = $boundary;
	}

	/**
	 *
	 * @param string $socket_timeout
	 */
	public function set_timeout($socket_timeout) {
		//TODO validation
		$this->_socket_timeout = $socket_timeout;
	}

	/**
	 *
	 * @param string $header
	 */
	public function add_header($header) {

		$this->_header_list[] = $header;
	}

	/**
	 *
	 * @param string $form_name
	 * @param string $text
	 */
	public function add_text($form_name, $text) {

		$text_dict = new HttpFormPostTextDictionary();

		$text_dict->form_name = $form_name;
		$text_dict->text = $text;

		$this->_text_list[] = $text_dict;
	}

	/**
	 *
	 * @param string $form_name
	 * @param array $keyval
	 */
	public function add_keyval_array($form_name, $keyval) {

		foreach ($keyval as $key => $text) {
			$this->add_text("{$form_name}[{$key}]", $text);
		}
	}

	/**
	 *
	 * @param string $form_name
	 * @param string $file_path
	 * @param string $file_name
	 * @param string $file_type
	 */
	public function add_file($form_name, $file_path, $file_name = "", $file_type = "") {

		if (! is_readable($file_path)) {
			throw new \Exception("NOT FOUND: {$file_path}");
		}

		if ("" == $file_name) {
			$file_name = basename($file_path);
		}

		if ("" == $file_type) {
			$file_type = "application/octet-stream";
		}

		$file_dict = new HttpFormPostFileDictionary();

		$file_dict->form_name = $form_name;
		$file_dict->file_path = $file_path;
		$file_dict->file_name = $file_name;
		$file_dict->file_type = $file_type;

		$this->_file_list[] = $file_dict;
	}

	/**
	 * @return int
	 */
	public function get_file_count() {
		return count($this->_file_list);
	}

	/**
	 *
	 * @return bool|string
	 */
	public function submit() {

		$content = $this->_make_content();
		if (! empty($this->_errors)) {
			return false;
		}

		//
		// make request
		//
		$header = $this->_header_list;
		$header[] = "Content-Type: multipart/form-data; boundary={$this->_boundary}";
		$header[] = "Content-Length: ".strlen($content);
		$header[] = "Cache-Control: no-cache";

		// TODO ここから下のオプションをプロパティ化する
		// TODO エラーが分かりやすい処理に書き換える

		$stream_context_options = [
			"http" => [
				"method" => "POST",
				"timeout" => 10,
				"header" => implode("\r\n", $header),
				"content" => $content,
				"ignore_errors" => true,
			],
			"ssl" => [
				"verify_peer" => false,
				"verify_peer_name" => false,
			],
		];

		$this->_ini_set($stream_context_options);

		$response = false;

		$stream_context = stream_context_create($stream_context_options);
		if ($stream_context) {
			$fp = fopen($this->_url, "rb", false, $stream_context);
			if ($fp) {
				$response = stream_get_contents($fp);
				if (false === $response) {
					$this->_errors[] = "Error: stream_get_contents";
				}
			}
			else {
				$this->_errors[] = "Error: fopen";
			}
		}
		else {
			$this->_errors[] = "Error: stream_context_create";
		}

		$this->_default_ini_set();
		return $response;
	}

	/**
	 *
	 * @param array $stream_context_options
	 */
	protected function _ini_set(&$stream_context_options) {

		// socket_timeout
		if (version_compare(phpversion(), "5.2.1", "<")) {
			ini_set('default_socket_timeout', $this->_socket_timeout);
		}
		else {
			$stream_context_options["http"]["timeout"] = $this->_socket_timeout;
		}

		// track_errors
		$this->_trac_errors = ini_get('track_errors');
		ini_set('track_errors', 1);

	}

	/**
	 *
	 */
	protected function _default_ini_set() {

		// socket_timeout
		if (version_compare(phpversion(), "5.2.1", "<")) {
			ini_set('default_socket_timeout', $this->_default_socket_timeout);
		}

		// track_errors
		ini_set('track_errors', $this->_trac_errors);

	}

	/**
	 *
	 * @return string
	 */
	protected function _make_content() {

		$content = [];

		foreach ($this->_text_list as $text_dict) {
			$this->_make_content_add_text($content, $text_dict);
		}

		foreach ($this->_file_list as $file_dict) {
			$this->_make_content_add_file($content, $file_dict);
		}

		$content[] = "--{$this->_boundary}--\r\n";

		$content = implode("", $content);

		return $content;
	}

	/**
	 *
	 * @param array $content
	 * @param HttpFormPostTextDictionary $text_dict
	 */
	protected function _make_content_add_text(&$content, HttpFormPostTextDictionary $text_dict) {

		$content[] = "--{$this->_boundary}\r\n";
		$content[] = "Content-Disposition: form-data; name=\"{$text_dict->form_name}\"\r\n\r\n{$text_dict->text}\r\n";
	}

	/**
	 *
	 * @param array $content
	 * @param HttpFormPostFileDictionary $file_dict
	 */
	protected function _make_content_add_file(&$content, HttpFormPostFileDictionary $file_dict) {

		if (! is_file($file_dict->file_path)) {
			$this->_errors[] = "Attachment not found: [{$file_dict->file_path}]";
			return;
		}

		$content[] = "--{$this->_boundary}\r\n";
		$content[] = "Content-Disposition: form-data; name=\"{$file_dict->form_name}\"; filename=\"{$file_dict->file_name}\"\r\n";
		$content[] = "Content-Type: {$file_dict->file_type}\r\n";
		$content[] = "Content-Transfer-Encoding: binary\r\n\r\n";
		$content[] = file_get_contents($file_dict->file_path)."\r\n";
	}

	/**
	 * @return array
	 */
	public function errors() {

		return $this->_errors;
	}
}

/**
 * @author naoyuki onishi
 */
class HttpFormPostTextDictionary {
	public $form_name = "";
	public $text = "";
}

/**
 * @author naoyuki onishi
 */
class HttpFormPostFileDictionary {
	public $form_name = "";
	public $file_path = "";
	public $file_name = "";
	public $file_type = "";
}
