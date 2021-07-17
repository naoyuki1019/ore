<?php
require dirname(__FILE__).'/../application/libraries/ORE_FTPUpload.php';

$dir = dirname(__FILE__);
$ftp = new \ORE\ORE_FTPUpload();
$ftp->echo = true;
$ftp->flush = true;
$ftp->set_sh_dir($dir.'/tmp');
$ftp->add_file($dir.'/tmp/upload1.txt', '/test/dir1/uploaded1.txt');
$ftp->add_file($dir.'/tmp/upload2.txt', '/test/dir2/uploaded2.txt');
$ftp->host = 'ホスト名';
$ftp->uid = 'ユーザー名';
$ftp->pass = 'パスワード';
$ftp->execute();
$message = $ftp->message();
// $ftp->remove_sh();

