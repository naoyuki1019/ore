<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

require dirname(__FILE__).'/../ORE_Require.php';
// require  dirname(__FILE__) . '/../application/libraries/ORE_ExecutionTime.php';
ORE_ExecutionTime::ENABLE();

ORE_ExecutionTime::START('パターン1');
for ($i = 0; $i < 1000000; ++$i) {
	$str = "水樹奈々 ";
	$str = $str."スマイルギャング";
}
ORE_ExecutionTime::END('パターン1');

ORE_ExecutionTime::START('パターン2');
for ($i = 0; $i < 1000000; ++$i) {
	$str = "水樹奈々 ";
	$str .= "スマイルギャング";
}
ORE_ExecutionTime::END('パターン2');

ORE_ExecutionTime::START('パターン3');
for ($i = 0; $i < 1000000; ++$i) {
	$str = "水樹奈々 ";
	$str = "{$str}スマイルギャング";
}
ORE_ExecutionTime::END('パターン3');

ORE_ExecutionTime::START('パターン4');
for ($i = 0; $i < 1000000; ++$i) {
	$str = "水樹奈々 %s";
	$str = sprintf($str, "スマイルギャング");
}
ORE_ExecutionTime::END('パターン4');

ORE_ExecutionTime::START('パターン5');
for ($i = 0; $i < 1000000; ++$i) {
	$str = "水樹奈々 %s";
	$str = str_replace('%s', "スマイルギャング", $str);
}
ORE_ExecutionTime::END('パターン5');

ORE_ExecutionTime::sfDump('', '');


