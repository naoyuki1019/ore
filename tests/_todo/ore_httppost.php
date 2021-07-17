<?php


// TODO

require '/application/libraries/ORE_HttpPost.php';
$o = new ORE_HttpPost();
$o->add_header('Authorization: Basic '.base64_encode('ユーザー名:パスワード'));
$o->set_url('https://...../product');
$o->add_text('sort', '3');
$o->add_text('limit', '25');
$o->add_text('page_no', '1');
$res = $o->submit();

if (FALSE === $res) {
	$errors = $o->errors();
}
else {
	echo $res;
}
