<?php
/**
 *
 * @package Ore
 * @author naoyuki onishi
 */
namespace ore;

require  dirname(__FILE__) . '/../ORE_Require.php';

$vo = new \stdClass();
$vo->user_password = '  aa bb cc  ';
$data = (array)$vo;
$v = new ORE_Data_validation();
$v->initialize();
$v->set_data($data);
$v->set_rules("user_password", "パスワード", "mb_trim|min_length[6]|alpha_numeric|max_length[12]|is_natural");
$v->run();
$error_array = $v->error_array();
echo (__FILE__.':('.__LINE__.')'."\n".'$error_array='.print_r($error_array, true))."\n";
$data = $v->data();
echo (__FILE__.':('.__LINE__.')'."\n".'$data='.print_r($data, true))."\n";
