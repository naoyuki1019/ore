<?php
namespace ore;
require  dirname(__FILE__) . '/../ORE_Require.php';


$array = array(
	'name' => '  名前三郎  ',
	'age' => 30,
	'zip' => '　460-0011　',
	'address' => 'abcdefgﾊﾝｶｸ　ｼﾞｭｳｼｮ',
	'child' => array(
		'name' => '  abc名前四郎c  ',
	),
);
echo (__FILE__.':('.__LINE__.')'."\n".'$array='.print_r($array, true))."\n";

$obj_arr = new ORE_Array($array);
$array = $obj_arr->array_map('mb_trim')
	->strtoupper()
	->mb_convert_kana('CKVas')
	->get_array();


echo (__FILE__.':('.__LINE__.')'."\n".'$array='.print_r($array, true))."\n";

// キー値アクセス
echo '$obj_arr->name='.$obj_arr->name."\n";
echo '$obj_arr->address='.$obj_arr->address."\n";


$obj_arr->str_replace('四郎', '一郎', true);
$obj_arr->str_replace('三郎', 'FALSE');
$array = $obj_arr->get_array();
echo (__FILE__.':('.__LINE__.')'."\n".'$array='.print_r($array, true))."\n";

