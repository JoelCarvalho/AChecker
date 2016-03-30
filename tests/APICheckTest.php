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

global $skipPHPUnit;
$skipPHPUnit=true; // used in vitals.inc.php to skip session_start

include(AC_INCLUDE_PATH.'config.inc.php');
include(AC_INCLUDE_PATH.'vitals.inc.php');

/**
 * APICheckTest.php
 * Only for Testing API
 * @Author Joel Carvalho
 * @version 1.6 - 27/05/2015
 */
class APICheckTest extends \PHPUnit_Framework_TestCase {

    /**
     * Execute Tests on the method hasDuplicateAttribute
     * @Author Joel Carvalho
     * @version 1.6 - 27/05/2015
     * @access public
     */
    public function testDefaultAPI() {
        global $aValidator, $errors;
        $_REQUEST["id"]="825bf3e5033d3ed1da82580dc5b701796364953d";
        $_REQUEST['uri']="http://www.joelcarvalho.pt/index2.html";
        //$_REQUEST['uri']="http://fuxi.qchecker-dev.pt/fuxi/common/html/template-login.html";
        //$_REQUEST['uri']="http://fuxi.qchecker-dev.pt/fuxi/wireframes/html/template-listagem2.html";
        //$_REQUEST['uri']="http://fuxi.qchecker-dev.pt/fuxi/common/html/template-modal.html";
        //$_REQUEST['uri']="http://www.ubi.pt";
        $_REQUEST["output"]="html";
        //$_REQUEST["guide"]="FUXI";
        //$_REQUEST["guide"]="508";
        $_REQUEST["context"]="Dev.PhantomJS";
        $_REQUEST["check"]="FXR-1";
        //$_REQUEST["config"]="fuxi_default";

        include_once('../api/check.php');

        $ok=$aValidator->getNumSuccess();
        $nok=$aValidator->getNumErrors();
        $skipped=$aValidator->getNumSkipped();

        $this->assertNotEquals(array(),$ok);
        $this->assertEquals(array(),$nok);
    }
}

?>