<?php

namespace ore\tests\application\core;

use ore\ORE_Array;
use ore\tests\oreTestCase;

require_once dirname(dirname(dirname(__FILE__))).'/oreTestCase.php';

/**
 * Class ORE_Array_Test
 *
 * @package ore
 */
class ORE_Array_Test extends oreTestCase {

	/**
	 *
	 */
	public function test_test() {

		$before = [
			'hankaku' => ' 　 ﾊﾝｶｸｶﾀｶﾅABC123  　',
			'lowercase' => 'abcdefg',
			'recursive' => [
				'trim' => '  trim  ',
				'hoge1' => ' 　abcﾊﾝｶｸﾍﾝｶﾝ　　 ',
				'hoge2' => 'edfﾊﾝｶｸﾍﾝｶﾝ',
			],
		];

		$obj = new ORE_Array($before);
		$obj->func('mb_trim')->func('strtoupper')->func('mb_convert_kana', true, 'CKVas');

		$this->assertSame('ハンカクカタカナABC123', $obj->hankaku);
		$this->assertSame('ABCハンカクヘンカン', $obj->recursive['hoge1']);
	}
}


