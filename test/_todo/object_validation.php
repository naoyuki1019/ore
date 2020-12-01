<?php
namespace ore;
require  dirname(__FILE__) . '/../ORE_Require.php';


$vo = new \stdClass();
$vo->user_password = '  dddaaad  ';
$v = new ORE_Object_validation();
$v->initialize();
$v->set_object($vo);
$v->set_rules("user_password", "パスワード", "mb_trim|min_length[6]|alpha_numeric|max_length[12]|is_natural");
$v->run();
$error_array = $v->error_array();
echo(__FILE__.':('.__LINE__.')'."\n".'$error_array='.print_r($error_array, true))."\n";
echo(__FILE__.':('.__LINE__.')'."\n".'$vo='.print_r($vo, true))."\n";
