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

use QChecker\DAO\GuidelinesDAO;

/**
* @ignore
*/
define('AC_INCLUDE_PATH', '../include/');

include(AC_INCLUDE_PATH.'vitals.inc.php');
include_once(AC_INCLUDE_PATH.'classes/DAO/GuidelinesDAO.class.php');

global $_current_user;

$guidelinesDAO = new GuidelinesDAO();

if ((isset($_POST['delete']) || isset($_POST['view']) || isset($_POST['edit']) || isset($_POST['open_to_public']) || isset($_POST['close_from_public'])) && !isset($_POST['id'])) {
	$msg->addError('NO_ITEM_SELECTED');
} 
else if ($_POST['view']) {
	header('Location: view_guideline.php?id='.$_POST['id']);
	exit;
}
else if ($_POST['delete']) {
	header('Location: delete_guideline.php?id='.$_POST['id']);
	exit;
}
else if ($_POST['edit']) {
	header('Location: create_edit_guideline.php?id='.$_POST['id']);
	exit;
}
else if ($_POST['open_to_public']) {
	$guidelinesDAO->setOpenToPublicFlag($_POST['id'], 1);
}
else if ($_POST['close_from_public']) {
	$guidelinesDAO->setOpenToPublicFlag($_POST['id'], 0);
}

include(AC_INCLUDE_PATH.'header.inc.php');

if ($_current_user->isAdmin() || $_current_user->isEditor()) {
	$my_guidelines = $guidelinesDAO->getCustomizedGuidelines();
	$savant->assign('title', _AC('customized_guidelines'));
}
else {
	$my_guidelines = $guidelinesDAO->getGuidelineByUserIDs(array($_SESSION['user_id']));
    if (!is_array($my_guidelines))
        $my_guidelines=array();
    $customized_open_guidelines = array_udiff($guidelinesDAO->getOpenCustomizedGuidelines(), $my_guidelines,
        function($a,$b){
            return ($a['guideline_id']-$b['guideline_id']);
        }
    );
	$savant->assign('title', _AC('my_guidelines'));
}

// generate section of "my guidelines" 
if (!empty($my_guidelines)) {
	$savant->assign('rows', $my_guidelines);
	$savant->assign('buttons', array('view', 'edit', 'delete'));
	$savant->assign('showStatus', true);
	$savant->assign('formName', 'myGuideline');
	$savant->assign('isAdmin', $_current_user->isAdmin() || $_current_user->isEditor());
	$savant->display('guideline/index.tmpl.php');
}

// generate section of "open customized guidelines"
if (!empty($customized_open_guidelines)) {
    $savant->assign('title', _AC('customized_guidelines'));
    $savant->assign('rows', $customized_open_guidelines);
    $savant->assign('buttons', array('view', 'edit'));
    $savant->assign('showStatus', true);
    $savant->assign('formName', 'openGuideline');
    $savant->display('guideline/index.tmpl.php');
}

// generate section of "standard guidelines" 
if ($_current_user->isAdmin() || $_current_user->isEditor()) {
	// admin can set standard guidelines open to or close from public
	$savant->assign('buttons', array('view', 'edit', 'open_to_public', 'close_from_public'));
}
else
	$savant->assign('buttons', array('view'));

$savant->assign('title', _AC('standard_guidelines'));
$savant->assign('rows', $guidelinesDAO->getStandardGuidelines());
$savant->assign('showStatus', false);
$savant->assign('formName', 'standardGuideline');
$savant->assign('isAdmin', $_current_user->isAdmin() || $_current_user->isEditor());
$savant->display('guideline/index.tmpl.php');


// display footer
include(AC_INCLUDE_PATH.'footer.inc.php');

?>
