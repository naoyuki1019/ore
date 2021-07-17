<?php

namespace ore\tests\application\libraries;

use ore\MY_Form_validation;
use ore\tests\oreTestCase;

require_once dirname(dirname(dirname(__FILE__))).'/oreTestCase.php';

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

	/**
	 *
	 */
	public function test_is_int() {
		$v = new MY_Form_validation();
		$this->assertTrue($v->is_int('00000'));
		$this->assertTrue($v->is_int('11111'));
		$this->assertTrue($v->is_int('-111111'));
		$this->assertFalse($v->is_int('1.1'));
		$this->assertFalse($v->is_int('-1,000'));
	}
}
