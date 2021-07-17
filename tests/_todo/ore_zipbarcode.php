<?php

/**
 *
 * @package Ore
 * @author naoyuki onishi
 */

namespace ore;

require dirname(__FILE__).'/../ORE_Require.php';
require dirname(__FILE__).'/../application/libraries/ORE_ZipBarcode.php';

// https://www.post.japanpost.jp/zipcode/zipmanual/p25.html
$tests = [
	[
		'zip' => '2 6 3 0 0 2 3',
		'addr' => '千葉市稲毛区緑町3丁目30-8　郵便ビル403号',
		'ans' => 'STC 2 6 3 0 0 2 3 3 - 3 0 - 8 - 4 0 3 CC4 CC4 CC4 5 SPC',
	],
	[
		'zip' => '0 1 4 0 1 1 3 ',
		'addr' => '秋田県大仙市堀見内　南田茂木　添60-1',
		'ans' => 'STC 0 1 4 0 1 1 3 6 0 - 1 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC8 SPC',
	],
	[
		'zip' => '1 1 0 0 0 1 6 ',
		'addr' => '東京都台東区台東5-6-3　ABCビル10F',
		'ans' => 'STC 1 1 0 0 0 1 6 5 - 6 - 3 - 1 0 CC4 CC4 CC4 CC4 CC4 9 SPC',
	],
	[
		'zip' => '0 6 0 0 9 0 6',
		'addr' => '北海道札幌市東区北六条東4丁目　郵便センター6号館',
		'ans' => 'STC 0 6 0 0 9 0 6 4 - 6 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC4 9 SPC',
	],
	[
		'zip' => '0 6 5 0 0 0 6 ',
		'addr' => '北海道札幌市東区北六条東8丁目　郵便センター10号館',
		'ans' => 'STC 0 6 5 0 0 0 6 8 - 1 0 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC4 9 SPC',
	],
	[
		'zip' => '4 0 7 0 0 3 3 ',
		'addr' => '山梨県韮崎市龍岡町下條南割　韮崎400',
		'ans' => 'STC 4 0 7 0 0 3 3 4 0 0 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC4 - SPC',
	],
	[
		'zip' => '2 7 3 0 1 0 2',
		'addr' => '千葉県鎌ケ谷市右京塚　東3丁目-20-5　郵便・A&bコーポB604号',
		'ans' => 'STC 2 7 3 0 1 0 2 3 - 2 0 - 5 CC1 1 6 0 4 CC4 CC4 0 SPC',
	],
	[
		'zip' => '1 9 8 0 0 3 6',
		'addr' => '東京都青梅市河辺町十一丁目六番地一号　郵便タワー601',
		'ans' => 'STC 1 9 8 0 0 3 6 1 1 - 6 - 1 - 6 0 1 CC4 CC4 CC4 CC8 SPC',
	],
	[
		'zip' => '0 2 7 0 2 0 3',
		'addr' => '岩手県宮古市大字津軽石第二十一地割大淵川480',
		'ans' => 'STC 0 2 7 0 2 0 3 2 1 - 4 8 0 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC5 SPC',
	],
	[
		'zip' => '5 9 0 0 0 1 6',
		'addr' => '大阪府堺市堺区中田出井町四丁六番十九号',
		'ans' => 'STC 5 9 0 0 0 1 6 4 - 6 - 1 9 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC2 SPC',
	],
	[
		'zip' => '0 8 0 0 8 3 1',
		'addr' => '北海道帯広市稲田町南七線　西28',
		'ans' => 'STC 0 8 0 0 8 3 1 7 - 2 8 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC4 CC7 SPC',
	],
	[
		'zip' => '3 1 7 0 0 5 5',
		'addr' => '茨城県日立市宮田町6丁目7-14　ABCビル2F',
		'ans' => 'STC 3 1 7 0 0 5 5 6 - 7 - 1 4 - 2 CC4 CC4 CC4 CC4 CC4 CC1 SPC',
	],
	[
		'zip' => '6 5 0 0 0 4 6',
		'addr' => '神戸市中央区港島中町9丁目7-6　郵便シティA棟1F1号',
		'ans' => 'STC 6 5 0 0 0 4 6 9 - 7 - 6 CC1 0 1 - 1 CC4 CC4 CC4 5 SPC',
	],
	[
		'zip' => '6 2 3 0 0 1 1',
		'addr' => '京都府綾部市青野町綾部6-7　LプラザB106',
		'ans' => 'STC 6 2 3 0 0 1 1 6 - 7 CC2 1 CC1 1 1 0 6 CC4 CC4 CC4 4 SPC',
	],
	[
		'zip' => '0 6 4 0 8 0 4',
		'addr' => '札幌市中央区南四条西29丁目1524-23　第2郵便ハウス501',
		'ans' => 'STC 0 6 4 0 8 0 4 2 9 - 1 5 2 4 - 2 3 - 2 - 3 SPC',
	],
	[
		'zip' => '9 1 0 0 0 6 7',
		'addr' => '福井県福井市新田塚3丁目80-25　J1ビル2-B',
		'ans' => 'STC 9 1 0 0 0 6 7 3 - 8 0 - 2 5 CC1 9 1 - 2 CC1 9 SPC',
	],
	[
		'zip' => '1 0 0 0 0 1 3',
		'addr' => '東京都千代田区霞が関1丁目3番2号　郵便プラザ503室',
		'ans' => 'STC 1 0 0 0 0 1 3 1 - 3 - 2 - 503 CC4 CC4 CC4 CC4 9 SPC',
	],
];

foreach ($tests as $test) {
	$zipbar = new \ore\ORE_ZipBarcode($test['zip'], $test['addr'], false);
	$code_cc_comp = implode('', $zipbar->code_cc_comp());
	$ans = preg_replace('[\s]', '', $test['ans']);

	echo $test['addr']."\n";
	echo "結果：";
	if ($code_cc_comp === $ans) {
		echo "◯\n";
		echo $code_cc_comp."\n";
		echo implode('', $zipbar->code_font())."\n";
	}
	else {
		echo "☓\n";
		echo $ans."\n";
		echo $code_cc_comp."\n";
	}
	echo "\n---------------------------\n\n";
}

