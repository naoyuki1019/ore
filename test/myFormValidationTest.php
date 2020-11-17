<?php

namespace ore;

require_once dirname(__FILE__).'/oreTestCase.php';

/**
 * Class myFormValidationTest
 *
 * @package ore
 */
class myFormValidationTest extends oreTestCase {

	/**
	 *
	 */
	public function testIsNatural() {
		$v = new ORE_Object_validation();
		$this->assertTrue($v->is_natural(0));
		$this->assertTrue($v->is_natural(1));
		$this->assertFalse($v->is_natural(-1));
		$this->assertFalse($v->is_natural(1.1));
	}

	/**
	 *
	 */
	public function testIsDateTime() {
		$v = new ORE_Object_validation();
		$this->assertTrue($v->is_datetime('2020/11/12 00:00:00', 1));
		$this->assertTrue($v->is_datetime('2020/11/32 00:00:00', 0));
		$this->assertFalse($v->is_datetime('2020/11/32 00:00:00', 1));
		$this->assertTrue($v->is_datetime('2020-11-12 00:00:00', 1));
		$this->assertFalse($v->is_datetime('2020-11-32 00:00:00', 1));
	}
}
