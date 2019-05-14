<?php

namespace ORE;

/**
 * Class exec
 *
 * @package ORE
 */
class ORE_FTPUpload {

	/**
	 * FTP接続情報
	 */
	public $host = null;
	public $port = 21;
	public $uid = null;
	public $pass = null;

	/**
	 * FTPスクリプト関連
	 */
	public $sh_file_nm = 'ftp_upload.sh';
	protected $_sh_dir = null;

	/**
	 * @param $dir
	 */
	public function set_sh_dir($dir) {
		if ('/' !== substr($dir, -1)) $dir = $dir.'/';
		$this->_sh_dir = $dir;
	}

	protected $_script = '#!/bin/bash
if [ $# -ne 4 ]; then
  echo \'コマンド引数不正\'
  exit 9
fi

ftp -nv << EOF
open ${1} ${2}
user ${3} ${4}
binary
{This string will be replaced}
close
bye
EOF

exit 0
';

	/**
	 * FTPアップロードファイル
	 *
	 * @var array
	 */
	protected $_files = array();

	/**
	 * @param $local_path
	 * @param $remote_path
	 */
	public function add_file($local_path, $remote_path) {
		$this->_files[] = array(
			'local_path' => $local_path,
			'remote_path' => $remote_path,
		);
	}

	/**
	 * メッセージ出力
	 */
	public $flush = false;
	public $echo = false;

	/**
	 * メッセージ設定
	 *
	 * @param $msg
	 */
	protected $_messages = array();

	protected function _set_message($msg) {
		if (TRUE === $this->echo) {
			echo($msg);
		}
		if (TRUE === $this->flush) {
			if (0 < ob_get_level()) {
				ob_flush();
				flush();
			}
		}
		$this->_messages[] = strip_tags($msg);
	}

	/**
	 * メッセージ取得
	 *
	 * @return bool
	 */
	public function message() {
		return $this->_messages;
	}

	/**
	 * 実行結果
	 *
	 * @var bool
	 */
	protected $_result = false;

	/**
	 * @return bool
	 */
	public function result() {
		return $this->_result;
	}

	/**
	 * @return bool
	 */
	public function execute() {
		$check = true;
		if (0 === count($this->_files)) {
			$this->_set_message("<div>Error: FTPアップロードするファイルが登録されていない</div>");
			$check = false;
		}

		if (! is_writable($this->_sh_dir)) {
			$this->_set_message("<div>Error: ディレクトリ[{$this->_sh_dir}]に書き込み権限がありません</div>");
			$check = false;
		}

		if ('' === strval($this->sh_file_nm)) {
			$this->_set_message("<div>Error: sh_file_nmが設定されていない</div>");
			$check = false;
		}

		if ('' === strval($this->host)) {
			$this->_set_message("<div>Error: hostが設定されていない</div>");
			$check = false;
		}

		if ('' === strval($this->uid)) {
			$this->_set_message("<div>Error: uidが設定されていない</div>");
			$check = false;
		}

		if ('' === strval($this->pass)) {
			$this->_set_message("<div>Error: passが設定されていない</div>");
			$check = false;
		}

		if (TRUE !== $check) {
			return false;
		}

		$sh_path = $this->_sh_dir.$this->sh_file_nm;
        $script = $this->_make_script($sh_path, $this->_script, $this->_files);
		$sh_path = escapeshellarg($sh_path);
		$host = escapeshellarg($this->host);
		$port = (ctype_digit((string)$this->port)) ? $this->port : 21;
		$uid = escapeshellarg($this->uid);
		$pass = escapeshellarg($this->pass);
		$command = "/bin/bash {$sh_path} {$host} {$port} {$uid} {$pass}";
		$command_log = "/bin/bash {$sh_path} {$host} {$port} #uid# #pass#";
		$command_log = "/bin/bash {$sh_path} {$host} {$port} {$uid} {$pass}";
		$this->_set_message("<div>コマンド実行: {$command_log}</div>");

		$output = array();
		$ret = null;
		exec($command, $output, $ret);
		$ret = strval($ret);
		if ('0' !== $ret) {
			if ('9' === $ret) {
				$this->_set_message("<div>Error: コマンド引数不正</div>");
			}
			else {
				if (! empty($output)) {
					$this->_set_message("<div>Error: ".implode('', $output)."</div>");
				}
				else {
					$this->_set_message("<div>Error: 不明なエラー</div>");
				}
			}
			return false;
		}

		$this->_set_message('<div>FTP実行メッセージ: '.nl2br(str_replace(" ", "&nbsp;", htmlspecialchars(print_r($output, true)))).'</div>');

		$cnt226msg = 0;
		foreach ($output as $ftp_message) {
			if ('226 Transfer complete' === $ftp_message) {
				$cnt226msg++;
			}
		}

		if (0 === $cnt226msg OR $cnt226msg != count($this->files)) {
			$this->_set_message("<div>Error: ファイル転送失敗</div>");
			return false;
		}

		$this->_set_message("<h1>成功 アップロードファイル数: {$cnt226msg}</h1>");

		// 削除せず残す事もある
		// $this->remove_sh();

		$this->_result = true;
		return $this->_result;
	}

	/**
	 * @param $sh_path
	 * @param $script
	 * @param $files
	 */
	protected function _make_script($sh_path, $script, $files) {
		$ftp_commands = $this->_make_ftp_commands($files);
		$script = str_replace('{This string will be replaced}', $ftp_commands, $script);
		file_put_contents($sh_path, $script);
	}

	/**
	 * @param $files
	 * @return string
	 */
	protected function _make_ftp_commands($files) {
		$puts = array();
        $remote_dirs = array();
		foreach($files as $arr) {

			$cnt = mb_substr_count($arr['remote_path'], '/');
			$dirs = array();
			$dir = $arr['remote_path'];
			for ($i = 1; $i < $cnt; $i++) {
				$dir = dirname($dir);
				$dirs[] = $dir;
			}
			foreach ($dirs as $dir) {
				$remote_dirs[$dir] = null;
			}

			$puts[] = 'put '.$arr['local_path'].' '.$arr['remote_path'];
		}
		ksort($remote_dirs);
		$keys = array_keys($remote_dirs);
        $ftp_commands = "mkdir " . implode("\nmkdir ", $keys)."\n". implode("\n", $puts);
		return $ftp_commands;
	}

	/**
	 *
	 */
	public function remove_sh() {
		unlink($this->_sh_dir.$this->sh_file_nm);
	}
}

