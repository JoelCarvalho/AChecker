<?php namespace QChecker\Tests;
/************************************************************************/
/* QChecker (former AChecker)											*/
/* AChecker - https://github.com/inclusive-design/AChecker				*/
/************************************************************************/
/* Inclusive Design Institute, Copyright (c) 2008 - 2015                */
/* RELEASE Group And PT Innovation, Copyright (c) 2015 - 2016			*/
/*                                                                      */
/* This program is free software. You can redistribute it and/or        */
/* modify it under the terms of the GNU General Public License          */
/* as published by the Free Software Foundation.                        */
/************************************************************************/

define('AC_INCLUDE_PATH','../include/');

use QChecker\Validator\BasicFunctions;
use QChecker\Validator\AccessibilityValidator;
use QChecker\Tests\SetupValidatorFunctions;

global $skipPHPUnit, $testUri;
$skipPHPUnit=true; // used in vitals.inc.php to skip session_start

include(AC_INCLUDE_PATH.'config.inc.php');
include(AC_INCLUDE_PATH.'vitals.inc.php');
include_once(AC_INCLUDE_PATH.'classes/Validator/BasicFunctions.class.php');
include_once(AC_INCLUDE_PATH.'classes/Validator/AccessibilityValidator.class.php');
include_once('SetupValidatorFunctions.class.php');

/**
 * BasicFunctionsTest.php
 * Only for Testing BasicFunctions from AChecker Validator
 * @Author Joel Carvalho
 * @version 1.0 - 01/04/2015
 */
class BasicFunctionsTest extends \PHPUnit_Framework_TestCase {

    /**
     * Execute Tests on the method hasDuplicateAttribute
     * @Author Joel Carvalho
     * @version 1.0 - 03/04/2015
     * @access public
     */
    public function testHasDuplicateAttribute() {
        global $global_e;
        $aValidator = SetupValidatorFunctions::getValidatorByCheckId('OK',185);
        $aValidator->validate();

        $this->assertEquals(BasicFunctions::hasDuplicateAttribute('id'),false);
        $this->assertEquals(BasicFunctions::hasDuplicateAttribute('name'),false);
    }
}

?>