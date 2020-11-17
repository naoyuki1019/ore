<?php

namespace ore;

use PHPUnit\Framework\TestCase;

class oreTestCase extends TestCase {

	/**
	 * テスト実行前にテストメソッド毎にインスタンスが生成される
	 *
	 * @param null $name
	 * @param array $data
	 * @param string $dataName
	 */
	public function __construct($name = null, array $data = [], $dataName = '') {
		parent::__construct($name, $data, $dataName);
	}

	/**
	 * 一番最初のテストメソッド実行前に呼ばれる
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		// echo __METHOD__."\n";
	}

	/**
	 * 一番最後のテストメソッド実行後に呼ばれる
	 */
	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		// echo __METHOD__."\n";
	}

	/**
	 * 各テストメソッドの実行前に呼ばれる
	 */
	protected function setUp() {
		parent::setUp();
		// echo __METHOD__."\n";

		\SC_Debug::ENABLE();
		\SC_Debug::sfReset();
	}

	/**
	 * 各テストメソッドの実行後に呼ばれる
	 */
	protected function tearDown() {
		parent::tearDown();
		// echo __METHOD__."\n";

		\SC_Debug::sfVarDump();
	}
}
