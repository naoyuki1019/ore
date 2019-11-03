<?php
namespace ore;
require  dirname(__FILE__) . '/../ORE_Require.php';

$array = array(
	'hankaku' => 'ﾊﾝｶｸｶﾀｶﾅABC123',
	'lowercase' => 'abcdefg',
	'recursive' => array(
		'trim' => '  trim  ',
		'hoge1' => 'abcﾊﾝｶｸﾍﾝｶﾝ',
		'hoge2' => 'edfﾊﾝｶｸﾍﾝｶﾝ',
	),
);
echo 'before ='.print_r($array, true)."\n";

$obj_arr = new ORE_Array($array);
$array = $obj_arr
	->array_map('trim')
	->strtoupper()
	->mb_convert_kana('CKVas')
	->get_array();

echo 'after  ='.print_r($array, true)."\n";


// キー値アクセス
echo '$obj_arr->hankaku='.$obj_arr->hankaku."\n";
echo '$obj_arr->lowercase='.$obj_arr->lowercase."\n";

// str_replace
$obj_arr->str_replace('ハンカクヘンカン', '全角になりました。', true);
$array = $obj_arr->get_array();
echo 'str_replace ='.print_r($array, true)."\n";

