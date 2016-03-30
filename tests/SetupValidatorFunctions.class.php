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

use QChecker\DAO\CheckExamplesDAO;
use QChecker\DAO\ChecksDAO;
use QChecker\DAO\FunctionExamplesDAO;
use QChecker\Validator\AccessibilityValidator;

global $skipPHPUnit, $testUri;
$skipPHPUnit=true; // used in vitals.inc.php to skip session_start
$testUri=QCHECKER_SERVER.'/tests/index.php';

include_once(AC_INCLUDE_PATH.'config.inc.php');
include_once(AC_INCLUDE_PATH.'vitals.inc.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/CheckExamplesDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/FunctionExamplesDAO.class.php');
include_once(AC_INCLUDE_PATH.'classes/Validator/AccessibilityValidator.class.php');

/**
 * SetupValidatorFunctions.class.php
 * Only for Testing AChecker Validator
 * @Author Joel Carvalho
 * @version 1.0 - 03/04/2015
 */
class SetupValidatorFunctions {

    /**
     * Get AChecker Validator to Check using Type and Check ID
     * @param string $type OK, NOK, or SKIP
     * @param int $id Check Id
     * @return mixed
     * @Author Joel Carvalho
     * @version 1.0 - 03/04/2015
     * @access public
     */
    public static function getValidatorByCheckId($type, $id, $context=null){
        global $testUri;
        //AccessibilityValidator($validate_content, array(1,2,3,6,9,16), array(185), $uri);

        if ($type!='NOK' && $type!='OK' && $type!='SKIP')
            $type='OK';

        $vUri=$testUri.'?type='.$type.'&check='.$id;

        $aValidator = new AccessibilityValidator(null, array($id), $vUri, $context);

        return $aValidator;
    }

    /**
     * Get AChecker Validator to Check a URL
     * @param string $uri page to check
     * @param int $id Check Id
     * @return mixed
     * @Author Joel Carvalho
     * @version 1.0 - 26/04/2015
     * @access public
     */
    public static function getValidatorByCheckIdAndURL($id, $uri, $context=null){
        $aValidator = new AccessibilityValidator(null, array($id), $uri, $context);

        return $aValidator;
    }


    /**
     * Execute one Checkpoint Validation and only Get the ErrorRpt
     * @param string $type OK, NOK, or SKIP
     * @param int $id Check Id
     * @return mixed
     * @author Joel Carvalho
     * @version 1.0 - 03/04/2015
     * @access public
     */
    public static function getValidationErrorByCheckId($type, $id, $context=null){
        $aValidator=SetupValidatorFunctions::getValidatorByCheckId($type,$id,$context);
        $aValidator->validate();
        return $aValidator->getValidationErrorRpt();
    }

    /**
     * Execute one Checkpoint Validation over a page and only Get the ErrorRpt
     * @param string $url URL of the page to check
     * @param int $id Check Id
     * @return mixed
     * @author Joel Carvalho
     * @version 1.0 - 26/04/2015
     * @access public
     */
    public static function getValidationErrorByCheckIdAndURL($id, $url){
        $aValidator=SetupValidatorFunctions::getValidatorByCheckIdAndURL($id, $url);
        $aValidator->validate();
        return $aValidator->getValidationErrorRpt();
    }

    /**
     * Get Html from DB given a checkpoint
     * @param string $type OK, NOK, SKIP
     * @param int $id Check Id
     * @return string html
     * @author Joel Carvalho
     * @version 1.0 - 06/04/2015
     * @access public
     */
    public static function getDBCheckHtml($type, $id) {
        $check  = new CheckExamplesDAO();

        if ($type!='NOK' && $type!='OK' && $type!='SKIP')
            $type='OK';

        $example=$check->getByCheckIDAndType($id, SetupValidatorFunctions::getType($type));
        if (is_array($example) && array_key_exists('content', $example[0]))
            //return SetupValidatorFunctions::trimBody($example[0]['content']); todo: uncomment
            return $example[0]['content'];

        return "";
    }

    /**
     * Get Html from DB given a function
     * @param string $type OK, NOK, SKIP
     * @param string $functionName BasicFunctions method name
     * @return string html
     * @author Joel Carvalho
     * @version 1.0 - 06/04/2015
     * @access public
     */
    public static function getDBFunctionHtml($type, $functionName) {
        $check  = new FunctionExamplesDAO();

        $example=$check->getByFunctionNameAndType($functionName, SetupValidatorFunctions::getType($type));
        if (is_array($example) && array_key_exists('content', $example[0]))
            return SetupValidatorFunctions::trimBody($example[0]['content']);

        return "";
    }

    /**
     * Get array of checkpoint id's to test
     * @return mixed Array of Check Id's with examples in the first position and without examples in the second position
     * @author Joel Carvalho
     * @version 1.0 - 06/04/2015
     * @access public
     */
    public static function getAllCheckIds() {
        $checkExamples  = new CheckExamplesDAO();
        $checkIDsEx = $checkExamples->getAllCheckID();

        $checkIDs = new ChecksDAO();
        $checkIDs = $checkIDs->getAll();

        $checkIDsNoEx = array_udiff($checkIDs, $checkIDsEx,
            function($a,$b){
                return ($a['check_id']-$b['check_id']);
            }
        );

        return array($checkIDsEx,$checkIDsNoEx);
    }

    /**
     * Remove anything before <body> and after </body> from the given string
     * @param string $str html with ...<body>...</body>
     * @return string html
     * @author Joel Carvalho
     * @version 1.0 - 06/04/2015
     * @access private
     */
    private static function trimBody($str){
        $start=strpos($str,'<body>')+6;
        $end=strrpos($str,'</body>');
        return substr($str,$start,$end-$start);
    }

    /**
     * Convert string type 'OK', 'NOK' and 'SKIP' into equivalent int used in DB
     * @param string $type OK, NOK, SKIP
     * @return int
     * @author Joel Carvalho
     * @version 1.0 - 06/04/2015
     * @access public
     */
    public static function getType($type){
        if (is_numeric($type) && intval($type)==1) return 'OK';
        if (is_numeric($type) && intval($type)==2) return 'SKIP';
        if (is_numeric($type) && intval($type)==0) return 'NOK';
        if ($type=='SKIP') return 2;
        if ($type=='NOK') return 0;
        return 1;
    }
}