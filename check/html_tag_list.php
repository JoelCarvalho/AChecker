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

use QChecker\DAO\ChecksDAO;

/**
* @ignore
*/
define('AC_INCLUDE_PATH', '../include/');

include_once(AC_INCLUDE_PATH.'vitals.inc.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/ChecksDAO.class.php');

$checksDAO = new ChecksDAO();

$all_html_tags = $checksDAO->getAllHtmlTags();

$savant->assign('all_html_tags', $checksDAO->getAllHtmlTags());

$savant->display('check/html_tag_list.tmpl.php');

?>