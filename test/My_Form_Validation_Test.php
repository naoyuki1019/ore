<?php

namespace ore\test;

use ore\MY_Form_validation;

require_once dirname(__FILE__).'/oreTestCase.php';

/**
 * Class myFormValidationTest
 *
 * @package ore
 */
class My_Form_Validation_Test extends oreTestCase {

	/**
	 *
	 */
	public function testIsDateTime() {
		$v = new MY_Form_validation();
		$this->assertTrue($v->is_datetime('2020/11/12 00:00:00', 1));
		$this->assertTrue($v->is_datetime('2020/11/32 00:00:00', 0));
		$this->assertFalse($v->is_datetime('2020/11/32 00:00:00', 1));
		$this->assertTrue($v->is_datetime('2020-11-12 00:00:00', 1));
		$this->assertFalse($v->is_datetime('2020-11-32 00:00:00', 1));
	}
}
