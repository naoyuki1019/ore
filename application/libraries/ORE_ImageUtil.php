<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */
namespace ore;

/**
 * Class ORE_ImageUtil
 *
 * @author naoyuki onishi
 */
class ORE_ImageUtil {

	/**
	 * @param resize_volume $o
	 * @return bool
	 * @throws \Exception
	 */
	public static function jpegoptim(resize_volume $o) {
		if (is_file($o->to_path)) {
			unlink($o->to_path);
		}
		$dest = dirname($o->to_path);
		$dest = escapeshellarg($dest);
		$org_path = escapeshellarg($o->org_path);
		$command = "jpegoptim --strip-all --max={$o->quality_optim} --dest={$dest} -o {$org_path}";
		$output = array();
		$ret = null;
		@exec($command, $output, $ret);
		if ('0' !== strval($ret)) {
			throw new \Exception('jpegoptim処理でエラーが発生しました。');
		}

		$message = implode("\n", $output);

		// 最適化済みのファイルはdestに作成されないため
		if (! is_readable($o->to_path)) {
			copy($o->org_path, $o->to_path);
//			throw new \Exception("jpegoptim処理 作成失敗 {$o->to_path} {$message}");
		}

		chmod($o->to_path, 0777);

		return true;
	}

	/**
	 * @param resize_volume $o
	 * @throws \Exception
	 */
	public static function resize_image(resize_volume $o) {

		if (! is_file($o->org_path)) {
			throw new \Exception("ファイル[{$o->org_path}]が存在しません");
		}

		if (! is_readable($o->org_path)) {
			throw new \Exception("ファイル[{$o->org_path}]が読み込めません");
		}

		if (IMAGETYPE_JPEG != $o->mime_type OR IMAGETYPE_PNG != $o->mime_type OR IMAGETYPE_GIF != $o->mime_type) {
			list($o->org_width, $o->org_height, $o->mime_type) = getimagesize($o->org_path);
		}
		if (0 == $o->to_width OR 0 == $o->to_height) {
			throw new \Exception('ターゲットサイズの設定をしてください');
		}

		list($o->to_width, $o->to_height) = self::calc_image_size($o->org_path, $o->to_width, $o->to_height);

		if (IMAGETYPE_JPEG == $o->mime_type) {
			$thumbnail_image = \imagecreatefromjpeg($o->org_path);
		}
		else if (IMAGETYPE_PNG == $o->mime_type) {
			$thumbnail_image = \imagecreatefrompng($o->org_path);
		}
		else if (IMAGETYPE_GIF == $o->mime_type) {
			$thumbnail_image = \imagecreatefromgif($o->org_path);
		}
		else {
			throw new \Exception('対応していないファイル形式です。: '.$o->mime_type);
		}

		if ($o->org_height <= $o->to_height AND $o->org_width <= $o->to_width) {
			copy($o->org_path, $o->to_path);
			return;
		}

		// 新しく描画するキャンバスを作成
		$canvas = imagecreatetruecolor($o->to_width, $o->to_height);
		imagecopyresampled($canvas, $thumbnail_image, 0,0,0,0, $o->to_width, $o->to_height, $o->org_width, $o->org_height);

		if (IMAGETYPE_JPEG == $o->mime_type) {
			imagejpeg($canvas, $o->to_path, $o->quality_thumb);
		}
		else if (IMAGETYPE_PNG == $o->mime_type) {
			imagepng($canvas, $o->to_path, 9);
		}
		else if (IMAGETYPE_GIF == $o->mime_type) {
			imagegif($canvas, $o->to_path);
		}

		// chmod($o->to_path, 0777);
		// 読み出したファイルは消去
		imagedestroy($thumbnail_image);
		imagedestroy($canvas);
	}

	/**
	 * @param $image_path
	 * @param $max_width
	 * @param $max_height
	 * @return array
	 */
	public static function calc_image_size ($image_path, $max_width, $max_height) {

		if ( ! file_exists($image_path)) {
			return array(0, 0);
		}

		list($width, $height, $type, $attr) = getimagesize($image_path);

		if ($width == 0 OR $height == 0) {
			return array(0, 0);
		}

		$rw = $max_width / $width;
		$rh = $max_height / $height;

		if ($rw > $rh) {
			$ratio = $rh;
		}
		else {
			$ratio = $rw;
		}

		$new_width = $width * $ratio;
		$new_height = $height * $ratio;

		return array($new_width, $new_height);
	}
}

/**
 * Class resize_volume
 * @package ore
 */
class resize_volume {

	public $mime_type = '';
	public $quality_thumb = '94';
	public $quality_optim = '84';
	public $org_path = '';
	public $org_height = '';
	public $org_width = '';
	public $to_path = '';
	public $to_height = 0;
	public $to_width = 0;

	/**
	 * @param $quality
	 */
	public function setQuality($quality) {
		$quality = intval($quality);
		if (TRUE === $this->_between($quality)) {
			$this->quality_thumb = $quality;
		}
	}

	/**
	 * @param $quality
	 */
	public function setQualityTim($quality) {
		$quality = intval($quality);
		if (TRUE === $this->_between($quality)) {
			$this->quality_optim = $quality;
		}
	}

	/**
	 * @param $str
	 * @return bool
	 */
	private function _is_int($str) {
		return (bool) (preg_match('/^[1-9]\d*$/', $str));
//		return (bool) (preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $str));
	}

	/**
	 * @param $str
	 * @param int $low
	 * @param int $high
	 * @return bool
	 */
	private function _between($str, $low=0, $high=100) {
		return (bool) ($low <= $str AND $str <= $high);
	}
}

