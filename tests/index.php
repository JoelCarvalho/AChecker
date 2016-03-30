<?php
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

use QChecker\Tests\SetupHtmlFunctions;

include('SetupHtmlFunctions.class.php');

/**
 * Index for Tests
 * @Author Joel Carvalho
 * @version 1.0 - 03/04/2015
 */

/*
todo: uncomment when needed
SetupHtmlFunctions::getHeader();
if (isset($_GET['check']) && empty(isset($_GET['type'])))
    SetupHtmlFunctions::getHtml('OK',$_GET['check']);
else if (isset($_GET['type']) && isset($_GET['check']))
    SetupHtmlFunctions::getHtml($_GET['type'],$_GET['check']);
else
    SetupHtmlFunctions::getHtml('OK','185');

SetupHtmlFunctions::getFooter();
*/
if (isset($_GET['type']) && isset($_GET['check']))
    SetupHtmlFunctions::getHtml($_GET['type'],$_GET['check']);

?>
