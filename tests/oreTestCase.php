<?php

namespace ore\tests;

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
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
	}

	/**
	 * 一番最後のテストメソッド実行後に呼ばれる
	 */
	public static function tearDownAfterClass(): void {
		parent::tearDownAfterClass();
	}

	/**
	 * 各テストメソッドの実行前に呼ばれる
	 */
	protected function setUp(): void {
		parent::setUp();

		\SC_Debug::ENABLE();
		\SC_Debug::sfReset();
	}

	/**
	 * 各テストメソッドの実行後に呼ばれる
	 */
	protected function tearDown(): void {
		parent::tearDown();

		\SC_Debug::sfVarDump();
	}
}
