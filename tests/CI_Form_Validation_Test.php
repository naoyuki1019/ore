<?php

namespace ore\test;

use ore\CI_Form_validation;

require_once dirname(__FILE__).'/oreTestCase.php';

/**
 * Class myFormValidationTest
 *
 * @package ore
 */
class CI_Form_Validation_Test extends oreTestCase {

	/**
	 *
	 */
	public function testIsNatural() {
		$v = new CI_Form_validation();
		$this->assertTrue($v->is_natural(0));
		$this->assertTrue($v->is_natural(1));
		$this->assertFalse($v->is_natural(-1));
		$this->assertFalse($v->is_natural('1.1.1.1.1'));
	}
}
