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

use QChecker\Tests\SetupValidatorFunctions;

include_once('SetupValidatorFunctions.class.php');


/**
 * SetupHtmlFunctions.class.php
 * Generate HTML only for Testing AChecker Validator
 * @Author Joel Carvalho
 * @version 1.0 - 01/04/2015
 */
class SetupHtmlFunctions {

    /**
     * Print Html header for default pages
     * @Author Joel Carvalho
     * @version 1.0 - 03/04/2015
     * @access public
     */
    public static function getHeader(){
        echo '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AChecker Test Checkpoints</title>
</head>
<body>
';
    }


    /**
     * Print Html given the Type of Test (OK, NOK, SKIP) And the Check Id
     * @Author Joel Carvalho
     * @version 1.0 - 03/04/2015
     * @access public
     */
    public static function getHtml($type, $id) {
        if (is_numeric($id))
            echo SetupValidatorFunctions::getDBCheckHtml($type,$id);
        else
            echo SetupValidatorFunctions::getDBFunctionHtml($type,$id);
    }

    /**
     * Print Html footer for default pages
     * @Author Joel Carvalho
     * @version 1.0 - 03/04/2015
     * @access public
     */
    public static function getFooter() {
        echo '
</body>
</html>
';
    }
}

?>
