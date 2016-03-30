<?php namespace QChecker\DAO;
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

if (!defined('AC_INCLUDE_PATH')) exit;

require_once(AC_INCLUDE_PATH. 'classes/DAO/DAO.class.php');

/**
 * DAO for "test_fail" table
 * @access    public
 * @author    Cindy Qi Li
 * @package    DAO
 */
class TestFailDAO extends DAO
{

    /**
     * Return check info of given check id
     * @access  public
     * @param   $checkID : check id
     * @return  table rows
     * @author  Cindy Qi Li
     */
    function getFailStepsByID($checkID) {
        $checkID = intval($checkID);
        $sql = "SELECT step_id, step
						FROM " . TABLE_PREFIX . "test_fail
						WHERE check_id=" . $checkID . "
						ORDER BY step_id";

        return $this->execute($sql);
    }

}

?>