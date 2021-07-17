<?php

namespace ore;
require dirname(__FILE__).'/../ORE_Require.php';

$vo = new \stdClass();
$vo->user_password = '  dddaaad  ';
$v = new ORE_Object_validation();
$v->initialize();
$v->set_object($vo);
$v->set_rules("user_password", "パスワード", "mb_trim|min_length[6]|alpha_numeric|max_length[12]|is_natural");
$v->run();
$error_array = $v->error_array();
echo (__FILE__.':('.__LINE__.')'."\n".'$error_array='.print_r($error_array, true))."\n";
echo (__FILE__.':('.__LINE__.')'."\n".'$vo='.print_r($vo, true))."\n";

// TODO 配列変換テスト

// public function testValidationArray() {
// 	\SC_Debug::ENABLE();
//
// 	// $v = new MY_Form_validation();
// 	if ('POST' === $_SERVER['REQUEST_METHOD']) {
//
// 		$v = new ORE_Object_validation();
// 		$obj = new objectCheckClass($_POST);
// 		echo('<div>'.nl2br(str_replace(" ", "&nbsp;", htmlspecialchars(__FILE__.':('.__LINE__.')'."\n".'$obj => '.print_r($obj, true)))).'</div>');
// 		$v->set_object($obj);
// 		// $v = new ORE_DATA_validation();
// 		// $v->set_data($_POST);
//
// 		$v->set_rules('xxx_kb[]', 'xxx区分', 'mb_trim|mb_convert_kana[as]|required');
// 		$v->set_rules('ruletest', 'ルールテスト', 'mb_trim|mb_convert_kana[as]|required');
// 		$v->set_rules('henkan', '変換テスト', 'mb_trim|mb_convert_kana[as]|required');
//
// 		$v->run();
// 		echo('<div>'.nl2br(str_replace(" ", "&nbsp;", htmlspecialchars(__FILE__.':('.__LINE__.')'."\n".'$obj => '.print_r($obj, true)))).'</div>');
//
// 		$error_array = $v->error_array();
// 		echo('<div>'.nl2br(str_replace(" ", "&nbsp;", htmlspecialchars(__FILE__.':('.__LINE__.')'."\n".'$error_array => '.print_r($error_array, true)))).'</div>');
// 	}
// 	if ('POST' === $_SERVER['REQUEST_METHOD']) {
// 		echo('<div>'.nl2br(str_replace(" ", "&nbsp;", htmlspecialchars(__FILE__.':('.__LINE__.')'."\n".'$_POST => '.print_r($_POST, true)))).'</div>');
// 	}
// }
// class objectCheckClass {
// 	public function __construct($array) {
// 		foreach ($array as $k => $v) {
// 			$this->{$k} = $v;
// 		}
// 	}
// }

// 	<form method="post">
// 		<input type="text" name="xxx_kb[abc]" value=""/><br>
// 		<input type="text" name="xxx_kb[def]" value="1"/><br>
// 		<input type="text" name="xxx_kb[]" value="２３４５"/><br>
// 		<input type="text" name="ruletest" value=""/><br>
// 		<input type="text" name="henkan" value="２３４５"/><br>
// 		<button type="submit">サブミット</button>
// 	</form>
