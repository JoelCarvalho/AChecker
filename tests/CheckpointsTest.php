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
 * CheckpointsTest .php
 * Only for Testing Checkpoints from AChecker Validator
 * @Author Joel Carvalho
 * @version 1.0 - 01/04/2015
 */
class CheckpointsTest extends \PHPUnit_Framework_TestCase {

    /**
     * Preparation for Testing
     * @Author Joel Carvalho
     * @version 1.0 - 03/04/2015
     * @access public
     */
    public function setUp() {

    }

    /**
     * Execute Test on Checkpoint using a URL
     * @param string $type OK, NOK, or SKIP
     * @param int $id Check Id
     * @param mixed $expected
     * @dataProvider checkPointProvide
     * @Author Joel Carvalho
     * @version 1.0 - 26/04/2015
     * @access public
     */
    public function testCheckpointURL($type, $id, $expected) {
        $uri="http://www.ubi.pt";
        if ($id==5){
            $res=SetupValidatorFunctions::getValidationErrorByCheckIdAndURL($id, $uri, 11);
            $this->assertEquals($res, $expected, "Checkpoint ". $id." failed with ".$type. " at ".$uri);
        }
    }

    /**
     * Execute Test on Checkpoint
     * @param string $type OK, NOK, or SKIP
     * @param int $id Check Id
     * @param string $key key expected
     * @param mixed $expected
     * @dataProvider checkPointProvider
     * @Author Joel Carvalho
     * @version 1.0 - 03/04/2015
     * @access public
     */
    public function testCheckpoint($type, $id, $key, $expected) {
        if ($id>=7 && $id<=7){
            $res=SetupValidatorFunctions::getValidationErrorByCheckId($type, $id, 11);
            //$res=SetupValidatorFunctions::getValidationErrorByCheckIdAndURL($id, "http:www.ubi.pt");
            if (count($res)>0 && array_key_exists($key, $res[0]))
                $res=$res[0][$key];

            $this->assertEquals($res, $expected, "Checkpoint ". $id." failed with ".$type);
        }
    }

    /**
     * Execute Tests on Checkpoints given the Type of Test (OK, NOK, SKIP),
     * the Check Id and the Expected Value using a specified key or none
     * @param string $type OK, NOK, or SKIP
     * @param int $id Check Id
     * @param string $key key expected
     * @param mixed $expected
     * @dataProvider checkPointProvider
     * @Author Joel Carvalho
     * @version 1.0 - 03/04/2015
     * @access public
     */
    public function testAllCheckpoints($type, $id, $key, $expected) {
        $res=SetupValidatorFunctions::getValidationErrorByCheckId($type, $id, 2);
        if (count($res)>0 && array_key_exists($key, $res[0]))
            $res=$res[0][$key];
        $this->assertEquals($res, $expected, "Checkpoint ". $id." failed with ".$type);
    }

    /**
     * DataProvider for Checkpoint Tests.
     * See Database _check_examples for "expected_attribute" and "expected_value". If not defined use default values.
     * @Author Joel Carvalho
     * @version 1.0 - 03/04/2015
     * @access public
     */
    public function checkPointProvider() {
        list($checkIDsEx,$checkIDsNoEx) = SetupValidatorFunctions::getAllCheckIds();
        $provider=array();

        foreach($checkIDsEx as $check){
            $nok=SetupValidatorFunctions::getType('NOK');
            if (($check['type'])==$nok && $check['expected_attribute']==null)
                $provider[]=array('NOK', $check['check_id'], 'result', 'fail');
            else{
                if (($check['expected_attribute'])==null){
                    $check['expected_attribute']='none';
                    $check['expected_value']=array();
                }
                $provider[]=array(
                    SetupValidatorFunctions::getType($check['type']),
                    $check['check_id'],
                    $check['expected_attribute'],
                    $check['expected_value']);
            }
        }

        foreach($checkIDsNoEx as $noEx)
            $provider[]=array('NOK', $noEx['check_id'], 'none', 'NO EXAMPLES');

        return $provider;
    }

}